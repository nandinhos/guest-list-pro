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

**Após ler este arquivo, execute `/activate-agents` ou leia `.aidev/QUICKSTART.md`**

*Atualizado em 2026-02-18*
