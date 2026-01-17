# Design System Premium - Sprints

## Visao Geral

| Sprint | Foco | Arquivos |
|--------|------|----------|
| Sprint 1 | Fundacao CSS | tokens, animations, utilities |
| Sprint 2 | Temas Filament | base-theme, admin, promoter, validator |
| Sprint 3 | Configuracao Build | app.css, vite.config, providers |
| Sprint 4 | Componentes UI | button, card, badge, input |
| Sprint 5 | Componentes Data/Feedback | stat-card, empty-state |
| Sprint 6 | Testes e Refinamentos | QA, ajustes, documentacao |

---

## Sprint 1: Fundacao CSS (Design Tokens + Animacoes + Utilidades) ✅ CONCLUIDA

### Objetivo
Criar a base do design system com tokens, animacoes e classes utilitarias.

### Tarefas

- [x] **1.1 Criar estrutura de pastas**
  - [x] `resources/css/design-system/`

- [x] **1.2 Design Tokens** (`tokens.css`)
  - [x] Cores brand por painel (admin/indigo, promoter/purple, validator/emerald)
  - [x] Cores semanticas (success, warning, danger, info)
  - [x] Variaveis glassmorphism (glass-bg-light, glass-bg-dark, glass-blur)
  - [x] Sombras premium (shadow-glass, shadow-glow-*, shadow-elevated)
  - [x] Transicoes (ease-premium, ease-bounce, duration-normal)
  - [x] Espacamentos e border-radius

- [x] **1.3 Animacoes** (`animations.css`)
  - [x] Keyframe: fade-in-up
  - [x] Keyframe: scale-in (bounce)
  - [x] Keyframe: shimmer (skeleton loading)
  - [x] Keyframe: pulse-glow
  - [x] Keyframe: float
  - [x] Classes utilitarias de animacao

- [x] **1.4 Utilidades CSS** (`utilities.css`)
  - [x] Glassmorphism (.glass, .glass-dark, .glass-card)
  - [x] Gradient borders (.gradient-border)
  - [x] Hover effects (.hover-lift, .hover-scale)
  - [x] Focus states (.focus-ring)
  - [x] Scrollbar customizada (.scrollbar-thin)
  - [x] Text utilities (.text-gradient)

### Entregaveis
- [x] `resources/css/design-system/tokens.css`
- [x] `resources/css/design-system/animations.css`
- [x] `resources/css/design-system/utilities.css`

---

## Sprint 2: Temas Filament (Base + Por Painel) ✅ CONCLUIDA

### Objetivo
Criar tema base compartilhado e temas especificos para cada painel.

### Tarefas

- [x] **2.1 Criar estrutura de pastas**
  - [x] `resources/css/filament/shared/`
  - [x] `resources/css/filament/promoter/`
  - [x] `resources/css/filament/validator/`

- [x] **2.2 Base Theme** (`shared/base-theme.css`)
  - [x] Import do Filament preset
  - [x] Tipografia (Outfit headings, Inter body)
  - [x] Background gradients (body, login)
  - [x] Sidebar glassmorphism
  - [x] Cards/sections com backdrop-blur
  - [x] Inputs estilizados (rounded-xl, transicoes)
  - [x] Tabelas com hover states
  - [x] Botoes base (gradients, sombras)
  - [x] Widgets com hover lift
  - [x] Modais com glassmorphism
  - [x] Animacao fadeInUp nas paginas
  - [x] Notifications estilizadas

- [x] **2.3 Tema Admin** (`admin/theme.css`)
  - [x] Refatorar para importar base-theme
  - [x] Customizacoes indigo (botoes, sidebar active, glow)
  - [x] Remover codigo duplicado

- [x] **2.4 Tema Promoter** (`promoter/theme.css`)
  - [x] Importar base-theme
  - [x] Customizacoes purple (botoes, sidebar active, glow)
  - [x] Background gradient purple

- [x] **2.5 Tema Validator** (`validator/theme.css`)
  - [x] Importar base-theme
  - [x] Customizacoes emerald (botoes, sidebar active, glow)
  - [x] Background gradient emerald

### Entregaveis
- [x] `resources/css/filament/shared/base-theme.css`
- [x] `resources/css/filament/admin/theme.css` (refatorado)
- [x] `resources/css/filament/promoter/theme.css`
- [x] `resources/css/filament/validator/theme.css`

---

## Sprint 3: Configuracao Build e Integracao ✅ CONCLUIDA

### Objetivo
Integrar o design system no build e configurar os paineis.

### Tarefas

- [x] **3.1 Atualizar app.css**
  - [x] Import tailwindcss
  - [x] Import design-system/tokens.css
  - [x] Import design-system/utilities.css
  - [x] Import design-system/animations.css
  - [x] Custom variant dark mode

- [x] **3.2 Atualizar vite.config.js**
  - [x] Adicionar entry: filament/admin/theme.css
  - [x] Adicionar entry: filament/promoter/theme.css
  - [x] Adicionar entry: filament/validator/theme.css

- [x] **3.3 Atualizar Panel Providers**
  - [x] AdminPanelProvider: viteTheme admin
  - [x] PromoterPanelProvider: viteTheme promoter
  - [x] ValidatorPanelProvider: viteTheme validator

- [x] **3.4 Build e Teste Inicial**
  - [x] Executar `npm run build`
  - [x] Verificar erros de compilacao
  - [ ] Testar acesso aos 3 paineis (manual)
  - [ ] Verificar dark mode em cada painel (manual)

### Entregaveis
- [x] `resources/css/app.css` (atualizado)
- [x] `vite.config.js` (atualizado)
- [x] `app/Providers/Filament/AdminPanelProvider.php` (atualizado)
- [x] `app/Providers/Filament/PromoterPanelProvider.php` (atualizado)
- [x] `app/Providers/Filament/ValidatorPanelProvider.php` (atualizado)

---

## Sprint 4: Componentes UI Blade

### Objetivo
Criar componentes Blade reutilizaveis para UI.

### Tarefas

- [ ] **4.1 Criar estrutura de pastas**
  - [ ] `resources/views/components/ui/`

- [ ] **4.2 Button Component** (`ui/button.blade.php`)
  - [ ] Props: variant (primary, secondary, ghost, danger)
  - [ ] Props: size (sm, md, lg)
  - [ ] Props: icon, iconRight
  - [ ] Props: loading, disabled
  - [ ] Props: href (para links)
  - [ ] Estilos com gradients e hover effects

- [ ] **4.3 Card Component** (`ui/card.blade.php`)
  - [ ] Props: variant (default, glass, elevated, bordered)
  - [ ] Props: hover (boolean)
  - [ ] Props: padding (none, sm, md, lg)
  - [ ] Slots: header, default (content), footer

- [ ] **4.4 Badge Component** (`ui/badge.blade.php`)
  - [ ] Props: variant (default, success, warning, danger, info, primary)
  - [ ] Props: size (sm, md, lg)
  - [ ] Props: dot (boolean)
  - [ ] Props: removable (boolean)

- [ ] **4.5 Input Component** (`ui/input.blade.php`)
  - [ ] Props: label, name, type, placeholder
  - [ ] Props: icon, iconRight
  - [ ] Props: error, hint
  - [ ] Props: required, disabled
  - [ ] Focus ring colorido

### Entregaveis
- `resources/views/components/ui/button.blade.php`
- `resources/views/components/ui/card.blade.php`
- `resources/views/components/ui/badge.blade.php`
- `resources/views/components/ui/input.blade.php`

---

## Sprint 5: Componentes Data e Feedback

### Objetivo
Criar componentes para exibicao de dados e feedback ao usuario.

### Tarefas

- [ ] **5.1 Criar estrutura de pastas**
  - [ ] `resources/views/components/data/`
  - [ ] `resources/views/components/feedback/`

- [ ] **5.2 Stat Card Component** (`data/stat-card.blade.php`)
  - [ ] Props: label, value
  - [ ] Props: change, changeType (up, down, neutral)
  - [ ] Props: icon, iconColor
  - [ ] Slot: footer
  - [ ] Animacao de entrada

- [ ] **5.3 Empty State Component** (`feedback/empty-state.blade.php`)
  - [ ] Props: icon, title, description
  - [ ] Props: actionLabel, actionUrl
  - [ ] Slot: action (para botao customizado)
  - [ ] Ilustracao SVG opcional

- [ ] **5.4 Skeleton Component** (`feedback/skeleton.blade.php`)
  - [ ] Props: type (text, card, avatar, table-row)
  - [ ] Props: lines (para text)
  - [ ] Animacao shimmer

- [ ] **5.5 Alert Component** (`feedback/alert.blade.php`)
  - [ ] Props: type (info, success, warning, danger)
  - [ ] Props: title, dismissible
  - [ ] Slot: default (mensagem)
  - [ ] Icones automaticos por tipo

### Entregaveis
- `resources/views/components/data/stat-card.blade.php`
- `resources/views/components/feedback/empty-state.blade.php`
- `resources/views/components/feedback/skeleton.blade.php`
- `resources/views/components/feedback/alert.blade.php`

---

## Sprint 6: Testes, Refinamentos e Documentacao

### Objetivo
Garantir qualidade, consistencia e documentar o uso.

### Tarefas

- [ ] **6.1 Testes Visuais**
  - [ ] Testar todos os paineis (admin, promoter, validator)
  - [ ] Testar dark mode em cada painel
  - [ ] Testar responsividade (mobile, tablet, desktop)
  - [ ] Verificar animacoes e transicoes

- [ ] **6.2 Testes de Componentes**
  - [ ] Criar pagina de showcase temporaria
  - [ ] Testar cada variante de button
  - [ ] Testar cada variante de card
  - [ ] Testar cada variante de badge
  - [ ] Testar input com todas as props
  - [ ] Testar stat-card com diferentes dados
  - [ ] Testar empty-state

- [ ] **6.3 Refinamentos**
  - [ ] Ajustar espacamentos inconsistentes
  - [ ] Corrigir problemas de contraste (acessibilidade)
  - [ ] Otimizar animacoes para performance
  - [ ] Garantir consistencia entre light/dark mode

- [ ] **6.4 Documentacao de Uso**
  - [ ] Documentar props de cada componente
  - [ ] Exemplos de uso em Blade
  - [ ] Guia de cores e tokens
  - [ ] Remover pagina de showcase

- [ ] **6.5 Limpeza**
  - [ ] Remover CSS duplicado/nao utilizado
  - [ ] Executar Pint nos arquivos PHP
  - [ ] Build final de producao

### Entregaveis
- Todos os componentes testados e funcionando
- Design system consistente nos 3 paineis
- Documentacao de uso (opcional)

---

## Cronograma Sugerido

| Sprint | Estimativa | Dependencias |
|--------|------------|--------------|
| Sprint 1 | - | Nenhuma |
| Sprint 2 | - | Sprint 1 |
| Sprint 3 | - | Sprint 1, 2 |
| Sprint 4 | - | Sprint 3 |
| Sprint 5 | - | Sprint 3 |
| Sprint 6 | - | Sprint 4, 5 |

---

## Notas de Implementacao

### Tailwind CSS v4
- Usar `@theme` para definir tokens customizados
- Usar `@custom-variant dark` para dark mode
- Aproveitar novas features como `@starting-style`

### Filament v4
- Temas sao aplicados via `->viteTheme()`
- Usar CSS variables do Filament quando possivel
- Respeitar estrutura de classes do Filament

### Acessibilidade
- Manter ratio de contraste minimo 4.5:1
- Focus states visiveis
- Animacoes respeitam `prefers-reduced-motion`

### Performance
- Evitar `box-shadow` muito complexos
- Usar `will-change` com moderacao
- Preferir `transform` e `opacity` para animacoes
