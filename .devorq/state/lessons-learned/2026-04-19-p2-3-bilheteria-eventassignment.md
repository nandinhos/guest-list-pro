# P2.3: BILHETERIA EventSelectorGrid - Filtro Redundante Removido

**Data:** 2026-04-19
**Projeto:** guest-list-pro
**Status:** ✅ Validada

## Contexto

A role BILHETERIA não conseguia ver eventos no `EventSelectorGrid` mesmo tendo `EventAssignment` criada. O problema era que o código usava um filtro `bilheteria_enabled` no model Event que era redundante e conflitava com a lógica de `EventAssignment`.

## Problema Identificado

**Antes (código problemático):**
```php
$events = Event::query()
    ->where('bilheteria_enabled', true)  // ❌ Filtro redundante
    ->whereIn('id', $user->getAssignedEvents()->pluck('id'))
    ->get();
```

**Source of truth:** `EventAssignment` é a tabela central de permissões. O campo `bilheteria_enabled` no model Event é apenas um flag de feature, não uma permissão de acesso.

## Solução Aplicada

```php
#[Computed]
public function events(): Collection
{
    $user = auth()->user();
    if (! $user) {
        return new Collection;
    }
    return $user->getAssignedEvents();  // ✅ Usa EventAssignment como source of truth
}
```

## Validação

- **E2E Tests:** 26/26 passando ✅
- **TC-BILHETERIA-001 a 004:** Todos passando
- O papel BILHETERIA agora vê apenas os eventos aos quais está atribuído via `EventAssignment`

## Arquitetura de Permissões Confirmada

| Conceito | Implementation |
|----------|----------------|
| Source of truth | `EventAssignment` (tabela central) |
| Roles (ADMIN, PROMOTER, VALIDATOR, BILHETERIA) | ` spatie/laravel-permission` |
| Session-based event selection | `session('selected_event_id')` |
| PromoterPermission | Alias deprecated de EventAssignment |

## Lições

1. **Não misturar flags de feature com permissões de acesso.** `bilheteria_enabled` é um flag de feature, não uma permissão.
2. **EventAssignment é a source of truth para todas as atribuições de eventos.** Qualquer filtro deve passar por ele.
3. **Testes E2E para roles específicas são essenciais** para validar permissões. Testes unitários de policy não capturam erros de filtro em componentes Livewire.

## Arquivos Modificados

- `app/Livewire/EventSelectorGrid.php` — Removido filtro `bilheteria_enabled`, agora usa `getAssignedEvents()` diretamente

## Tags

`permissions` `bilheteria` `eventassignment` `livewire` `e2e`