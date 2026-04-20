# Proposal: Automação de Gates DEVORQ — Análise de Gaps

**Documento:** TD-001-2026-04-19
**Tipo:** Technical Debt / Feature Request
**Stack:** DEVORQ v2.1
**Data:** 2026-04-19
**Autor:** Nando Dev
**Versão:** 1.0

---

## 1. Resumo Executivo

O DEVORQ v2.1 define um pipeline de 10 gates com fluxo obrigatório (init → scope-guard → pre-flight → handoff → TDD → quality-gate → session-audit → learned-lesson → lessons validate → lessons apply), porém **3 gates críticos não são automatizados**:

- **Gate 5** (`/learned-lesson`): Captura de lições aprendidas
- **Gate 6** (`devorq lessons validate`): Validação via Context7
- **Gate 7** (`devorq lessons apply`): Aplicação e indexação

**Impacto:** Lições aprendidas durante desenvolvimento não são capturadas automaticamente, gerando perda de conhecimento organizacional e retrabalho em issues similares.

---

## 2. Problema Identificado

### 2.1 Fluxo Definido vs Implementado

| # | Gate | Comando Defined | Automation Status | Problem |
|---|------|-----------------|-------------------|---------|
| 1 | Scope Guard | `/scope-guard` | ⚠️ Manual | Requer slash command |
| 2 | Pre-flight | `/pre-flight` | ⚠️ Manual | Requer slash command |
| 3 | Quality Gate | `/quality-gate` | ⚠️ Manual | Requer slash command |
| 4 | Handoff | `devorq handoff generate` | ⚠️ Manual | CLI command |
| 5 | **Learned Lesson** | **`/learned-lesson`** | ❌ **NÃO EXISTE** | Slash command não existe |
| 6 | **Lessons Validate** | **`devorq lessons validate`** | ⚠️ **Parcial** | Requer execução manual |
| 7 | **Lessons Apply** | **`devorq lessons apply`** | ⚠️ **Parcial** | Requer execução manual |

### 2.2 Comportamento Observado

**Cenário real em projeto brownfield (guest-list-pro):**

```
Task implementada → Tests passando → SPEC movida → FIM
                                                          ↑
                                            Gate 5-7 não disparados
```

**Lição aprendida durante debug:**
- Repeater usava `../event_id` → deveria ser `../../event_id`
- Tentativa de usar `allowDuplicates(false)` → método inexistente
- Capturei manualmente após perceber, mas **NÃO** foi via gate automatizado

### 2.3 Gap Principal

O DEVORQ espera que o LLM execute:
```bash
devorq lessons validate
devorq lessons apply <nome>
```

Mas **não há trigger automático** para esses comandos após:
1. Task completada com erro/debug
2. Bug identificado e corrigido
3. Issue não预料 encontrada

---

## 3. Arquitetura Atual

### 3.1 Estrutura de Pastas

```
.devorq/
├── state/
│   └── lessons-learned/     # 22 lições catalogadas
├── rules/
│   └── project.md           # Stack e regras (stack: generic ❌)
├── plans/
│   └── 2026-04-19-*.md     # Planos implementados
└── version                  # v2.1
```

### 3.2 Comandos Disponíveis

```bash
devorq lessons list                  # Mostra: 0 pendentes, 0 validadas
devorq lessons validate               # Valida via Context7
devorq lessons apply <nome> [--target] # Aplica lição
```

**Saída atual:**
```
=== LIÇÕES PENDENTES ===
  Nenhuma lição pendente.
=== LIÇÕES VALIDADAS ===
  Nenhuma lição validada.
=== LIÇÕES APLICADAS ===
  Nenhuma lição aplicada.
```

**Problema:** Lições estão em `.devorq/state/lessons-learned/*.md` mas o `devorq lessons` não as detecta.

### 3.3 Stack Não Detectada

```bash
$ devorq info
Stack:     generic  ❌ ERRADO (deveria ser "filament")
```

Mesmo com `.devorq/rules/project.md` configurado corretamente, o `devorq context` mostra `generic`.

---

## 4. Impacto

### 4.1 Quantitativo

| Métrica | Valor |
|---------|-------|
| Lições aprendidas perdidas por sprint | ~3-5 |
| Tempo perdido em issues similares | ~2h por issue |
| Gates executados manualmente | 0% (nenhum) |

### 4.2 Qualitativo

1. **Conhecimento não captura:** Lições aprendidas durante debug são perdidas
2. **Repetição de erros:** Mesmo bug ocorre em diferentes contextos
3. **Inconsistência:** Gates executados "quando lembrar" ao invés de sempre
4. **Gap de automation:** DEVORQ se propõe a ser "orquestrador" mas não orchestra

---

## 5. Proposta de Solução

### 5.1 Solução 1: Hooks Automatizados (Recomendado)

**Adicionar git hooks que disparam gates em momentos específicos:**

```bash
# .git/hooks/post-commit
devorq lessons validate  # Após cada commit

# .git/hooks/post-merge
devorq lessons apply --recent  # Após merge de feature
```

**Vantagens:**
- Execução automática garantida
- Não depende de disciplina do LLM
- Captura imediata do contexto

**Problemas:**
- Hooks precisam ser instalados (`devorq hooks install` não funciona)
- Requer configuração adicional no projeto

### 5.2 Solução 2: Inline Triggers no Código

**Adicionar comentários especiais que disparam captura:**

```php
// @devorq-learned: Repeater precisa de ../../ para acessar campos do formulário
// @devorq-gate: 5
```

**Vantagens:**
- Captura no momento exato
- Contexto preservado

**Problemas:**
- Invasivo no código
- Requer parser especial

### 5.3 Solução 3: Post-Implementation Checklist

**Documentar e seguir checklist obrigatório:**

```markdown
## Checklist de Task Completa (já em project.md)

- [ ] Código implementado
- [ ] Tests passando
- [ ] SPEC movida para implemented/
- [ ] GATE 5: Lição documentada ❌
- [ ] GATE 6: devorq lessons validate ❌
- [ ] GATE 7: ctx_index ❌
```

**Vantagens:**
- Simples de implementar
- Não requer mudanças no DEVORQ

**Problemas:**
- Depende de disciplina
- Não é "automágico"

---

## 6. Recomendação

### 6.1 Implementação Proposta

**Prioridade alta:**

1. **Corrigir detecção de stack** — `devorq context` deve ler `.devorq/rules/project.md`

2. **Criar hook funcional** — `devorq hooks install` deve criar `.git/hooks/pre-commit` que executa:
   ```bash
   # Pré-commit: verificar se há lições pendentes
   devorq lessons validate
   ```

3. **Adicionar trigger pós-task** — Após subagent completar task, verificar se houve:
   - Erro/debug
   - Bug identificado
   - Issue não预料
   Se sim → sugerir captura de lição

### 6.2 Mudanças no CLI

```bash
# Novo comando
devorq lessons capture --auto  # Captura automática via AI

# Gate 5 deveria ser acessível via
/devorq-learned               # Slash command para captura
```

### 6.3 Interface com OpenCode

O OpenCode não suporta slash commands customizados (`/learned-lesson` não existe).

**Alternativas:**
1. Criar skill DEVORQ que captura lições via interação
2. Adicionar trigger no hook `pretooluse.mjs` do context-mode
3. Documentar que gates devem ser executados manualmente (workaround)

---

## 7. Testes de Validação

### 7.1 Cenário de Teste

```bash
# 1. Implementar feature com bug
# 2. Corrigir bug
# 3. Verificar se lição foi capturada
devorq lessons list
# Esperado: 1 lição pendente
```

### 7.2 Critérios de Aceite

- [ ] `devorq lessons list` detecta lições em `.devorq/state/lessons-learned/`
- [ ] `devorq lessons validate` funciona sem CLI manual
- [ ] Stack correta é detectada (`filament` ao invés de `generic`)
- [ ] Hook instalado funciona no post-commit

---

## 8. Arquivos de Referência

### 8.1 Configuração Atual do Projeto

```markdown
.devorq/rules/project.md:
  stack: filament ✅ (mas não detectado)
  gates: 5-7 obrigatórios (documentados mas não executados)

.devorq/state/lessons-learned/:
  22 lições catalogadas
  0 detectadas pelo devorq lessons
```

### 8.2 Output do DEVORQ

```bash
$ devorq info
Stack: generic ❌ (deveria ser filament)

$ devorq lessons list
Nenhuma lição pendente ❌ (há 22 arquivos .md)
```

---

## 9. Conclusão

O DEVORQ v2.1 possui a **estrutura conceitual correta** (gates, pipeline, lessons), mas **falta implementação de automation** para os gates 5-7.

O fluxo manual funciona quando há disciplina, mas em ambiente de alta velocidade (sprints), as lições são perdidas.

**Próximo passo recomendado:**
1. Criar issue no repositório DEVORQ com este documento
2. Propor `devorq lessons capture --auto` como feature
3. Implementar hook funcional (`devorq hooks install`)

---

## 10. Appendix

### A. Comandos Testados

```bash
devorq --version              # v2.1 ✓
devorq activate               # ✓
devorq context                # Mostra generic ❌
devorq hooks install          # Erro: hook não encontrado
devorq lessons list           # 0 lições ❌
devorq lessons validate       # "nenhuma pendente" ❌
devorq update                 # Already up to date ✓
```

### B. Estrutura de Lições

```
.devorq/state/lessons-learned/
├── 2026-02-17-*.md (4 arquivos)
├── 2026-02-21-*.md (14 arquivos)
├── 2026-04-19-p2-3-bilheteria-eventassignment.md
└── 2026-04-19-ticket-type-resource-repeater.md
```

Total: 22 arquivos

---

**Documento criado para proposição de melhoria no DEVORQ.**
**Repository:** github.com/nandinhos/devorq