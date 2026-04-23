# SPEC-0012: Fix Login Redirect Loop

## Objetivo
Corrigir loop de redirecionamento quando usuário logado acessa `/login`

## Problema
Quando usuário está logado e acessa `/login` ou clica "Entrar" na landing page, o sistema redirecionava para `/` causando loop.

## Causa Raiz
`Login::mount()` sempre redirecionava para `/` sem verificar a role do usuário.

## Solução
1. `AuthenticationService` já tem método `getPanelUrl(UserRole $role)`
2. Apenas atualizar URLs para incluir `/select-event` onde necessário
3. `Login::mount()` usar `getPanelUrl()` para detectar painel correto

## Arquivos
```
M app/Services/AuthenticationService.php
M app/Livewire/Auth/Login.php
```

## Comportamento Esperado
| Role | Redireciona para |
|------|-----------------|
| admin | /admin |
| promoter | /promoter/select-event |
| validator | /validator/select-event |
| excursionista | /excursionista/select-event |
| bilheteria | /bilheteria/select-event |

## Gates
- [x] Gate 1: SPEC
- [ ] Gate 2: Pre-Flight
- [ ] Gate 3: Quality
- [ ] Gate 4: Code Review
- [ ] Gate 5: Lesson Learned
- [ ] Gate 6: Handoff
- [ ] Gate 7: Deploy
