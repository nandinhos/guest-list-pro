# SPEC-0011: User Profile Page

## Objetivo
Adicionar página de perfil do usuário logado acessível via dropdown do header Filament, permitindo editar dados pessoais e alterar senha.

## Requisitos

### 1. Campos do Perfil
| Campo | Tipo | Obrigatório | Editável |
|-------|------|-------------|----------|
| Nome | text | Sim | Sim |
| Email | email | Sim | Sim |
| Senha Atual | password | Sim* | Não |
| Nova Senha | password | Sim* | Não |
| Confirmar Nova Senha | password | Sim* | Não |

*Apenas quando o usuário solicitar mudança de senha.

### 2. Validações
- Nome: max:255 caracteres
- Email: email válido, único (ignorando usuário atual)
- Senha: min:8 caracteres, confirmada
- Senha atual: obrigatória para alteração de senha

### 3. Localização
- Página acessível via **dropdown do usuário** (avatar/icon no header Filament)
- Menu item: "Meu Perfil" ou similar

### 4. Interface
- Template Filament nativo com cards/sections
- Botão "Salvar" para persistir alterações
- Mensagens de sucesso/erro via Livewire

## Arquivos Previstos
```
app/Filament/Admin/Pages/ProfilePage.php    # Página de perfil
resources/views/filament/admin/pages/profile-page.blade.php  # Template
routes/web.php                              # Rota (se necessário)
```

## Gate 1 - SPEC: ✅ Aprovado
## Gate 2 - Pre-Flight: Pendente
## Gate 3 - Quality: Pendente
## Gate 4 - Code Review: Pendente
## Gate 5 - Lesson Learned: Pendente
## Gate 6 - Handoff: Pendente
## Gate 7 - Deploy: Pendente
