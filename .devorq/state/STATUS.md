# Status do Projeto — guest-list-pro

**Última atualização:** 2026-04-20
**Stack:** Laravel 12 + Filament v4 + Livewire v4
**Orquestrador:** DEVORQ v2.1

---

## Resumo Executivo

**Refatoração QR Code → guest_token COMPLETA.** Sistema removido, renomeado e testado com sucesso.

---

## Marcos Alcançados ✅

### QR Code Removal (SPEC-0006)
- [x] Remoção completa do QR code system
- [x] Renomeação `qr_token` → `guest_token` via migration
- [x] Atualização de 18 arquivos, 4 deletados
- [x] `Notification::sendTo()` → `sendToDatabase()`
- [x] Commit: `41be79b refactor: remove QR code system and migrate to DEVORQ orchestrator`

### E2E Tests — 3 Pendências Corrigidas
- [x] TC-VALIDATOR-003: Guest count 0 → **5 guests** (EventAssignment adicionado)
- [x] TC-PROMOTER-001: Quota 0/0 → **regex corrigido**
- [x] TC-TICKETPRICING-001: Link não encontrado → **navegação direta**

### DEVORQ Orchestrator Migration
- [x] `.aidev` → `.devorq` v2.1
- [x] AGENTS.md atualizado
- [x] docs/CONSOLIDATED/INDEX.md atualizado
- [x] ANTIGRAVITY.md atualizado

### Lições Aprendidas (LL-024)
- [x] Documentação completa de E2E/Seeder fixes
- [x] 24 lições catalogadas no total

---

## Commits Recentes

| Commit | Descrição |
|--------|-----------|
| `0ba6870` | docs: add LL-024 e2e-tests-and-seeder-fixes lesson learned |
| `94e1b3c` | fix: resolve validator guest list test and improve E2E seeder |
| `d39f014` | fix: resolve 3 smoke test issues |
| `41be79b` | refactor: remove QR code system and migrate to DEVORQ orchestrator |

---

## Status dos Testes

### Unit Tests
```
✓ 29 tests passing (70 assertions)
✓ Duration: ~4s
```

### E2E Smoke Tests (27 tests)
```
✓ TC-AUTH-001 a TC-AUTH-007 (7 tests) — Authentication
✓ TC-ADMIN-001 a TC-ADMIN-007 (7 tests) — Admin Panel
✓ TC-BILHETERIA-001 a TC-BILHETERIA-004 (4 tests) — Bilheteria Panel
✓ TC-VALIDATOR-001 a TC-VALIDATOR-004 (4 tests) — Validator Panel
✓ TC-PROMOTER-001 a TC-PROMOTER-004 (4 tests) — Promoter Panel
✓ TC-TICKETPRICING-001 (1 test) — Ticket Pricing
```

---

## Arquitetura Atual

### Resources Filament
```
Admin:
├── Dashboard
├── Guests
├── Events
├── Approvals
├── TicketType (TicketTypeResource) ← adicionada policy
├── Sectors
├── Users
├── PromoterPermissions (EventAssignments)
└── Audits

Bilheteria:
├── Dashboard
├── Sales (TicketSales)
└── CreateSale

Validator:
├── Dashboard
├── Guests (Check-in list)
└── MyRequests

Promoter:
├── Dashboard
├── Guests
└── QuotaOverview
```

### Middleware
- `EnsureEventSelected` — Panels: promoter, validator, bilheteria
- Redireciona para `select-event` se `session('selected_event_id')` não existe

---

## Pendências Técnicas

### Bugs Conhecidos (não críticos)
- LSP errors em `GuestResource.php`, `GuestsTable.php`, `PromoterQuotaOverview.php` — métodos `.user()` e `.id()` indefinidos (provavelmente false positives do LSP com Filament)

### Melhorias Identificadas
- Testes E2E podem se beneficiar de page objects mais robustos para event selection
- PromoterQuotaOverview widget formato de display pode mudar entre versões

---

## Database Schema

### Tabelas Principais
- `users` — com role (ADMIN, PROMOTER, VALIDATOR, BILHETERIA)
- `events` — com EventStatus (DRAFT, ACTIVE, FINISHED, CANCELLED)
- `sectors` — vinculados a eventos
- `event_assignments` — permissão de usuários por evento/role
- `guests` — com guest_token (ULID), document_type (CPF/RG/PASSPORT)
- `ticket_sales` — vendas de bilheteria
- `ticket_types` — tipos de ingresso por setor
- `approval_requests` — solicitações pendentes

### Índices de Performance
- `guests_event_id_document_unique` — unique constraint
- Performance indexes em `guests.normalized_search`, `document_normalized`
- Cache de eventos por promoter

---

## Fluxo de Check-in

```
1. Validator acessa /validator/guests
2. EnsureEventSelected middleware verifica session
3. Se não há evento → redirect para select-event
4. EventSelectorGrid mostra eventos do user (via EventAssignment)
5. User seleciona evento → session('selected_event_id') setado
6. Guest list carrega com filtros de evento
7. Check-in via documento ou busca
```

---

## Próximos Passos Sugeridos

1. **Investigar LSP errors** — verificar se são false positives ou bugs reais
2. **Testes de integração** — cobrir mais fluxos de approval/check-in
3. **Performance** — se necessário, adicionar cache para EventSelectorGrid
4. **Documentação** — SPEC-0006 precisa ser referenciada em docs/

---

*Status gerado automaticamente após correção de E2E tests*
*Para validar lições: `devorq lessons validate`*