# HANDOVER.md - Guest List Pro

> **Para agentes autônomos:** Leia este documento completamente antes de iniciar qualquer tarefa.

**Última atualização:** 2026-04-21
**Versão do Projeto:** 2.1.1
**Stack:** Laravel 12 + Filament 4 + Livewire 3

---

## 1. VISÃO GERAL DO PROJETO

Sistema de gestão de convidados com controle de duplicidade, aprovações e bilheteria.

### 1.1 Objetivos
- Controle de acesso a eventos por setores
- Gestão de promotores e validadores
- Sistema de aprovações para convidados duplicados
- Bilheteria com vendas de ingressos
- Relatórios e dashboards em tempo real

### 1.2 Painéis do Sistema

| Painel | Path | Descrição |
|--------|------|-----------|
| Admin | `/admin` | Gestão completa: eventos, setores, usuários, tickets, relatórios |
| Bilheteria | `/bilheteria` | Vendas de ingressos com seleção por setor/tipo |
| Promoter | `/promoter` | Cadastro de convidados com quota limitada |
| Validator | `/validator` | Check-in de convidados |

---

## 2. ARQUITETURA ATUAL

### 2.1 Modelos Principais

```
Event
├── Sector (1:N)
├── TicketType (1:N)
│   └── TicketTypeSector (N:N com Sector - preços por setor)
├── Guest (1:N)
├── TicketSale (1:N)
└── EventAssignment (N:N com User - permissões)

User
├── EventAssignment (1:N) - permissões por evento
├── Guest (1:N) - convidados cadastrados
└── TicketSale (1:N) - vendas realizadas
```

### 2.2 Ticket Pricing - Arquitetura Refatorada (2026-04-21)

**ANTES:** TicketType tinha campo `price` (preço único global)

**AGORA:**
- TicketType é apenas um "molde" (ex: "1º Lote", "2º Lote")
- Preço existe apenas na combinação **TicketType + Setor** via `ticket_type_sector`
- Campo `is_visible` controla quais tipos aparecem na bilheteria

```
TIPO: "Pista Premium"
├── PISTA     → R$ 150,00
├── VIP       → R$ 250,00
├── CAMAROTE  → R$ 400,00
└── BACKSTAGE → R$ 300,00
```

**Arquivos da refatoração:**
- `app/Models/TicketType.php` - sem `price`, com `is_visible`
- `app/Services/TicketSaleService.php` - lança exceção se preço não configurado
- `app/Filament/Resources/TicketType/Schemas/TicketTypeForm.php` - setor_prices obrigatório
- `app/Filament/Bilheteria/Resources/TicketSales/Schemas/TicketSaleForm.php` - setor primeiro

### 2.3 Fluxo na Bilheteria (Atual)

```
1. Selecionar SETOR primeiro
2. Listar TicketTypes que:
   - is_visible = true
   - Têm configuração em ticket_type_sector para o setor
3. Selecionar TIPO
4. Preço aparece automaticamente
```

---

## 3. STACK TÉCNICA

### 3.1 Tecnologias
- **Backend:** Laravel 12, PHP 8.4
- **Frontend:** Filament 4, Livewire 3, Alpine.js, Tailwind CSS
- **Database:** MySQL (produção), SQLite (dev), PostgreSQL (suportado)
- **Cache:** Laravel Cache (database driver)
- **Auth:** Filament built-in

### 3.2 Estrutura de Diretórios

```
app/
├── Enums/                 # Enumerações (DocumentType, PaymentMethod, etc)
├── Filament/
│   ├── Admin/            # Painel Admin
│   ├── Bilheteria/       # Painel Bilheteria
│   ├── Promoter/         # Painel Promoter
│   ├── Validator/        # Painel Validator
│   └── Widgets/          # Widgets compartilhados
├── Http/
│   └── Middleware/       # EnsureEventSelected
├── Livewire/             # Componentes Livewire
├── Models/              # Eloquent models
├── Policies/            # Policies de autorização
├── Rules/               # Regras de validação customizadas
├── Services/            # Serviços (GuestService, TicketSaleService)
└── Observers/           # Observers (GuestObserver)
```

### 3.3 Database Migrations

Migrations importantes em ordem cronológica:

| Migration | Descrição |
|-----------|-----------|
| `2026_04_19_041341_create_ticket_type_sector_table.php` | Tabela pivot para preços por setor |
| `2026_04_21_161945_update_ticket_types_remove_price_add_is_visible.php` | Remove price, adiciona is_visible |

### 3.4 Comandos Úteis

```bash
# Docker/Sail (SEMPRE usar vendor/bin/sail)
alias sail='vendor/bin/sail'

# Testes
sail artisan test                    # Unit tests
node node_modules/.bin/playwright test e2e/smoke-tests.spec.ts  # E2E

# Database
sail artisan migrate
sail artisan migrate:fresh --seed --seeder=ShowcaseTestSeeder
sail artisan db:seed --class=ShowcaseTestSeeder

# Cache
sail artisan cache:clear
sail artisan config:clear
```

---

## 4. DADOS DE TESTE

### 4.1 Usuários (senha: `password`)

| Email | Role |
|-------|------|
| admin@guestlist.pro | Admin |
| promoter@guestlist.pro | Promoter |
| validador@guestlist.pro | Validator |
| bilheteria@guestlist.pro | Bilheteria |

### 4.2 Evento de Teste
- **Nome:** Festival Teste 2026 (ID: 1)
- **Setores:** Pista, VIP, Camarote, Backstage
- **Bilheteria:** Habilitada

### 4.3 Ticket Types Configurados

| Tipo | Pista | VIP | Camarote | Backstage |
|------|-------|-----|----------|----------|
| Pista Premium | R$150 | R$250 | R$400 | R$300 |
| VIP Experience | R$250 | R$350 | R$500 | R$450 |
| Camarote Open Bar | R$400 | R$500 | R$600 | R$550 |
| Backstage Pass | R$300 | R$450 | R$550 | R$800 |

---

## 5. PENDÊNCIAS E ROADMAP

### 5.1 Bugs/Issues Conhecidos

| Issue | Prioridade | Descrição |
|-------|------------|-----------|
| - | - | Nenhum bug crítico conhecido |

### 5.2 Melhorias Planejadas

| Feature | Status | Descrição |
|---------|--------|-----------|
| SPEC-0005 Ticket Pricing | ✅ Implementado | Refatoração completa em 2026-04-21 |
| E2E Tests Ticket Pricing | ⚠️ Pendente | Criar testes E2E para validar nova arquitetura |
| DEVORQ v3 | 🔍 Em investigação | Versão atual 2.1.1 |

### 5.3 SPECs Implementadas

```
docs/SPECS/implemented/
├── SPEC-0001-07-04-2026-code-review-fixes.md
├── SPEC-0002-17-04-2026-refatoracao-evolucao.md
├── SPEC-0003-17-04-2026-traducao-formatacao.md
├── SPEC-0004-17-04-2026-e2e-infraestrutura.md
├── SPEC-0005-19-04-2026-ticket-pricing-por-setor.md (REFATORADO 2026-04-21)
└── SPEC-PERM-17-04-2026-permissions-design.md
```

---

## 6. LIÇÕES APRENDIDAS

### 6.1 Arquivos de Lições

```
.devorq/state/lessons-learned/
├── _INDEX.md
├── 2026-04-20-e2e-tests-and-seeder-fixes.md      (LL-024)
└── 2026-04-21-sqlite-vs-mysql-production.md     (LL-025)
```

### 6.2 Pontos Importantes

1. **Sempre usar `vendor/bin/sail`** para comandos Laravel
2. **E2E Test Seeder** precisa criar EventAssignment para o validator
3. **SQLite não tem função HOUR()** - usar `strftime('%H', col)` para compatibilidade
4. **TicketType.price** removido em favor de `ticket_type_sector`

---

## 7. AUTENTICAÇÃO E SESSÃO

### 7.1 Middleware EnsureEventSelected

Os painéis `promoter`, `validator` e `bilheteria` requerem que um evento esteja selecionado na sessão (`session('selected_event_id')`).

Se não houver evento selecionado, o middleware redireciona para `/select-event`.

### 7.2 Seleção de Evento

- Feita via `EventSelectorGrid` (Livewire component)
- Armazenada em `session('selected_event_id')`
- Usada em todos os widgets e formulários

---

## 8. POLICIES E AUTORIZAÇÃO

| Policy | Descrição |
|--------|-----------|
| `TicketTypePolicy` | Admin-only para CRUD |
| `TicketSalePolicy` | Verifica se evento selecionado é o mesmo |

---

## 9. WIDGETS DO SISTEMA

### 9.1 Widgets Admin
- `AdminOverview` - Visão geral com métricas
- `SalesTimelineChart` - Timeline de vendas (⚠️ SQLite compatible)
- `CheckinFlowChart` - Fluxo de check-ins (⚠️ SQLite compatible)
- `SectorMetricsTable` - Métricas por setor
- `SectorOccupancyChart` - Ocupação por setor
- `TicketTypeReportTable` - Relatório de tipos de ingresso
- `ApprovalMetricsChart` - Métricas de aprovação

### 9.2 Widgets Bilheteria
- `BilheteriaOverview` - Visão geral da bilheteria

### 9.3 Widgets Promoter
- `PromoterQuotaOverview` -Quota do promoter
- `PendingRequestsTableWidget` - Solicitações pendentes

### 9.4 Widgets Validator
- `PendingRequestsWidget` - Solicitações pendentes
- `ValidatorOverview` - Visão geral

---

## 10. TESTES

### 10.1 Unit Tests
- **Local:** `tests/Unit/`
- **Feature:** `tests/Feature/`
- **Status:** 72 tests passing

### 10.2 E2E Tests
- **Local:** `e2e/smoke-tests.spec.ts`
- **Framework:** Playwright
- **Status:** 27 tests passing

### 10.3 Rodar Testes

```bash
# Unit tests
vendor/bin/sail artisan test

# E2E tests
node node_modules/.bin/playwright test e2e/smoke-tests.spec.ts --reporter=list
```

---

## 11. DEPLOY E CONFIGURAÇÃO

### 11.1 Variáveis de Ambiente

```bash
# .env.production (template)
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=guestlist.fssdev.com.br
DB_DATABASE=guestlist_pro
```

### 11.2 Observações de Deploy

- Produção usa **MySQL** (não SQLite)
- SQLite funciona localmente mas gráficos usam `HOUR()` que não existe no SQLite
- Para compatibilidade SQLite, usar `strftime('%H', col)` ao invés de `HOUR(col)`

---

## 12. PRÓXIMOS PASSOS SUGERIDOS

1. **Criar E2E Tests para Ticket Pricing** - Validar nova arquitetura
2. **Investigar DEVORQ v3** - Versão atual é 2.1.1
3. **Implementar notificações push** - Enhancement opcional
4. **Relatórios exportáveis** - Enhancement opcional

---

## 13. ARQUIVOS MODIFICADOS RECENTEMENTE

| Arquivo | Data | Descrição |
|---------|------|-----------|
| `app/Models/TicketType.php` | 2026-04-21 | Removido price, adicionado is_visible |
| `app/Services/TicketSaleService.php` | 2026-04-21 | Lança exceção se preço não encontrado |
| `app/Filament/Resources/TicketType/Schemas/TicketTypeForm.php` | 2026-04-21 | setor_prices obrigatório |
| `app/Filament/Bilheteria/Resources/TicketSales/Schemas/TicketSaleForm.php` | 2026-04-21 | Setor primeiro, filtra tipos |
| `database/migrations/..._update_ticket_types_...` | 2026-04-21 | Nova migration |
| `database/seeders/ShowcaseTestSeeder.php` | 2026-04-21 | Preços por setor |
| `docs/SPECS/implemented/SPEC-0005-...` | 2026-04-21 | Atualizado com nova arquitetura |

---

## 14. CONTATO E SUPORTE

- **Documentação:** `docs/CONSOLIDATED/INDEX.md`
- **Regras do Projeto:** `.devorq/rules/project.md`
- **Status:** `.devorq/state/STATUS.md`

---

*Este documento foi gerado automaticamente para handover de agente autônomo.*
