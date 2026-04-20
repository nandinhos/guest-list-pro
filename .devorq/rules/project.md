# Regras do Projeto - guest-list-pro (Brownfield)

## Contexto
- Tipo: Projeto em andamento
- Stack: filament (Laravel 12 + Filament 4 + Livewire v3)
- Regra: "First, do no harm"

---

## Gates DEVORQ — Obrigatórios

Após cada task implementation, OBRIGATÓRIO seguir esta sequência:

```
IMPLEMENTAÇÃO COMPLETA
        ↓
GATE 5: Capturar lição (auto-trigger se >15min ou flag)
        ↓
GATE 6: Validar lição (devorq lessons validate)
        ↓
GATE 7: Indexar e aplicar (ctx_index + skill update)
```

### Gate 5 — Lesson Capture
- Dispara automaticamente se task durou >15min
- Captura: contexto, problema, solução, prevenção
- Formato: `.devorq/state/lessons-learned/YYYY-MM-DD-title.md`
- NÃO prosseguir sem capturar se houve erro/bug/issue

### Gate 6 — Lessons Validate
- Executar `devorq lessons validate` após cada captura
- Verificar: completude, padrões DEVORQ, duplicatas

### Gate 7 — Lessons Apply
- Indexar lição no ctx via `ctx_index`
- Atualizar skills se relevante
- Referenciar em future tasks similares

---

## Regras de Ouro

1. **Consistência** com padrões existentes
2. **TDD** para novas funcionalidades (RED → GREEN → REFACTOR)
3. **Minimalismo** — não sobreengenharias
4. **Documentar** TODA lição aprendida
5. **Usar sail** para comandos artisan (Docker environment)

---

## Fluxo de Desenvolvimento

```
[ ] Gate 1: Scope Guard (escopo validado ✅)
[ ] Gate 2: Pre-flight (pré-requisitos)
[X] Gate 3: Quality Gate (testes passando)
[ ] Gate 4: Handoff (quando necessário)
[ ] Gate 5: Learned Lesson (OBRIGATÓRIO após bugs/errors)
[ ] Gate 6: Lessons Validate (OBRIGATÓRIO pós-captura)
[ ] Gate 7: Lessons Apply (OBRIGATÓRIO após validação)
```

---

## Estrutura de Pastas

```
.devorq/
├── plans/          # Planos de implementação
├── state/
│   ├── lessons-learned/   # Lições capturadas
│   └── session.json      # Estado da sessão
├── rules/          # Este arquivo
└── docs/           # SPECs do projeto
```

---

## Stack Correta

```json
{
  "stack": "filament",
  "project_type": "brownfield",
  "llm": "opencode",
  "runtime": "terminal-cli",
  "database": "mysql"
}
```

---

## Checklist de Task Completa

- [ ] Código implementado
- [ ] Tests passando (E2E ou unitários)
- [ ] SPEC movida para `implemented/`
- [ ] GATE 5 executado (lição documentada se bug/error)
- [ ] GATE 6 executado (`devorq lessons validate`)
- [ ] GATE 7 executado (ctx_index + update _INDEX.md)
- [ ] Arquivos limpos (sem TODO, sem comentários desnecessários)