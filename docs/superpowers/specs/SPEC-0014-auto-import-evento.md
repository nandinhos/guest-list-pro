# SPEC-0014: Importação Automática de Evento a partir de Arquivo .md

**Status:** Em revisão — correções de code review aplicadas  
**Data:** 2026-04-24  
**Code Review:** 3 criticals + 4 importants corrigidos  
**Salvar em:** `docs/superpowers/specs/SPEC-0014-auto-import-evento.md`

---

## Objetivo

Eliminar a seleção manual de evento na tela de importação de convidados. O arquivo `.md` já contém todos os dados necessários — o sistema deve parsear o cabeçalho e criar automaticamente: Event, Sectors, Users (promoters) e EventAssignments.

---

## Formato do Arquivo (validado com `docs/lists/listageral.md`)

```
# Dados do Evento #
**Evento:** XXXPERIENCE 30 ANOS
**Data:** 25/04/2026
**Local:** Fazenda Santa Rita - Itu-SP
**Horário:** 14:00 - 06:00

# Dados das Listas de Convidados #
## Convidados Erick ##
### BACKSTAGE ###
Wellington Miranda, 27589191841
Priscilla Stocco, 22010456823
### PISTA ###
LUCAS SÓGLIA LAROTONDA, 41813773858
```

---

## O Que Já Existe (NÃO reescrever)

| Arquivo | O que já funciona |
|---------|-------------------|
| `app/Services/GuestImportService.php` | `parseFile()`, `import(int $eventId, int $adminUserId)`, `getPreviewSummary()`, `generateEmail()`, relatório de duplicatas com localização |
| `app/Filament/Admin/Pages/ImportGuestsPage.php` | Upload com `afterStateUpdated` + `->live()`, preview, botão de confirmação |
| `resources/views/filament/admin/pages/import-guests.blade.php` | Preview completo com tabela de convidados, resumo por promotor/setor, relatório de duplicatas com tabela de auditoria, seção de ajuda com novo estilo |
| `app/Models/Event.php` | `firstOrCreate`-friendly; `fillable`: name, location, date, start_time, end_time, status |
| `app/Models/Sector.php` | `fillable`: event_id, name, capacity |
| `app/Models/EventAssignment.php` | `fillable`: user_id, role, event_id, sector_id, guest_limit, plus_one_enabled |

---

## Code Review Fixes Aplicadas

| # | Severidade | Problema | Correção |
|---|------------|----------|----------|
| 1 | Critical | Transaction scope errada — Event/Sector fora do DB::beginTransaction() | Event/Sector creation movidos para dentro da transaction |
| 2 | Critical | `promoters_created` não existia no `$importResult` | Já estava na spec; implementação deve usar o initialization correto |
| 3 | Critical | Colisão de email — nomes similares normalizam para mesmo email causing unique constraint violation | `importGuest()` agora verifica email case-insensitive antes de criar User |
| 4 | Important | `import()` signature change não mostrava call site atualizado | `ImportGuestsPage.php import()` mostrado explicitamente |
| 5 | Important | Event `firstOrCreate` usava só `name + date` — risco de sobrescrita | Adicionado `location` ao uniqueness check |
| 6 | Important | Sem validação se header do evento está ausente | Validação no início de `import()` retorna erro descritivo |
| 7 | Minor | promoterCache construído do DB antes do loop | `importGuest()` modifica `$promoterCache` por referência; EventAssignment usa cache atualizado |

---

## Mudanças Necessárias

### 1. `app/Services/GuestImportService.php`

**Adicionar propriedade:**
```php
public array $parsedEvent = [];
```

**Novo método `parseEventData(string $content): void`:**
```php
protected function parseEventData(string $content): void
{
    $this->parsedEvent = [];

    if (preg_match('/\*\*Evento:\*\*\s*(.+)/u', $content, $m)) {
        $this->parsedEvent['name'] = trim($m[1]);
    }
    if (preg_match('/\*\*Data:\*\*\s*(\d{2}\/\d{2}\/\d{4})/', $content, $m)) {
        $this->parsedEvent['date'] = \Carbon\Carbon::createFromFormat('d/m/Y', trim($m[1]))->format('Y-m-d');
    }
    if (preg_match('/\*\*Local:\*\*\s*(.+)/u', $content, $m)) {
        $this->parsedEvent['location'] = trim($m[1]);
    }
    if (preg_match('/\*\*Horário:\*\*\s*(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $content, $m)) {
        $this->parsedEvent['start_time'] = trim($m[1]);
        $this->parsedEvent['end_time']   = trim($m[2]);
    }
}
```

**Atualizar `parseFile()`** — adicionar `$this->parseEventData($content);` no início.

**FIX CRITICAL 1+2+3+5+6: Reescrever `import()` completo** — transaction scope corrigido, validação de header, email collision tratado, location no uniqueness check:

```php
public function import(int $adminUserId): array
{
    $this->importResult = [
        'imported' => 0, 'duplicates' => 0,
        'errors' => [], 'warnings' => [], 'promoters_created' => 0,
    ];

    // FIX CRITICAL 6: Validar header do evento antes de começar
    if (empty($this->parsedEvent['name']) || empty($this->parsedEvent['date'])) {
        $this->importResult['errors'][] = 'Arquivo não contém cabeçalho de evento válido (Evento + Data são obrigatórios)';
        return $this->importResult;
    }

    // FIX CRITICAL 5: Usar name + date + location no firstOrCreate para evitar sobrescrita de eventos diferentes
    $event = Event::firstOrCreate(
        [
            'name'     => $this->parsedEvent['name'],
            'date'     => $this->parsedEvent['date'],
            'location' => $this->parsedEvent['location'] ?? null,
        ],
        [
            'location'   => $this->parsedEvent['location'] ?? null,
            'start_time' => $this->parsedEvent['start_time'] ?? null,
            'end_time'   => $this->parsedEvent['end_time'] ?? null,
            'status'     => \App\Enums\EventStatus::ACTIVE,
        ]
    );

    // FIX CRITICAL 1: Tudo dentro da transaction — rollback completo em qualquer falha
    DB::beginTransaction();
    try {
        // 1. Criar setores detectados no arquivo
        $sectorsNeeded = collect($this->parsedData)->pluck('sector_name')->unique();
        $sectors = [];
        foreach ($sectorsNeeded as $sectorName) {
            $sector = Sector::firstOrCreate(
                ['event_id' => $event->id, 'name' => $sectorName]
            );
            $sectors[$sectorName] = $sector;
        }

        // 2. Importar guests (lógica existente, sem alteração)
        $promoterCache = $this->getPromoterCache($event->id);

        foreach ($this->parsedData as $item) {
            $result = $this->importGuest($event, $sectors, $promoterCache, $item, $adminUserId);
            if ($result === 'imported') {
                $this->importResult['imported']++;
            } elseif ($result === 'duplicate') {
                $this->importResult['duplicates']++;
            } else {
                $this->importResult['errors'][] = $result;
            }
        }

        // 3. Criar EventAssignment por (promoter × sector) único
        // Nota: promoterCache é atualizado por referência dentro de importGuest()
        $assignments = collect($this->parsedData)
            ->map(fn ($i) => [$i['promoter_name'], $i['sector_name']])
            ->unique(fn ($pair) => implode('|', $pair));

        foreach ($assignments as [$promoterName, $sectorName]) {
            $promoterId = $promoterCache[$promoterName] ?? null;
            $sector     = $sectors[$sectorName] ?? null;
            if ($promoterId && $sector) {
                \App\Models\EventAssignment::firstOrCreate(
                    [
                        'user_id'   => $promoterId,
                        'event_id'  => $event->id,
                        'sector_id' => $sector->id,
                    ],
                    ['role' => 'promoter']
                );
            }
        }

        DB::commit();
    } catch (\Exception $e) {
        DB::rollBack();
        $this->importResult['errors'][] = 'Erro_transaction: ' . $e->getMessage();
    }

    return $this->importResult;
}
```

**Nova dependência:** O método `importGuest()` precisa ser atualizado para tratar colisão de email (critical 3). Dentro de `importGuest()`, quando criar promoter via `User::create()`, substituir por:

```php
// FIX CRITICAL 3: Tratar colisão de email case-insensitive
// Se email já existe (por nome similar que normaliza para o mesmo), buscar ao invés de criar
$email = $this->generateEmail($promoterName);
$existingByEmail = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();
if ($existingByEmail) {
    $user = $existingByEmail;
} else {
    $user = User::create([
        'name'     => $promoterName,
        'email'    => $email,
        'password' => bcrypt('password'),
        'is_active' => true,
    ]);
    $this->importResult['promoters_created']++;
}
```

**Adicionar import no topo:** `use App\Models\EventAssignment;`

---

### 2. `app/Filament/Admin/Pages/ImportGuestsPage.php`

- **Remover** a `Section::make('Configuração')` com o `Select::make('event_id')` e todo o seu conteúdo
- **Adicionar** propriedade pública: `public array $parsedEvent = [];`
- **Atualizar `parsePreview()`**: após `$this->previewSummary = ...`, adicionar `$this->parsedEvent = $service->parsedEvent;`
- **Atualizar `import()` method** — o método atual é aproximadamente:
  ```php
  public function import(): void
  {
      $eventId = $this->data['event_id'] ?? null;
      if (!$eventId) { /* erro */ return; }
      $service = new GuestImportService($this->fileContent);
      $service->parseFile();
      $this->importResult = $service->import($eventId, auth()->id());
      // ...
  }
  ```
  **Substituir por:**
  ```php
  public function import(): void
  {
      $service = new GuestImportService($this->fileContent);
      $service->parseFile();
      // FIX IMPORTANT 4: import() agora só recebe adminUserId — event é criado internamente
      $this->importResult = $service->import(auth()->id());
      $this->showResult = true;
  }
  ```
- **Atualizar `resetForm()`**: adicionar `parsedEvent` no array de reset:
  ```php
  $this->parsedEvent = [];
  ```

---

### 3. `resources/views/filament/admin/pages/import-guests.blade.php`

**No bloco `@if($showPreview && !empty($preview))`**, adicionar antes dos Summary Cards:

```html
{{-- Evento Detectado --}}
<div class="mb-5 grid grid-cols-2 gap-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 p-4">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Evento</p>
        <p class="font-semibold text-gray-900 dark:text-white">{{ $parsedEvent['name'] ?? '—' }}</p>
    </div>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Data</p>
        <p class="font-semibold text-gray-900 dark:text-white">
            {{ isset($parsedEvent['date']) ? \Carbon\Carbon::parse($parsedEvent['date'])->format('d/m/Y') : '—' }}
        </p>
    </div>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Local</p>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $parsedEvent['location'] ?? '—' }}</p>
    </div>
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-0.5">Horário</p>
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ ($parsedEvent['start_time'] ?? '—') }} — {{ ($parsedEvent['end_time'] ?? '—') }}
        </p>
    </div>
</div>
```

---

## O Que É Criado Automaticamente

| Entidade | Estratégia | Duplicata |
|----------|-----------|-----------|
| `Event` | `firstOrCreate(name + date + location)` | Reutiliza existente se name+date+location coincidirem |
| `Sector` | `firstOrCreate(event_id + name)` | Reutiliza existente |
| `User` (promoter) | `firstOrCreate` por nome; email `nome@guestlist.pro`; senha `password` | Colisão de email case-insensitive → busca existente (Critical 3 fix) |
| `EventAssignment` | `firstOrCreate(user_id + event_id + sector_id)` | Não duplica |
| `Guest` | `create` — duplicata por CPF gera warning com localização do existente | Não importa |

---

## Regras de Negócio

- Evento criado com `status = active`
- Event: uniqueness por `name + date + location` — evita sobrescrever eventos diferentes com mesmo nome
- Promoter criado com `is_active = true`, senha `password` (provisória)
- Promoter email: se nome similar gerar email duplicado (case-insensitive), reutiliza promoter existente
- Setores: somente os que aparecem no arquivo (PISTA, BACKSTAGE, ou outros)
- EventAssignment: uma linha por combinação única (promoter × setor)
- Se evento/setor/promoter já existem: reutilizados sem erro
- **Validação**: se arquivo não tiver `**Evento:**` e `**Data:**`, retorna erro antes de criar qualquer coisa

---

## Verificação

1. `vendor/bin/sail artisan migrate:fresh --seed`
2. Acessar `/admin/import-guests`
3. Upload de `docs/lists/listageral.md`
4. Preview deve mostrar card com: XXXPERIENCE 30 ANOS, 25/04/2026, Fazenda Santa Rita, 14:00—06:00
5. Confirmar importação
6. Verificar no banco:
   - `events`: 1 registro — XXXPERIENCE 30 ANOS
   - `sectors`: PISTA e BACKSTAGE ligados ao event
   - `users`: erick@guestlist.pro, miler@guestlist.pro, angelica@guestlist.pro, etc. (role = promoter)
   - `event_assignments`: uma linha por (promoter × setor) detectado
   - `guests`: todos os convidados do arquivo
7. **Testar colisão de email**: o arquivo tem `Maria da Conceição maia salheb` (linha 29) e `María da Conceição Maia Salheb` (linha 107) — ambos normalizam para `maria.da.conceicao.maia.salheb@guestlist.pro`. A importação deve criar apenas 1 promoter (não dois), sem erro de unique constraint.
8. **Testar validação**: upload de arquivo sem header de evento → deve retornar erro `Arquivo não contém cabeçalho de evento válido`
9. `vendor/bin/sail artisan test --compact` — 73 testes passando

---

## HANDOFF para Minimax

### Contexto do Projeto

- **Stack:** Laravel 12 + Filament 4 + Livewire 3 + MySQL + Sail
- **Credencial admin:** `nando@guestlist.pro` / `password`
- **Banco:** limpo (só 1 usuário admin)
- **CI:** GitHub Actions verde (73 testes)

### O Que Já Foi Feito Nesta Sessão

1. ✅ Tela de importação `/admin/import-guests` funcionando com upload, preview e importação de convidados
2. ✅ `GuestImportService` com parsing de markdown, detecção de duplicatas com auditoria, geração de email `@guestlist.pro`
3. ✅ Bug corrigido: `afterStateHydrated` → `afterStateUpdated` + `->live()` no FileUpload
4. ✅ Bug corrigido: `getSectorsByEvent` usava `->toArray()` quebrando `$sector->id`
5. ✅ Relatório de duplicatas mostra: quem tentou importar + onde o convidado já está (promotor + setor)
6. ✅ Banco limpo com migrate:fresh — só `nando@guestlist.pro` como admin
7. ✅ Migration `seed_production_users` atualizada para refletir apenas o admin real

### Próxima Tarefa (implementar)

**SPEC-0014** — auto-criar Event, Sectors, Promoters e EventAssignments a partir do header do `.md`.  
**Todos os detalhes técnicos estão neste documento acima.**

### Comandos Essenciais

```bash
# SEMPRE via Sail
vendor/bin/sail artisan test --compact
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail bin pint --dirty
```

### Arquivos Críticos

```
app/Services/GuestImportService.php          ← principal — modificar aqui
app/Filament/Admin/Pages/ImportGuestsPage.php ← remover Select de evento
resources/views/filament/admin/pages/import-guests.blade.php ← adicionar card evento
app/Models/Event.php                          ← fillable já correto
app/Models/Sector.php                         ← fillable já correto
app/Models/EventAssignment.php                ← fillable já correto
docs/lists/listageral.md                      ← arquivo de teste real
```
