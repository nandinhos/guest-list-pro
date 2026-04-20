# Lição: Alinhamento de Topbar e Truncagem Flexbox

**Data**: 2026-02-21
**Stack**: Tailwind CSS + Filament Layout
**Tags**: css|filament|layout

## Contexto

Nome da marca e nome do usuário ficam grudados em telas pequenas no layout fullscreen do Filament. Texto longo não é truncado corretamente.

**Ambiente**: Filament Topbar / Header
**Frequência**: Média (telas pequenas)
**Impacto**: Médio — texto sobrepõe

## Problema

```html
<!-- ERRADO: sem min-w-0, truncate não funciona -->
<div class="flex justify-between">
    <span class="truncate">Nome muito longo da marca ou empresa</span>
    <span class="truncate">Nome do Usuário</span>
</div>
```

Elementos flex com `truncate` sem `min-w-0` não respeitam limite de largura.

## Solução

Aplicar `min-w-0` no bloco de texto com `truncate`. Isso força o Flexbox a calcular o espaço correto antes de aplicar o corte:

```html
<!-- CORRETO -->
<div class="flex justify-between">
    <span class="min-w-0 truncate">Nome muito longo</span>
    <span class="min-w-0 truncate">Nome do Usuário</span>
</div>
```

**Classe utilitária Tailwind**:
```html
<span class="min-w-0 truncate">...</span>
```

## Prevenção

- [ ] Sempre usar `min-w-0` em elementos flex com `truncate`
- [ ] Testar em viewport pequeno (320px)
- [ ] Verificar que texto longo é truncado corretamente

## Referências

- [Tailwind Flexbox](https://tailwindcss.com/docs/flexbox)
- [min-width - CSS Trick](https://css-tricks.com/flexbox-truncated-text/)
- [Filament Layout](https://filament.dev/docs/layout/topbar)