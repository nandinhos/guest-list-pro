# LL-026: Middleware 'guest' Não Registrado no Laravel 11+

**Data:** 2026-04-21
**Severity:** High (bloqueou autenticação)
**Status:** Resolvida

## Problema

Acessar `/login` redirecionava para a landing page (`/`). O middleware `guest` em `routes/web.php` não estava funcionando.

## Causa Raiz

No Laravel 11+, o middleware `guest` não vem mais registrado por padrão. A definição:

```php
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});
```

O Laravel interpretava `guest` de forma inesperada (não como RedirectIfAuthenticated), causando redirect para `/`.

## Solução

Remover o middleware `guest` da rota de login em `routes/web.php`. O componente Login já trata autenticação internamente via `AuthenticationService`:

```php
// web.php
Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
```

## Alternativa (se precisar do middleware)

Registrar explicitamente em `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
    ]);
})
```

## Arquivos Envolvidos

- `routes/web.php`

## Prevenção

- Documentar middleware customizados necessários no projeto
- Usar apenas middlewares explicitly registrados no Laravel 11+

---

*Encontrado durante implementação do RefundRequestResource (SPEC-0006)*
