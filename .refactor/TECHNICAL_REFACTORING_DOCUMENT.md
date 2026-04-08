# Documento Técnico de Refatoração — guest-list-pro

**Versão**: 1.0  
**Data**: 2026-04-08  
**Stack**: Laravel 12 + Filament v4 + Livewire v3  
**Objetivo**: Documentação técnica para embasar o planejamento de refatoração do sistema

---

## 1. Visão Geral do Sistema

### 1.1 Descrição

O **guest-list-pro** é um sistema de gestão de convidados para eventos com as seguintes funcionalidades principais:

- **Gestão de Eventos**: Criação e administração de eventos com setores
- **Cadastro de Convidados**: Promoters cadastram convidados com controle de limite por setor
- **Sistema de Aprovações**: workflow de aprovação para inclusão de convidados
- **Check-in**: Validação de entrada via QR Code
- **Controle de Duplicidade**: Detecção de documentos duplicados e nomes similares

### 1.2 Stack Tecnológica

| Componente | Tecnologia | Versão |
|------------|------------|--------|
| Framework | Laravel | 12.x |
| Admin Panel | Filament | v4 |
| Componentes | Livewire | v3 |
| Estilização | Tailwind CSS | 3.x |
| Banco de Dados | MySQL | 8.x |
| Logs | Spatie Activitylog | — |

### 1.3 Estrutura de Diretórios

```
app/
├── Filament/          # Recursos e páginas do admin
│   ├── Bilheteria/    # Módulo bilheteria
│   ├── Promoter/      # Módulo promoter
│   ├── Resources/     # Resource files
│   └── Validator/     # Módulo validador
├── Http/              # Controllers HTTP
├── Livewire/          # Componentes Livewire
├── Models/            # Models Eloquent
├── Services/         # Serviços de negócio
├── Notifications/    # Notificações
├── Observers/        # Observers Eloquent
├── Policies/         # Policies de autorização
└── Enums/            # Enums do sistema
```

---

## 2. Modelo de Dados

### 2.1 Entidades Principais

#### 2.1.1 Event

```php
// app/Models/Event.php
class Event extends Model
{
    protected $fillable = [
        'name', 'banner_path', 'banner_url', 'location',
        'date', 'start_time', 'end_time', 'status',
        'ticket_price', 'bilheteria_enabled',
    ];
}
```

**Relacionamentos**:
- `sectors()` → HasMany(Sector)
- `guests()` → HasMany(Guest)
- `permissions()` → HasMany(PromoterPermission)
- `assignments()` → HasMany(EventAssignment)
- `ticketSales()` → HasMany(TicketSale)

**Atributos**:
| Campo | Tipo | Descrição |
|-------|------|-----------|
| name | string | Nome do evento |
| banner_path | string? | Path local do banner |
| banner_url | string? | URL externa do banner |
| location | string? | Localização |
| date | date | Data do evento |
| start_time | time | Horário de início |
| end_time | time | Horário de término |
| status | EventStatus | Status (draft, published, ongoing, finished, cancelled) |
| ticket_price | decimal | Preço do ingresso |
| bilheteria_enabled | boolean | Bilheteria ativa |

---

#### 2.1.2 Guest

```php
// app/Models/Guest.php
class Guest extends Model
{
    protected $fillable = [
        'event_id', 'sector_id', 'promoter_id',
        'name', 'document', 'document_type',
        'email', 'is_checked_in', 'checked_in_at', 'checked_in_by',
    ];
}
```

**Relacionamentos**:
- `event()` → BelongsTo(Event)
- `sector()` → BelongsTo(Sector)
- `promoter()` → BelongsTo(User)
- `validator()` → BelongsTo(User)

**Atributos**:
| Campo | Tipo | Descrição |
|-------|------|-----------|
| event_id | bigint | Evento associado |
| sector_id | bigint | Setor do evento |
| promoter_id | bigint | Promoter que cadastrou |
| name | string | Nome do convidado |
| document | string | CPF/RG/Passaporte |
| document_type | DocumentType | Tipo de documento |
| email | string? | Email do convidado |
| is_checked_in | boolean | Status de check-in |
| checked_in_at | datetime? | Data/hora do check-in |
| checked_in_by | bigint? | Validator que fez o check-in |

**Constraints**:
- Unique: `(event_id, document)` — Impede duplicata por documento

---

#### 2.1.3 ApprovalRequest

```php
// app/Models/ApprovalRequest.php
class ApprovalRequest extends Model
{
    protected $fillable = [
        'event_id', 'sector_id', 'type', 'status',
        'requester_id', 'guest_name', 'guest_document',
        'guest_document_type', 'guest_email', 'guest_id',
        'requester_notes', 'reviewer_id', 'reviewed_at',
        'reviewer_notes', 'ip_address', 'user_agent', 'expires_at',
    ];
}
```

**Atributos**:
| Campo | Tipo | Descrição |
|-------|------|-----------|
| event_id | bigint | Evento solicitado |
| sector_id | bigint | Setor solicitado |
| type | RequestType | Tipo (GUEST_INCLUSION, EMERGENCY_CHECKIN) |
| status | RequestStatus | Status (PENDING, APPROVED, REJECTED, CANCELLED, EXPIRED) |
| requester_id | bigint | Usuário que solicitou |
| guest_* | string | Dados do convidado |
| guest_id | bigint? | Guest criado após aprovação |
| reviewer_id | bigint? | Admin que revisou |
| reviewed_at | datetime? | Data da revisão |

**Scopes Úteis**:
- `pending()` → Solicitações pendentes
- `forEvent($id)` → Por evento
- `byType($type)` → Por tipo
- `expired()` → Expiradas

---

### 2.2 Entidades Secundárias

| Entidade | Descrição |
|----------|-----------|
| Sector | Setor do evento (VIP, Arquibancada, etc) |
| User | Usuários do sistema (Admin, Validator, Promoter) |
| EventAssignment | Permissão de promoter por evento/setor |
| TicketSale | Vendas de bilheteria |
| CheckinAttempt | Tentativas de check-in |
| PromoterPermission | Permissões legadas |

---

## 3. Domínio de Negócio

### 3.1 Regras de Negócio Identificadas

#### 3.1.1 Regra: Unique Document per Event
- **Descrição**: Um documento (CPF/RG/Passaporte) não pode se repetir no mesmo evento
- **Implementação Atual**: 
  - Database unique constraint em `guests(event_id, document)`
  - Verificação em `ApprovalRequestService::checkForDuplicates()`
- **Localização**: `app/Services/ApprovalRequestService.php:33-53`

#### 3.1.2 Regra: Guest Limit per Sector
- **Descrição**: Promoters têm limite de convidados por setor
- **Implementação**: Verificação em `GuestService::canRegisterGuest()`
- **Localização**: `app/Services/GuestService.php:100-111`

#### 3.1.3 Regra: Time Window for Registration
- **Descrição**: Cadastro só pode ser feito em horário específico
- **Implementação**: Verificação em `GuestService::canRegisterGuest()`
- **Localização**: `app/Services/GuestService.php:84-98`

#### 3.1.4 Regra: Approval Workflow
- **Descrição**: Inclusão de convidados requer aprovação de admin
- **Implementação**: `ApprovalRequestService::approve()`, `reject()`, etc.
- **Localização**: `app/Services/ApprovalRequestService.php:191-501`

#### 3.1.5 Regra: Emergency Check-in
- **Descrição**: Validator pode incluir + fazer check-in simultâneo
- **Implementação**: Tipo `EMERGENCY_CHECKIN` cria guest já checkado
- **Localização**: `app/Services/ApprovalRequestService.php:159-184`

### 3.2 Invariantes

| Invariante | Descrição |
|------------|-----------|
| Guest precisa de evento | `event_id` é obrigatório |
| Guest precisa de setor | `sector_id` é obrigatório |
| Check-in requer validator | Apenas ADMIN/VALIDATOR podem fazer check-in |
| Aprovação requer admin | Apenas ADMIN podem aprovar/rejeitar |
| Auto-aprovação proibida | Usuário não pode aprovar própria solicitação |

### 3.3 Relacionamentos

```
User (1) ──< (N) EventAssignment
Event (1) ──< (N) Guest
Event (1) ──< (N) Sector
Event (1) ──< (N) ApprovalRequest
Event (1) ──< (N) TicketSale
Guest (N) ──< (1) Sector
Guest (N) ──< (1) User (promoter)
Guest (N) ──< (1) User (validator)
ApprovalRequest (N) ──< (1) Guest
ApprovalRequest (N) ──< (1) User (requester)
ApprovalRequest (N) ──< (1) User (reviewer)
```

---

## 4. Fluxos Principais

### 4.1 Fluxo: Check-in via QR Code

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Validator lê QR Code (qr_token)                        │
├─────────────────────────────────────────────────────────────┤
│ 2. GuestService::checkinByQrToken($qrToken, $validator)  │
│    ├─ Verifica permissão do validator                      │
│    ├─ Busca guest pelo qr_token                            │
│    ├─ Verifica se já checkado                              │
│    └─ Atualiza is_checked_in = true                        │
└─────────────────────────────────────────────────────────────┘
```

**Arquivos Envolvidos**:
- `app/Services/GuestService.php:16-56`

---

### 4.2 Fluxo: Solicitação de Inclusão de Convidado

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Promoter submete formulário                             │
│    ├─ nome, documento, email, setor                       │
├─────────────────────────────────────────────────────────────┤
│ 2. ApprovalRequestService::checkForDuplicates()         │
│    ├─ Verifica documento duplicado (ERRO)                 │
│    └─ Verifica nome duplicado (AVISO)                     │
├─────────────────────────────────────────────────────────────┤
│ 3. ApprovalRequestService::createGuestInclusionRequest()│
│    ├─ Cria ApprovalRequest PENDING                        │
│    └─ Notifica admins                                     │
├─────────────────────────────────────────────────────────────┤
│ 4. Admin revisa (approve/reject)                          │
│    ├─ approve(): cria Guest + notifica                    │
│    └─ reject(): apenas atualiza status                   │
└─────────────────────────────────────────────────────────────┘
```

**Arquivos Envolvidos**:
- `app/Services/ApprovalRequestService.php:127-152`
- `app/Services/ApprovalRequestService.php:23-120` (duplicatas)
- `app/Services/ApprovalRequestService.php:191-206` (aprovação)

---

### 4.3 Fluxo: Check-in Emergencial

```
┌─────────────────────────────────────────────────────────────┐
│ 1. Validator submete dados do convidado                    │
│    (sem QR code, dados manuais)                           │
├─────────────────────────────────────────────────────────────┤
│ 2. ApprovalRequestService::createEmergencyCheckinRequest│
│    ├─ Cria ApprovalRequest tipo EMERGENCY_CHECKIN          │
│    └─ Status PENDING                                       │
├─────────────────────────────────────────────────────────────┤
│ 3. Admin APPROVA                                            │
│    ├─ ApprovalRequestService::approve()                  │
│    └─ Cria Guest com is_checked_in = true                  │
└─────────────────────────────────────────────────────────────┘
```

**Nota**: Este fluxo é similar à inclusão normal, mas o guest já entra checkado.

---

## 5. Análise de Riscos

### 5.1 Riscos Identificados

#### 5.1.1 Risco: Validação Duplicada

| Atributo | Valor |
|----------|-------|
| **Tipo** | duplicate-validation |
| **Severidade** | HIGH |
| **Descrição** | Validação de duplicata em múltiplos lugares |

**Locations**:
- `app/Services/ApprovalRequestService.php:33-119` — Verificação completa
- `app/Models/ApprovalRequest.php:223-241` — `findExistingGuest()`
- `app/Models/ApprovalRequest.php:237-256` — `existingGuestInSameSector()`

**Impacto**: Manutenção difícil, potencial inconsistência

**Sugestão**: Centralizar validação em um único serviço/validator

---

#### 5.1.2 Risco: Lógica de Negócio no Model

| Atributo | Valor |
|----------|-------|
| **Tipo** | SRP Violation |
| **Severidade** | MEDIUM |
| **Descrição** | Model ApprovalRequest contém lógica de negócio |

**Locations**:
- `app/Models/ApprovalRequest.php:136-218` — Métodos de validação de estado
  - `canBeReviewed()`, `canBeCancelled()`, `canBeReconsidered()`, `canBeReverted()`
- `app/Models/ApprovalRequest.php:223-256` — Buscas por guests

**Impacto**: Dificuldade de teste, acoplamento

**Sugestão**: Mover lógica para Service/Repository

---

#### 5.1.3 Risco: Falta de Transaction em Operações Críticas

| Atributo | Valor |
|----------|-------|
| **Tipo** | missing-transaction |
| **Severidade** | MEDIUM |
| **Descrição** | Operações com múltiplas寫入 sem transaction |

**Locations**:
- `app/Services/ApprovalRequestService.php:199-205` — `approve()` já tem transaction ✓
- `app/Services/ApprovalRequestService.php:332-363` — `approveWithSectorUpdate()` já tem transaction ✓

**Status**: Operations críticas já estão protegidas

---

#### 5.1.4 Risco: Facade Usage em Services

| Atributo | Valor |
|----------|-------|
| **Tipo** | facade-abuse |
| **Severidade** | LOW |
| **Descrição** | Uso de Facades diretamente nos Services |

**Locations**:
- `app/Services/GuestService.php:19` — `in_array($validator->role, ...)` — Não é facade
- Services usam injeção de dependência corretamente

**Status**: OK — Código segue boas práticas

---

#### 5.1.5 Risco: Acoplamento entre Services

| Atributo | Valor |
|----------|-------|
| **Tipo** | coupling |
| **Severidade** | MEDIUM |
| **Descrição** | ApprovalRequestService depende de GuestSearchService |

**Location**: `app/Services/ApprovalRequestService.php:29`

```php
$searchService = app(GuestSearchService::class);
```

**Impacto**: Dificuldade de mock em testes

**Sugestão**: Injetar via construtor

---

## 6. Análise de Código

### 6.1 ApprovalRequestService — Análise

| Aspecto | Status | Observação |
|---------|--------|-------------|
| SRP | ⚠️ | Service faz muitas coisas (duplicatas + workflow) |
| Tamanho | Grande | 611 linhas, múltiplas responsabilidades |
| Validação | ⚠️ | Duplicada em múltiplos lugares |
| Transactions | ✓ | Uso correto de DB::transaction() |
| Notifications | ✓ | Bem estruturadas |

**Métodos públicos** (15):
1. `checkForDuplicates()` — Verifica duplicatas
2. `createGuestInclusionRequest()` — Cria solicitação
3. `createEmergencyCheckinRequest()` — Cria check-in emerg.
4. `approve()` — Aprova solicitação
5. `approveWithSectorUpdate()` — Aprova com mudança de setor
6. `reject()` — Rejeita solicitação
7. `cancel()` — Cancela solicitação
8. `reconsider()` — Reconsidera rejeição
9. `revert()` — Reverte aprovação
10. `expireRequestsForFinishedEvents()` — Expira automático
11. `expireOldRequests()` — Expira antigo
12. `getPendingCount()` — Contagem
13. `getPendingForEvent()` — Lista pendentes
14. `getRequestsByUser()` — Lista por usuário
15. `canReview()` — Verifica permissão

---

### 6.2 GuestService — Análise

| Aspecto | Status | Observação |
|---------|--------|-------------|
| SRP | ✓ | Service coeso |
| Tamanho | Pequeno | 143 linhas |
| Validação | ✓ | Bem separada |
| Transactions | N/A | Não precisa |

**Métodos públicos** (4):
1. `checkinByQrToken()` — Check-in via QR
2. `canRegisterGuest()` — Verifica permissão
3. `getAuthorizedEvents()` — Lista eventos autorizados
4. `getAuthorizedSectors()` — Lista setores autorizados

---

### 6.3 Models — Análise

| Model | Tamanho | Observações |
|-------|---------|-------------|
| Guest | 87 linhas | Bem estruturado, uso de Observer |
| Event | 112 linhas | Bem estruturado |
| ApprovalRequest | 274 linhas | **Muita lógica de negócio** — precisa refatorar |

---

## 7. Recomendações de Refatoração

### 7.1 Prioridade 1 — Crítica

#### 7.1.1 Extrair DuplicateValidator

**Problema**: Validação de duplicatas em 3 lugares

**Solução**: Criar `DuplicateGuestValidator` único

```php
// Proposta
app/Services/DuplicateGuestValidator.php

class DuplicateGuestValidator
{
    public function check(int $eventId, string $name, ?string $document): ?DuplicateResult;
    public function checkForApprovalRequest(ApprovalRequest $request): ?DuplicateResult;
}
```

**Impacto**: Alto — Reduz manutenção e risco de inconsistência

---

### 7.2 Prioridade 2 — Alta

#### 7.2.1 Mover Lógica do Model para Service

**Problema**: `ApprovalRequest` tem métodos de negócio

**Solução**: Criar `ApprovalRequestPolicy` ou mover métodos

```php
// Proposta: app/Policies/ApprovalRequestPolicy.php
class ApprovalRequestPolicy
{
    public function canBeReviewed(User $user, ApprovalRequest $request): bool;
    public function canBeReverted(User $user, ApprovalRequest $request): bool;
}
```

**Impacto**: Médio — Melhora testabilidade

---

#### 7.2.2 Injeção de Dependência no ApprovalRequestService

**Problema**: `app(GuestSearchService::class)` dentro do método

**Solução**: Injetar via construtor

```php
// Atual
public function checkForDuplicates(...): ?array {
    $searchService = app(GuestSearchService::class);
    // ...
}

// Proposta
public function __construct(
    private GuestSearchService $searchService,
) {}

public function checkForDuplicates(...): ?array {
    // use $this->searchService
}
```

---

### 7.3 Prioridade 3 — Média

#### 7.3.1 Separar ApprovalRequestService

**Problema**: Service faz太多coisas

**Solução**: Dividir em:
- `DuplicateCheckService` — Verificação de duplicatas
- `ApprovalWorkflowService` — Workflow de aprovações

**Impacto**: Médio — Melhora manutenibilidade

---

#### 7.3.2 Adicionar Repository Layer

**Problema**: Models usados diretamente nos Services

**Solução**: Criar repositories para operações complexas

```php
app/Repositories/GuestRepository.php
app/Repositories/ApprovalRequestRepository.php
```

---

### 7.4 Prioridade 4 — Baixa

#### 7.4.1 Queue para Notificações

**Problema**: Notificações síncronas

**Solução**: Usar queue para `notifyAdmins()`

```php
// Atual
$admin->notify(new NewApprovalRequestNotification($request));

// Proposta
NewApprovalRequestNotification::dispatch($request)->onQueue('notifications');
```

---

## 8. Plano de Migração Sugerido

### Fase 1: Correções Críticas (1 semana)

| Task | Esforço | Risco Endereçado |
|------|---------|------------------|
| Extrair DuplicateGuestValidator | Medium | duplicate-validation |
| Injetar GuestSearchService | Low | coupling |

### Fase 2: Refatoração de Domínio (2-3 semanas)

| Task | Esforço |
|------|---------|
| Mover lógica de ApprovalRequest para Policy | Medium |
| Criar ApprovalRequestRepository | Medium |
| Separar ApprovalRequestService | High |

### Fase 3: Melhorias (2 semanas)

| Task | Esforço |
|------|---------|
| Adicionar queue para notificações | Low |
| Adicionar testes unitários | Medium |

---

## 9. Métricas do Sistema

### 9.1 Código

| Métrica | Valor |
|---------|-------|
| Total Models | 9 |
| Total Services | 5 |
| Total Controllers | — |
| Total Migrations | 21 |

### 9.2 Complexidade

| Service | Linhas | Métodos |
|---------|--------|---------|
| ApprovalRequestService | 611 | 15 |
| GuestService | 143 | 4 |
| GuestSearchService | ~300 | 8 |
| DocumentValidationService | ~200 | 4 |

---

## 10. Conclusão

O sistema guest-list-pro apresenta uma estrutura geral sólida com:

**Pontos Fortes**:
- Uso correto de Transactions
- Separação razoável de Services
-Boa estrutura de Models com relationships
- Workflow de aprovações bem definido

**Pontos de Atenção**:
- Validação de duplicatas duplicada
- Muita lógica de negócio em Models
- Acoplamento entre services
- Service ApprovalRequestService muito grande

**Recomendação**: Seguir o plano de migração sugerido, priorizando a extração do DuplicateGuestValidator para reduzir riscos de inconsistência.

---

*Documento gerado com base na análise da pipeline ia-refactor*
*Última atualização: 2026-04-08*
