# Lição: Centralização de Cards Customizados no Mobile

**Data**: 2026-02-21
**Stack**: Tailwind CSS + Filament Tables
**Tags**: mobile|css|filament

## Contexto

Cards customizados para mobile parecem "deslocados" para a direita devido ao padding interno da tabela Filament. O alinhamento visual fica comprometido.

**Ambiente**: Filament Table com ViewColumn mobile card
**Frequência**: Alta (tabelas mobile)
**Impacto**: Médio — visual desalinhado

## Problema

Tabela Filament tem padding lateral (`px-4` ou similar) que interfere no card customizado que tenta ocupar 100% da largura.

```html
<!-- Card tenta 100% mas herda padding da célula -->
<td class="px-4">
    <div class="w-full">VAZA PARA FORA</div> <!-- 100% + padding = overflow -->
</td>
```

## Solução

Usar margem negativa e cálculo de largura compensatória:

```css
/* Card mobile centralizado */
.mobile-card {
    /* Compensa padding da célula (通常 0.75rem = 12px) */
    margin-left: -0.75rem;  /* -ml-3 */
    width: calc(100% + 1.5rem);  /* w-[calc(100%+0.75rem)] */
}
```

**Tailwind classes**: `-ml-3 w-[calc(100%+0.75rem)]`

```blade
<div class="-ml-3 w-[calc(100%+0.75rem)]">
    <!-- Card content -->
</div>
```

## Prevenção

- [ ] Sempre usar margem negativa compensatória em cards mobile
- [ ] Testar em viewport 375px
- [ ] Verificar overflow no DevTools

## Referências

- [Tailwind Width](https://tailwindcss.com/docs/width)
- [Tailwind Margin](https://tailwindcss.com/docs/margin)
- [CSS Calc](https://developer.mozilla.org/en-US/docs/Web/CSS/calc)