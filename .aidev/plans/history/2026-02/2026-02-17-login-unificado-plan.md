# Plano: Login Unificado

## Visão Geral

Criar um sistema de login centralizado que redireciona automaticamente para o painel correto baseado no perfil do usuário.

**Complexidade**: Média-Alta  
**Estimativa**: 8-12 horas  
**Pré-requisitos**: Landing page concluída

---

## Escopo

### Login Centralizado (Rota `/login`)
- Uma única tela com email + senha
- Ao autenticar, redireciona automaticamente para o painel correto
- Recuperação de senha
- Design profissional alinhado com a Landing

### Fluxo de Autenticação

```
Usuário acessa /login
       ↓
   Insere email + senha
       ↓
Sistema autentica
       ↓
Verifica role do usuário
       ↓
Redireciona para painel:
- ADMIN → /admin
- PROMOTER → /promoter
- VALIDATOR → /validator
- BILHETERIA → /bilheteria
```

---

## Tasks de Implementação

### Task 1: AuthenticationService
**Arquivos:**
- `app/Services/AuthenticationService.php`

**Responsabilidades:**
- Validar credenciais
- Verificar role do usuário
- Retornar painel de destino

### Task 2: Livewire Login
**Arquivos:**
- `app/Livewire/Auth/Login.php`
- `resources/views/livewire/auth/login.blade.php`

**Funcionalidades:**
- Validação de email/senha
- "Lembrar-me"
- Link para recuperação de senha
- Feedback de erro

### Task 3: Rota e Controller
**Arquivos:**
- `routes/web.php`
- `app/Http/Controllers/AuthController.php`

### Task 4: Logout
**Arquivos:**
- `app/Http/Controllers/LogoutController.php`

### Task 5: Recuperação de Senha (opcional)
- Implementar se necessário

---

## Estrutura de Arquivos

```
app/
├── Http/Controllers/
│   ├── AuthController.php
│   └── LogoutController.php
├── Livewire/
│   └── Auth/
│       └── Login.php
├── Services/
│   └── AuthenticationService.php

resources/views/
└── livewire/
    └── auth/
        └── login.blade.php
```

---

## Design

O login deve seguir o mesmo design system da landing page:
- Glassmorphism
- Cores por perfil (opcional)
- Responsivo

---

## Checklist de Validação

- [ ] Login funciona para todos os 4 perfis
- [ ] Redirect correto após login
- [ ] Logout funciona
- [ ] Erros exibidos corretamente
- [ ] Segurança (CSRF, rate limiting)
- [ ] Testes passando

---

## Decisões Técnicas a Serem Tomadas

1. **Manter logins dos painéis ou substituir?**
   - Opção A: Manter como alternativa
   - Opção B: Substituir completamente

2. **Rota da landing page:**
   - Manter em `/` com login button
   - Ou mover para `/features`

---

**Data**: 2026-02-17  
**Status**: Backlog  
**Prioridade**: Alta  
**Dependência**: Landing page
