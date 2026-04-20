---
id: SPEC-0004-17-04-2026
title: Correção da Infraestrutura E2E — guest-list-pro
domain: qualidade
status: draft
priority: high
author: Nando Dev
owner: team-core
source: e2e-test-analysis
created_at: 2026-04-17
updated_at: 2026-04-17
related_files:
  - e2e/api.spec.ts
  - e2e/smoke-tests.spec.ts
  - playwright.config.ts
  - e2e/pages/LoginPage.ts
  - e2e/pages/AdminPages.ts
related_tasks:
  - A1
  - E1
  - E2
  - E3
  - E4
---

# SPEC-0004: Correção da Infraestrutura E2E — guest-list-pro

**Versão:** 1.0
**Data:** 2026-04-17
**Status:** Draft
**Prioridade:** Alta

---

## 1. Objetivo

Corrigir a infraestrutura de testes E2E do projeto guest-list-pro, resolvendo problemas críticos que impedem a execução correta dos testes. O objetivo é estabelecer uma base sólida para testes automatizados antes de prosseguir para a fase de permissões.

**Resultados Esperados:**
- API tests funcionando com credenciais corretas
- Retries habilitados para testes flakies
- Waits padronizados para elementos Livewire
- Navegação e widgets funcionando sem timeouts

---

## 2. Escopo

### 2.1 Escopo Incluído

| ID | Fase | Item | Prioridade | Status |
|----|------|------|------------|--------|
| A1 | Authentication | Corrigir email em api.spec.ts | 🔴 Crítica | Pending |
| E1 | Infrastructure | Habilitar retries no playwright.config.ts | 🔴 Crítica | Pending |
| E2 | Infrastructure | Padronizar wait strategy (constants + helpers) | 🟠 Alta | Pending |
| E3 | Admin UI | Corrigir timeout de Events navigation | 🟠 Alta | Pending |
| E4 | Admin UI | Corrigir widget visibility (Sector Metrics, Ticket Type Report) | 🟠 Alta | Pending |

### 2.2 Escopo Excluído

- Criação de novos testes E2E (futuro sprint)
- Testes de performance/carga
- Integração com CI/CD
- Testes de API de outros usuários além de admin

---

## 3. Pré-requisitos

- [ ] Ambiente Docker/Sail configurado e rodando
- [ ] Banco de dados populado com ShowcaseTestSeeder
- [ ] Playwright instalado (`npx playwright install`)
- [ ] Navegador acessível em `http://localhost:8888`
- [ ] Documentação E2E existente revisada

---

## 4. Detalhamento das Tarefas

### FASE A: Authentication

---

### A1: Corrigir Credenciais em api.spec.ts

**Dependência:** Nenhuma

#### Problema

Os testes de API usam `admin@admin.com` mas o banco de dados (ShowcaseTestSeeder) cria `admin@guestlist.pro`. Isso causa falha de autenticação 401 em todos os 18+ testes de API.

#### Locais Afetados

| Linha | Contexto | Valor Atual | Valor Correto |
|-------|----------|-------------|--------------|
| 7 | API login test | `admin@admin.com` | `admin@guestlist.pro` |
| 43 | Logout test | `admin@admin.com` | `admin@guestlist.pro` |
| 65 | Events test beforeAll | `admin@admin.com` | `admin@guestlist.pro` |
| 132 | Guests test beforeAll | `admin@admin.com` | `admin@guestlist.pro` |
| 235 | Approvals test beforeAll | `admin@admin.com` | `admin@guestlist.pro` |
| 319 | Stats test beforeAll | `admin@admin.com` | `admin@guestlist.pro` |
| 372 | Validation test | `admin@admin.com` | `admin@guestlist.pro` |

#### Implementação

Substituir todas as ocorrências de `admin@admin.com` por `admin@guestlist.pro` no arquivo `e2e/api.spec.ts`.

#### Critérios de Aceitação

- [ ] Arquivo `e2e/api.spec.ts` não contém mais `admin@admin.com`
- [ ] Todos os 7 lugares corrigidos
- [ ] Teste de login API retorna 200 com `admin@guestlist.pro`

#### Risks

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Emailhardcoded em outro lugar | Baixa | Alta | Verificar com grep antes de fechar |

---

### FASE E: E2E Infrastructure

---

### E1: Habilitar Retries no Playwright

**Dependência:** Nenhuma

#### Problema

`playwright.config.ts` tem `retries: 0`, o que significa que testes flakies falham permanentemente sem chance de retry.

#### Configuração Atual

```typescript
// playwright.config.ts
export default {
  retries: 0,  // ← Problema
  workers: 1,
  // ...
};
```

#### Implementação

Alterar `retries` para `1` (mínimo recomendado) ou `2` para容忍 flakies de rede.

```typescript
export default {
  retries: 1,
  workers: 1,
  // ...
};
```

#### Critérios de Aceitação

- [ ] `retries: 1` ou superior configurado
- [ ] Testes que falham por timeout tentam novamente

#### Risks

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Aumento de tempo de execução | Baixa | Baixa | Monitorar tempo total |

---

### E2: Padronizar Wait Strategy

**Dependência:** E1

#### Problema

Page objects usam `waitForTimeout()` hardcoded com valores inconsistentes (300ms a 1500ms) e `waitForLoadState('networkidle')` que não funciona bem com Livewire.

#### Valores Atuais Inconsistentes

| Duração | Quantidade | Uso |
|---------|------------|-----|
| 300ms | 2 | After selectOption (Bilheteria) |
| 500ms | 10 | After filters, searches, modal opens |
| 1000ms | 9 | After event selection, search, check-in |
| 1500ms | 1 | After form submission (Bilheteria) |

#### Implementação Proposta

**1. Criar arquivo de constantes `e2e/config/wait-times.ts`:**

```typescript
export const WAIT_TIMES = {
  // Livewire/Network waits (after async operations)
  LIVEWIRE_NETWORK: 500,      // After selectOption, input fills
  LIVEWIRE_FORM_SUBMIT: 1500, // After form submissions
  LIVEWIRE_SEARCH: 800,       // After search input

  // Page navigation waits
  NAVIGATION: 1000,           // After click that triggers navigation
  URL_MATCH: 10000,           // For waitForURL

  // Element visibility waits
  ELEMENT_VISIBLE: 10000,     // Standard toBeVisible timeout
  MODAL_OPEN: 500,            // For modal/overlay appearance

  // Legacy (evitar)
  ARBITRARY_SHORT: 300,
  ARBITRARY_MEDIUM: 1000,
} as const;
```

**2. Criar helper Livewire `e2e/helpers/livewire-helpers.ts`:**

```typescript
import { Page } from '@playwright/test';

export async function waitForLivewireLoad(page: Page) {
  await page.waitForLoadState('networkidle');
  await page.waitForFunction(() => {
    const loading = document.querySelector('[wire\\:loading]');
    return !loading || window.getComputedStyle(loading).display === 'none';
  }, { timeout: 10000 });
}

export async function waitForLivewireResponse(page: Page) {
  await Promise.all([
    page.waitForResponse(
      resp => resp.url().includes('/livewire/') || resp.url().includes('/__livewire/'),
      { timeout: 10000 }
    ),
  ]);
  await page.waitForLoadState('networkidle');
}
```

**3. Atualizar page objects para usar as constantes:**

```typescript
// Antes
await this.page.waitForTimeout(1000);

// Depois
import { WAIT_TIMES } from '../config/wait-times';
await this.page.waitForTimeout(WAIT_TIMES.LIVEWIRE_NETWORK);
```

#### Critérios de Aceitação

- [ ] Arquivo `e2e/config/wait-times.ts` criado com constantes
- [ ] Arquivo `e2e/helpers/livewire-helpers.ts` criado
- [ ] Page objects principais atualizados (LoginPage, AdminPages, BilheteriaPages)
- [ ] Valores hardcoded removidos ou substituídos

#### Risks

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Wait times muito curtos para ambiente lento | Média | Média | Ajustar valores baseado em resultados |
| Wait times muito longos | Baixa | Baixa | Testes demoram mais mas mais estáveis |

---

### E3: Corrigir Timeout de Events Navigation

**Dependência:** E2

#### Problema

Teste de navegação para Events management falha com timeout de 13s porque o elemento `table` não aparece.

```
Error: expect(locator).toBeVisible() failed
Locator: locator('table')
Expected: visible
Timeout: 10000ms
```

#### Sintomas

- Duração: 12,922ms
- Screenshot capturado em `test-results/admin-tests-🔄-Admin-CRUD-*-navigate-to-events-management-chromium/`

#### Hipóteses

1. **Rota errada** - URL incorreta
2. **Middleware bloqueando** - Usuário sem acesso
3. **Dados não carregam** - Event list vazia
4. **Livewire não termina** - Loading state não completa

#### Implementação

1. Adicionar espera por Livewire após navegação:

```typescript
// AdminPages.ts
async gotoEvents() {
  await this.page.goto('/admin/events');
  await this.page.waitForLoadState('networkidle');
  // Aguardar widget de loading do Livewire
  await this.page.waitForFunction(() => {
    const spinner = document.querySelector('.fi-loading-indicator');
    return !spinner || window.getComputedStyle(spinner).display === 'none';
  }, { timeout: 15000 });
  await this.expectTableToBeVisible();
}
```

2. Usar seletor mais específico para tabela:

```typescript
// Ao invés de locator('table')
// Usar seletor mais específico do Filament
await expect(this.page.locator('.filament-resources-table-container')).toBeVisible({ timeout: 15000 });
```

#### Critérios de Aceitação

- [ ] Navegação para `/admin/events` funciona sem timeout
- [ ] Tabela de eventos visível em até 15s
- [ ] Screenshot mostra dados carregados

#### Risks

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Problema é no backend (dados faltando) | Baixa | Alta | Verificar se Events existem no banco |

---

### E4: Corrigir Widget Visibility

**Dependência:** E2

#### Problema

Testes de widgets falham porque textos esperados não são encontrados:

**Sector Metrics Table:**
```
Locator: locator('text=Métricas por Setor, text=Setor').first()
Error: element(s) not found
Duration: 13,106ms
```

**Ticket Type Report:**
```
Locator: locator('text=Relatório por Tipo').first()
Error: element(s) not found
Duration: 13,592ms
```

#### Hipóteses

1. **Texto diferente** - UI usa texto diferente do esperado
2. **Widget não carrega** - Dados não existem ou falha de renderização
3. **Timing** - Livewire ainda carregando quando teste verifica

#### Implementação

1. **Aumentar timeout e adicionar wait for Livewire:**

```typescript
// AdminPages.ts
async expectSectorMetricsVisible() {
  await this.page.waitForLoadState('networkidle');
  // Aguardar widget específico
  await this.page.waitForFunction(() => {
    const widget = document.querySelector('[class*="sector-metrics"], [class*="metric-table"]');
    return widget !== null;
  }, { timeout: 20000 });
  await expect(this.page.locator('text=Métricas por Setor')).toBeVisible({ timeout: 10000 });
}
```

2. **Verificar texto exato no widget:**

Inspecionar o widget real no browser para，确认 texto correto.

3. **Adicionar fallback para texto alternativo:**

```typescript
// Tentar texto principal, senão texto alternativo
const selector = page.locator('text=Métricas por Setor, text=Setores, text=Metrics');
await expect(selector.first()).toBeVisible({ timeout: 10000 });
```

#### Critérios de Aceitação

- [ ] Widget "Métricas por Setor" visível
- [ ] Widget "Relatório por Tipo" visível
- [ ] Ambos carregam em até 20s

#### Risks

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Texto não existe no widget | Média | Alta | Inspecionar UI real primeiro |
| Widget depende de dados | Baixa | Média | Verificar se seeder cria dados suficientes |

---

## 5. Critérios de Aceitação Gerais

### Definition of Done (DoD)

- [ ] A1: Credenciais corrigidas, API login retorna 200
- [ ] E1: Retries habilitados
- [ ] E2: Wait strategy padronizada
- [ ] E3: Events navigation funciona
- [ ] E4: Widgets visíveis
- [ ] Suite E2E executa sem erros críticos (P0/P1)

### Checklist de Implementação

| Tarefa | Implementada | Data | Observação |
|--------|-------------|------|------------|
| A1: Corrigir email | ⬜ | - | - |
| E1: Habilitar retries | ⬜ | - | - |
| E2: Padronizar waits | ⬜ | - | - |
| E3: Events navigation | ⬜ | - | - |
| E4: Widget visibility | ⬜ | - | - |

---

## 6. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Problemas de backend (dados, auth) | Média | Alta | Verificar banco e auth antes de mexer em waits |
| Waits muito curtos para ambiente lento | Média | Média | Começar com valores altos e reduzir |
| Fragilidade de seletores | Alta | Alta | Usar seletores mais específicos do Filament |

---

## 7. Estimativas

| Tarefa | Estimativa | Complexidade |
|--------|------------|--------------|
| A1: Corrigir email | 5 min | Baixa |
| E1: Habilitar retries | 1 min | Baixa |
| E2: Padronizar waits | 2-3h | Alta |
| E3: Events navigation | 1-2h | Média |
| E4: Widget visibility | 1-2h | Média |
| **TOTAL** | **5-8h** | - |

---

## 8. Validação

### Pré-implementação
- [ ] Verificar banco de dados com `ShowcaseTestSeeder`
- [ ] Confirmar que `admin@guestlist.pro` existe
- [ ] Inspecionar widgets no browser para confirmar textos

### Pós-implementação
- [ ] Rodar `npx playwright test api.spec.ts` - login deve passar
- [ ] Rodar `npx playwright test smoke-tests.spec.ts` - sem timeouts
- [ ] Rodar `npx playwright test admin-tests.spec.ts` - todos widgets visíveis
- [ ] Gerar relatório HTML com `npx playwright show-report`

---

## 9. Referências

- [E2E Test Report](./../report_e2e/E2E_TEST_REPORT.md)
- [Test Cases](./../report_e2e/test-cases/TEST_CASES.md)
- [Methodology](./../report_e2e/methodology/METHODOLOGY.md)
- [Playwright Config](./../../playwright.config.ts)
- [API Spec](./../../e2e/api.spec.ts)
- [Smoke Tests](./../../e2e/smoke-tests.spec.ts)
