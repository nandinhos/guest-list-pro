# 📚 Documentação Consolidada - Guest List Pro

> **ÍNDICE CENTRAL** - Este arquivo é a porta de entrada para toda documentação.
> ⚠️ **REGRA OBRIGATÓRIA**: Ao criar novo arquivo ou pasta nesta pasta `CONSOLIDATED/`, você DEVE atualizar este índice!

---

## 🚀 Quick Links

| Necessidade | Arquivo |
|-------------|---------|
| Contexto do projeto | `.devorq/rules/project.md` |
| **⚠️ Docker/Sail** | Use sempre `vendor/bin/sail artisan ...` |
| Arquitetura do sistema | `architecture/system.md` |
| Stack (Laravel/Filament) | `stack/` |
| Design System | `stack/design-system.md` |
| Fluxos de trabalho | `processes/workflows.md` |
| Comandos úteis | `reference/commands.md` |
| Testes | `reference/testing.md` |

---

## 📂 Estrutura

### architecture/
- [system.md](architecture/system.md) - Arquitetura geral do sistema
- [database.md](architecture/database.md) - Modelos e migrations
- [panels.md](architecture/panels.md) - Estrutura dos painéis Filament

### stack/
- [laravel.md](stack/laravel.md) - Regras e padrões Laravel
- [filament.md](stack/filament.md) - Regras e padrões Filament
- [design-system.md](stack/design-system.md) - Design System completo

### processes/
- [workflows.md](processes/workflows.md) - Fluxos de desenvolvimento
- [planning.md](processes/planning.md) - Sistema de planejamento

### reference/
- [commands.md](reference/commands.md) - Comandos úteis
- [testing.md](reference/testing.md) - Guia de testes
- [troubleshooting.md](reference/troubleshooting.md) - Problemas comuns

### legacy/
- [antigravity/](legacy/antigravity/) - Documentação original do Antigravity
- [agent/](legacy/agent/) - Workflows do .agent
- Arquivos diversos: DESIGN-SYSTEM.md, DEVELOPMENT_STANDARDS.md, etc.

---

## 📋 Como Usar

1. **Para nova funcionalidade**: Comece pelo `architecture/system.md`
2. **Para dúvidas de código**: Consulte `stack/filament.md` ou `stack/laravel.md`
3. **Para processos**: Veja `processes/workflows.md`
4. **Para referência**: `reference/` tem comandos e soluções

---

## ⚠️ Regra de Atualização

**SEMPRE** que criar um novo arquivo ou pasta em `docs/CONSOLIDATED/`, você deve:

1. Atualizar este índice adicionando o novo arquivo na seção apropriada
2. Adicionar descrição breve do conteúdo
3. Manter ordem alfabética dentro de cada seção

**Formato de atualização:**
```markdown
- [nome.md](pasta/nome.md) - Descrição breve
```

---

## ⚠️ Regra Docker/Sail (IMPORTANTE)

> Este projeto roda em containers Docker. **SEMPRE** use `vendor/bin/sail` ou o alias `sail` para comandos Artisan!

### Formato Correto
```bash
# Errado (funciona apenas dentro do container)
php artisan test

# Correto (funciona de fora do container)
vendor/bin/sail artisan test
sail artisan test

# Para npm também
vendor/bin/sail npm run dev
sail npm run build
```

### Atalho (alias)
Se não tiver `sail` como alias, adicione ao seu shell:
```bash
alias sail='vendor/bin/sail'
```

---

## 🎯 Agentes e Contexto

Para desenvolvimento assistido por IA, consulte:
- `.devorq/rules/project.md` - Regras e gates do projeto
- `.devorq/state/lessons-learned/_INDEX.md` - Índice de lições aprendidas
- `.devorq/version` - Versão do DEVORQ (v2.1)

---

*Última atualização: 2026-04-20*
