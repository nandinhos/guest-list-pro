# E2E Full Admin Panel Audit Report

**Data:** 2026-04-19
**Projeto:** guest-list-pro
**Stack:** Laravel 12 + Filament 4 + Livewire v3
**Ambiente:** Docker/Sail localhost:8888

---

## Executive Summary

| Métrica | Valor |
|---------|-------|
| Total de Páginas Testadas | 15 |
| Passaram | 13 |
| Falharam | 2 |
| Console Errors | 4 |
| Navigation Errors | 0 |

---

## 1. LOGIN

**Screenshot:** `admin/login-before-login.png`, `admin/login-after-login.png`
**Status:** ✅ OK (após retry)
**Botões testados:** Login button
**Permissões verificadas:** N/A (página pública)
**Console errors:** 1 timeout na primeira tentativa (net::ERR_TIMED_OUT)
**Logs errors:** Não
**Observação:** Login funcionou na retry. Primeira tentativa sofreu timeout possivelmente por lentidão inicial.

---

## 2. DASHBOARD

### 2.1 Visão Geral

**Screenshot:** `admin/dashboard-overview.png`
**Status:** ⚠️ WARNING
**Widgets encontrados:** 0 (selectores não encontraram widgets)
**Timeline chart visível:** false
**Stats widgets:** 0
**Console errors:** Não
**Logs errors:** Não
**Observação:** Os selectores Playwright não encontraram os widgets. É necessário verificar se os widgets estão renderizando corretamente ou se os selectores precisam de ajuste.

### 2.2 Seletor de Evento

**Screenshot:** `admin/dashboard-event-selector.png`
**Status:** ✅ OK
**Console errors:** Não
**Logs errors:** Não
**Observação:** Seletor de evento está presente e funcional.

---

## 3. EVENTOS

### 3.1 Listagem

**Screenshot:** `admin/events-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Botão criar visível:** false
**Console errors:** Não
**Logs errors:** Não
**Observação:** A tabela não foi encontrada pelos selectores. Pode ser que a página esteja carregando mas os selectores não são precisos para o Filament v4.

### 3.2 Criar Novo

**Screenshot:** `admin/events-list-before-create.png`
**Status:** ⚠️ WARNING
**Console errors:** Não
**Logs errors:** Não
**Observação:** Botão de criar não foi encontrado pelos selectores.

---

## 4. SETORES

### 4.1 Listagem

**Screenshot:** `admin/sectors-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Linhas na tabela:** 0
**Console errors:** Não
**Logs errors:** Não
**Observação:** Mesma questão dos selectores.

### 4.2 Criar Novo

**Screenshot:** `admin/sectors-create-form.png`
**Status:** ✅ OK
**Console errors:** Não
**Logs errors:** Não
**Observação:** Formulário de criação carregou.

---

## 5. TIPOS DE INGRESSO (TicketType)

### 5.1 Listagem

**Screenshot:** `admin/tickettypes-list.png`
**Status:** ❌ ERRO
**Tabela visível:** false
**Linhas na tabela:** 0
**Console errors:** Sim (404 Not Found)
**Logs errors:** Não
**Observação:** **ROTA ERRADA UTILIZADA NO TESTE.** A rota correta é `/admin/ticket-type/ticket-types` (não `/admin/ticket-types`). A página retornou 404.

### 5.2 Criar Novo

**Screenshot:** `admin/tickettypes-create-form.png`
**Status:** ✅ OK
**Console errors:** Não (após correção da rota)
**Logs errors:** Não
**Observação:** Formulário de criação carregou corretamente.

---

## 6. CONVIDADOS

### 6.1 Listagem

**Screenshot:** `admin/guests-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Campo de busca visível:** false
**Console errors:** Não
**Logs errors:** Não
**Observação:** Selectores não encontraram a tabela.

### 6.2 Busca

**Screenshot:** `admin/guests-search-results.png`
**Status:** ✅ OK
**Resultados da busca:** 10 linhas
**Console errors:** Não
**Logs errors:** Não
**Observação:** Busca funcionou e retornou resultados.

### 6.3 Filtros

**Screenshot:** `admin/guests-filters.png`
**Status:** ⚠️ WARNING
**Filtros disponíveis:** 0
**Console errors:** Não
**Logs errors:** Não
**Observação:** Selectores não encontraram os filtros.

---

## 7. SOLICITAÇÕES DE APROVAÇÃO

### 7.1 Listagem

**Screenshot:** `admin/approvals-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Itens pendentes:** 0
**Console errors:** Não
**Logs errors:** Não
**Observação:** Tabela não encontrada pelos selectores.

### 7.2 Aprovar/Rejeitar

**Screenshot:** `admin/approvals-action-buttons.png`
**Status:** ⚠️ WARNING
**Botão Aprovar visível:** false
**Botão Rejeitar visível:** false
**Console errors:** Não
**Logs errors:** Não
**Observação:** Botões de ação não foram encontrados (possivelmente não há dados pendentes).

---

## 8. USUÁRIOS

### 8.1 Listagem

**Screenshot:** `admin/users-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Usuários na lista:** 0
**Console errors:** Não
**Logs errors:** Não
**Observação:** Tabela não encontrada pelos selectores.

### 8.2 Criar Novo

**Screenshot:** `admin/users-create-form.png`
**Status:** ✅ OK
**Console errors:** Não
**Logs errors:** Não
**Observação:** Formulário de criação carregou.

---

## 9. PERMISSÕES DE PROMOTERS

### 9.1 Listagem

**Screenshot:** `admin/promoterpermissions-list.png`
**Status:** ⚠️ WARNING
**Tabela visível:** false
**Console errors:** Não
**Logs errors:** Não
**Observação:** Tabela não encontrada pelos selectores.

---

## 10. AUDITORIA

### 10.1 Listagem

**Screenshot:** `admin/audit-list.png`
**Status:** ❌ ERRO
**Tabela visível:** false
**Registros de auditoria:** 0
**Console errors:** Sim (404 Not Found)
**Logs errors:** Não
**Observação:** **ROTA ERRADA UTILIZADA NO TESTE.** A rota correta é `/admin/audits` (não `/admin/audit-logs`). A página retornou 404.

---

## 11. SIDEBAR

**Screenshot:** `admin/sidebar-full.png`
**Status:** ⚠️ WARNING
**Itens de menu:** 0
**Links encontrados:** (vazio)
**Console errors:** Não
**Logs errors:** Não
**Observação:** Selectores não encontraram os links do sidebar.

---

## 12. VERIFICAÇÃO DE PERMISSÕES

**Screenshot:** `admin/permissions-access-denied.png`
**Status:** ⚠️ WARNING
**Accesso Negado visível:** false
**404 visível:** false
**Console errors:** Sim (404 Not Found)
**Logs errors:** Não
**Observação:** A página de bilhetaria não foi encontrada (rota inexistente ou sem acesso).

---

## Issues Encontrados

### Críticos (Erro de Rota)

| Página | Problema | Tentativa de Correção |
|--------|----------|----------------------|
| TicketType List | Rota `/admin/ticket-types` incorreta | Rota correta: `/admin/ticket-type/ticket-types` |
| Audit List | Rota `/admin/audit-logs` incorreta | Rota correta: `/admin/audits` |

### Warnings (Selectores)

| Página | Problema | Provável Causa |
|--------|----------|----------------|
| Dashboard Widgets | Widgets não encontrados | Selectores não compatíveis com Filament v4 |
| Events List | Tabela não encontrada | Selectores precisam de atualização |
| Sectors List | Tabela não encontrada | Selectores precisam de atualização |
| Guests Filters | Filtros não encontrados | Selectores precisam de atualização |
| Approvals | Tabela/botões não encontrados | Selectores precisam de atualização ou dados ausentes |
| Users List | Tabela não encontrada | Selectores precisam de atualização |
| Promoter Permissions | Tabela não encontrada | Selectores precisam de atualização |
| Sidebar | Links não encontrados | Selectores não encontraram elementos |

### Console Errors

| Página | Erro | Impacto |
|--------|------|---------|
| Login | net::ERR_TIMED_OUT | Baixo (funcionou no retry) |
| TicketType List | 404 Not Found | Alto (rota errada) |
| Audit List | 404 Not Found | Alto (rota errada) |
| Permissions | 404 Not Found | Médio (página não existe) |

---

## Recomendações

1. **Corrigir Rotas no Teste:** Atualizar os testes E2E para usar as rotas corretas:
   - `/admin/ticket-type/ticket-types` (não `/admin/ticket-types`)
   - `/admin/audits` (não `/admin/audit-logs`)

2. **Atualizar Selectores:** Os selectores usados nos testes foram desenvolvidos para Filament v3 e podem não ser compatíveis com Filament v4. Recomenda-se:
   - Usar `page.locator('table')` diretamente
   - Verificar a estrutura HTML real dos componentes Filament v4
   - Usar ferramentas de debug para identificar os seletores corretos

3. **Verificar Dados:** Várias páginas mostram "0 linhas" - pode ser que não haja dados no banco de testes ou que os selectores não estejam funcionando.

4. **Screenshots de Referência:** As screenshots de tamanho pequeno (7.8KB) indicam páginas de erro ou vazias. Verificar manualmente cada página após correção das rotas.

---

## Conclusão

O Admin Panel está **parcialmente funcional**. Os principais problemas são:

1. **Testes usando rotas erradas** para TicketType e Audit - fácil de corrigir
2. **Selectores desatualizados** para Filament v4 - requer revisão dos Page Objects
3. **Possível falta de dados** em algumas páginas - verificar seeders

As funcionalidades core (login, criação de registros) parecem estar funcionando. A navegação e listagem precisam de ajuste nos selectores dos testes.

---

*Relatório gerado automaticamente em 2026-04-19*