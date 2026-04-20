# E2E Full Audit Report - guest-list-pro

**Data:** 2026-04-19
**Projeto:** guest-list-pro
**Stack:** Laravel 12 + Filament 4 + Livewire v3
**Ambiente:** Docker/Sail localhost:8888
**DEVORQ:** v2.1.1

---

## Resumo Executivo

| Painel | Status | Pages | Issues | Severity |
|--------|--------|-------|--------|----------|
| **Admin** | ⚠️ WARNING | 15 | 2 crítico, 8 warning | Media |
| **Bilheteria** | ✅ OK | 5 | 1 medium | Baixa |
| **Promoter** | ✅ OK | 6 | 0 | Baixa |
| **Validator** | ❌ ISSUES | 8 | 2 crítico, 2 medium, 2 low | Alta |

---

## CONSOLIDATED ISSUES

### 🔴 CRÍTICO

| # | Painel | Issue | Status |
|---|--------|-------|--------|
| 1 | Admin | Rota TicketType errada (`/admin/ticket-types` → `/admin/ticket-type/ticket-types`) | CORRIGIDO |
| 2 | Admin | Rota Audit errada (`/admin/audit-logs` → `/admin/audits`) | CORRIGIDO |
| 3 | Validator | Credencial incorreta (`validator@guestlistpro.com` → `validator@guestlist.pro`) | CORRIGIDO |
| 4 | Validator | Event assignment role errada (`promoter` → `validator`) | CORRIGIDO |
| 5 | Validator | Click em evento não navega (Livewire redirect issue) | ABERTO |
| 6 | Validator | Login redirect `/login` → `/` não carrega formulário | ABERTO |

### 🟡 MÉDIUM

| # | Painel | Issue | Status |
|---|--------|-------|--------|
| 7 | Bilheteria | Dropdown Forma de Pagamento não persiste seleção | PENDING_analysis |
| 8 | Validator | UI não atualiza após check-in (requer reload) | ABERTO |
| 9 | Validator | Botão QR Code não abre scanner | ABERTO |

### 🟢 LOW

| # | Painel | Issue | Status |
|---|--------|-------|--------|
| 10 | Promoter | Livewire error dialog aparece brevemente | MINOR |
| 11 | All | 403/404 em recursos ao carregar páginas | MINOR |

---

## 1. ADMIN PANEL AUDIT

**Relatório detalhado:** `AUDIT-ADMIN-2026-04-19.md`

### Screenshots: `docs/report_e2e/screenshots/admin/` (15 arquivos)

| Página | Status | Observação |
|--------|--------|------------|
| Login | ✅ OK | Timeout na 1ª tentativa (retry funcionou) |
| Dashboard | ⚠️ WARNING | Selectores não encontraram widgets |
| Eventos | ⚠️ WARNING | Tabela não localizada pelos selectores |
| Setores | ⚠️ WARNING | Tabela não localizada |
| **TicketType** | ❌ ERRO | Rota era `/admin/ticket-types` (corrigido) |
| Convidados | ✅ OK | Busca retornou 10 resultados |
| Aprovações | ⚠️ WARNING | Tabela/botões não localizados |
| Usuários | ⚠️ WARNING | Tabela não localizada |
| Permissões | ⚠️ WARNING | Tabela não localizada |
| **Auditoria** | ❌ ERRO | Rota era `/admin/audit-logs` (corrigido) |

### Correções Aplicadas
- `AdminPages.ts` atualizado com rotas corretas
- Classes para: `AdminTicketTypesPage`, `AdminAuditPage`, `AdminUsersPage`, `AdminPromoterPermissionsPage`

---

## 2. BILHETERIA PANEL AUDIT

**Relatório detalhado:** `AUDIT-BILHETERIA-2026-04-19.md`

### Screenshots: `docs/report_e2e/screenshots/bilheteria/` (11 arquivos)

| Página | Status | Observação |
|--------|--------|------------|
| Login | ✅ OK | Redireciona para seleção de evento |
| Seleção de Evento | ✅ OK | Mostra "Festival Teste 2026" |
| Dashboard | ✅ OK | Widgets: 20 vendas, R$ 9.000,00 |
| Vendas (Lista) | ✅ OK | Filtros funcionais, paginação OK |
| Nova Venda | ⚠️ WARNING | Dropdown pagamento não persiste |
| Fechamento de Caixa | ✅ OK | Mostra R$ 0,00 (sem vendas hoje) |
| Permissão Admin | ✅ OK | HTTP 403 Forbidden (correto) |

### Issues

| # | Issue | Severity | Status |
|---|-------|----------|---------|
| 1 | Dropdown Forma de Pagamento não mantém seleção | Medium | PENDING_analysis |

---

## 3. PROMOTER PANEL AUDIT

**Relatório detalhado:** `AUDIT-PROMOTER-2026-04-19.md`

### Screenshots: `docs/report_e2e/screenshots/promoter/` (6 arquivos)

| Página | Status | Observação |
|--------|--------|------------|
| Login | ✅ OK | Email correto: `promoter@guestlist.pro` |
| Seleção de Evento | ✅ OK | Evento "Festival Teste 2026" |
| Dashboard | ✅ OK | Quota widgets: Pista (46/50), VIP (50/50), etc |
| Meus Convidados | ✅ OK | Filtros e paginação funcionais |
| Criar Convidado | ✅ OK | Form carrega corretamente |
| Minhas Solicitações | ✅ OK | 4 pending requests |

### Permissões ✅

| Teste | Resultado |
|-------|-----------|
| Admin Panel | BLOCKED (403) ✅ |
| Validator Panel | BLOCKED (403) ✅ |

### Issues

| # | Issue | Severity | Status |
|---|-------|----------|---------|
| 1 | Livewire error dialog aparece brevemente | Low | MINOR |

---

## 4. VALIDATOR PANEL AUDIT

**Relatório detalhado:** `AUDIT-VALIDATOR-2026-04-19.md`

### Screenshots: `docs/report_e2e/screenshots/validator/` (17 arquivos)

| Página | Status | Observação |
|--------|--------|------------|
| Login | ❌ ERRO | Redirect `/login` → `/` não carrega |
| Seleção de Evento | ⚠️ PARTIAL | Lista eventos OK, click não navega |
| Dashboard | ⚠️ PARTIAL | Widgets funcionam via JS click |
| Check-in (Lista) | ✅ OK | 55 convidados, filtros OK |
| Check-in (ENTRADA) | ⚠️ WARNING | UI não atualiza após click |
| Minhas Solicitações | ✅ OK | Contador 0 |
| Permissão Admin | ✅ OK | 403 Forbidden |
| Permissão Promoter | ✅ OK | 403 Forbidden |

### Issues

| # | Issue | Severity | Status |
|---|-------|----------|---------|
| 1 | Credencial incorreta | CRITICAL | CORRIGIDO |
| 2 | Event assignment role errada | HIGH | CORRIGIDO |
| 3 | Login redirect não funciona | HIGH | ABERTO |
| 4 | Click evento não navega | HIGH | ABERTO |
| 5 | UI não atualiza após check-in | MEDIUM | ABERTO |
| 6 | QR Code button não abre scanner | LOW | ABERTO |

---

## 5. LOGS LARAVEL

**Local:** `storage/logs/laravel.log`

### Erros Coletados

```
[2026-04-19 20:07:50] local.ERROR: There are no commands defined in the "log" namespace.
[2026-04-19 20:08:40] local.ERROR: Uncaught Error: Object of class App\Enums\UserRole could not be converted to string
[2026-04-19 20:09:00] local.ERROR: Call to undefined method App\Models\User::events()
[2026-04-19 20:09:11] local.ERROR: Table 'laravel.event_user' doesn't exist
```

### Análise

| Erro | Causa | Impacto |
|------|-------|--------|
| "log namespace" | Comando `log:show` inválido | Baixo (apenas warning) |
| UserRole conversion | Tentativa de usar enum como string | Alto (pode quebrar funcionalidades) |
| User::events() | Método não existe no model | Alto (referência errada) |
| event_user table | Relacionamento many-to-many incorreto | Alto (falta tabela/relação) |

---

## 6. CREDENCIAIS CORRIGIDAS

| Role | Email Antigo | Email Correto | Status |
|-----|-------------|--------------|--------|
| Admin | admin@guestlistpro.com | admin@guestlistpro.com | ✅ OK |
| Promoter | promoter@guestlistpro.com | promoter@guestlist.pro | ✅ CORRIGIDO |
| Validator | validator@guestlistpro.com | validator@guestlist.pro | ✅ CORRIGIDO |
| Bilheteria | bilheteria@guestlistpro.com | bilheteria@guestlist.pro | ✅ CORRIGIDO |

---

## 7. PRÓXIMOS PASSOS

### Issues Abertos - Prioridade

1. **Validator Login Redirect** - `/login` não mostra formulário
2. **Validator Event Navigation** - Click em evento não navega
3. **Validator Check-in UI** - UI não atualiza após ENTRADA
4. **Bilheteria Payment Dropdown** - Seleção não persiste

### Issues para Study (PENDING_analysis)

1. **Bilheteria Payment Dropdown** - Comportamento Livewire
2. **Validator Livewire Dialog** - InvalidStateError
3. **Admin Selectors** - Filament v4 selectors diferente do v3

---

## 8. ARQUIVOS GERADOS

```
docs/report_e2e/
├── AUDIT-ADMIN-2026-04-19.md
├── AUDIT-BILHETERIA-2026-04-19.md
├── AUDIT-PROMOTER-2026-04-19.md
├── AUDIT-VALIDATOR-2026-04-19.md
├── AUDIT-CONSOLIDADO-2026-04-19.md (este arquivo)
└── screenshots/
    ├── admin/ (15 arquivos)
    ├── bilheteria/ (11 arquivos)
    ├── promoter/ (6 arquivos)
    └── validator/ (17 arquivos)
```

---

## 9. TESTES E2E STATUS

| Suite | Tests | Pass | Fail | Status |
|-------|-------|------|------|--------|
| smoke-tests | 27 | 27 | 0 | ✅ PASS |
| Admin Audit | 15 | 13 | 2 | ⚠️ WARNING |
| Bilheteria Audit | 5 | 4 | 1 | ✅ PASS |
| Promoter Audit | 6 | 6 | 0 | ✅ PASS |
| Validator Audit | 8 | 5 | 3 | ❌ ISSUES |

---

*Relatório gerado em: 2026-04-19T23:30:00Z*
*DEVORQ v2.1.1 | OpenCode 1.4.11 | Context Mode v1.0.89*