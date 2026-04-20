# DEVORQ v2.1 - Orquestrador de Desenvolvimento

## Visão Geral

Este projeto usa **DEVORQ** como orquestrador único de desenvolvimento.

---

## Ativação

Execute `/activate-agents` ou leia `.devorq/rules/project.md`

---

## Estrutura DEVORQ

```
.devorq/
├── rules/project.md    # Regras e gates do projeto
├── state/lessons-learned/  # 23 lições aprendidas
├── plans/              # Planos de implementação
└── version             # v2.1.1
```

---

## Fluxo DEVORQ

1. **Gate 1-4**: Especificação e validação
2. **Gate 5**: Captura de lições aprendidas
3. **Gate 6**: Validação de lições
4. **Gate 7**: Aplicação de lições

---

## Princípios

- **TDD**: RED → GREEN → REFACTOR
- **Documentação antes do código**
- **Sempre use sail** para comandos Artisan

---

*Substitui o antigo .aidev - limpa em 2026-04-20*