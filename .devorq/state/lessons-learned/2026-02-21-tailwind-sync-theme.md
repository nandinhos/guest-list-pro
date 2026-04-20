# Lição: Sincronização de Temas Tailwind no Filament

**Data**: 2026-02-21
**Stack**: Tailwind CSS v4 + Filament
**Tags**: frontend|tailwind|build

## Contexto

Estilos de componentes customizados sumindo após o `build` de produção. O Tailwind v4 ignora arquivos fora dos diretórios padrão se não forem explicitamente mapeados.

**Ambiente**: Filament + Theme customizado
**Frequência**: Baixa (após build produção)
**Impacto**: Crítico — estilos somem em produção

## Problema

Componentes Blade customizados em `resources/views/components/**/*.blade.php` não são incluidos no build de produção do Tailwind.

```css
/* theme.css Filament */
@import "tailwindcss";

/* Componentes customizados NÃO incluídos! */
```

## Causa Raiz

Tailwind v4 usa paths pattern `sources` default. Se componentes estão fora de `resources/css` ou `views`, não são escaneados.

## Solução

Sempre adicionar diretiva `@source` apontando para os componentes Blade:

```css
/* resources/css/filament/theme.css */
@import "tailwindcss";

/* ⚠️ ESSENCIAL: incluir caminhos dos componentes */
@source "../views/components/**/*.blade.php";
@source "../../resources/views/components/**/*.blade.php";
```

**Localização típica**: `resources/css/filament/theme.css`

## Prevenção

- [ ] Sempre adicionar `@source` para componentes customizados
- [ ] Após criar novo componente Blade, rebuild theme
- [ ] Testar em produção (build local não reflete produção)

## Referências

- [Tailwind CSS v4 - Source Files](https://tailwindcss.com/docs/source-files)
- [Filament Themes](https://filament.dev/docs/support/themes)