# BOOTSTRAP - Leia este arquivo primeiro

> Para qualquer LLM ou assistente de IA:
> Este arquivo contém instruções para ativar o modo agente deste Projeto.

---

## IMPORTANTE: Docker/Sail

> Este projeto roda em containers Docker. SEMPRE use `vendor/bin/sail` para comandos!

```bash
# Errado
php artisan test

# Correto
vendor/bin/sail artisan test
```

Adicione ao seu shell: `alias sail='vendor/bin/sail'`

---

## Ativacao Rapida

Execute o workflow de ativacao:

```
/activate-agents
```

Ou manualmente:

1. Leia `.devorq/rules/project.md`
2. Leia `.devorq/state/lessons-learned/_INDEX.md`
3. Confirme ativacao ao desenvolvedor

---

## Sobre este Projeto

| Atributo | Valor |
|----------|-------|
| **Nome** | guest-list-pro |
| **Stack** | filament (Laravel 12 + Filament v4 + Livewire v3) |
| **Objetivo** | Sistema de Gestao de Convidados com controle de duplicidade, aprovacoes e bilheteria |
| **Docker** | Use sempre `vendor/bin/sail` |
| **Orquestrador** | DEVORQ v2.1 |

---

## Arquivos Importantes

| Arquivo | Proposito |
|---------|-----------|
| `.devorq/rules/project.md` | Regras e gates do projeto |
| `.devorq/state/lessons-learned/_INDEX.md` | Indice de licoes aprendidas |
| `docs/CONSOLIDATED/INDEX.md` | Indice de documentacao |
| `docs/CONSOLIDATED/stack/` | Regras de stack |

---

## Principio Fundamental

> Documentacao vem antes do codigo.
> Sempre valide antes de implementar.
> Sempre use sail para comandos!
> Sempre use TDD (RED -> GREEN -> REFACTOR).

---

## Context Mode (Obrigatorio)

Este projeto usa context-mode para protecao do context window.

### Think in Code - MANDATORIO
Ao analisar dados: escreva codigo via `ctx_execute` e `console.log()` apenas o resultado.

### Comandos BLOCKED
- `curl`/`wget` - use `ctx_fetch_and_index`
- HTTP inline - use `ctx_execute` com fetch

### Hierarquia de Ferramentas
1. `ctx_batch_execute` - multiplos comandos + busca em uma chamada
2. `ctx_search(queries)` - consulta conteudo indexado
3. `ctx_execute` / `ctx_execute_file` - execucao em sandbox
4. `ctx_fetch_and_index` + `ctx_search` - web

### Comandos Uteis
- `ctx stats` - estatisticas de economia de tokens
- `ctx doctor` - diagnostico da instalacao

---

Apos ler este arquivo, execute `/activate-agents` ou leia `.devorq/rules/project.md`

*Atualizado em 2026-04-20*
