# 🔄 HANDOVER — guest-list-pro

**Para:** Agente de IA autônomo
**De:** Sessão anterior (2026-04-20 17:45 BRT)
**Objetivo:** Contribuir no projeto guest-list-pro com autonomia e seguimento das práticas DEVORQ

---

## 🎯 Contexto do Projeto

### O que é o guest-list-pro?
Sistema de gestão de convidados para eventos/festas com:
- **Controle de duplicidade** — guests únicos por documento+evento
- **Sistema de aprovações** — approval requests pendentes
- **Bilheteria** — vendas de tickets
- **Portal do validator** — check-in de convidados
- **Portal do promoter** — cadastro de convidados com quota

### Stack Tecnológica
| Componente | Tecnologia |
|------------|------------|
| Backend | Laravel 12 (PHP 8.4) |
| Admin Panel | Filament v4 |
| Frontend | Livewire v4 + Alpine.js |
| Frontend CSS | Tailwind CSS |
| Database | MySQL (Docker) |
| Tests E2E | Playwright |
| Orquestrador | DEVORQ v2.1 |

---

## 📁 Estrutura de Diretórios (Completa)

```
guest-list-pro/
├── app/
│   ├── Enums/                          # UserRole, DocumentType, EventStatus, RequestStatus
│   ├── Filament/
│   │   ├── Admin/                      # Painel Admin
│   │   │   ├── Resources/
│   │   │   │   ├── Guests/
│   │   │   │   │   ├── Pages/
│   │   │   │   │   ├── Schemas/
│   │   │   │   │   └── Tables/
│   │   │   │   ├── TicketType/         # RECENTLY ADDED - verificar policy
│   │   │   │   ├── Event/
│   │   │   │   ├── Sector/
│   │   │   │   ├── User/
│   │   │   │   └── PromoterPermission/
│   │   │   └── Widgets/
│   │   ├── Bilheteria/                 # Painel Bilheteria
│   │   │   └── Resources/TicketSale/
│   │   ├── Validator/                  # Painel Validator (check-in)
│   │   │   └── Resources/Guests/
│   │   └── Promoter/                  # Painel Promoter
│   │       └── Resources/Guests/
│   ├── Http/
│   │   └── Middleware/
│   │       └── EnsureEventSelected.php  # Redirect para select-event se não há evento na session
│   ├── Livewire/
│   │   └── EventSelectorGrid.php       # Grid de seleção de evento (Livewire component)
│   ├── Models/
│   │   ├── Guest.php                   # guest_token (ULID), document_type, is_checked_in
│   │   ├── Event.php                   # EventStatus enum
│   │   ├── EventAssignment.php         # Permissão user/evento/role (substitui PromoterPermission)
│   │   ├── Sector.php
│   │   ├── TicketSale.php
│   │   ├── TicketType.php
│   │   ├── User.php                    # getAssignedEvents() consulta event_assignments
│   │   └── ApprovalRequest.php
│   ├── Observers/
│   │   ├── GuestObserver.php           # normalizeDocument, validateUniqueDocumentInEvent, generateGuestToken
│   │   └── TicketSaleObserver.php      # Notification via sendToDatabase (não sendTo anymore!)
│   ├── Policies/
│   │   ├── GuestPolicy.php
│   │   ├── TicketTypePolicy.php        # ADMIN e BILHETERIA apenas
│   │   └── ...
│   ├── Providers/
│   │   └── Filament/AdminPanelProvider.php  # Auto-discovers resources
│   └── Services/
│       ├── GuestService.php
│       └── GuestValidationService.php
├── config/
│   └── filament.php
├── database/
│   ├── migrations/
│   │   ├── 2026_04_20_014917_rename_qr_token_to_guest_token_in_guests_table.php  # QR → guest_token
│   │   └── ... (outras migrations)
│   └── seeders/
│       ├── DatabaseSeeder.php          # Chama UserSeeder + EventSimulationSeeder
│       ├── UserSeeder.php              # admin@guestlist.pro, promoter@guestlist.pro, validator@guestlist.pro, bilheteria@guestlist.pro
│       ├── E2ETestSeeder.php            # Para testes - credentials em email@email.com, EVENTO DIFERENTE
│       ├── EventSimulationSeeder.php   # Summer Festival 2026, Night Club Live, Retro Party 2025
│       └── ShowcaseTestSeeder.php      # Demo completo com guests
├── e2e/
│   ├── config/
│   │   └── wait-times.ts
│   ├── helpers/
│   │   └── livewire-helpers.ts
│   ├── pages/
│   │   ├── LoginPage.ts
│   │   ├── AdminPages.ts
│   │   ├── BilheteriaPages.ts
│   │   ├── ValidatorPages.ts           # CRÍTICO: goto() precisa lidar com event selection overlay
│   │   ├── PromoterPages.ts
│   │   └── ...
│   ├── *.spec.ts                       # Playwright tests
│   └── playwright.config.ts
├── docs/
│   ├── SPECS/implemented/
│   │   └── SPEC-0006-20-04-2026-qrcode-removal-and-guest-token-refactoring.md
│   └── report_e2e/
│       └── AUDIT-FULL-2026-04-20.md
├── resources/
│   └── views/
│       ├── filament/
│       │   └── pages/
│       │       └── select-event.blade.php   # Usa Livewire:event-selector-grid
│       └── livewire/
│           └── event-selector-grid.blade.php
├── routes/
│   └── api.php                         # Sem rota de checkin/qr mais (removida)
└── .devorq/
    ├── version                         # 2.1
    ├── rules/project.md
    ├── state/
    │   ├── STATUS.md                   # STATUS ATUAL - VER SEMPRE ANTES DE COMEÇAR
    │   ├── session.json
    │   └── lessons-learned/
    │       ├── _INDEX.md               # ÍNDICE com 24 lições
    │       └── 2026-04-20-e2e-tests-and-seeder-fixes.md  # ÚLTIMA LIÇÃO
    └── plans/
```

---

## 🔧 Comandos Essenciais (NUNCA ESQUECER)

```bash
# Docker/Sail - OBRIGATÓRIO
alias sail='vendor/bin/sail'

# Tests unitários
vendor/bin/sail test tests/Unit --no-coverage

# Tests E2E (Playwright) - pode demorar
npx playwright test e2e/smoke-tests.spec.ts

# Test específico
npx playwright test e2e/smoke-tests.spec.ts --grep="TC-VALIDATOR-003"

# Reset database completo
vendor/bin/sail artisan migrate:fresh --seed

# Seed específico (depois de migrate)
vendor/bin/sail artisan db:seed --class=E2ETestSeeder

# Limpar cache
vendor/bin/sail artisan cache:clear && vendor/bin/sail artisan config:clear

# Ver logs
vendor/bin/sail logs -f
```

---

## 📖 Regras do Projeto (OBRIGATÓRIAS)

### AGENTS.md — Início de Cada Sessão

```
1. SEMPRE use vendor/bin/sail para comandos (não php artisan direto!)
2. SEMPRE use TDD (RED -> GREEN -> REFACTOR)
3. Documentação vem antes do código
4. Sempre valide antes de implementar
```

### DEVORQ Workflow

```
1. Ler .devorq/rules/project.md
2. Consultar lições aprendidas relevantes (.devorq/state/lessons-learned/)
3. Criar SPEC ou plano antes de implementar
4. Implementar com TDD
5. Documentar nova lição se discover algo novo
6. Commitar com mensagem descritiva
7. Atualizar STATUS.md se necessário
```

### Mensagens de Commit

```
fix: corrigir bug no guest check-in
feat: adicionar novo tipo de relatório
docs: atualizar documentação de API
refactor: simplificar lógica de validação
test: adicionar testes para fluxo de aprovação
```

---

## ✅ Estado Atual do Projeto (2026-04-20)

### Completo
| Item | Status | Commit |
|------|--------|--------|
| QR Code system | ✅ REMOVIDO | `41be79b` |
| `qr_token` → `guest_token` | ✅ Migration aplicada | `41be79b` |
| DEVORQ orchestrator | ✅ Configurado | `41be79b` |
| E2E Test: TC-VALIDATOR-003 | ✅ Corrigido (5 guests) | `94e1b3c` |
| E2E Test: TC-PROMOTER-001 | ✅ Regex corrigido | `d39f014` |
| E2E Test: TC-TICKETPRICING-001 | ✅ Navegação direta | `d39f014` |
| E2E Seeder: EventAssignment | ✅ Adicionado | `94e1b3c` |
| Lições aprendidas | ✅ 24 catalogadas | `0ba6870` |
| STATUS.md | ✅ Criado | `2b5a049` |

### Métricas de Testes
```
Unit Tests:   29 passing (70 assertions, ~4s)
E2E Smoke:    27 passing (autenticação, admin, bilheteria, validator, promoter, ticket pricing)
```

### Pendências Técnicas (Não-Bloqueantes)
| Item | Severidade | Nota |
|------|------------|------|
| LSP errors em Filament resources | Baixa | False positives do LSP com syntax Filament |
| PromoterQuotaOverview pode mudar formato | Baixa | Widget depende de implementação |

---

## 🧠 Lições Aprendidas (Highlights)

### LL-024: E2E Tests e Seeder (MAIS IMPORTANTE)
**Arquivo:** `.devorq/state/lessons-learned/2026-04-20-e2e-tests-and-seeder-fixes.md`

Pontos críticos:

1. **E2E Seeder NÃO cria EventAssignment por padrão**
   - Seeder cria users, eventos, setores, guests
   - MAS não cria `EventAssignment` para os users
   - Resultado: Validator não vê eventos no selector → redirect loop

2. **Como criar EventAssignment:**
```php
use App\Models\EventAssignment;
use App\Enums\UserRole;

EventAssignment::updateOrCreate(
    ['user_id' => $validator->id, 'event_id' => $event->id],
    ['role' => UserRole::VALIDATOR, 'guest_limit' => 0]
);
```

3. **Credentials não batem**
   - UserSeeder usa: `admin@guestlist.pro`, `validator@guestlist.pro`
   - E2ETestSeeder criava: `admin@admin.com`, `validator@validator.com`
   - **CORRIGIDO:** E2ETestSeeder agora usa `validador@guestlist.pro`

4. **Tabela `guests` não tem coluna `status`**
   - Guest::create() não pode receber `status => 'confirmed'`

5. **URLs do Filament para resources compostos**
   - ERRADO: `/admin/ticket-types`
   - CORRETO: `/admin/ticket-type/ticket-types` (singular/plural)

6. **Regex de parsing de widget**
   - Widget exibe "X restantes" ou "used/total"
   - Regex deve capturar ambos: `/(\d+)\s*(?:\/\s*(\d+)|restantes)/`

### LL-023: UserFactory sem role
**Arquivo:** `.devorq/state/lessons-learned/2026-04-20-userfactory-role-auth-bug.md`

```php
// ERRADO - factory sem role
$user = User::factory()->create(); // role = null → 403 em tests

// CORRETO - factory com role
$user = User::factory()->admin()->create(); // role = ADMIN
```

---

## 🔍 Como Investigar Issues

### Framework Systematic Debugging

```
1. IDENTIFICAR O SYMPTOM
   - O que está quebrado? O que deveria acontecer?

2. ISOLAR A CAUSA RAIZ (não o symptom!)
   - O erro no log é a causa ou efeito?
   - Testar em isolamento

3. FORMAR HIPÓTESE
   - O que eu acho que está causando?

4. TESTAR
   - Adicionar logs, breakpoints, testes

5. CORRIGIR OU DESCARTAR
   - Se não ajudar,voltar ao passo 2
```

### Debugging Checklist para E2E Tests

```typescript
// Ao investigar, verificar:
1. Seeder criou users/guests corretamente?
   // Roles: ADMIN, PROMOTER, VALIDATOR, BILHETERIA
   // Credentials devem bater com TEST_USERS no spec

2. EventAssignment existe para o user?
   // Sem isso, EventSelectorGrid não mostra eventos

3. URL do resource está correta?
   // Check Filament naming: ticket-type/ticket-types (não ticket-types)

4. Regex de parsing captura formato real do widget?
   // Widget pode mostrar "44 restantes" ou "12/50"

5. Event status é ACTIVE?
   // getAssignedEvents() filtra por EventStatus::ACTIVE

6. Session tem selected_event_id?
   // EnsureEventSelected middleware verifica isso
```

---

## 🔗 Recursos e Links

### Arquivos de Referência
| Arquivo | Propósito |
|---------|-----------|
| `.devorq/state/STATUS.md` | Status completo do projeto |
| `.devorq/state/lessons-learned/_INDEX.md` | Índice de 24 lições |
| `docs/SPECS/implemented/SPEC-0006-*.md` | Especificação QR→guest_token |
| `app/Http/Middleware/EnsureEventSelected.php` | Lógica de seleção de evento |
| `app/Livewire/EventSelectorGrid.php` | Component que lista eventos |
| `app/Models/User.php` | Método getAssignedEvents() |

### Models e Relaciones

```php
// User → EventAssignment → Event (getAssignedEvents)
User 1 ──< EventAssignment >── Event

// Guest pertence a Event + Sector + Promoter(User)
Guest → Event
Guest → Sector
Guest → User (promoter_id)

// EventAssignment define permissões
EventAssignment: user_id, event_id, role, guest_limit, sector_id (nullable)
```

### Fluxo de Check-in (Validator)

```
1. Validator acessa /validator/guests
2. Middleware EnsureEventSelected:
   - Verifica session('selected_event_id')
   - Se não existe → redirect para /validator/pages/select-event
3. SelectEvent page carrega EventSelectorGrid
4. EventSelectorGrid usa User::getAssignedEvents():
   - Consulta event_assignments WHERE user_id = auth()->id
   - Retorna apenas eventos com EventStatus::ACTIVE
5. User clica em evento → session('selected_event_id') = eventId
6. Redireciona para /validator (dashboard)
7. Validator pode navegar para /validator/guests
8. Guest list mostra apenas guests do evento selecionado
```

---

## 🚀 Próximos Passos Sugeridos para Agente

### Prioridade Alta
1. **Validar LSP errors** — 确定 se são bugs reais ou false positives
   - Files com errors: GuestResource.php, GuestsTable.php, PromoterQuotaOverview.php
   - Testar se código funciona apesar do LSP

2. **Testes de integração** — cobrir mais fluxos:
   - Fluxo completo: Promoter cria guest → Bilheteria vende → Validator check-in
   - Approval request: criar, aprovar, rejeitar

### Prioridade Média
3. **Performance review** — EventSelectorGrid pode se beneficiar de cache
4. **Documentação de APIs** — Gerar documentação OpenAPI/Swagger

### Prioridade Baixa
5. **Cleanup de código legado** — Remover comentários obsoletos
6. **Testes de stress** — Simular muitos concurrent users

---

## 📝 Checklist para Novas Features

```markdown
- [ ] Verificar se SPEC já existe em .devorq/plans/
- [ ] Consultar lições aprendidas relevantes
- [ ] Criar SPEC se não existir
- [ ] Implementar com TDD
- [ ] Commitar com mensagem descritiva
- [ ] Atualizar STATUS.md se necessário
- [ ] Documentar nova lição aprendida se discovering algo novo
```

---

## ⚠️ Armadilhas Comuns (Evitar)

1. **Não usar `php artisan` direto** — usar `vendor/bin/sail artisan`
2. **Não assumir que seeder criou EventAssignment** — verificar sempre
3. **Não usar `status` em Guest::create()** — tabela não tem essa coluna
4. **Não assumir URL do Filament** — resources compostos usam path estranho
5. **Não commitar sem rodar testes** — verificar se não quebrou nada

---

## 📞 Comunicação e Reporting

- **Idioma:** Português do Brasil (preferencial) ou English
- **Commits:** Mensagens descritivas seguindoConventional Commits
- **STATUS.md:** Atualizar após mudanças significativas
- **Lições:** Documentar se discovering algo novo que outros deveriam saber

---

## 🧬 DNA do Projeto

### Valores
- **TDD** — sempre escrever testes antes
- **Documentação** — antes de codar, documentar
- **Idempotência** — seeders podem rodar múltiplas vezes
- **Performance** — índices, cache, queries otimizadas

### Code Smells a Evitar
- Métodos longos (refatorar)
- Nomes não descritivos
- Comentários desatualizados
- Duplicação de lógica

### Patterns Recomendados
- Policy classes para autorização (GuestPolicy, TicketTypePolicy)
- Form Request classes para validação
- Service classes para lógica de negócio
- Observer classes para side effects (GuestObserver, TicketSaleObserver)

---

## 🎓 Onde Aprendi Mais (Lições Mais Valiosas)

| LL # | Título | Por Que Importa |
|------|--------|-----------------|
| LL-024 | E2E Tests e Seeder Fixes | EventAssignment é crucial para permissions |
| LL-023 | UserFactory sem role | Tests falham silenciosamente sem role |
| LL-016 | Notifications não suportam Actions | breaking change do Laravel |
| LL-015 | ULID para QR Codes | performance em check-in massivo |

---

*Atenção: Follow DEVORQ rules, use TDD, document lessons learned.*
*Última atualização: 2026-04-20 17:45 BRT*
*Commit do HANDOVER: inclua no commit message "HANDOVER.md updated"*
*Para perguntas: releia STATUS.md e lições aprendidas antes de perguntar.*