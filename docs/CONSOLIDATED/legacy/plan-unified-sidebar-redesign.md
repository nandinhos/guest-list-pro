# Plano de Unificação da Sidebar (Glassmorphism & SPA)

O objetivo é replicar o redesign da sidebar (Glassmorphism, Floating, SPA) aplicado ao Admin nos outros painéis (Promoter e Validator), mantendo a identidade de cor de cada um.

## Estratégia Técnica

### 1. Centralização do CSS (DRY)
Em vez de copiar e colar o CSS em cada `theme.css`, vamos mover as regras de "Glass Sidebar" para `resources/css/filament/shared/base-theme.css`.
Todos os painéis já importam este arquivo base.

### 2. Cores Dinâmicas
O CSS atual do Admin usa cores *hardcoded* (Indigo). Para funcionar nos outros painéis (Purple, Green, etc.), vamos refatorar o CSS para usar as variáveis CSS `--panel-color` que já são definidas em cada `theme.css`.

**Exemplo de Refatoração:**
*   De: `oklch(0.55 0.22 265 / 0.15)` (Indigo fixo)
*   Para: `oklch(from var(--panel-color) l c h / 0.15)` (Cor do Painel com opacidade)

### 3. SPA Mode (Wire:navigate)
Habilitar a navegação sem refresh (SPA) nos Providers dos outros painéis para garantir que a sidebar não pisque.

## Passos para Implementação

### Passo 1: Configurar Providers
Habilitar `->spa()` em:
*   `app/Providers/Filament/PromoterPanelProvider.php`
*   `app/Providers/Filament/ValidatorPanelProvider.php`

### Passo 2: Migrar CSS para Base
1.  **Copiar** o bloco "GLASS SIDEBAR REDESIGN" do `admin/theme.css`.
2.  **Adicionar** ao final de `shared/base-theme.css`.
3.  **Refatorar cores** para usar `var(--panel-color)` e `var(--panel-color-dark)`.
4.  **Remover** o bloco do `admin/theme.css` (para não duplicar).

### Passo 3: Build & Validar
Rodar `npm run build` e verificar se todos os painéis herdaram o estilo corretamente.

## Benefícios
*   **Consistência:** Qualquer alteração futura no design da sidebar reflete em todos os painéis.
*   **Identidade:** Cada painel mantém sua cor automaticamente.
*   **Performance:** Menos CSS duplicado.
