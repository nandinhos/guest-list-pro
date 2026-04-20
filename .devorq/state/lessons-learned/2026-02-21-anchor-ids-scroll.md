# Lição: IDs de Âncoras e Smooth Scroll

**Data**: 2026-02-21
**Stack**: HTML + Tailwind CSS
**Tags**: frontend|accessibility

## Contexto

Links de navegação na landing page usando IDs com caracteres especiais (ex: `#panéis`) causam comportamento inconsistente no scroll suave entre browsers.

**Ambiente**: Landing page / Marketing site
**Frequência**: Baixa (apenas links de âncora)
**Impacto**: Médio — experiência degradada em browsers específicos

## Problema

Scroll suave não funciona em alguns browsers quando o ID da âncora contém:
- Caracteres especiais (acentos, ç, ã)
- Espaços
- Caracteres Unicode

Exemplo: `#panéis` pode não funcionar, mas `#paineis` funciona.

## Causa Raiz

Browsers interpretam IDs de forma diferente quando contêm caracteres Unicode. A especificação HTML5 permite Unicode em IDs, mas nem todos os browsers implementam corretamente.

## Solução

**Regra**: IDs de âncoras devem ser sempre **ASCII Puro**.

```html
<!-- ERRADO -->
<a href="#panéis">Ver painéis</a>
<div id="panéis">...</div>

<!-- CORRETO -->
<a href="#paineis">Ver painéis</a>
<div id="paineis">...</div>
```

**Observação**: O texto visível pode conter acentos (ex: "Ver painéis"), apenas o ID da âncora deve ser ASCII.

## Prevenção

- [ ] Ao criar IDs de âncoras, usar apenas `a-z`, `0-9`, `-` e `_`
- [ ] Converter texto para slug (ex: "Ver painéis" → "ver-paineis")
- [ ] Testar em Chrome, Firefox e Safari

## Referências

- [HTML5 Spec - IDs](https://html.spec.whatwg.org/multipage/dom.html#the-id-attribute)
- [Can I Use - CSS scroll-behavior](https://caniuse.com/css-scroll-behavior)