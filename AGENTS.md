# 🚀 BOOTSTRAP — Leia este arquivo primeiro

> **Para qualquer LLM ou assistente de IA:**
> Este arquivo contém instruções para ativar o modo agente deste projeto.

---

## ⚠️ IMPORTANTE: Docker/Sail

> Este projeto roda em containers Docker. **SEMPRE** use `vendor/bin/sail` para comandos!

```bash
# Errado
php artisan test

# Correto
vendor/bin/sail artisan test
```

Adicione ao seu shell: `alias sail='vendor/bin/sail'`

---

## Ativação Rápida

Execute o workflow de ativação:

```
/activate-agents
```

Ou manualmente:

1. Leia `.aidev/QUICKSTART.md`
2. Leia `.aidev/agents/orchestrator.md`
3. Confirme ativação ao desenvolvedor

---

## Sobre este Projeto

| 属性 | Valor |
|------|-------|
| **Nome** | guest-list-pro |
| **Stack** | filament (Laravel 12 + Filament v4 + Livewire v3) |
| **Objetivo** | Sistema de Gestão de Convidados com controle de duplicidade, aprovações e bilheteria |
| **Docker** | Use sempre `vendor/bin/sail` |

---

## Arquivos Importantes

| Arquivo | Propósito |
|---------|-----------|
| `.aidev/QUICKSTART.md` | Quickstart do modo agente |
| `.aidev/agents/orchestrator.md` | Orquestrador principal |
| `.aidev/context/PROJECT.md` | Contexto do projeto |
| `docs/CONSOLIDATED/INDEX.md` | Índice de documentação |
| `docs/CONSOLIDATED/stack/` | Regras de stack |

---

## Princípio Fundamental

> **Documentação vem antes do código.**
> Sempre valide antes de implementar.
> Sempre use **sail** para comandos!
> Sempre use TDD (RED → GREEN → REFACTOR).

---

## Context Mode (Obrigatório)

Este projeto usa context-mode para proteção do context window.

### Think in Code — MANDATÓRIO
Ao analisar dados: **escreva código** via `ctx_execute` e `console.log()` apenas o resultado.

### Comandos BLOCKED
- `curl`/`wget` — use `ctx_fetch_and_index`
- HTTP inline — use `ctx_execute` com fetch

### Hierarquia de Ferramentas
1. `ctx_batch_execute` — múltiplos comandos + busca em uma chamada
2. `ctx_search(queries)` — consulta conteúdo indexado
3. `ctx_execute` / `ctx_execute_file` — execução em sandbox
4. `ctx_fetch_and_index` + `ctx_search` — web

### Comandos Úteis
- `ctx stats` — estatísticas de economia de tokens
- `ctx doctor` — diagnóstico da instalação

---

**Após ler este arquivo, execute `/activate-agents` ou leia `.aidev/QUICKSTART.md`**

*Atualizado em 2026-02-18*
