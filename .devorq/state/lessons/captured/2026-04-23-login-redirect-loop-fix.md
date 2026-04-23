# Lesson Learned: Redirect Loop em Login with Multiple Panels

## Data: 2026-04-23
## Feature: SPEC-0012

## Problema
Usuário logado acessando `/login` ou clicando "Entrar" na landing page entrava em loop de redirect.

## Causa Raiz
`Login::mount()` sempre redirecionava para `/` sem verificar role do usuário.

## Solução
1. reutilizar `AuthenticationService::getPanelUrl()` existente
2. atualizar `PANEL_ROUTES` para incluir `/select-event`
3. usar `getPanelUrl(Auth::user()->role)` no mount

## Código Antes:
```php
public function mount(): void
{
    if (Auth::check()) {
        $this->redirect('/', navigate: false); // ❌ Sempre /
    }
}
```

## Código Depois:
```php
public function mount(): void
{
    if (Auth::check()) {
        $authService = app(\App\Services\AuthenticationService::class);
        $redirectUrl = $authService->getPanelUrl(Auth::user()->role);
        $this->redirect($redirectUrl, navigate: false);
    }
}
```

## Arquivos Modificados:
- `app/Services/AuthenticationService.php` - URLs corretas
- `app/Livewire/Auth/Login.php` - mount() usa getPanelUrl()

## takeaway
Verificar se já existe serviço/método que pode ser reutilizado antes de criar novo código.
