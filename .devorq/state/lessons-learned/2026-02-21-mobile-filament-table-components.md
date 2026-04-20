# Lição: Mobile-First e Componentes de Tabela Filament

**Data**: 2026-02-21
**Stack**: Filament v4 + Laravel + Tailwind
**Tags**: mobile|frontend|filament

## Contexto

Tabelas horizontais em mobile degradam severamente a experiência do usuário, tornando dados ilegíveis e ações impossíveis.

**Ambiente**: Filament Admin Panel
**Frequência**: Contínua (toda tabela mobile)
**Impacto**: Crítico — UX mobile destruída

## Problema

`Filament\Tables\Columns\Layout\View` não suporta métodos de Resource como `labels()` ou `hidden()`. Usá-lo como coluna de topo em mobile resulta em:
- Layout quebrado
- Colunas sobrepostas
- Impossibilidade de scroll horizontal

## Causa Raiz

`Layout\ViewColumn` é um componente de layout genérico que não conhece o contexto de tabela do Filament. Ele não respeita breakpoints do Filament.

## Solução

Usar `Filament\Tables\Columns\ViewColumn` para cards mobile customizados e `visibleFrom('md')` para esconder colunas desktop:

```php
// COLUNA MOBILE (cards customizados)
ViewColumn::make('mobile_card')
    ->view('filament.tables.columns.mobile-card')
    ->visibleFrom('md'), // Esconde em mobile, mostra em desktop

// COLUNAS DESKTOP (normais)
TextColumn::make('name')->visibleFrom('md'),
TextColumn::make('email')->visibleFrom('md'),
// ...
```

**CRÍTICO**: Nunca use `Layout\View` como coluna de topo. Use `ViewColumn` com template Blade próprio.

## Prevenção

- [ ]Sempre usar `ViewColumn` para renderização customizada
- [ ] Usar `visibleFrom('md')` para esconder colunas desktop quando card mobile aparece
- [ ] Testar em viewport mobile (375px) antes de merge

## Referências

- [Filament Table Columns](https://filament.dev/docs/tables/columns)
- [Visible From](https://filament.dev/docs/tables/columns#column-visibility)