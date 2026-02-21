# Plano: Unificação de Documentação

## Objetivo

Centralizar toda a documentação em `.aidev/` como centro de verdade, arquivar o legado em `archive/legacy/`, e criar um sistema de índice para facilitar a navegação das LLMs.

---

## Estrutura Proposta

### docs/CONSOLIDATED/
```
docs/CONSOLIDATED/
├── INDEX.md                          ← ÍNDICE PRINCIPAL (SEMPRE ATUALIZAR)
├── architecture/
│   ├── system.md                     # Arquitetura do sistema
│   ├── database.md                   # Modelos e migrations
│   └── panels.md                     # Estrutura dos painéis Filament
├── stack/
│   ├── laravel.md                   # Regras Laravel
│   ├── filament.md                   # Regras Filament
│   └── design-system.md             # Design System completo
├── processes/
│   ├── workflows.md                  # Fluxos de desenvolvimento
│   └── planning.md                   # Sistema de planejamento
├── reference/
│   ├── commands.md                   # Comandos úteis
│   ├── testing.md                    # Guia de testes
│   └── troubleshooting.md            # Solução de problemas comuns
└── legacy/                          # Arquivado
```

### .aidev/
```
.aidev/
├── context/
│   ├── PROJECT.md                   # Contexto do projeto
│   └── STATE.md                     # Estado atual
├── rules/
│   ├── PROJECT.md                   # Regras específicas guest-list-pro
│   └── STACK.md                     # Regras combinadas
└── plans/                           # (já existe)
```

---

## Execução Proposta

### Fase 1: Arquivar Legado
- [ ] Criar `docs/CONSOLIDATED/legacy/`
- [ ] Mover `.antigravity/` → `docs/CONSOLIDATED/legacy/`
- [ ] Mover `.agent/` → `docs/CONSOLIDATED/legacy/`
- [ ] Mover `docs/*.md` → `docs/CONSOLIDATED/legacy/`

### Fase 2: Criar Estrutura Base
- [ ] Criar `docs/CONSOLIDATED/INDEX.md`
- [ ] Criar pastas: `architecture/`, `stack/`, `processes/`, `reference/`
- [ ] Criar `.aidev/context/PROJECT.md`
- [ ] Criar `.aidev/rules/PROJECT.md`

### Fase 3: Popular Arquivos
- [ ] Consolidar `architecture/system.md`
- [ ] Consolidar `stack/filament.md`
- [ ] Consolidar `stack/design-system.md`
- [ ] Consolidar `processes/workflows.md`

### Fase 4: Atualizar Orquestrador
- [ ] Adicionar referências ao índice
- [ ] Adicionar regras aprendidas

### Fase 5: Criar Regra de Índice
- [ ] Adicionar "ATUALIZE O ÍNDICE" ao INDEX.md
- [ ] Documentar no orchestrator

---

## Origem dos Dados

| Fonte | Tipo de Conteúdo |
|-------|-----------------|
| `.antigravity/context.md` | Contexto, agentes, métricas |
| `.antigravity/rules/*.md` | Regras Laravel, Filament, coding standards |
| `.antigravity/agents/*.md` | Definições originais de agentes |
| `.antigravity/workflows/*.md` | Fluxos de trabalho |
| `.aidev/agents/*.md` | Agentes atualizados |
| `.aidev/skills/*.md` | Skills |
| `docs/*.md` | Design System, Standards, etc |

---

**Data**: 2026-02-18  
**Status**: Backlog  
**Prioridade**: Alta
