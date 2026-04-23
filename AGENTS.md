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

Execute o workflow DEVORQ:

```bash
devorq flow "<tarefa>"
```

Ou comandos individuais:

```bash
devorq context      # Ver contexto atual
devorq gate [1-7]   # Executar gate especifico
devorq lessons search "<query>"  # Buscar licoes
```

---

## Sobre este Projeto

| Atributo | Valor |
|----------|-------|
| **Nome** | guest-list-pro |
| **Stack** | filament (Laravel 12 + Filament v4 + Livewire v3) |
| **Objetivo** | Sistema de Gestao de Convidados com controle de duplicidade, aprovacoes e bilheteria |
| **Docker** | Use sempre `vendor/bin/sail` |
| **Orquestrador** | DEVORQ v3.2.1 |

---

## DEVORQ v3 — Workflow

```
devorq flow "<intent>"     # Workflow completo (gates 1-7)
devorq gate [1-7]          # Executar gate especifico
devorq lessons capture     # Capturar licao aprendida
devorq lessons validate    # Validar licao com Context7
devorq compact            # Gerar handoff
devorq stats              # Ver estatisticas
```

### Os 7 Gates (Bloqueantes)

| Gate | Nome | Criterio |
|------|------|----------|
| 1 | SPEC | Contrato detalhado aprovado |
| 2 | Pre-Flight | Tipos, enums, deps validados |
| 3 | Quality | Pint clean, tests passando |
| 4 | Code Review | Revisao por pares |
| 5 | Lesson Learned | Captura de learnings |
| 6 | Handoff | Contexto compactado |
| 7 | Deploy | Deploy aprovado |

---

## Arquivos Importantes

| Arquivo | Proposito |
|---------|-----------|
| `.devorq/state/context.json` | Contexto atual do projeto |
| `.devorq/state/lessons/` | Licoes aprendidas (captured/downloaded) |
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

Apos ler este arquivo, execute `devorq context` para ver o estado atual do projeto.

*Atualizado em 2026-04-22 - DEVORQ v3.2.1*