# AUDIT-BILHETERIA-2026-04-19

## Resumo Executivo

| Item | Total |
|------|-------|
| Páginas testadas | 5 |
| Issues encontrados | 2 |
| Warnings | 1 |
| Screenshots capturados | 11 |

---

## Credenciais Utilizadas

- **Email:** bilheteria@guestlist.pro
- **Senha:** password
- **Role:** Bilheteria

---

## 1. Login

**Screenshot:** bilheteria/login-page.png  
**Status:** OK  
**Botões testados:** Entrar no Painel  
**Permissões verificadas:** Sim (acesso correto ao portal)  
**Console errors:** Não  
**Logs errors:** Não  
**Observação:** Usuário redirecionado para seleção de evento após login

---

## 2. Seleção de Evento

**Screenshot:** bilheteria/select-event-page.png  
**Status:** OK  
**Botões testados:** Festival Teste 2026  
**Permissões verificadas:** Sim  
**Console errors:** Não  
**Logs errors:** Não  
**Observação:** Mostra evento "Festival Teste 2026" com data 20/04/2026

---

## 3. Dashboard (Painel de Controle)

**Screenshot:** bilheteria/dashboard-main.png  
**Status:** OK  
**Widgets verificados:**
- Total de Vendas: 20
- Receita Total: R$ 9.000,00
- Vendas Hoje: 0
- Tipos Ativos: 5

**Permissões verificadas:** Sim  
**Console errors:** Não  
**Logs errors:** Não  
**Warning:** CSS preload não utilizado (não crítico)
**Observação:** Todos os widgets carregados corretamente

---

## 4. Vendas (Lista)

**Screenshot:** bilheteria/vendas-list.png  
**Status:** OK  
**Botões testados:** Nova Venda, Limpar filtros, Aplicar filtros, Ordenação de colunas  
**Funcionalidades verificadas:**
- Filtros: Tipo de Ingresso, Setor, Pagamento, Vendedor, Data
- Tabela com ordenação funcional
- Paginação funcionando (10 por página)

**Permissões verificadas:** Sim  
**Console errors:** Não  
**Logs errors:** Não  
**Observação:** 20 vendas listadas, todas com dados mascarados (CPF)

---

## 5. Nova Venda (Criar)

**Screenshot:** bilheteria/criar-venda.png, criar-venda-tipo-selecionado.png, criar-venda-preenchido.png, criar-venda-form-final.png, criar-venda-erro-validacao.png  
**Status:** WARNING

### Problemas Encontrados

#### 5.1 Validação de CPF

**Descrição:** O sistema valida CPF com dígito verificador. CPFs inválidos são rejeitados com mensagem clara.

**Screenshot:** bilheteria/criar-venda-erro-validacao.png

**Tentativas:**
1. CPF "123.456.789-00" → Rejeitado: "CPF inválido (dígito verificador incorreto)."
2. CPF "12345678900" → Rejeitado: "CPF inválido (dígito verificador incorreto)."

**Status:** OK (comportamento esperado)

#### 5.2 Dropdown de Forma de Pagamento

**Descrição:** O dropdown de Forma de Pagamento não mantém seleção corretamente quando clicado e fechado sem selecionar.

**Screenshot:** bilheteria/criar-venda-form-final.png

**Tentativas:**
1. Clique no dropdown → Abre lista com "Dinheiro" selecionado por padrão
2. Clique em outro lugar sem selecionar → Dropdown fecha
3. Submit do formulário → Erro: "É obrigatória a indicação de um valor para o campo forma de Pagamento."

**Status:** PENDING_analysis

**Análise:** O campo "Dinheiro" aparece como `active` na listbox, mas não é realmente selecionado no estado do formulário. Problema de UI/interação.

---

## 6. Fechamento de Caixa

**Screenshot:** bilheteria/fechamento-caixa.png  
**Status:** OK  
**Botões testados:** Exportar PDF (disabled), Atualizar  
**Funcionalidades verificadas:**
- Filtros por período (Início/Fim)
- Filtro por Forma de Pagamento
- Cards: Dinheiro, Cartão de Crédito, Cartão de Débito, PIX
- Total Geral e Total de Vendas

**Permissões verificadas:** Sim  
**Console errors:** Não  
**Logs errors:** Não  
**Observação:** Nenhuma venda hoje - todos os valores R$ 0,00

---

## 7. Teste de Permissão - Admin Panel

**Screenshot:** bilheteria/admin-forbidden.png  
**Status:** OK  
**Teste:** Acesso à URL /admin com usuário Bilheteria  
**Resultado:** HTTP 403 Forbidden  
**Permissões verificadas:** Sim (bloqueio correto)  
**Console errors:** Sim (1 error HTTP)  
**Logs errors:** Não  
**Observação:** Sistema bloqueia corretamente acesso a painéis não autorizados

---

## Issues Resumidos

| # | Página | Issue | Severity | Status |
|---|--------|-------|----------|--------|
| 1 | Nova Venda | Dropdown Forma de Pagamento não mantém seleção | Medium | PENDING_analysis |

---

## Screenshots Capturados

| Arquivo | Descrição |
|---------|-----------|
| login-page.png | Página de login |
| select-event-page.png | Seleção de evento |
| dashboard-main.png | Dashboard principal |
| vendas-list.png | Lista de vendas |
| criar-venda.png | Formulário vazio |
| criar-venda-tipo-selecionado.png | Tipo selecionado |
| criar-venda-preenchido.png | Dados preenchidos |
| criar-venda-form-final.png | Form completo |
| criar-venda-form-completo.png | Form completo v2 |
| criar-venda-erro-validacao.png | Erro de validação |
| fechamento-caixa.png | Fechamento de caixa |
| admin-forbidden.png | Acesso negado admin |

---

## Conclusão

O painel de **Bilheteria está funcional** com permissões corretas. O único issue identificado é o comportamento do dropdown de Forma de Pagamento que não mantém seleção ao ser fechado sem interação, requiring análise adicional.

**Recomendação:** Investigar comportamento do componente Select em Criar Venda para Forma de Pagamento.