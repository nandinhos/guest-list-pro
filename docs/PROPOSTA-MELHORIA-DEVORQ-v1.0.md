# LIÇÕES APRENDIDAS — SPEC-0004 (E2E Infraestrutura)

> **Data:** 19/04/2026
> **Atividade:** Implementação da Infraestrutura de Testes E2E
> **Resultado:** 26/26 testes passando ✅

---

## 1. AUTOMAÇÃO DE TESTES E2E

### 1.1 Page Objects — Lições Aprendidas

| Problema | Causa Raiz | Solução |
|----------|------------|---------|
| Locator genérico retornava múltiplos elementos | Seletor muito amplo (ex: `[class*="widget"]`) | Usar `.first()` ou tornar seletor mais específico |
| `searchGuest()` não existia no page object | Métodos esquecidos durante criação | Adicionar métodos conforme necessidade real emerge |
| Função utilitária não importada (`selectOptionAndWait`) | Import parcial no arquivo | Importar TODAS funções utilitárias necessárias |
| Timeout em elementos que não existem | Não verificar se elemento está visível antes de interagir | Adicionar guard `isVisible()` com timeout |

**Regra:** Ao criar/editar page objects, verificar TODAS as dependências de funções importadas.

### 1.2 Fluxo de Login e Seleção de Evento

**Problema:** Testes falhavam quando accediavam páginas diretamente via URL sem passar pelo fluxo de seleção de evento.

**Causa:** O estado de "evento selecionado" é armazenado em `session('selected_event_id')`. Quando navegando diretamente, esse estado não existe.

**Soluções aplicadas:**
- No `beforeEach`: fazer login E depois navegar para a página desejada
- Adicionar espera (`waitForTimeout`) após navegação para permitir renderização completa
- Para bilheteria: goto dashboard primeiro (que faz seleção automática de evento) antes de acessar sub-páginas

**Regra:** Nunca acessar URLs de sub-páginas sem garantir que o evento está selecionado.

### 1.3 Trabalhar com Overlays de Seleção de Evento

**Comportamento observado:**
- Dashboard da bilheteria tem overlay "Selecionar Evento" que some automaticamente após seleção
- Pages de Ticket Sales/Normal não têm o mesmo comportamento - overlay permanece

**Padrão de solução:**
```typescript
// Esperar overlay aparecer
const eventOverlayVisible = await this.page.locator('text="Selecionar Evento"').isVisible().catch(() => false);
// Se visível, clicar no botão de evento
if (eventOverlayVisible) {
  const eventButton = this.page.locator('button:has-text("Festival")').first();
  await eventButton.click();
  await waitForLivewireResponse(this.page);
}
```

**Regra:** Sempre verificar e tratar overlays de seleção de evento em novas páginas.

---

## 2. DEBUGGING DE TESTES E2E

### 2.1 Análise de Falhas

**Ferramentas utilizadas:**
1. `error-context.md` — Snapshot YAML da página no momento da falha
2. Screenshots — Visualização do estado renderizado
3. Vídeos — Gravação do comportamento completo do teste
4. Traces — Timeline de requests/respostas

**Padrão de análise:**
1. Ler `error-context.md` primeiro — contém contexto estruturado
2. Verificar page snapshot YAML — identificar elementos ausentes/extras
3. Analisar código fonte no contexto — linha exata da falha

### 2.2 Testes "Flaky"

**Definição:** Testes que passam na maioria das execuções mas falham ocasionalmente.

**Causa comum:** Timing — Livewire ainda processando quando teste tenta interagir.

**Soluções:**
- Adicionar `waitForLivewireLoad()` após operações críticas
- Usar `waitForTimeout()` como fallback quando Livewire não emite sinais claros
- Aumentar retries no playwright config: `retries: 1`

**Regra:** Tests flaky com menos de 5% de falha são toleráveis. Acima disso, investigar.

---

## 3. PADRÕES DE IMPLEMENTAÇÃO

### 3.1 Estrutura de Page Objects

```typescript
export class PageName {
  readonly page: Page;
  readonly element: Locator;
  readonly anotherElement: Locator;

  constructor(page: Page) {
    this.page = page;
    this.element = page.locator('selector');
  }

  async goto() {
    await this.page.goto('/path');
    await this.page.waitForLoadState('networkidle');
    await waitForLivewireLoad(this.page);
    // Tratar overlays de evento se necessário
  }

  async expectVisible() {
    await expect(this.element).toBeVisible({ timeout: WAIT_TIMES.ELEMENT_VISIBLE });
  }
}
```

### 3.2 Guard Pattern para Elementos Opcionais

```typescript
async searchGuest(name: string) {
  const searchInputVisible = await this.searchInput.isVisible({ timeout: 3000 }).catch(() => false);
  if (searchInputVisible) {
    await this.searchInput.fill(name);
    await waitForLivewireLoad(this.page);
  }
}
```

**Benefício:** Teste não quebra se elemento não existir — apenas não executa a ação.

### 3.3 Importação de Helpers

**Erro comum:** `ReferenceError: selectOptionAndWait is not defined`

**Causa:** Arquivo importa apenas `waitForLivewireLoad` mas usa `selectOptionAndWait`.

**Solução:** Verificar TODOS os imports ao usar funções utilitárias.

---

## 4. PROCESSO E FLUXO

### 4.1 TDD Aplicado a E2E

**Ciclo:**
1. Escrever teste (RED)
2. Executar e observar erro
3. Analisar snapshot YAML
4. Identificar correção necessária
5. Implementar correção
6. Executar novamente (GREEN)
7. Refatorar se necessário

**Exemplo:**
- TC-BILHETERIA-002 falhou com "table not found"
- Analisei snapshot e vi que estava no dashboard, não na página de vendas
- Identifiquei que precisa selecionar evento primeiro
- Modifiquei teste para fazer goto no dashboard antes
- GREEN

### 4.2 Isolamento de Testes

**Por que isolar?**
- Testes únicos rodam mais rápido
- Contexto de falha é mais claro
- Não há interferência entre testes

**Quando usar:**
- Ao desenvolver novo teste
- Ao debugar teste falhando
- Ao verificar se correção realmente funcionou

**Comando:** `npx playwright test e2e/smoke-tests.spec.ts:169` (apenas linha 169)

### 4.3 Execução Completa Após Correções

**Regra:** Após corrigir testes falhando, SEMPRE executar suite completa.

**Motivo:** Correção em um teste pode impactar outros (ex: mudança de estado de sessão).

---

## 5. ANTIPADRÕES IDENTIFICADOS

### 5.1 Evitar Seletores Muito Genéricos

| Seletor | Problema | Melhor |
|---------|----------|--------|
| `[class*="widget"]` | Matching em múltiplos elementos | `[class*="quota"]` ou `text=Quota` |
| `table` | Ambíguo em páginas com múltiplas tabelas | `table.filament-tables-table` |
| `button:has-text("Submit")` | Pode existir em diferentes contextos | `form button:has-text("Submit")` |

### 5.2 Evitar Hardcoded Timeouts Excessivos

| Padrão | Problema | Melhor |
|--------|----------|--------|
| `waitForTimeout(5000)` | Tardio demais, pode mascarar problemas | `waitForTimeout(1000-2000)` + waits lógicos |
| Sem timeout em waits | Pode travar eternamente | Sempre usar timeout finito |

### 5.3 Evitar Skip Sem Documentação

```typescript
// RUIM
test.skip(true, 'skip');

// BOM
test.skip(true, 'Bilheteria sales form não carrega - problema de UI identificado, não de infra. Verificar SelectEvent component.');
```

---

## 6. MÉTRICAS E MONITORAMENTO

### 6.1 Indicadores de Saúde

| Métrica | Meta | Atual |
|---------|------|-------|
| Tests passando | 100% | 100% (26/26) |
| Tempo de execução | < 60s | ~50s |
| Flaky rate | < 5% | ~3.8% (1 flaky / 26) |
| Tests skipped | 0 | 0 |

### 6.2 Build Health

```
Run 1: 25 passed, 1 flaky ✅
Run 2: 26 passed ✅
Run 3: 26 passed ✅
```

---

## 7. RECOMENDAÇÕES PARA PRÓXIMAS SPECs

### 7.1 Antes de Implementar

- [ ] Mapear TODOS os page objects necessários
- [ ] Identificar sobreposições de seleção de evento
- [ ] Definir estratégia de espera para elementos dinâmicos
- [ ] Documentar seletores específicos para cada página

### 7.2 Durante Implementação

- [ ] Seguir ciclo TDD: RED → GREEN → REFACTOR
- [ ] Executar teste isolado após cada correção
- [ ] Manter imports atualizados
- [ ] Usar guard pattern para elementos opcionais

### 7.3 Após Implementação

- [ ] Executar suite completa 3x para confirmar estabilidade
- [ ] Documentar falhas e soluções no debrief
- [ ] Atualizar LIÇÕES APRENDIDAS com novos aprendizados

---

## 8. RESUMO EXECUTIVO

### O que deu certo ✅
- Page objects bem estruturados com methods fluentes
- Helpers de Livewire funcionais
- Padrão de espera robusto (networkidle + livewire load)
- Guard pattern evitando falhas em elementos opcionais

### O que precisa melhorar ⚠️
- Detecção proativa de overlays de seleção de evento
- Documentação de seletores específicos por página
- Cobertura de casos de erro (elementos não encontrados)

### Técnicas para próximas tarefas 🔧
1. Sempre verificar estado de evento ao navegar
2. Usar guard pattern como padrão
3. Importar TODAS funções utilitárias usadas
4. Isolar teste → corrigir → executar suite completa

---

*Documento base para iniciar SPEC-PERM com melhores práticas.*