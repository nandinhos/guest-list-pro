# LL-024: E2E Tests e Seeder — Lições da Sessão QR Code Removal

**Data:** 2026-04-20
**Projeto:** guest-list-pro
**Session:** Refatoração QR Code → guest_token + correções E2E

---

## Problema Original

Testes E2E mostravam 3 pendências não críticas:
1. TC-VALIDATOR-003: Guest count 0
2. TC-PROMOTER-001: Quota 0/0
3. TC-TICKETPRICING-001: Link não encontrado

---

## Causa Raiz — Descobertas

### 1. TC-VALIDATOR-003: Guest count 0

**Sintoma:** Validator fazia login mas não via nenhum convidado na lista.

**Investigação:**
- Teste navegava para `/validator/guests`
- Middleware `EnsureEventSelected` redirecionava para página "Selecionar Evento"
- Overlay mostrava ZERO eventos para selecionar

**Causa Raiz:** Validator sem `EventAssignment` para nenhum evento.

O método `User::getAssignedEvents()` consulta a tabela `event_assignments`:
```php
public function getAssignedEvents(): Collection
{
    return Event::query()
        ->whereHas('assignments', fn ($query) => $query
            ->where('user_id', $this->id)
            ->where('role', $this->role->value)
        )
        ->where('status', EventStatus::ACTIVE)
        ->get();
}
```

Sem `EventAssignment`, o validator não vê nenhum evento no `EventSelectorGrid`.

**Solução:** Adicionar `EventAssignment` no E2ETestSeeder:
```php
EventAssignment::updateOrCreate(
    ['user_id' => $validator->id, 'event_id' => $festivalEvent->id],
    ['role' => UserRole::VALIDATOR, 'guest_limit' => 0]
);
```

---

### 2. Fluxo de Seleção de Evento no Validator

**Sintoma:** Mesmo após corrigir EventAssignment, guests ainda mostravam 0.

**Causa:** Após clicar no evento, o `EventSelectorGrid::selectEvent()` redireciona para dashboard (`/validator`). O teste depois navegava para `/validator/guests` mas sem guardar o evento selecionado corretamente.

**Solução:** Após selecionar evento, navegar novamente para `/validator/guests` para carregar a lista:
```typescript
if (count > 0) {
  await eventButtons.first().click();
  await waitForLivewireResponse(this.page);
  await this.page.waitForTimeout(2000);
  await this.page.goto('/validator/guests');  // Refresh para carregar lista
}
```

---

### 3. TC-PROMOTER-001: Quota 0/0

**Sintoma:** Widget de quota mostrava "0/0" em vez de números.

**Causa:** Widget exibe formato "X restantes" (single number) mas regex esperava "used/total":
```typescript
// ERRADO - só capturava "12/50"
const match = quotaText?.match(/(\d+)\s*\/\s*(\d+)/);

// CORRETO - captura ambos formatos
const match = quotaText?.match(/(\d+)\s*(?:\/\s*(\d+)|restantes)/);
```

---

### 4. TC-TICKETPRICING-001: Link não encontrado

**Sintoma:** Menu admin não mostrava link para Ticket Types.

**Causa:** `TicketTypeResource` não tinha associação com `TicketTypePolicy`.

**Solução:** Adicionar `protected static $policy = TicketTypePolicy::class;` no resource.

---

## Outras Correções da Sessão

### E2E Seeder - Guests não eram criados

**Bug:** `E2ETestSeeder` não criava nenhum guest. Apenas usuários, evento e setores.

**Fix:** Adicionar loop de criação de guests após setores:
```php
$guests = [
    ['name' => 'Ana Silva', 'document' => '12345678901'],
    // ... 10 guests
];
foreach ($guests as $index => $guestData) {
    $sector = $sectors[$index % count($sectors)];
    $existingGuest = Guest::where('event_id', $event->id)
        ->where('document', $guestData['document'])
        ->first();
    if (! $existingGuest) {
        Guest::create([...]);
    }
}
```

### Credential Mismatch

**Bug:** Tests usavam `validador@guestlist.pro` mas E2ETestSeeder criava `validator@validator.com`.

**Fix:** Corrigir email para `validador@guestlist.pro` (matching UserSeeder).

### Campo 'status' inexistente no Guest

**Bug:** Seeder tentava inserir `status => 'confirmed'` mas tabela `guests` não tem coluna `status`.

**Fix:** Remover campo do Guest::create().

---

## Middleware EnsureEventSelected

Painéis que requerem evento selecionado: `['promoter', 'validator', 'bilheteria']`

Se `session('selected_event_id')` não existe → redirect para `filament.{panel}.pages.select-event`

Rotas ignoradas: autenticação e select-event.

---

## Checklist para Fix de Tests E2E

- [ ] Verificar se users existem no seeder com credentials corretas
- [ ] Verificar se eventos têm `EventAssignment` para o user
- [ ] Verificar se guests existem para o evento selecionado
- [ ] Verificar se URL do resource está correta (não `/admin/ticket-types` mas `/admin/ticket-type/ticket-types`)
- [ ] Verificar se regex de parsing captura formato correto do widget

---

## Tags

`e2e`, `testing`, `seeder`, `eventassignment`, `filament`, `auth`, `permissions`

---

*Criado em: 2026-04-20*
*Commit: 94e1b3c*