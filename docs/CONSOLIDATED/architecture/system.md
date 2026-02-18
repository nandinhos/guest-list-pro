# Arquitetura - Guest List Pro

> Documentação técnica da arquitetura do sistema para referência dos agentes de IA.

---

## 1. Visão Geral

```
┌─────────────────────────────────────────────────────────────┐
│                      FILAMENT PANELS                        │
├──────────┬──────────┬──────────────┬───────────────────────┤
│  Admin   │ Promoter │  Validator   │      Bilheteria       │
│ (indigo) │ (purple) │  (emerald)  │       (amber)        │
└────┬─────┴────┬─────┴──────┬───────┴───────────┬───────────┘
     │          │            │                   │
     └──────────┴────────────┴───────────────────┘
                        │
              ┌─────────▼─────────┐
              │   SERVICE LAYER   │
              ├───────────────────┤
              │ ApprovalRequest   │
              │ Guest             │
              │ GuestSearch       │
              │ DocumentValidation│
              └─────────┬─────────┘
                        │
              ┌─────────▼─────────┐
              │   MODEL LAYER     │
              ├───────────────────┤
              │ + Observers       │
              │ + Activity Log    │
              └─────────┬─────────┘
                        │
              ┌─────────▼─────────┐
              │     DATABASE      │
              │  SQLite / MySQL   │
              └───────────────────┘
```

---

## 2. Painéis Filament

| Painel | Path | Guard | Role | Cor | Descrição |
|--------|------|-------|------|-----|-----------|
| Admin | `/admin` | web | ADMIN | Indigo | Gestão completa |
| Promoter | `/promoter` | web | PROMOTER | Purple | Gestão de convidados |
| Validator | `/validator` | web | VALIDATOR | Emerald | Check-in |
| Bilheteria | `/bilheteria` | web | BILHETERIA | Amber | Vendas |

### Estrutura de Cada Painel

```
app/Filament/{Panel}/
├── Pages/
│   └── Dashboard.php
├── Resources/
│   ├── {Resource}Resource.php
│   └── {Resource}Resource/
│       └── Pages/
│           ├── Create{Resource}.php
│           ├── Edit{Resource}.php
│           └── List{Resources}.php
├── Widgets/
│   └── {Widget}Widget.php
└── {Panel}PanelProvider.php
```

---

## 3. Models e Relacionamentos

### Diagrama ER Simplificado

```
┌─────────┐       ┌─────────┐       ┌─────────┐
│  User   │──────│  Guest  │>──────│  Event  │
└─────────┘       └────┬────┘       └────┬────┘
     │                 │                  │
     │            ┌────▼────┐        ┌────▼────┐
     │            │ Checkin │        │ Sector  │
     │            │ Attempt │        └─────────┘
     │            └─────────┘
     │
┌────▼────────────┐       ┌─────────────┐
│ ApprovalRequest │───────│ TicketSale  │
└─────────────────┘       └─────────────┘
```

### Models Principais

| Model | Tabela | Descrição |
|-------|--------|-----------|
| User | users | Usuários do sistema |
| Event | events | Eventos gerenciados |
| Sector | sectors | Setores de eventos |
| Guest | guests | Convidados |
| ApprovalRequest | approval_requests | Solicitações de aprovação |
| CheckinAttempt | checkin_attempts | Tentativas de check-in |
| TicketSale | ticket_sales | Vendas de ingressos |
| EventAssignment | event_assignments | Atribuição user-evento |

### Campos Críticos

**Guest:**
- `name_normalized` - Nome sem acentos (busca)
- `document_normalized` - Documento sem pontuação (unicidade)
- `checked_in_at` - Timestamp do check-in
- `checked_in_by` - User que fez check-in

**ApprovalRequest:**
- `type` - Enum: GuestInclusion, EmergencyCheckin
- `status` - Enum: Pending, Approved, Rejected
- `expires_at` - Expiração automática
- `requester_id` - Quem solicitou
- `approver_id` - Quem aprovou/rejeitou

---

## 4. Services Layer

### ApprovalRequestService

**Responsabilidade:** Toda lógica de aprovações

```php
class ApprovalRequestService
{
    public function create(array $data): ApprovalRequest;
    public function approve(ApprovalRequest $request, User $approver): void;
    public function reject(ApprovalRequest $request, User $approver, string $reason): void;
    public function checkForDuplicates(string $document, int $eventId): ?Guest;
    public function isExpired(ApprovalRequest $request): bool;
    public function getStats(int $eventId): array;
}
```

**Localização:** `app/Services/ApprovalRequestService.php`
**Linhas:** ~576 (candidato a refatoração)

### GuestService

**Responsabilidade:** Verificações e permissões de guests

```php
class GuestService
{
    public function canUserManage(User $user, Guest $guest): bool;
    public function canUserCheckIn(User $user, Guest $guest): bool;
    public function getGuestsForUser(User $user, int $eventId): Collection;
}
```

**Localização:** `app/Services/GuestService.php`

### GuestSearchService

**Responsabilidade:** Busca otimizada de guests

```php
class GuestSearchService
{
    public function search(string $query, int $eventId): Collection;
    public function findByDocument(string $document, int $eventId): ?Guest;
    public function findSimilar(string $name, int $eventId, float $threshold = 0.8): Collection;
}
```

**Localização:** `app/Services/GuestSearchService.php`

### DocumentValidationService

**Responsabilidade:** Validação de documentos brasileiros

```php
class DocumentValidationService
{
    public function validateCPF(string $cpf): bool;
    public function validateRG(string $rg): bool;
    public function normalize(string $document): string;
    public function getDocumentType(string $document): ?string;
}
```

**Localização:** `app/Services/DocumentValidationService.php`

---

## 5. Enums

| Enum | Valores | Uso |
|------|---------|-----|
| `UserRole` | Admin, Promoter, Validator, Bilheteria | Roles de usuário |
| `ApprovalStatus` | Pending, Approved, Rejected | Status de aprovação |
| `ApprovalType` | GuestInclusion, EmergencyCheckin | Tipos de solicitação |
| `CheckinResult` | Success, Duplicate, NotFound, Suspicious | Resultado de check-in |
| `PaymentMethod` | Cash, Credit, Debit, Pix | Métodos de pagamento |

**Localização:** `app/Enums/`

---

## 6. Observers

### GuestObserver

- Log de criação/atualização/deleção
- Normalização de nome e documento
- Activity logging via Spatie

### ApprovalRequestObserver

- Log de mudanças de status
- Envio de notificações

**Localização:** `app/Observers/`

---

## 7. Widgets por Painel

### Admin

| Widget | Descrição |
|--------|-----------|
| StatsOverview | Métricas gerais |
| RecentGuests | Últimos guests |
| ApprovalsPending | Aprovações pendentes |

### Promoter

| Widget | Descrição |
|--------|-----------|
| PromoterStats | Métricas do promoter |
| PromoterPerformanceChart | Gráfico de performance |
| MyGuestsTable | Meus convidados |

### Validator

| Widget | Descrição |
|--------|-----------|
| CheckinStats | Métricas de check-in |
| SuspiciousCheckins | Tentativas suspeitas |
| RecentCheckins | Check-ins recentes |

### Bilheteria

| Widget | Descrição |
|--------|-----------|
| SalesStats | Métricas de vendas |
| TodaySales | Vendas de hoje |
| CashierBalance | Saldo do caixa |

---

## 8. Fluxos Críticos

### Fluxo de Inclusão de Guest
1. Promoter cria Guest → GuestObserver normaliza dados
2. Verifica duplicidade por documento
   - Duplicado: Bloqueia criação
   - Único: Salva guest
3. Admin visualiza pending → Aprova/rejeita → Notifica requester

### Fluxo de Check-in
1. Validator busca guest → GuestSearchService.search()
2. Validator confirma check-in
   - Verifica se já fez check-in
   - Registra CheckinAttempt (success/duplicate)

### Fluxo de Aprovação
1. Promoter cria ApprovalRequest (GuestInclusion/EmergencyCheckin)
2. Admin revisa → ApprovalRequestService.approve()/reject()
3. Expiração automática via Job

---

## 9. Gargalos Conhecidos

### Performance

| Componente | Problema | Solução |
|------------|----------|---------|
| CheckinAttempt | Sem índices | Criar índices |
| GuestsImport | Síncrono | Usar Queue |
| GuestSearchService | Carrega tudo | Paginação |
| PromoterPerformanceChart | 2x queries | Cache |
| SuspiciousCheckins | N+1 | Eager loading |

### Código

| Componente | Problema | Solução |
|------------|----------|---------|
| ApprovalRequestService | 576 linhas | Extrair classes |
| ImportGuests | Duplicado | Extrair base |
| GuestForm | Inconsistente | Unificar |
| checkForDuplicates | 4 queries | Otimizar |

---

## 10. Decisões Arquiteturais (ADR)

### ADR-001: SPA Desabilitado
**Contexto:** Chart.js e Alpine.js apresentavam bugs com SPA habilitado.
**Decisão:** Desabilitar `->spa()` em todos os painéis.
**Consequência:** Navegação full-page, mas sem bugs de JS.

### ADR-002: SQLite em Desenvolvimento
**Contexto:** Simplicidade para desenvolvimento local.
**Decisão:** SQLite para dev, MySQL para produção.
**Consequência:** Algumas features MySQL não disponíveis em dev.

### ADR-003: Normalização de Documentos
**Contexto:** Comparação de documentos com diferentes formatações.
**Decisão:** Campos `*_normalized` para busca e unicidade.
**Consequência:** Observer deve manter sincronizado.

### ADR-004: Activity Logging
**Contexto:** Auditoria de ações no sistema.
**Decisão:** Usar Spatie Activity Log.
**Consequência:** Logs em todas as operações críticas.

---

**Última atualização:** 2026-02-18
