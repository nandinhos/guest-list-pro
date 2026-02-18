# guest-list-pro - Instruções para IA

## AI Dev Superpowers

Este projeto usa **AI Dev Superpowers** para governança de desenvolvimento com IA.

### ⚠️ IMPORTANTE: Docker/Sail

> Este projeto roda em containers Docker. Use **SEMPRE** `vendor/bin/sail`!

```bash
# Errado
php artisan test

# Correto
vendor/bin/sail artisan test
```

Adicione ao seu shell: `alias sail='vendor/bin/sail'`

---

### Ativação do Modo Agente

**Opção 1 - Comando direto (recomendado):**
```bash
aidev agent
```
Copie o prompt gerado e cole aqui.

**Opção 2 - Ativação por trigger:**
O usuário dirá um dos seguintes:
- **"modo agente"**
- **"aidev"**
- **"superpowers"**
- **"ativar agentes"**

### O que fazer ao ativar

1. Leia o arquivo `.aidev/agents/orchestrator.md`
2. Siga as diretrizes do orquestrador
3. Use TDD obrigatoriamente (RED -> GREEN -> REFACTOR)
4. Use **sail** para todos os comandos!

### Agentes Disponíveis (9)

| Agente | Responsabilidade |
|--------|------------------|
| orchestrator | Coordenação geral e classificação de intent |
| architect | Design e planejamento |
| backend | Implementação server-side (TDD) |
| frontend | Implementação client-side (TDD) |
| code-reviewer | Revisão de qualidade e padrões |
| qa | Testes e validação |
| security-guardian | Segurança e OWASP |
| devops | Deploy e infra |
| legacy-analyzer | Código legado |

### Informações do Projeto

- **Nome**: guest-list-pro
- **Stack**: filament (Laravel 12 + Filament v4 + Livewire v3)
- **Docker**: Sempre usar `vendor/bin/sail`

---

*Atualizado em 2026-02-18*