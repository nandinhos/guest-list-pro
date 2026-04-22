# guest-list-pro — Specification Index

> Este arquivo aponta para as specs do projeto.

## Specs Ativas

| Arquivo | Descrição | Status |
|---------|-----------|--------|
| [SPEC-0007-painel-excursionista.md](.devorq/state/specs/SPEC-0007-painel-excursionista.md) | Painel Excursionista | **EM REVISÃO** (GATE-1 pendente) |

---

## SPEC-0007 — Painel Excursionista

**Status:** EM REVISÃO — Aguarda GATE-1 (Nando)

```
Branch: feat/SPEC-0007-excursionista
Painel: /excursionista
Role: EXCURSIONISTA
```

### Resumo

- 3 entidades: Excursao, Veiculo, Monitor
- 1 painel Filament dedicado
- Admin extension (ExcursionistaResource)

### Gates

```
GATE-1  │  SPEC aprovada pelo Nando           │  ← AGUARDANDO
GATE-2  │  Pre-Flight: migrations + tests
GATE-3  │  Quality: Pint + E2E smoke
```

### Critérios de Aceite

- [ ] CA-01: Usuário EXCURSIONISTA acessa /excursionista
- [ ] CA-02: Dashboard com contadores
- [ ] CA-03: CRUD Excursão + Veículos
- [ ] CA-04: CRUD Monitor com Select reativo
- [ ] CA-05: Criação inline de Excursão via modal
- [ ] CA-06: Validação CPF
- [ ] CA-07: Validação Placa Mercosul
- [ ] CA-08: Admin gerencia excursionistas
- [ ] CA-09: Cascade delete
- [ ] CA-10: Tests passando
- [ ] CA-11: Pint clean
- [ ] CA-12: E2E smoke tests

---

**Stack:** Laravel 12 + Filament v4 + Livewire v3 + PostgreSQL
**DEVORQ:** v3.2.1
**Atualizado:** 2026-04-22
