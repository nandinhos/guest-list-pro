# Plano de Implementação: SPEC-0007 — Painel Excursionista

> **Para agentes:** SKILL OBRIGATÓRIA: Use `subagent-driven-development` (recomendado) ou `executing-plans` para implementar task-by-task. Passos usam sintaxe checkbox (`- [ ]`).

**Meta:** Criar painel completo para excursionistas cadastrarem excursões, veículos e monitores vinculados a eventos.

**Branch:** `feat/SPEC-0007-excursionista`

**Referência:** `.devorq/state/specs/SPEC-0007-painel-excursionista.md`

**Stack:** Laravel 12, Filament 4, Livewire 3, TDD

---

## Estrutura de Arquivos

```
app/Enums/UserRole.php                          MODIFICAR
app/Enums/TipoVeiculo.php                       CRIAR
app/Models/Excursao.php                          CRIAR
app/Models/Veiculo.php                           CRIAR
app/Models/Monitor.php                           CRIAR
app/Models/User.php                              MODIFICAR (canAccessPanel + getAssignedEvents)
app/Http/Middleware/EnsureEventSelected.php      MODIFICAR (add excursionista)
app/Providers/Filament/ExcursionistaPanelProvider.php  CRIAR
app/Filament/Excursionista/...                   CRIAR (estrutura completa)
bootstrap/providers.php                          MODIFICAR
database/migrations/...                          CRIAR (3 migrations)
database/seeders/ExcursionistaSeeder.php         CRIAR
resources/css/filament/excursionista/theme.css   CRIAR
```

---

## Task 1: Branch e Scaffolding Inicial

- [ ] **Passo 1: Criar branch**

```bash
git checkout -b feat/SPEC-0007-excursionista
```

- [ ] **Passo 2: Verificar branch atual**

```bash
git branch --show-current
```

---

## Task 2: Enum TipoVeiculo + Atualizar UserRole

**Arquivos:**
- Criar: `app/Enums/TipoVeiculo.php`
- Modificar: `app/Enums/UserRole.php`

- [ ] **Passo 1: Criar enum TipoVeiculo**

```php
<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TipoVeiculo: string implements HasLabel, HasColor, HasIcon
{
    case Onibus = 'onibus';
    case Van = 'van';

    public function getLabel(): string
    {
        return match($this) {
            self::Onibus => 'Ônibus',
            self::Van => 'Van',
        };
    }

    public function getColor(): string|array|null
    {
        return match($this) {
            self::Onibus => 'info',
            self::Van => 'warning',
        };
    }

    public function getIcon(): ?string
    {
        return match($this) {
            self::Onibus => 'heroicon-o-truck',
            self::Van => 'heroicon-o-truck',
        };
    }
}
```

- [ ] **Passo 2: Adicionar EXCURSIONISTA ao UserRole**

No arquivo `app/Enums/UserRole.php`, adicionar o case:
```php
case Excursionista = 'excursionista';
```
E atualizar os métodos `getLabel()`, `getColor()`, `getIcon()` com os valores para Excursionista.

- [ ] **Passo 3: Verificar sintaxe**

```bash
vendor/bin/sail php -l app/Enums/TipoVeiculo.php && vendor/bin/sail php -l app/Enums/UserRole.php
```

---

## Task 3: Migrations

**Arquivos:**
- Criar: migrations para excursoes, veiculos, monitores

- [ ] **Passo 1: Gerar migrations**

```bash
vendor/bin/sail artisan make:migration create_excursoes_table --no-interaction
vendor/bin/sail artisan make:migration create_veiculos_table --no-interaction
vendor/bin/sail artisan make:migration create_monitores_table --no-interaction
```

- [ ] **Passo 2: Implementar migration excursoes**

```php
Schema::create('excursoes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
    $table->string('nome', 150);
    $table->timestamps();

    $table->index(['event_id']);
});
```

- [ ] **Passo 3: Implementar migration veiculos**

```php
Schema::create('veiculos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('excursao_id')->constrained('excursoes')->cascadeOnDelete();
    $table->string('tipo', 20); // onibus|van
    $table->string('placa', 10)->nullable();
    $table->timestamps();

    $table->index(['excursao_id']);
});
```

- [ ] **Passo 4: Implementar migration monitores**

```php
Schema::create('monitores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->foreignId('criado_por')->constrained('users')->cascadeOnDelete();
    $table->string('nome', 150);
    $table->string('cpf', 14);
    $table->timestamps();

    $table->index(['event_id']);
    $table->index(['veiculo_id']);
});
```

- [ ] **Passo 5: Executar migrations**

```bash
vendor/bin/sail artisan migrate --no-interaction
```

- [ ] **Passo 6: Verificar tabelas criadas**

```bash
vendor/bin/sail artisan migrate:status
```

---

## Task 4: Models Eloquent

**Arquivos:**
- Criar: `app/Models/Excursao.php`, `app/Models/Veiculo.php`, `app/Models/Monitor.php`
- Modificar: `app/Models/User.php`, `app/Models/Event.php`

- [ ] **Passo 1: Criar model Excursao**

```bash
vendor/bin/sail artisan make:model Excursao --factory --no-interaction
```

Implementar relações: `BelongsTo` event, `BelongsTo` criador (User), `HasMany` veiculos.

- [ ] **Passo 2: Criar model Veiculo**

```bash
vendor/bin/sail artisan make:model Veiculo --factory --no-interaction
```

Implementar relações: `BelongsTo` excursao, `HasOne` monitor. Cast `tipo` para `TipoVeiculo`.

- [ ] **Passo 3: Criar model Monitor**

```bash
vendor/bin/sail artisan make:model Monitor --factory --no-interaction
```

Implementar relações: `BelongsTo` veiculo, `BelongsTo` event, `BelongsTo` criador (User).

- [ ] **Passo 4: Adicionar relações ao User**

```php
public function excursoes(): HasMany
{
    return $this->hasMany(Excursao::class, 'criado_por');
}

public function monitores(): HasMany
{
    return $this->hasMany(Monitor::class, 'criado_por');
}
```

- [ ] **Passo 5: Adicionar relações ao Event**

```php
public function excursoes(): HasMany
{
    return $this->hasMany(Excursao::class);
}

public function monitores(): HasMany
{
    return $this->hasMany(Monitor::class);
}
```

- [ ] **Passo 6: Atualizar `User::canAccessPanel()` para role excursionista**

Adicionar case para `id === 'excursionista'` com verificação de `UserRole::Excursionista`.

- [ ] **Passo 7: Verificar sintaxe**

```bash
vendor/bin/sail php -l app/Models/Excursao.php
vendor/bin/sail php -l app/Models/Veiculo.php
vendor/bin/sail php -l app/Models/Monitor.php
```

---

## Task 5: ExcursionistaPanelProvider

**Arquivos:**
- Criar: `app/Providers/Filament/ExcursionistaPanelProvider.php`
- Modificar: `bootstrap/providers.php`
- Criar: `resources/css/filament/excursionista/theme.css`

- [ ] **Passo 1: Criar provider via Artisan**

```bash
vendor/bin/sail artisan make:filament-panel excursionista --no-interaction
```

- [ ] **Passo 2: Configurar o provider** (baseado em PromoterPanelProvider)

```php
return $panel
    ->id('excursionista')
    ->path('excursionista')
    ->brandName('Portal do Excursionista')
    ->colors(['primary' => Color::Teal])
    ->font('Inter')
    ->defaultThemeMode(ThemeMode::Dark)
    ->viteTheme('resources/css/filament/excursionista/theme.css')
    ->discoverResources(in: app_path('Filament/Excursionista/Resources'), for: 'App\Filament\Excursionista\Resources')
    ->discoverPages(in: app_path('Filament/Excursionista/Pages'), for: 'App\Filament\Excursionista\Pages')
    ->pages([SelectEvent::class, Dashboard::class])
    ->discoverWidgets(in: app_path('Filament/Excursionista/Widgets'), for: 'App\Filament\Excursionista\Widgets')
    ->authMiddleware([Authenticate::class, EnsureEventSelected::class])
    ->login();
```

- [ ] **Passo 3: Criar arquivo de tema CSS**

Copiar estrutura de `resources/css/filament/promoter/theme.css` e ajustar imports.

- [ ] **Passo 4: Registrar em bootstrap/providers.php**

Adicionar `App\Providers\Filament\ExcursionistaPanelProvider::class` ao array.

- [ ] **Passo 5: Adicionar 'excursionista' ao EnsureEventSelected**

Em `app/Http/Middleware/EnsureEventSelected.php`, adicionar `'excursionista'` ao array de painéis que requerem evento.

- [ ] **Passo 6: Verificar rota disponível**

```bash
vendor/bin/sail artisan route:list | grep excursionista
```

---

## Task 6: Pages do Painel — SelectEvent e Dashboard

**Arquivos:**
- Criar: `app/Filament/Excursionista/Pages/SelectEvent.php`
- Criar: `app/Filament/Excursionista/Pages/Dashboard.php`

- [ ] **Passo 1: Criar SelectEvent** (copiar padrão de Promoter/Pages/SelectEvent.php)

```php
<?php

namespace App\Filament\Excursionista\Pages;

use App\Filament\Pages\SelectEventBase;

class SelectEvent extends SelectEventBase
{
    protected static string $panelId = 'excursionista';
}
```

- [ ] **Passo 2: Criar Dashboard com widget de estatísticas**

```bash
vendor/bin/sail artisan make:filament-page Dashboard --panel=excursionista --no-interaction
```

---

## Task 7: Resource — ExcursaoResource

**Arquivos:**
- Criar: `app/Filament/Excursionista/Resources/ExcursaoResource/`

- [ ] **Passo 1: Criar resource via Artisan**

```bash
vendor/bin/sail artisan make:filament-resource Excursao --panel=excursionista --no-interaction
```

- [ ] **Passo 2: Implementar formulário** (nome + veículos como Repeater)

Campos do form:
- `nome` (TextInput, required)
- `veiculos` (Repeater com: tipo Select (TipoVeiculo), placa TextInput nullable)

- [ ] **Passo 3: Implementar tabela** (colunas: nome, qtd veículos, evento, created_at)

- [ ] **Passo 4: Filtrar por evento selecionado na sessão**

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if ($eventId = session('selected_event_id')) {
        $query->where('event_id', $eventId);
    }

    return $query->where('criado_por', auth()->id());
}
```

- [ ] **Passo 5: Auto-preencher event_id e criado_por no create**

Usar `mutateFormDataBeforeCreate()` para injetar `event_id` e `criado_por`.

---

## Task 8: Resource — MonitorResource

**Arquivos:**
- Criar: `app/Filament/Excursionista/Resources/MonitorResource/`

- [ ] **Passo 1: Criar resource via Artisan**

```bash
vendor/bin/sail artisan make:filament-resource Monitor --panel=excursionista --no-interaction
```

- [ ] **Passo 2: Implementar formulário com Select reativo**

Campos:
- `nome` (TextInput, required)
- `cpf` (TextInput, required, máscara 000.000.000-00)
- `excursao_id` (Select com `createOptionForm` inline para criar nova excursão)
- `veiculo_id` (Select reativo — filtra pelo `excursao_id` selecionado, usando `Get $get`)

Exemplo do Select reativo:
```php
Select::make('veiculo_id')
    ->label('Veículo')
    ->options(fn (Get $get): array =>
        Veiculo::where('excursao_id', $get('excursao_id'))
            ->get()
            ->mapWithKeys(fn ($v) => [$v->id => $v->tipo->getLabel() . ($v->placa ? " ({$v->placa})" : '')])
            ->toArray()
    )
    ->required()
    ->live(),
```

- [ ] **Passo 3: Implementar tabela** (colunas: nome, CPF mascarado, excursão, veículo, created_at)

- [ ] **Passo 4: Filtrar por evento selecionado**

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if ($eventId = session('selected_event_id')) {
        $query->where('event_id', $eventId);
    }

    return $query->where('criado_por', auth()->id());
}
```

- [ ] **Passo 5: Auto-preencher event_id e criado_por no create**

---

## Task 9: Widget de Estatísticas

**Arquivos:**
- Criar: `app/Filament/Excursionista/Widgets/ExcursaoStatsWidget.php`

- [ ] **Passo 1: Criar widget**

```bash
vendor/bin/sail artisan make:filament-widget ExcursaoStatsWidget --panel=excursionista --stats-overview --no-interaction
```

- [ ] **Passo 2: Implementar 3 cards**: Excursões, Veículos, Monitores (filtrados pelo evento da sessão)

---

## Task 10: Seeder e Dados de Desenvolvimento

**Arquivos:**
- Criar: `database/seeders/ExcursionistaSeeder.php`
- Modificar: `database/seeders/UserSeeder.php`

- [ ] **Passo 1: Criar usuário excursionista no UserSeeder**

```php
User::factory()->create([
    'name' => 'Excursionista Demo',
    'email' => 'excursionista@guestlist.pro',
    'role' => UserRole::Excursionista,
    'password' => Hash::make('password'),
]);
```

- [ ] **Passo 2: Criar ExcursionistaSeeder**

Criar EventAssignment vinculando o usuário excursionista ao evento de demo com role `excursionista`.

- [ ] **Passo 3: Rodar seeders**

```bash
vendor/bin/sail artisan db:seed --class=ExcursionistaSeeder --no-interaction
```

---

## Task 11: Testes

**Arquivos:**
- Criar: `tests/Feature/Excursionista/ExcursaoResourceTest.php`
- Criar: `tests/Feature/Excursionista/MonitorResourceTest.php`

- [ ] **Passo 1: Criar testes de ExcursaoResource** (criar, listar, editar)

- [ ] **Passo 2: Criar testes de MonitorResource** (criar com select reativo, listar, editar)

- [ ] **Passo 3: Rodar testes específicos**

```bash
vendor/bin/sail artisan test --compact tests/Feature/Excursionista/
```

- [ ] **Passo 4: Rodar suite completa**

```bash
vendor/bin/sail artisan test --compact
```

---

## Task 12: Pint + Quality Gate

- [ ] **Passo 1: Formatar código**

```bash
vendor/bin/sail bin pint --dirty
```

- [ ] **Passo 2: Rodar suite completa**

```bash
vendor/bin/sail artisan test --compact
```

- [ ] **Passo 3: Verificar rotas**

```bash
vendor/bin/sail artisan route:list | grep excursionista
```

---

## Task 13: Commit Final

- [ ] **Passo 1: Verificar status**

```bash
git status
git diff --stat
```

- [ ] **Passo 2: Commitar**

```bash
git add -A
git commit -m "feat (excursionista): implementa painel excursionista com excursoes, veiculos e monitores"
```

---

## Checklist de Verificação Final (GATE-3)

- [ ] UserRole::Excursionista existe e funciona
- [ ] Enum TipoVeiculo com ONIBUS e VAN
- [ ] 3 migrations criadas e executadas
- [ ] 3 models com relações corretas
- [ ] Painel `/excursionista` acessível com role correto
- [ ] SelectEvent funciona (redireciona sem evento na sessão)
- [ ] ExcursaoResource CRUD operacional
- [ ] MonitorResource CRUD operacional com select reativo
- [ ] Criação inline de excursão via modal no MonitorResource
- [ ] Widget de estatísticas no dashboard
- [ ] Usuário de demo criado no seeder
- [ ] EventAssignment criado para excursionista
- [ ] Pint limpo (`--dirty` sem alterações)
- [ ] Todos os testes passando

---

## Opções de Execução

**1. Subagent-Driven (recomendado)** — Dispatch fresh subagent por task, review entre tasks

**2. Inline Execution** — Execute tasks nesta sessão usando `executing-plans`
