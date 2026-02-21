# Plano: Landing Page Profissional (Vitrine)

## Visão Geral

Criar uma landing page profissional como "vitrine" do sistema Guest List Pro, mantendo o design glassmorphism existente mas elevando o nível com seções profissionais para conversão.

**Complexidade**: Média  
**Estimativa**: 5-6 horas  
**Pré-requisitos**: Design System existente (Tailwind v4, glassmorphism)

---

## Estrutura Proposta

```
┌─────────────────────────────────────────────────┐
│  🎫 Guest List Pro                              │
│  Sistema de Gestão de Eventos                   │
│  [Entrar] ← Botão principal                     │
├─────────────────────────────────────────────────┤
│  HERO SECTION                                   │
│  ┌──────────────────┐  ┌────────────────────┐   │
│  │ Título Impacto   │  │ Dashboard Preview │   │
│  │ Subtítulo       │  │ (Screenshot real) │   │
│  │ CTA Principal   │  │                   │   │
│  │ CTA Secundária  │  │                   │   │
│  └──────────────────┘  └────────────────────┘   │
├─────────────────────────────────────────────────┤
│  FEATURES (4 cards principais)                  │
│  ┌────────┐ ┌────────┐ ┌────────┐ ┌────────┐ │
│  │ 👥      │ │ ✅      │ │ 🎟️      │ │ 📊      │ │
│  │ Gestão  │ │ Check-in│ │ Bilhete │ │ Relatórios│ │
│  │ Convidados│ │ QR Code│ │ ria     │ │ em tempo│ │
│  └────────┘ └────────┘ └────────┘ └────────┘ │
├─────────────────────────────────────────────────┤
│  BENEFÍCIOS                                     │
│  • Controle de duplicidade                     │
│  • Aprovações hierárquicas                     │
│  • Multi-painéis (admin, promoter, validator)  │
│  • Importação em massa Excel                   │
├─────────────────────────────────────────────────┤
│  ROLES (4 cards interativos)                   │
│  Ao clicar → /login?role=admin                 │
├─────────────────────────────────────────────────┤
│  FOOTER                                         │
│  © 2026 Guest List Pro                         │
└─────────────────────────────────────────────────┘
```

---

## Tasks de Implementação (TDD)

### Task 1: Criar componente Hero
**Arquivos:**
- `app/Livewire/Landing/Hero.php`
- `resources/views/livewire/landing/hero.blade.php`

**Teste (escrever primeiro):**
```php
it('renders hero section with title and CTA buttons', function () {
    Livewire::test(Hero::class)
        ->assertSee('Guest List Pro')
        ->assertSee('Sistema de Gestão de Eventos')
        ->assertSee('Entrar')
        ->assertSee('Ver Demo');
});
```

**Implementação:**
- Título principal impactante
- Subtítulo com proposta de valor
- Dashboard screenshot (usar placeholder ou captura real)
- Botões CTA: "Entrar" e "Ver Demo"

---

### Task 2: Criar componente Features
**Arquivos:**
- `app/Livewire/Landing/Features.php`
- `resources/views/livewire/landing/features.blade.php`

**Teste:**
```php
it('renders 4 feature cards', function () {
    Livewire::test(Features::class)
        ->assertSee('Gestão de Convidados')
        ->assertSee('Check-in QR Code')
        ->assertSee('Bilheteria')
        ->assertSee('Relatórios em Tempo Real');
});
```

**Implementação:**
- 4 cards com ícones Heroicons
- Cada card: ícone + título + descrição curta
- Hover effect com glassmorphism

---

### Task 3: Criar componente Benefits
**Arquivos:**
- `app/Livewire/Landing/Benefits.php`
- `resources/views/livewire/landing/benefits.blade.php`

**Teste:**
```php
it('renders benefits list', function () {
    Livewire::test(Benefits::class)
        ->assertSee('Controle de duplicidade')
        ->assertSee('Aprovações hierárquicas')
        ->assertSee('Multi-painéis');
});
```

**Implementação:**
- Lista de benefícios com checkmarks
- Foco em diferencial competitivo

---

### Task 4: Adaptar Welcome cards existentes
**Arquivos:**
- `resources/views/livewire/welcome.blade.php` (modificar)
- `app/Livewire/Welcome.php` (modificar)

**Modificações:**
- Manter os 4 cards atuais (Admin, Promoter, Validator, Bilheteria)
- Adicionar links para `/login?role=xxx`
- Melhorar styling com glassmorphism

---

### Task 5: Criar novo layout landing-v2
**Arquivos:**
- `resources/views/components/layouts/landing-v2.blade.php`

**Implementação:**
- Baseado no `landing.blade.php` existente
- Adicionar suporte para sections scroll
- Melhorar background effects
- Manter theme toggle

---

### Task 6: Atualizar rota principal
**Arquivos:**
- `routes/web.php`

**Modificações:**
```php
Route::get('/', \App\Livewire\Landing\Index::class)->name('home');
// Manter rota antiga para login direto
Route::get('/painel/{panel}', \App\Livewire\Welcome::class)->name('panel.redirect');
```

---

### Task 7: Criar Landing Index
**Arquivos:**
- `app/Livewire/Landing/Index.php`

**Implementação:**
- Componente que compila todas as sections
- Responsivo (mobile-first)
- Lazy loading para imagens

---

## Estrutura Final de Arquivos

```
app/
└── Livewire/
    └── Landing/
        ├── Index.php       # Componente principal
        ├── Hero.php        # Section hero
        ├── Features.php    # Section features
        └── Benefits.php   # Section benefits

resources/views/
└── livewire/
    └── landing/
        ├── index.blade.php
        ├── hero.blade.php
        ├── features.blade.php
        └── benefits.blade.php

resources/views/components/layouts/
└── landing-v2.blade.php  (novo layout)

routes/web.php
```

---

## Design System a Utilizar

### Cores disponíveis no projeto
```css
--color-brand-admin-500   /* Indigo */
--color-brand-promoter-500 /* Purple */
--color-brand-validator-500 /* Emerald */
--color-brand-bilheteria-500 /* Orange */
```

### Classes glassmorphism
```css
.glass-card
.glass-subtle
.landing-gradient-top
```

### Ícones (Heroicons)
- Gestão: `heroicon-o-user-group`
- Check-in: `heroicon-o-qr-code`
- Bilheteria: `heroicon-o-ticket`
- Relatórios: `heroicon-o-chart-bar`

---

## Checklist de Validação

- [ ] Hero section responsivo (mobile-first)
- [ ] Features cards com hover effects
- [ ] Benefits com checkmarks
- [ ] Cards de painéis com links corretos
- [ ] Theme toggle funcionando
- [ ] Animações suaves (CSS)
- [ ] Todos os testes passando
- [ ] PSR-12 compliance
- [ ] Performance (lazy load imagens)

---

## Próximos Passos (Pós-Landing)

1. **Login Unificado** (`/login`)
   - Email + Senha
   - Redirect automático por role
   - Design alinhado com landing

2. **Dashboard Pós-Login** (`/dashboard`)
   - Atalhos por perfil
   - Estatísticas rápidas

---

## Referências

- Layout base: `resources/views/components/layouts/landing.blade.php`
- Design System: `resources/css/design-system/`
- Welcome atual: `resources/views/livewire/welcome.blade.php`
- Tokens: `resources/css/design-system/tokens.css`

---

**Data**: 2026-02-17  
**Status**: Backlog  
**Prioridade**: Alta
