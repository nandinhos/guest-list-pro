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

---

## SPEC-0008 — Central de Gestão de Excursionistas (CRUD)

**Status:** ATIVO

**Objetivo:** Converter a página de relatório `/admin/reports/excursoes` em central de gestão CRUD completa para Excursões, Veículos e Monitores, usando 3 abas com tabela Filament nativa.

### Gates

```
GATE-1  │  Converter ExcursoesReport → ExcursoesGestao com HasTable + 3 abas
GATE-2  │  CRUD Excursões (criar/editar modal + excluir com confirmação)
GATE-3  │  CRUD Veículos (criar/editar modal com Select de excursão filtrado)
GATE-4  │  CRUD Monitores (criar/editar modal com documento + veículo opcional)
GATE-5  │  View Blade com abas clicáveis e contadores
GATE-6  │  Testes atualizados cobrindo CRUD das 3 entidades
```

### Critérios de Aceite

- [ ] CA-01: Página acessível em `/admin/reports/excursoes`
- [ ] CA-02: 3 abas com contador de registros do evento selecionado
- [ ] CA-03: Criar excursão salva `event_id` e `criado_por` automaticamente
- [ ] CA-04: Editar excursão abre modal com dados preenchidos
- [ ] CA-05: Excluir excursão remove cascade (veículos + monitores)
- [ ] CA-06: Select de excursão no formulário de veículo filtra por evento
- [ ] CA-07: Monitor sem veículo exibe `Sem veículo` na tabela
- [ ] CA-08: Unique constraint de documento exibe validação amigável
- [ ] CA-09: Trocar aba recarrega tabela corretamente
- [ ] CA-10: Todos os testes passando + Pint clean
