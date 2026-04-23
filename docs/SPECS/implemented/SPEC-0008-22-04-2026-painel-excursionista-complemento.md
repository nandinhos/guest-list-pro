# SPEC-0008 — Painel Excursionista: Complemento de Desenvolvimento

> **Status:** IMPLEMENTADO
> **Criada em:** 2026-04-22
> **Atualizada em:** 2026-04-22
> **DEVORQ v3 | Stack:** Laravel 12 + Filament v4 + Livewire v3

## Implementado

| # | Item | Status |
|---|------|--------|
| 1 | DocumentType.php — add CNH case + mask + icon + label | ✅ |
| 2 | Migration — update monitores: remove cpf, add document_type + document_number | ✅ |
| 3 | Monitor.php — update fillable + casts | ✅ |
| 4 | ExcursionistaStatsWidget.php — stats: excursoes, veiculos, monitores | ✅ |
| 5 | ExcursionistaPanelProvider — register widget | ✅ |
| 6 | ExcursionistaForm.php — name, email, password, eventAssignments | ✅ |
| 7 | ExcursionistaResource.php (Admin) — use ExcursionistaForm | ✅ |
| 8 | PromoterPermissionForm.php — add EXCURSIONISTA to assignableRoles | ✅ |
| 9 | MonitoresRelationManager — document_type Select + document_number masked | ✅ |
| 10 | Pint clean | ✅ |
| 11 | Tests passing (73 tests) | ✅ |
