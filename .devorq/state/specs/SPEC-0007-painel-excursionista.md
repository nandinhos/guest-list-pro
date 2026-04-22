# SPEC-0007 — Painel Excursionista

> **Status:** EM REVISÃO — Aguarda GATE-1 (Nando)
> **Criada em:** 2026-04-22
> **Atualizada em:** 2026-04-22 05:55 BRT
> **Branch:** `feat/SPEC-0007-excursionista`
> **DEVORQ v3 | Stack:** Laravel 12 + Filament v4 + Livewire v3

---

## Resumo Executivo

```
┌─────────────────────────────────────────────────────────────────────────┐
│  PROBLEMA                                                                  │
│  Organizadores de excursões (caravanas) não têm painel dedicado.        │
│  Gerenciam monitores e veículos de forma descentralizada.                 │
├─────────────────────────────────────────────────────────────────────────┤
│  SOLUÇÃO                                                                   │
│  Painel /excursionista com gestão de excursões, veículos e monitores.   │
│  Admin cria usuário EXCURSIONISTA → atribui a evento → pronto.          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 1. Visão Geral

### 1.1 O que é

Painel Filament dedicado para **organizadores de excursões** (caravanas de ônibus/vans) gerenciarem seus monitores e veículos vinculados a eventos do sistema.

### 1.2 O que não é

- Sistema de check-in de monitores
- Relatórios avançados para Admin
- Integração com venda de ingressos (TicketSales)
- Aprovação de monitores pelo Admin

### 1.3 Stack

```
Laravel 12 + Filament v4 + Livewire v3 + PostgreSQL
 ├── Panels: Admin | Promoter | Validator | Bilheteria | Excursionista (NOVO)
 ├── Auth: session-based (igual aos outros painéis)
 └── Event context: EnsureEventSelected middleware
```

---

## 2. Arquitetura

### 2.1 Hierarquia de Painéis

```
PAINÉIS FILAMENT
├── /admin        → UserRole::ADMIN
├── /promoter     → UserRole::PROMOTER
├── /validator    → UserRole::VALIDATOR
├── /bilheteria   → UserRole::BILHETERIA
└── /excursionista → UserRole::EXCURSIONISTA (NOVO)
```

### 2.2 Modelo de Dados

```
┌──────────────────┐       ┌─────────────────┐       ┌────────────────┐
│     User         │       │    Event        │       │   EventAssignment │
│  (has Role)      │       │                 │       │  (user+event+role) │
└────────┬─────────┘       └─────────────────┘       └────────────────┘
         │                                                  │
         │ role=EXCURSIONISTA                              │
         │ (via EventAssignment)                           │
         │                                                  │
         ▼                                                  ▼
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  ┌──────────────┐       ┌─────────────────┐       ┌─────────────┐ │
│  │  Excursao     │ 1    │    Veiculo      │ 1    │   Monitor   │ │
│  │  (event_id)   │──┐    │  (excursao_id)  │──┐    │ (veiculo_id)│ │
│  │  nome          │  │    │  tipo (ONIBUS) │  │    │  nome, cpf  │ │
│  │  criado_por    │  └──►│  placa          │  └──►│  criado_por  │ │
│  └──────────────┘       └─────────────────┘       └─────────────┘ │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

### 2.3 Entidades

#### Excursão (`excursoes`)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint PK | auto |
| `event_id` | FK → `events` | obrigatório, INDEX |
| `nome` | string(150) | obrigatório |
| `criado_por` | FK → `users` | obrigatório (excursionista logado) |
| `created_at` | timestamp | auto |
| `updated_at` | timestamp | auto |

**Índices:** `event_id`, `criado_por`

#### Veículo (`veiculos`)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint PK | auto |
| `excursao_id` | FK → `excursoes` | obrigatório, CASCADE |
| `tipo` | enum(ONIBUS, VAN) | obrigatório |
| `placa` | string(10) | opcional |
| `created_at` | timestamp | auto |
| `updated_at` | timestamp | auto |

#### Monitor (`monitores`)

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `id` | bigint PK | auto |
| `veiculo_id` | FK → `veiculos` | obrigatório, CASCADE |
| `event_id` | FK → `events` | obrigatório (denormalizado) |
| `nome` | string(150) | obrigatório |
| `cpf` | string(14) | obrigatório, UNIQUE por event_id |
| `criado_por` | FK → `users` | obrigatório |
| `created_at` | timestamp | auto |
| `updated_at` | timestamp | auto |

**Índices:** `event_id`, `veiculo_id`, `cpf` (com event_id)

---

## 3. Novos Componentes

### 3.1 Enum UserRole (MODIFICAR)

```php
enum UserRole: string
{
    case ADMIN = 'admin';
    case PROMOTER = 'promoter';
    case VALIDATOR = 'validator';
    case BILHETERIA = 'bilheteria';
    case EXCURSIONISTA = 'excursionista';  // ← NOVO

    // + getLabel()      → 'Excursionista'
    // + getColor()      → 'teal' ou similar
    // + getIcon()       → 'heroicon-m-bus' ou similar
}
```

### 3.2 Enum TipoVeiculo (NOVO)

```php
enum TipoVeiculo: string
{
    case ONIBUS = 'onibus';
    case VAN = 'van';

    public function label(): string
    {
        return match($this) {
            self::ONIBUS => 'Ônibus',
            self::VAN => 'Van',
        };
    }
}
```

### 3.3 Models (NOVOS)

#### Excursao.php

```php
class Excursao extends Model
{
    protected $table = 'excursoes';

    public function event(): BelongsTo
    public function veiculos(): HasMany
    public function criadoPor(): BelongsTo
}
```

#### Veiculo.php

```php
class Veiculo extends Model
{
    protected $table = 'veiculos';

    public function excursao(): BelongsTo
    public function monitores(): HasMany
}
```

#### Monitor.php

```php
class Monitor extends Model
{
    protected $table = 'monitores';

    public function veiculo(): BelongsTo
    public function event(): BelongsTo
    public function criadoPor(): BelongsTo
}
```

---

## 4. Painel Excursionista

### 4.1 URL e Autenticação

```
URL:        /excursionista
Middleware: EnsureEventSelected (obrigatório — mesmo fluxo dos outros painéis)
Auth:       Filament session (igual Promoter/Validator)
```

### 4.2 Estrutura de Páginas

```
/excursionista
├── /select-event              (SelectEvent — obrigatório)
└── /                          (Dashboard)
    ├── /monitores
    │   ├── /list             (ListMonitores)
    │   ├── /create            (CreateMonitor)
    │   └── /edit/{id}         (EditMonitor)
    └── /excursoes
        ├── /list             (ListExcursoes)
        ├── /create            (CreateExcursao)
        └── /edit/{id}         (EditExcursao)
```

### 4.3 Dashboard

```
┌─────────────────────────────────────────────────────────────┐
│  PAINEL EXCURSIONISTA — {evento_selecionado}               │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │ EXCURSÕES   │  │  VEÍCULOS   │  │  MONITORES  │        │
│  │    12       │  │     24      │  │     48      │        │
│  │  [+ Nova]   │  │  [+ Novo]   │  │  [+ Novo]   │        │
│  └─────────────┘  └─────────────┘  └─────────────┘        │
│                                                             │
│  ÚLTIMOS CADASTRADOS                                        │
│  ┌─────────────────────────────────────────────────────────┐│
│  │ Monitor           │ Excursão    │ Veículo │ Criado em ││
│  │ Maria Santos       │ Carnaval 24 │ Ônibus 1 │ 2h atrás  ││
│  │ João Silva         │ Carnaval 24 │ Van 2    │ 5h atrás  ││
│  └─────────────────────────────────────────────────────────┘│
└─────────────────────────────────────────────────────────────┘
```

### 4.4 Fluxo: Criar Monitor com Excursão Inline

```
┌──────────────────────────────────────────────────────────────┐
│  CRIAR MONITOR                                                │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  Nome do Monitor  [________________________________]          │
│                                                               │
│  CPF           [___.___.___-__]  (máscara automática)       │
│                                                               │
│  Excursão  [Carnaval 24 - Ônibus ____________] [+] ← modal  │
│                                                               │
│  Veículo   [Ônibus 1 - Placa ABC-1234__________] (reactive) │
│                                                               │
│                              [Cancelar]  [Salvar Monitor]    │
└──────────────────────────────────────────────────────────────┘

  [+ Nova Excursão — MODAL]
  ┌──────────────────────────────────────┐
  │  Nova Excursão                        │
  ├──────────────────────────────────────┤
  │                                      │
  │  Nome    [__________________________] │
  │                                      │
  │  Veículos:                           │
  │  ┌────────────────────────────────┐  │
  │  │ [ONIBUS ▼] [Placa ABC-1234] [X]│  │
  │  │ [+ Adicionar veículo]          │  │
  │  └────────────────────────────────┘  │
  │                                      │
  │           [Cancelar]  [Criar]        │
  └──────────────────────────────────────┘
```

**Pós-criação:** modal fecha, select de Excursão atualiza com nova opção já selecionada.

---

## 5. Admin — Alterações

### 5.1 ExcursionistaResource

```
/admin/excursionistas
├── Lista de usuários com role=EXCURSIONISTA
├── Criar/Editar excursionista
│   ├── nome, email, password
│   └── eventos atribuídos (MultiSelect)
└── Ações: Atribuir a evento
```

### 5.2 EventAssignment (extensão)

O `EventAssignment` existente já suporta roles dinâmicos. Adicionar:

```php
// Em EventAssignmentPolicy ou no select de roles:
'allowed_roles' => ['admin', 'promoter', 'validator', 'bilheteria', 'excursionista']
```

### 5.3 Resources Gerenciais (Admin)

| Resource | Visibilidade Admin | Operações |
|----------|-------------------|-----------|
| `ExcursaoResource` | read-only | view |
| `VeiculoResource` | read-only | view |
| `MonitorResource` | read-only | view |

---

## 6. Validações e Regras

### 6.1 CPF

- **Armazenamento:** string sem formatação (somente números)
- **Exibição:** máscara `000.000.000-00`
- **Validação:** regex `^\d{11}$` (apenas números)
- **Duplicidade:** UNIQUE por `event_id` + `cpf`

### 6.2 Placa

- **Formato:** `ABC-1234` ou `ABC1D23` (Mercosul)
- **Validação:** regex `^[A-Z]{3}[-]?[0-9]{4}$|^[A-Z]{3}[0-9][A-Z][0-9]{2}$`

### 6.3 Middleware

```php
// ExcursionistaPanelProvider
->middleware([
    EnsureEventSelected::class,  // ← obrigatório
    // ...
])
```

---

## 7. Migrations

### 7.1 Sequência de Execução

```
1. YYYY_create_excursoes_table.php
2. YYYY_create_veiculos_table.php
3. YYYY_create_monitores_table.php
```

### 7.2 Detail

```php
// 1. excursoes
Schema::create('excursoes', function (Blueprint $table) {
    $table->id();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->string('nome', 150);
    $table->foreignId('criado_por')->constrained('users');
    $table->timestamps();
    $table->index(['event_id', 'criado_por']);
});

// 2. veiculos
Schema::create('veiculos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('excursao_id')->constrained('excursoes')->cascadeOnDelete();
    $table->string('tipo', 20); // onibus | van
    $table->string('placa', 10)->nullable();
    $table->timestamps();
});

// 3. monitores
Schema::create('monitores', function (Blueprint $table) {
    $table->id();
    $table->foreignId('veiculo_id')->constrained('veiculos')->cascadeOnDelete();
    $table->foreignId('event_id')->constrained()->cascadeOnDelete();
    $table->string('nome', 150);
    $table->string('cpf', 14); // formatado para display
    $table->foreignId('criado_por')->constrained('users');
    $table->timestamps();
    $table->unique(['event_id', 'cpf']);
    $table->index(['event_id', 'veiculo_id']);
});
```

---

## 8. Estrutura de Arquivos

```
app/
├── Enums/
│   ├── UserRole.php                          # MODIFICAR — add EXCURSIONISTA
│   └── TipoVeiculo.php                       # CRIAR
│
├── Models/
│   ├── Excursao.php                           # CRIAR
│   ├── Veiculo.php                            # CRIAR
│   └── Monitor.php                            # CRIAR
│
├── Filament/
│   └── Excursionista/
│       ├── ExcursionistaPanelProvider.php    # CRIAR
│       ├── Pages/
│       │   ├── SelectEvent.php               # CRIAR (extends SelectEventBase)
│       │   └── Dashboard.php                 # CRIAR
│       ├── Resources/
│       │   ├── ExcursaoResource/
│       │   │   ├── ExcursaoResource.php     # CRIAR
│       │   │   ├── Pages/
│       │   │   │   ├── ListExcursoes.php    # CRIAR
│       │   │   │   ├── CreateExcursao.php   # CRIAR
│       │   │   │   └── EditExcursao.php     # CRIAR
│       │   │   └── RelationManagers/
│       │   │       └── VeiculosRelationManager.php  # CRIAR
│       │   └── MonitorResource/
│       │       ├── MonitorResource.php      # CRIAR
│       │       └── Pages/
│       │           ├── ListMonitores.php    # CRIAR
│       │           ├── CreateMonitor.php     # CRIAR
│       │           └── EditMonitor.php       # CRIAR
│       └── Widgets/
│           └── ExcursionistaStatsWidget.php  # CRIAR
│
├── Http/
│   └── Middleware/
│       └── EnsureEventSelected.php           # JÁ EXISTE — verificar EXCURSIONISTA
│
├── Providers/Filament/
│   └── ExcursionistaPanelProvider.php        # CRIAR
│
database/
├── migrations/
│   ├── YYYY_create_excursoes_table.php        # CRIAR
│   ├── YYYY_create_veiculos_table.php         # CRIAR
│   └── YYYY_create_monitores_table.php       # CRIAR
└── seeders/
    └── ExcursionistaSeeder.php               # CRIAR
│
resources/
└── css/filament/excursionista/
    └── theme.css                             # CRIAR
│
bootstrap/
└── providers.php                             # MODIFICAR — add ExcursionistaPanelProvider
│
routes/
└── panels.php                                 # VERIFICAR — adicionar ExcursionistaPanelProvider
```

---

## 9. Fluxo de Trabalho DEVORQ

### 9.1 Gates

```
┌────────────────────────────────────────────────────────────────┐
│  GATE-1  │  SPEC aprovada pelo Nando           │   ← VOCÊ ESTÁ AQUI  │
├────────────────────────────────────────────────────────────────┤
│  GATE-2  │  Pre-Flight: migrations, factories, tests       │                    │
├────────────────────────────────────────────────────────────────┤
│  GATE-3  │  Quality: Pint clean, E2E smoke passing          │                    │
└────────────────────────────────────────────────────────────────┘
```

### 9.2 Implementação Sugerida (ORDEM)

```
FASE A — Infraestrutura (GATE-2)
  1. Migration: excursoes
  2. Migration: veiculos
  3. Migration: monitores
  4. Enum TipoVeiculo
  5. Enum UserRole (add EXCURSIONISTA)
  6. Model Excursao + relationships
  7. Model Veiculo + relationships
  8. Model Monitor + relationships
  9. Factories: ExcursaoFactory, VeiculoFactory, MonitorFactory
  10. Tests: unitários das models

FASE B — Painel (GATE-2)
  1. ExcursionistaPanelProvider
  2. SelectEvent (extends SelectEventBase)
  3. Dashboard
  4. ExcursaoResource + CRUD
  5. VeiculosRelationManager (dentro de Excursao)
  6. MonitorResource + CRUD
  7. ExcursionistaStatsWidget
  8. Tests: feature do painel

FASE C — Admin Extensions (GATE-2)
  1. ExcursionistaResource (Admin)
  2. Verificar EventAssignment com role EXCURSIONISTA
  3. Tests

FASE D — Quality (GATE-3)
  1. Pint --fix
  2. E2E smoke tests
  3. Testes gerais passando
```

---

## 10. Critérios de Aceite

| # | Critério | Validação |
|---|---------|-----------|
| CA-01 | Usuário `EXCURSIONISTA` faz login e acessa `/excursionista` | Manual |
| CA-02 | Seleciona evento → redirect para dashboard com contadores zerados | Manual |
| CA-03 | Cria excursão com veículo(s) e retorna à lista | E2E |
| CA-04 | Cria monitor vinculado a veículo existente | E2E |
| CA-05 | Cria monitor com criação inline de excursão via modal | E2E |
| CA-06 | CPF validado (11 dígitos) e máscara aplicada | Unitário |
| CA-07 | Placa validada no formato Mercosul | Unitário |
| CA-08 | Admin vê excursionistas na lista de users e atribui a evento | E2E |
| CA-09 | Excursão/Veículo/Monitor deletados em CASCADE | Unitário |
| CA-10 | `sail artisan test` passa (unit + feature) | CI |
| CA-11 | Pint clean | CI |
| CA-12 | E2E smoke tests passam (27+场景) | E2E |

---

## 11. Fora do Escopo (SPEC-0007)

```
✗ Check-in de monitores
✗ Relatórios de excursões para Admin
✗ Integração com TicketSales
✗ Aprovação de monitores pelo Admin
✗ Envio de notificações (WhatsApp/email)
✗ Importação em massa de monitores
```

> Estes items podem virar specs futuras (SPEC-0008, SPEC-0009...).

---

## 12. Referências

| Arquivo | Descrição |
|---------|-----------|
| `app/Providers/Filament/PromoterPanelProvider.php` | Referência para criar ExcursionistaPanelProvider |
| `app/Filament/Promoter/Pages/SelectEvent.php` | SelectEvent que o Excursionista estende |
| `app/Filament/Pages/SelectEventBase.php` | Classe base para SelectEvent |
| `app/Enums/UserRole.php` | Enum a ser modificado |
| `app/Models/Event.php` | Reference para relationships |
| `docs/CONSOLIDATED/` | Regras de stack do projeto |

---

**SPEC Version:** 1.0
**Criada por:** DEVORQ v3 (Hermes Agent)
**Aprovação:** Aguarda Nando (GATE-1)
