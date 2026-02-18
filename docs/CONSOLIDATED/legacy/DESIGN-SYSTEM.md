# Design System Premium - Guest List Pro

## Objetivo

Criar um Design System completo, moderno e premium para a aplicacao TALL Stack, com estilizacao consistente de tabelas, botoes, widgets, formularios e todos os elementos visuais. Mobile-first e responsivo.

---

## Stack Tecnologico

- **Tailwind CSS v4** com `@theme` e `@custom-variant`
- **Filament v4** com 3 paineis (Admin/Indigo, Promoter/Purple, Validator/Emerald)
- **Livewire v3** + **Alpine.js v3**
- **Fontes**: Outfit (headings), Inter (body)

---

## Arquitetura de Arquivos

```
resources/
├── css/
│   ├── app.css                          # CSS principal (importa design-system)
│   ├── design-system/
│   │   ├── tokens.css                   # Design tokens (cores, tipografia, espacamento)
│   │   ├── utilities.css                # Classes utilitarias (glass, hover-lift, etc.)
│   │   └── animations.css               # Keyframes e animacoes premium
│   └── filament/
│       ├── shared/
│       │   └── base-theme.css           # Estilos compartilhados Filament
│       ├── admin/
│       │   └── theme.css                # Tema Admin (Indigo)
│       ├── promoter/
│       │   └── theme.css                # Tema Promoter (Purple)
│       └── validator/
│           └── theme.css                # Tema Validator (Emerald)
└── views/
    └── components/
        └── ui/
            ├── button.blade.php         # Botao premium com variantes
            ├── card.blade.php           # Card glassmorphism
            ├── badge.blade.php          # Badge com cores semanticas
            ├── input.blade.php          # Input estilizado
            ├── stat-card.blade.php      # Card de estatistica
            └── empty-state.blade.php    # Estado vazio
```

---

## Fase 1: Design Tokens

**Arquivo**: `resources/css/design-system/tokens.css`

### Cores do Sistema
- **Admin**: Indigo (brand-admin-500, brand-admin-600)
- **Promoter**: Purple (brand-promoter-500, brand-promoter-600)
- **Validator**: Emerald (brand-validator-500, brand-validator-600)
- **Semanticas**: success, warning, danger, info

### Glassmorphism
- `--glass-bg-light`: rgba(255, 255, 255, 0.8)
- `--glass-bg-dark`: rgba(30, 41, 59, 0.8)
- `--glass-blur`: 12px / 20px

### Sombras Premium
- `--shadow-glass`: efeito de vidro suave
- `--shadow-glow-*`: glow colorido por painel
- `--shadow-elevated`: sombra para elementos elevados

### Transicoes
- `--ease-premium`: cubic-bezier(0.4, 0, 0.2, 1)
- `--ease-bounce`: cubic-bezier(0.34, 1.56, 0.64, 1)
- `--duration-normal`: 300ms

---

## Fase 2: Animacoes Premium

**Arquivo**: `resources/css/design-system/animations.css`

### Keyframes
- `fade-in-up`: entrada suave de baixo
- `scale-in`: entrada com escala (bounce)
- `shimmer`: loading skeleton
- `pulse-glow`: pulsacao de glow
- `float`: flutuacao suave

### Utility Classes
- `animate-fade-in-up`
- `animate-scale-in`
- `animate-shimmer`
- `animate-float`

---

## Fase 3: Utilidades CSS

**Arquivo**: `resources/css/design-system/utilities.css`

### Glassmorphism
- `glass`: bg + blur + border para light mode
- `glass-dark`: versao dark mode
- `glass-card`: card completo com glassmorphism

### Gradient Borders
- `gradient-border`: borda com gradiente via pseudo-elemento

### Hover Effects
- `hover-lift`: translateY(-2px) + shadow elevada
- `hover-scale`: scale(1.02) com bounce

### Focus States
- `focus-ring`: ring consistente para acessibilidade

### Scrollbar
- `scrollbar-thin`: scrollbar customizada fina

---

## Fase 4: Tema Filament Compartilhado

**Arquivo**: `resources/css/filament/shared/base-theme.css`

### Estilos Globais
- Tipografia com Outfit para headings
- Background gradients para body e login
- Sidebar com glassmorphism
- Cards/sections com backdrop-blur
- Inputs com rounded-xl e transicoes
- Tabelas com hover states suaves
- Botoes com gradients e sombras
- Widgets com hover lift
- Modais com glassmorphism
- Animacao fadeInUp nas paginas

---

## Fase 5: Temas por Painel

### Admin (`filament/admin/theme.css`)
- Importa base-theme.css
- Botoes: gradient indigo
- Sidebar active: bg-indigo-500/20
- Glow hover: indigo

### Promoter (`filament/promoter/theme.css`)
- Importa base-theme.css
- Botoes: gradient purple
- Sidebar active: bg-purple-500/20
- Background: purple gradient
- Glow hover: purple

### Validator (`filament/validator/theme.css`)
- Importa base-theme.css
- Botoes: gradient emerald
- Sidebar active: bg-emerald-500/20
- Background: emerald gradient
- Glow hover: emerald

---

## Fase 6: Componentes Blade

### Button (`ui/button.blade.php`)
- Variantes: primary, secondary, ghost, danger
- Tamanhos: sm, md, lg
- Suporte a icones (esquerda/direita)
- Loading state com spinner
- href para links

### Card (`ui/card.blade.php`)
- Variantes: default, glass, elevated, bordered
- Slots: header, content, footer
- Hover effect opcional
- Padding configuravel

### Badge (`ui/badge.blade.php`)
- Variantes: default, success, warning, danger, info, primary
- Dot indicator opcional
- Removable com X button
- Tamanhos: sm, md, lg

### Input (`ui/input.blade.php`)
- Label integrado
- Icone esquerda/direita
- Error state com mensagem
- Hint text
- Focus ring colorido

### Stat Card (`ui/stat-card.blade.php`)
- Label + Value grande
- Change indicator (up/down/neutral)
- Icone com background colorido
- Footer slot opcional

### Empty State (`ui/empty-state.blade.php`)
- Icone grande
- Titulo e descricao
- Action button opcional

---

## Fase 7: Configuracoes

### vite.config.js
```javascript
input: [
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/css/filament/admin/theme.css',
    'resources/css/filament/promoter/theme.css',
    'resources/css/filament/validator/theme.css',
],
```

### app.css
```css
@import 'tailwindcss';
@import './design-system/tokens.css';
@import './design-system/utilities.css';
@import './design-system/animations.css';

@custom-variant dark (&:where(.dark, .dark *));
```

### Panel Providers
- AdminPanelProvider: `->viteTheme('resources/css/filament/admin/theme.css')`
- PromoterPanelProvider: `->viteTheme('resources/css/filament/promoter/theme.css')`
- ValidatorPanelProvider: `->viteTheme('resources/css/filament/validator/theme.css')`

---

## Arquivos a Modificar/Criar

### Criar (Novos)
1. `resources/css/design-system/tokens.css`
2. `resources/css/design-system/utilities.css`
3. `resources/css/design-system/animations.css`
4. `resources/css/filament/shared/base-theme.css`
5. `resources/css/filament/promoter/theme.css`
6. `resources/css/filament/validator/theme.css`
7. `resources/views/components/ui/button.blade.php`
8. `resources/views/components/ui/card.blade.php`
9. `resources/views/components/ui/badge.blade.php`
10. `resources/views/components/ui/input.blade.php`
11. `resources/views/components/data/stat-card.blade.php`
12. `resources/views/components/feedback/empty-state.blade.php`

### Modificar (Existentes)
1. `resources/css/app.css` - Adicionar imports do design-system
2. `resources/css/filament/admin/theme.css` - Refatorar para usar base-theme
3. `vite.config.js` - Adicionar novos entry points
4. `app/Providers/Filament/PromoterPanelProvider.php` - Apontar para tema proprio
5. `app/Providers/Filament/ValidatorPanelProvider.php` - Apontar para tema proprio

---

## Verificacao

1. **Build dos Assets**
   ```bash
   vendor/bin/sail npm run build
   ```

2. **Testar Paineis**
   - Acessar `/admin` - verificar tema Indigo
   - Acessar `/promoter` - verificar tema Purple
   - Acessar `/validator` - verificar tema Emerald

3. **Testar Dark Mode**
   - Toggle no header de cada painel
   - Verificar glassmorphism e gradients

4. **Testar Responsividade**
   - Mobile (< 640px)
   - Tablet (640px - 1024px)
   - Desktop (> 1024px)

5. **Testar Componentes Blade**
   - Criar pagina de teste temporaria
   - Renderizar cada componente com todas as variantes

6. **Verificar Animacoes**
   - Hover effects em cards e botoes
   - Transicoes de pagina
   - Loading states

---

## Ordem de Implementacao

1. Design tokens (tokens.css)
2. Animacoes (animations.css)
3. Utilidades (utilities.css)
4. Base theme Filament (shared/base-theme.css)
5. Temas por painel (admin, promoter, validator)
6. Atualizar app.css e vite.config.js
7. Atualizar Panel Providers
8. Build e teste inicial
9. Componentes Blade
10. Testes finais e ajustes
