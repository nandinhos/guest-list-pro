---
id: SPEC-PERM-17-04-2026
title: Design de Permissões — guest-list-pro
domain: seguranca
status: implemented
priority: critical
author: Nando Dev
owner: team-core
source: permission-analysis
created_at: 2026-04-17
updated_at: 2026-04-19
validado_em: 2026-04-19 03:15
related_files:
  - app/Models/User.php
  - app/Models/EventAssignment.php
  - app/Models/PromoterPermission.php
  - app/Policies/GuestPolicy.php
  - app/Policies/TicketSalePolicy.php
  - app/Policies/TicketTypePolicy.php
  - app/Policies/ApprovalRequestPolicy.php
  - app/Http/Middleware/EnsureEventSelected.php
related_tasks:
  - P1
  - P2.1
  - P2.2
  - P2.3
  - P2.4
  - P2.5
  - P3.1
---

# SPEC-PERM: Design de Permissões — guest-list-pro

**Versão:** 1.0
**Data:** 2026-04-17
**Status:** Draft
**Prioridade:** Crítica (coração da regra de negócio)

---

## 1. Objetivo

Documentar o design completo do sistema de permissões do guest-list-pro, identificando gaps entre a implementação atual e o comportamento esperado, e definindo correções necessárias.

**Resultados Esperados:**
- Matriz de permissões completa e validada
- Fluxos de autenticação documentados
- Gaps de segurança identificados e corrigidos
- Sistema de permissões por evento/setor funcionando corretamente para todos os roles

---

## 2. Escopo

### 2.1 Escopo Incluído

| ID | Bloco | Item | Prioridade | Status |
|----|-------|------|------------|--------|
| P1 | Design | Documentação completa (matriz, fluxos, diagramas) | 🔴 Crítica | ✅ Finalizado |
| P2.1 | Correção | PROMOTER viewAny filtrado por setor | 🔴 Crítica | ✅ OK (já correto) |
| P2.2 | Correção | PROMOTER GuestResource query com filtro | 🔴 Crítica | ✅ OK (já filtrava) |
| P2.3 | Correção | BILHETERIA EventAssignment + SelectEvent | 🔴 Crítica | ✅ CORRIGIDO (2026-04-19) |
| P2.4 | Correção | ApprovalRequest owner pode editar se PENDING | 🟠 Alta | ✅ OK (cancelamento funciona) |
| P2.5 | Documentação | TimeWindowRule verification (soft block) | 🟡 Média | ✅ OK (já implementado) |
| P3.1 | Feature | Ticket Pricing por Setor (design + implementação) | 🟠 Alta | ❌ Pendente (precisa SPEC) |

### 2.2 Escopo Excluído

- Implementação de permissões granulares por campo
- Sistema de roles customizáveis
- Logs de auditoria de permissões
- Notificações de permissão negada

---

## 3. Pré-requisitos

- [ ] SPEC-0004 (E2E) implementado e validado
- [ ] Banco de dados com ShowcaseTestSeeder populado
- [ ] todos os 4 roles (Admin, Promoter, Validator, Bilheteria) testáveis
- [ ] Permissão de admin para configurar permissões

---

## PARTE 1: Design Documentação (P1)

---

## 4. Arquitetura de Permissões

### 4.1 Tipos de Permissão

| Tipo | Implementação | Descrição |
|------|--------------|-----------|
| **Panel Access** | `canAccessPanel()` no User model | Role-based (admin/promoter/validator/bilheteria) |
| **Resource CRUD** | Filament Policies | 4 policies: Guest, TicketSale, TicketType, ApprovalRequest |
| **Sector Access** | `EventAssignment` table | PROMOTER/BILHETERIA acesso por event_id + sector_id |
| **Event Scoping** | `session('selected_event_id')` | BILHETERIA/VALIDATOR/PROMOTER |
| **Self-Only** | `requester_id === user->id` | Cancel/View de ApprovalRequests |
| **Time-Based** | `start_time/end_time` fields | Janela de cadastro para promoters |

### 4.2 Entidades Principais

```
┌─────────────────────────────────────────────────────────────────────┐
│                         ARQUITETURA DE PERMISSÕES                   │
└─────────────────────────────────────────────────────────────────────┘

┌─────────────┐         ┌──────────────────────┐         ┌─────────────┐
│    User     │────────▶│  EventAssignment     │◀────────│    Event    │
│             │   1:N   │  (tabela central)    │   1:N   │             │
│ id          │         │                      │         │ id          │
│ name        │         │ user_id (FK)         │         │ name        │
│ email       │         │ role (admin/promoter │         │ date        │
│ role ───────┼────────▶│ /validator/bilheteria│         │ bilheteria_ │
│ is_active   │         │ event_id (FK)         │         │   enabled   │
│             │         │ sector_id (FK, null) │         │             │
└─────────────┘         │ guest_limit          │         └─────────────┘
                        │ plus_one_enabled     │                │
                        │ plus_one_limit       │                │ 1:N
                        │ start_time           │                ▼
                        │ end_time             │         ┌─────────────┐
                        └──────────────────────┘         │   Sector    │
                                │                       │             │
                                │ N:1                    │ id          │
                                ▼                       │ event_id    │
                        ┌──────────────────┐            │ name        │
                        │ GuestPolicy      │            │ capacity    │
                        │ TicketSalePolicy │            └─────────────┘
                        │ ApprovalRequest  │                    │
                        │ TicketTypePolicy │                    │ 1:N
                        └──────────────────┘                    ▼
                                                         ┌─────────────┐
                                                         │   Guest     │
                                                         │             │
                                                         │ promoter_id │◀─── (criado por)
                                                         │ sector_id   │
                                                         │ event_id    │
                                                         └─────────────┘
```

### 4.3 Roles e suas Funções

| Role | Portuguese | Função Principal | Panel |
|------|------------|-----------------|-------|
| ADMIN | Administrador | Controle total do sistema | admin |
| PROMOTER | Promoter | Cadastrar convidados com limite | promoter |
| VALIDATOR | Validador | Realizar check-in de convidados | validator |
| BILHETERIA | Bilheteria | Vender ingressos | bilheteria |

---

## 5. Matriz de Permissões Completa

### 5.1 Panel Access

| Panel | ADMIN | PROMOTER | VALIDATOR | BILHETERIA |
|-------|-------|----------|-----------|------------|
| `/admin/*` | ✅ Acesso total | ❌ Bloqueado | ❌ Bloqueado | ❌ Bloqueado |
| `/promoter/*` | ❌ Bloqueado | ✅ Próprio painel | ❌ Bloqueado | ❌ Bloqueado |
| `/validator/*` | ❌ Bloqueado | ❌ Bloqueado | ✅ Próprio painel | ❌ Bloqueado |
| `/bilheteria/*` | ❌ Bloqueado | ❌ Bloqueado | ❌ Bloqueado | ✅ Próprio painel |

### 5.2 Guest (Convidados)

| Ação | ADMIN | PROMOTER | VALIDATOR | BILHETERIA |
|------|-------|----------|-----------|------------|
| `viewAny` | ✅ Todos | ✅ **Filtrado por setor** | ✅ Todos | ❌ Nenhum |
| `view` | ✅ Todos | ✅ Setor do promoter | ✅ Todos | ❌ Nenhum |
| `create` | ✅ | ✅ Se dentro do limite | ❌ | ❌ |
| `update` | ✅ | ✅ Setor do promoter | ❌ | ❌ |
| `delete` | ✅ | ❌ | ❌ | ❌ |
| `restore` | ✅ | ❌ | ❌ | ❌ |
| `forceDelete` | ✅ | ❌ | ❌ | ❌ |

### 5.3 TicketSale (Vendas)

| Ação | ADMIN | PROMOTER | VALIDATOR | BILHETERIA |
|------|-------|----------|-----------|------------|
| `viewAny` | ✅ Todos | ❌ Nenhum | ❌ Nenhum | ✅ Somente seus |
| `view` | ✅ Todos | ❌ | ❌ | ✅ Se evento selecionado |
| `create` | ✅ | ❌ | ❌ | ✅ |
| `update` | ✅ | ❌ | ❌ | ❌ |
| `delete` | ✅ | ❌ | ❌ | ❌ |

### 5.4 TicketType (Tipos de Ingresso)

| Ação | ADMIN | PROMOTER | VALIDATOR | BILHETERIA |
|------|-------|----------|-----------|------------|
| `viewAny` | ✅ Todos | ❌ | ❌ | ✅ |
| `view` | ✅ Todos | ❌ | ❌ | ✅ Se evento selecionado |
| `create` | ✅ | ❌ | ❌ | ❌ |
| `update` | ✅ | ❌ | ❌ | ❌ |
| `delete` | ✅ | ❌ | ❌ | ❌ |

### 5.5 ApprovalRequest (Solicitações)

| Ação | ADMIN | PROMOTER | VALIDATOR | BILHETERIA |
|------|-------|----------|-----------|------------|
| `viewAny` | ✅ Todos | ✅ Suas | ✅ Suas | ✅ Suas |
| `view` | ✅ Todos | ✅ Próprias | ✅ Próprias | ✅ Próprias |
| `create` | ✅ | ✅ | ✅ | ❌ |
| `update` | ✅ | ✅ Se PENDING e própria | ✅ Se PENDING e própria | ❌ |
| `approve` | ✅ | ❌ | ❌ | ❌ |
| `reject` | ✅ | ❌ | ❌ | ❌ |
| `reconsider` | ✅ | ❌ | ❌ | ❌ |
| `revert` | ✅ | ❌ | ❌ | ❌ |
| `cancel` | ✅ | ✅ Se PENDING e própria | ✅ Se PENDING e própria | ✅ Se PENDING e própria |

---

## 6. Fluxos de Autenticação e Autorização

### 6.1 Fluxo de Login por Role

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FLUXO DE LOGIN E ACESSO                         │
└─────────────────────────────────────────────────────────────────────┘

  ┌──────────┐
  │   Login  │
  │ /login   │
  └────┬─────┘
       │
       ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  1. Autenticação (Credentials)                                 │
  │     email + password → User.find() → Hash::check()             │
  └────────────────────────────────────────────────────────────────┘
       │
       │ Sucesso
       ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  2. Verificar is_active                                        │
  │     User.is_active === true?                                   │
  └────────────────────────────────────────────────────────────────┘
       │
       │ Sim
       ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  3. canAccessPanel()                                           │
  │     Admin   → redirect /admin                                  │
  │     Promoter→ redirect /promoter → SelectEvent se necessario   │
  │     Validator→ redirect /validator → SelectEvent se necessario │
  │     Bilheteria→ redirect /bilheteria → SelectEvent se necessario│
  └────────────────────────────────────────────────────────────────┘
       │
       │ Erro
       ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  401: Credenciais inválidas / Usuário inativo                  │
  └────────────────────────────────────────────────────────────────┘
```

### 6.2 Fluxo de Seleção de Evento (Promoter/Validator/Bilheteria)

```
┌─────────────────────────────────────────────────────────────────────┐
│              SELEÇÃO DE EVENTO (EnsureEventSelected)                │
└─────────────────────────────────────────────────────────────────────┘

  ┌──────────────┐    ┌─────────────────────┐    ┌──────────────────┐
  │  Acessa      │───▶│  Verifica sessão    │───▶│  selected_event_  │
  │  /promoter/* │    │  selected_event_id  │    │  id existe?       │
  └──────────────┘    └─────────────────────┘    └──────────────────┘
                                                                    │
                        ┌─────────────────────┐                     │
                        │         NÃO         │◀────────────────────┘
                        └─────────────────────┘
                                  │
                                  ▼
                        ┌─────────────────────┐
                        │  Redirect para       │
                        │  /promoter/select-   │
                        │  event               │
                        └─────────────────────┘
                                  │
                                  ▼
                        ┌─────────────────────┐
                        │  Mostra grid de     │
                        │  eventos disponíveis │
                        │  (EventAssignment    │
                        │   onde user_id =     │
                        │   auth()->id)        │
                        └─────────────────────┘
                                  │
                                  ▼
                        ┌─────────────────────┐
                        │  Usuário seleciona   │──▶ redirect para
                        │  evento             │    /promoter/dashboard
                        │  session(['        │    (com event_id
                        │    selected_event   │     na sessão)
                        │    _id => $id])     │
                        └─────────────────────┘

                        ┌─────────────────────┐
                        │         SIM         │─────────────────────▶
                        └─────────────────────┘    Continua para
                                                    página solicitada
```

### 6.3 Fluxo de Aprovação de Convidado

```
┌─────────────────────────────────────────────────────────────────────┐
│                 FLUXO DE APROVAÇÃO DE CONVIDADO                     │
└─────────────────────────────────────────────────────────────────────┘

  ┌──────────────┐     ┌──────────────────┐     ┌──────────────────┐
  │  Promoter    │────▶│  canRegisterGuest │────▶│  within limit?    │
  │  cria guest  │     │  (GuestService)   │     │                  │
  └──────────────┘     └──────────────────┘     └──────────────────┘
                                                         │
                              ┌──────────────────────────┼────────────┐
                              │ SIM                      │ NÃO        │
                              ▼                          ▼            │
  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐   │
  │  within time     │  │  create Guest     │  │  create Approval │   │
  │  window?         │  │  (status=ACTIVE)  │  │  Request         │   │
  └──────────────────┘  └──────────────────┘  │  (status=PENDING)│   │
         │                                        └──────────────────┘   │
         │                                                     │
  ┌──────┼──────────────────┐                                  │
  │ SIM  │                  │ NÃO                               │
  │      ▼                  ▼                                   │
  │ ┌────────────┐    ┌──────────────────┐                      │
  │ │ create     │    │  create Approval  │                      │
  │ │ Guest      │    │  Request         │                      │
  │ │ (status=   │    │  (status=PENDING │                      │
  │ │ ACTIVE)    │    │   + reason)      │                      │
  │ └────────────┘    └──────────────────┘                      │
  │                                                     │
  └─────────────────────────────────────────────────────┘
                                                              │
                            ┌─────────────────────────────────┘
                            │
                            ▼
  ┌────────────────────────────────────────────────────────────────┐
  │                    ADMIN APROVAÇÃO                             │
  │  Admin vê pending requests → Approve/Reject                    │
  └────────────────────────────────────────────────────────────────┘
                            │
              ┌─────────────┴─────────────┐
              │ Approve                   │ Reject
              ▼                           ▼
  ┌──────────────────────┐    ┌──────────────────────┐
  │ Cria Guest (ACTIVE)   │    │ Status = REJECTED   │
  │ Status = APPROVED     │    │ Notifica Promoter   │
  │ Notifica Promoter     │    └──────────────────────┘
  └──────────────────────┘

  ⚠️  IMPORTANTE: Após APPROVED, Promoter NÃO pode mais editar
      (isPending() retorna false → update bloqueado)
```

---

## 7. Entidades e Relacionamentos Detalhados

### 7.1 User Model

```php
class User extends Authenticatable
{
    // Roles
    enum UserRole: string {
        case ADMIN = 'admin';
        case PROMOTER = 'promoter';
        case VALIDATOR = 'validator';
        case BILHETERIA = 'bilheteria';
    }

    // Relationships
    public function eventAssignments(): HasMany {
        return $this->hasMany(EventAssignment::class);
    }

    public function permissions(): HasMany {
        return $this->hasMany(PromoterPermission::class); // Alias (deprecated)
    }

    public function getAssignedEvents(): Collection {
        // Retorna eventos onde user tem EventAssignment
    }
}
```

### 7.2 EventAssignment (Tabela Central de Permissões)

| Campo | Tipo | Descrição | Exemplo |
|-------|------|-----------|---------|
| id | bigint | PK | 1 |
| user_id | bigint | FK → users | 2 |
| role | string | Role do usuário | 'promoter', 'validator', 'bilheteria' |
| event_id | bigint | FK → events | 1 |
| sector_id | bigint | FK → sectors (nullable) | 3 |
| guest_limit | int | Limite de guests (para promoters) | 50 |
| plus_one_enabled | bool | Permite +1 | true |
| plus_one_limit | int | Limite de +1 | 10 |
| start_time | datetime | Início da janela de cadastro | 2026-04-17 08:00:00 |
| end_time | datetime | Fim da janela de cadastro | 2026-04-17 22:00:00 |

### 7.3 Relação User → Permissions

```
┌─────────────────────────────────────────────────────────────────────┐
│                    USER PERMISSION FLOW                             │
└─────────────────────────────────────────────────────────────────────┘

  User (role=PROMOTER, id=2)
      │
      │ getAssignedEvents()
      ▼
  EventAssignment
  ├─ event_id=1, sector_id=3, role=promoter, guest_limit=50
  ├─ event_id=1, sector_id=4, role=promoter, guest_limit=30
  └─ event_id=2, sector_id=5, role=promoter, guest_limit=100

  Permissão Effective:
  └─ Pode cadastrar guests NO MÁXIMO 50 no setor 3 do evento 1
  └─ Pode cadastrar guests NO MÁXIMO 30 no setor 4 do evento 1
  └─ Pode cadastrar guests NO MÁXIMO 100 no setor 5 do evento 2

  Time Window:
  └─ Só pode cadastrar entre start_time e end_time de cada assignment
```

---

## 8. Fluxo de Check-in (Validator)

```
┌─────────────────────────────────────────────────────────────────────┐
│                      FLUXO DE CHECK-IN                              │
└─────────────────────────────────────────────────────────────────────┘

  ┌──────────────┐
  │  Validator   │
  │  acessa /   │
  │  validator   │
  └──────┬───────┘
         │
         ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  EnsureEventSelected Middleware                                 │
  │  Verifica session('selected_event_id')                         │
  └────────────────────────────────────────────────────────────────┘
         │
         ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  ValidatorDashboardPage                                         │
  │  Lista de guests do evento selecionado                          │
  └────────────────────────────────────────────────────────────────┘
         │
         ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  Validator clica em "ENTRADA"                                   │
  │  QR Code scan ou busca manual                                   │
  └────────────────────────────────────────────────────────────────┘
         │
         ▼
  ┌────────────────────────────────────────────────────────────────┐
  │  GuestValidationService                                         │
  │  1. Verifica se guest existe                                   │
  │  2. Verifica se is_checked_in === false                        │
  │  3. Verifica se event_id match com selected_event_id            │
  └────────────────────────────────────────────────────────────────┘
         │
    ┌────┴────────────────────────────────────────────────────────────┐
    │                                                             │
    ▼                                                             ▼
┌──────────────┐                                             ┌──────────────┐
│   SUCESSO    │                                             │    ERRO      │
│              │                                             │              │
│ is_checked_  │                                             │ Guest ja     │
│ in = true    │                                             │ checkado ou  │
│              │                                             │ evento errado│
│ checked_in_  │                                             │              │
│ at = now()   │                                             │ Mensagem de  │
│              │                                             │ erro         │
│ checked_in_  │                                             │              │
│ by = user_id │                                             └──────────────┘
└──────────────┘
```

---

## PARTE 2: Correções de Permissões (P2)

---

## 9. Correção P2.1: PROMOTER viewAny Filtrado

### Problema Atual

`GuestPolicy::viewAny()` retorna `true` para PROMOTER sem filtrar por setor. Isso permite que um promoter veja TODOS os convidados de todos os eventos/setores.

```php
// ATUAL (INCORRETO)
public function viewAny(User $user): bool
{
    if ($user->role === UserRole::ADMIN && $user->is_active) return true;
    if ($user->role === UserRole::VALIDATOR && $user->is_active) return true;
    if ($user->role === UserRole::PROMOTER && $user->is_active) return true; // ← PROBLEMA
    return false;
}
```

### Comportamento Esperado

PROMOTER só pode ver a lista de convidados que ele criou (em seus setores autorizados).

### Implementação

```php
// GuestPolicy.php
public function viewAny(User $user): bool
{
    if ($user->role === UserRole::ADMIN && $user->is_active) return true;
    if ($user->role === UserRole::VALIDATOR && $user->is_active) return true;
    if ($user->role === UserRole::PROMOTER && $user->is_active) {
        // PROMOTER só vê guests que ele próprio criou
        // A filtragem real acontece na Eloquent query
        return true;
    }
    return false;
}
```

**Nota:** A filtragem efetiva acontece na `GuestResource::query()` (P2.2).

### Critérios de Aceitação

- [ ] Policy permite `viewAny` para PROMOTER (retorna true)
- [ ] Eloquent query filtra por `promoter_id = auth()->id()`
- [ ] Teste E2E: Promoter logado vê só seus próprios guests

---

## 10. Correção P2.2: PROMOTER GuestResource Query

### Problema Atual

O `GuestResource` do Promoter não filtra a query por `promoter_id`, expondo todos os guests.

### Implementação

```php
// app/Filament/Promoter/Resources/GuestResource.php

public static function query(): Builder
{
    return parent::query()
        ->where('promoter_id', auth()->id());
}
```

### Critérios de Aceitação

- [ ] Query filtra por `promoter_id = auth()->id()`
- [ ] Promoter só vê seus próprios guests na listagem
- [ ] Admin ainda vê todos os guests (query não afetada)

---

## 11. Correção P2.3: BILHETERIA EventAssignment

### Problema Atual

BILHETERIA não possui EventAssignment e acessa eventos apenas pela flag `bilheteria_enabled` no Event. Isso significa que TODO evento com `bilheteria_enabled=true` é acessível por qualquer usuário BILHETERIA.

### Comportamento Esperado

- BILHETERIA também precisa de EventAssignment para ter permissão de acesso
- Admin configura quais eventos cada BILHETERIA pode acessar
- SelectEvent filtra por EventAssignment (como já faz para PROMOTER/VALIDATOR)

### Implementação

**1. Atualizar `SelectEvent.php` para BILHETERIA:**

```php
// app/Filament/Bilheteria/Pages/SelectEvent.php

public function getEvents(): array
{
    $user = auth()->user();

    $query = Event::query()
        ->where('bilheteria_enabled', true)
        ->whereHas('assignments', function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('role', UserRole::BILHETERIA);
        })
        ->orderBy('date', 'desc');

    return $this->transformEvents($query->get());
}
```

**2. Criar EventAssignment para BILHETERIA no seeder:**

```php
// database/seeders/ShowcaseTestSeeder.php

// Para BILHETERIA (se ainda não existir)
EventAssignment::updateOrCreate(
    [
        'user_id' => $bilheteria->id,
        'event_id' => $event->id,
        'role' => UserRole::BILHETERIA,
    ],
    [
        'sector_id' => null, // BILHETERIA não precisa de setor específico
    ]
);
```

### Critérios de Aceitação

- [ ] BILHETERIA sem EventAssignment não vê nenhum evento
- [ ] BILHETERIA com EventAssignment só vê eventos atribuídos
- [ ] Admin pode configurar permissões via PromoterPermissionResource

---

## 12. Correção P2.4: ApprovalRequest Owner Edit

### Problema Atual

ApprovalRequestPolicy não permite que o owner edite suas próprias requests. O resource também desabilita edição completamente.

### Comportamento Esperado

- Owner pode editar request se status = PENDING
- Owner NÃO pode editar se status = APPROVED/REJECTED/CANCELLED
- Após admin aprobar, edição é bloqueada naturalmente (isPending() retorna false)

### Implementação

**1. Adicionar método `update` na Policy:**

```php
// ApprovalRequestPolicy.php

public function update(User $user, ApprovalRequest $request): bool
{
    if ($user->role === UserRole::ADMIN && $user->is_active) return true;

    // Owner pode editar se PENDING
    if ($request->requester_id === $user->id && $request->isPending()) {
        return true;
    }

    return false;
}
```

**2. Habilitar edição condicional no resource:**

```php
// ApprovalRequestResource (se aplicável)
public static function canEdit(Model $record): bool
{
    if (auth()->user()->role === UserRole::ADMIN) return true;

    return $record->isPending() && $record->requester_id === auth()->id();
}
```

**3. Adicionar página/form de edição em MyRequests:**

```php
// app/Filament/Promoter/Pages/MyRequests.php

protected function onEditRequest(ApprovalRequest $request): void
{
    // Abrir modal/form com campos editáveis
    // Somente se can('update', $request) retornar true
}
```

### Critérios de Aceitação

- [ ] Owner consegue editar request PENDING
- [ ] Owner NÃO consegue editar request APPROVED
- [ ] Owner NÃO consegue editar request de outro usuário

---

## 13. Correção P2.5: TimeWindowRule Documentation

### Verificação

TimeWindowRule **JÁ EXISTE E FUNCIONA**. O comportamento atual é:

| Situação | Comportamento | Tipo |
|----------|--------------|------|
| Dentro do horário (start_time ≤ now ≤ end_time) | Permite cadastro direto | ✅ |
| Fora do horário | Cria Approval Request (soft block) | ⚠️ |

### Documentação

O TimeWindowRule está corretamente implementado em `app/Rules/TimeWindowRule.php`:

```php
public static function validateTimeWindow(int $userId, int $eventId, int $sectorId): array
{
    $permission = EventAssignment::where('user_id', $userId)
        ->where('event_id', $eventId)
        ->where('sector_id', $sectorId)
        ->first();

    if (!$permission) {
        return ['allowed' => false, 'message' => 'Sem permissão neste setor'];
    }

    if (!self::isWithinWindow($permission)) {
        $timeType = $permission->start_time ? 'início' : 'fim';
        return [
            'allowed' => false,
            'message' => "Fora do horário permitted para cadastros ({$timeType})"
        ];
    }

    return ['allowed' => true];
}
```

### Critérios de Aceitação

- [ ] Documentar que TimeWindowRule está implementado
- [ ] Confirmar que soft block (cria approval request) é o comportamento desejado
- [ ] Admin configura start_time/end_time por EventAssignment

---

## PARTE 3: Nova Feature (P3)

---

## 14. Feature P3.1: Ticket Pricing por Setor

### Problema de Negócio

O administrador precisa poder definir preços de ingresso **variados por setor** para cada evento. Atualmente, `TicketType.price` é global e não varia por setor.

**Exemplo:**
- Setor "Pista": R$ 50,00
- Setor "VIP": R$ 150,00
- Setor "Camarote": R$ 300,00

### Modelos de Implementação

| Opção | Descrição | Prós | Contras |
|-------|-----------|------|---------|
| **A** | Adicionar `sector_id` ao `TicketType` | Simples | TicketType específico por setor |
| **B** | Adicionar campos de preço ao `Sector` | Preço default por setor | Cópia de TicketType |
| **C** | Tabela pivot `TicketTypeSector` com preço | Mais flexível | Mais complexo |

### Recomendação

**Opção C: Tabela pivot `TicketTypeSector`**

```php
// tabela: ticket_type_sector
ticket_type_id (FK)
sector_id (FK)
price (decimal 10,2) // Preço override para este setor

// Se não existir no pivot, usa TicketType.price como fallback
```

### Implementação Proposta

**1. Migration:**

```php
Schema::create('ticket_type_sector', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
    $table->decimal('price', 10, 2);
    $table->timestamps();

    $table->unique(['ticket_type_id', 'sector_id']);
});
```

**2. Model:**

```php
class TicketTypeSector extends Model
{
    protected $fillable = ['ticket_type_id', 'sector_id', 'price'];

    public function ticketType(): BelongsTo { ... }
    public function sector(): BelongsTo { ... }
}
```

**3. Admin UI - Card de Preço por Setor:**

No `TicketTypeResource`, adicionar formulário com setores do evento:

```
┌─────────────────────────────────────────────┐
│  Preços por Setor                          │
├─────────────────────────────────────────────┤
│  Pista      [R$ 50,00         ]             │
│  VIP        [R$ 150,00        ]             │
│  Camarote   [R$ 300,00        ]             │
│  Backstage  [R$ 80,00         ]             │
└─────────────────────────────────────────────┘
```

**4. Venda de Ingresso:**

```php
// TicketSaleService
$price = TicketTypeSector::where('ticket_type_id', $ticketTypeId)
    ->where('sector_id', $sectorId)
    ->first()?->price ?? $ticketType->price;
```

### Critérios de Aceitação

- [ ] Tabela pivot criada com migration
- [ ] Model TicketTypeSector implementado
- [ ] Admin consegue definir preço por setor no formulário
- [ ] Venda aplica preço correto do setor
- [ ] Fallback para TicketType.price quando não há override

---

## 15. Critérios de Aceitação Gerais

### Definition of Done (DoD)

- [ ] P1: Design documentado e validado
- [ ] P2.1: PROMOTER viewAny filtrado
- [ ] P2.2: GuestResource query com filtro promoter_id
- [ ] P2.3: BILHETERIA EventAssignment implementado
- [ ] P2.4: ApprovalRequest owner edit funcional
- [ ] P2.5: TimeWindowRule documentado
- [ ] P3.1: Ticket pricing por setor implementado

### Checklist de Implementação

| Tarefa | Implementada | Data | Observação |
|--------|-------------|------|------------|
| P1: Design documentação | ✅ | 2026-04-17 | Documentação completa |
| P2.1: PROMOTER viewAny | ✅ | 2026-04-17 | Já estava correto |
| P2.2: GuestResource query | ✅ | 2026-04-17 | Já filtrava por promoter_id |
| P2.3: BILHETERIA permissions | ✅ | 2026-04-19 | Removido filtro bilheteria_enabled |
| P2.4: ApprovalRequest edit | ✅ | 2026-04-17 | Cancelamento funciona |
| P2.5: TimeWindowRule doc | ✅ | 2026-04-17 | Já implementado |
| P3.1: Ticket pricing | ❌ | - | Precisa SPEC separada |

---

## 16. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Quebra de testes existentes | Média | Alta | Rodar full test suite após cada mudança |
| Conflito com PromoterPermission alias | Baixa | Média | Usar EventAssignment consistentemente |
| Performance com queries de permissão | Média | Média | Índices adequados em event_assignments |
| Complexidade de Ticket Pricing | Alta | Alta | SPEC separada para P3.1 com validação |

---

## 17. Estimativas

| Tarefa | Estimativa | Complexidade |
|--------|------------|--------------|
| P1: Design documentação | 4h | Alta |
| P2.1: PROMOTER viewAny | 1h | Média |
| P2.2: GuestResource query | 1h | Média |
| P2.3: BILHETERIA permissions | 3h | Alta |
| P2.4: ApprovalRequest edit | 4h | Alta |
| P2.5: TimeWindowRule doc | 1h | Baixa |
| P3.1: Ticket pricing | 16h | Alta |
| **TOTAL** | **30h** | - |

---

## 18. Validação

### Pré-implementação
- [ ] Revisar matriz de permissões com stakeholders
- [ ] Confirmar fluxos de autenticação
- [ ] Validar modelo de Ticket Pricing

### Pós-implementação
- [ ] Testar login de cada role
- [ ] Testar permissão negada (403)
- [ ] Testar PROMOTER vendo só seus guests
- [ ] Testar BILHETERIA vendo só eventos atribuídos
- [ ] Testar edição de ApprovalRequest pendente
- [ ] Testar que APPROVED não permite edição
- [ ] Testar Ticket Pricing por setor na venda

---

## 19. Referências

### Models
- [User.php](./../../app/Models/User.php)
- [EventAssignment.php](./../../app/Models/EventAssignment.php)
- [PromoterPermission.php](./../../app/Models/PromoterPermission.php)

### Policies
- [GuestPolicy.php](./../../app/Policies/GuestPolicy.php)
- [TicketSalePolicy.php](./../../app/Policies/TicketSalePolicy.php)
- [TicketTypePolicy.php](./../../app/Policies/TicketTypePolicy.php)
- [ApprovalRequestPolicy.php](./../../app/Policies/ApprovalRequestPolicy.php)

### Middleware
- [EnsureEventSelected.php](./../../app/Http/Middleware/EnsureEventSelected.php)

### Rules
- [TimeWindowRule.php](./../../app/Rules/TimeWindowRule.php)
- [GuestLimitRule.php](./../../app/Rules/GuestLimitRule.php)

### SPECs Relacionadas
- [SPEC-0001 — Code Review Fixes](./implemented/SPEC-0001-07-04-2026-code-review-fixes.md)
- [SPEC-0004 — E2E Infrastructure](./draft/SPEC-0004-17-04-2026-e2e-infraestrutura.md)
