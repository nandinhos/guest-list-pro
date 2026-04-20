# Lição: Rota password.request do Laravel

**Data**: 2026-02-21
**Stack**: Laravel Auth + Filament
**Tags**: backend|auth|laravel

## Contexto

Erros de rota não encontrada ao apontar para "esqueci senha" sem Breeze/Jetstream. O helper `route('password.request')` referencia rota que não existe em setups customizados.

**Ambiente**: Laravel sem Jetstream/Breeze
**Frequência**: Baixa (apenas página de login customizada)
**Impacto**: Médio — usuário não consegue redefinir senha

## Problema

Em layouts de autenticação customizados (sem Breeze/Jetstream):
```blade
<a href="{{ route('password.request') }}">Esqueci minha senha</a>

{{-- ERRO: Route [password.request] not defined --}}
```

## Causa Raiz

Laravel Breeze/Jetstream registram rotas de reset password. Sem eles, `route('password.request')` não existe.

## Solução

**Opção 1**: Verificar se rotas existem antes de usar:
```blade
@if(Route::has('password.request'))
    <a href="{{ route('password.request') }}">Esqueci minha senha</a>
@endif
```

**Opção 2**: Usar placeholder até feature implementada:
```blade
<a href="javascript:void(0)" onclick="alert('Em breve')">Esqueci minha senha</a>
```

**Opção 3**: Implementar feature completa (futuro):
- Criar controller `ForgotPasswordController`
- Adicionar rota `password.request`
- Criar view de solicitação de reset

## Prevenção

- [ ] Sempre verificar `Route::has()` antes de usar rotas de auth
- [ ] Usar placeholders para features não implementadas
- [ ] Documentar rotas de auth faltantes

## Referências

- [Laravel Password Reset](https://laravel.com/docs/11.x/passwords)
- [Route Helpers](https://laravel.com/docs/11.x/urls#generating-urls)