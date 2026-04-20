# UserFactory sem role/is_active causa 403 em Tests de Resources Filament

**Data:** 2026-04-20
**Tags:** testing, auth, filament, policies

## Contexto

Ao criar testes Feature para Resources Filament que possuem Policies (como `TicketSaleResource` com `TicketSalePolicy`), os testes retornavam HTTP 403 Forbidden.

## Problema

```php
// Teste que falhava
$user = User::factory()->create();  // role=NULL, is_active=NULL
$this->actingAs($user);
Livewire::test(ListTicketSales::class)->assertStatus(200);  // 403!
```

## Causa Raiz

`UserFactory` define apenas `name`, `email`, `password`, `remember_token`. Não define `role` nem `is_active`.

```php
// UserFactory.php - valores default
role = NULL
is_active = NULL
```

`TicketSalePolicy::viewAny()` exige:
```php
return in_array($user->role, [UserRole::ADMIN, UserRole::BILHETERIA]) && $user->is_active;
// NULL não está em [ADMIN, BILHETERIA] → false
// NULL && true → false → 403
```

## Solução

Sempre especificar `role` e `is_active` ao criar users para testes de Resources:

```php
$user = User::factory()->create([
    'role' => UserRole::BILHETERIA,
    'is_active' => true,
]);
$this->actingAs($user);
```

## Prevenção

Ao criar testes Feature para Filament Resources com Policies:
1. Verificar qual role a Policy espera
2. Criar user com role adequada
3. Sempre definir `is_active: true` explicitamente

## Arquivos Modificados

- `tests/Feature/TicketSalesMobileViewTest.php` - Corrigido setup()

## Leitura Adicional

- `app/Policies/TicketSalePolicy.php`
- `database/factories/UserFactory.php`
