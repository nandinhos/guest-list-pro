# Lição: Glassmorphism — Container e Vazamento de Layout

**Data**: 2026-02-21
**Stack**: Tailwind CSS + Filament
**Tags**: frontend|css|glassmorphism

## Contexto

Elementos com `absolute inset-0` dentro de containers sem `relative` vazam para o container pai mais próximo, quebrando o layout esperado.

**Ambiente**: Theme customizado com glassmorphism
**Frequência**: Média (cada efeito glass)
**Impacto**: Alto — layout completamente quebrado

## Problema

Efeitos de glass/glossy (overlay de brilho) com positioning absolute não funcionam porque o elemento pai não cria contexto de posicionamento.

```html
<!-- ERRADO: overflow vazando -->
<div class="glass-container">
  <div class="absolute inset-0 bg-white/50">...</div> <!-- VAZA! -->
</div>

<!-- CORRETO: container cria contexto -->
<div class="glass-container relative overflow-hidden">
  <div class="absolute inset-0 bg-white/50">...</div> <!-- CONTIDO! -->
</div>
```

## Solução

**Regra 1**: Elemento pai precisa de `relative` para criar contexto de posicionamento.

**Regra 2**: Elemento pai precisa de `overflow-hidden` para conter elementos absolute.

```css
/* Classe utility para glass container */
.glass-container {
  position: relative;
  overflow: hidden;
}

/* Elemento glass interno */
.glass-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(...);
}
```

## Dark Mode Adicional

Backgrounds de página no modo escuro devem usar tokens de `surface` redefinidos na classe `.dark`:

```css
/* ERRADO: usa cor do :root */
.dark body { background: #1a1a1a; }

/* CORRETO: usa token de surface */
.dark {
  --color-surface-base: #1a1a1a;
}
body { background: var(--color-surface-base); }
```

## Prevenção

- [ ] Sempre adicionar `relative overflow-hidden` em container de elementos absolute
- [ ] Testar em mobile com viewport pequeno
- [ ] Verificar no DevTools se overlay está vazando

## Referências

- [Tailwind Position](https://tailwindcss.com/docs/position)
- [MDN - overflow](https://developer.mozilla.org/en-US/docs/Web/CSS/overflow)