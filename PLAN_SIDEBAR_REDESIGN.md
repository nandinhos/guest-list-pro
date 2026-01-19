# Plano de Redesign da Sidebar (Filament Glassmorphism)

O objetivo é transformar a sidebar padrão do Filament (fixa, fundo sólido) no estilo "Glass Card Flutuante" apresentado na documentação do Design System (`/docs/design-system`).

## Viabilidade
**Sim, é totalmente viável.** O Filament v4 é altamente customizável via CSS (Tailwind) e Blade Views (Layouts).

## Estratégias de Implementação

Existem dois níveis de intervenção possíveis:

### Nível 1: Customização Visual Avançada (CSS/Theme)
Esta é a abordagem mais rápida e segura. Mantemos a estrutura HTML, mas alteramos radicalmente a apresentação visual.

**Mudanças Necessárias em `resources/css/filament/admin/theme.css`:**

1.  **Desacoplar a Sidebar (Floating Style):**
    Em vez de preencher 100% da altura e estar colada à esquerda:
    ```css
    @media (min-width: 1024px) {
        .fi-sidebar {
            /* Transformar em card flutuante */
            margin: 1rem; 
            height: calc(100vh - 2rem) !important;
            border-radius: 1.25rem;
            border: 1px solid var(--glass-border);
            
            /* Efeito Glass */
            background-color: var(--glass-bg-strong) !important;
            backdrop-filter: blur(12px);
            box-shadow: var(--shadow-elevated);
        }
        
        .fi-sidebar-header {
            background-color: transparent !important;
            border-bottom: 1px solid var(--glass-border);
        }
    }
    ```

2.  **Estilizar Itens de Menu:**
    Remover o estilo padrão "bloco" e aplicar o estilo "link limpo" da doc:
    ```css
    .fi-sidebar-item {
        margin: 0.25rem 0.75rem; 
    }
    
    .fi-sidebar-item-active {
        background: var(--color-surface-100) !important; /* Mais sutil */
        border-left: none !important; /* Remove barra lateral grossa */
        border-radius: 0.5rem;
    }
    
    .dark .fi-sidebar-item-active {
        background: rgba(255, 255, 255, 0.05) !important;
    }
    ```

### Nível 2: Layout Personalizado (Estrutural)
Se quiser alterar a organização dos elementos (ex: mover o User Menu, Search, ou Toggle Theme), é necessário criar um Layout View.

1.  Criar `resources/views/filament/layout/sidebar.blade.php` baseada na sidebar da Doc.
2.  Configurar no `AdminPanelProvider`:
    ```php
    ->sidebarCollapsibleOnDesktop() // ou configurações de layout
    ->renderHook('panels::sidebar.nav.start', fn () => ...) 
    ```

## Minha Recomendação
**[CONCLUÍDO]** O Nível 1 (CSS) foi aplicado e validado.
- SPA Mode ativado.
- CSS Glassmorphism flutuante aplicado usando tokens do sistema.
- Altura dinâmica (fit-content) implementada.

### Próximos Passos (Opcional)
Se precisar de mudanças estruturais (ordem dos elementos), considere o Nível 2.
