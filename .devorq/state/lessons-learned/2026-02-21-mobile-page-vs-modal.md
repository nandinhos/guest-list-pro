# Lição: UX Mobile — Navegação por Página vs Modal na Edição

**Data**: 2026-02-21
**Stack**: Filament v4
**Tags**: mobile|ux|filament

## Contexto

Edição de registros complexos em modais mobile prejudica visibilidade e uso. Formulários extensos ficam cortados ou com scroll problemático em viewport pequeno.

**Ambiente**: Filament Admin em Mobile
**Frequência**: Alta (ações de edição mobile)
**Impacto**: Alto — usuário não consegue completar ação

## Problema

Edição via modal (`mountTableAction`):
- Campos cortados (form большой)
- Sem scroll adequado
- Confirmação visual ruim
- Difícil voltar atrás

## Solução

Para refinar qualidade visual em mobile, usar `getUrl('edit')` para navegar para página dedicada:

```php
// Table definition
Table::make()
    ->actions([
        EditAction::make()
            ->url(fn (Model $record) => static::getUrl('edit', ['record' => $record])),
    ])

// Resource
public static function getPages(): array
{
    return [
        'index' => Pages\ListRecords::route('/'),
        'edit' => Pages\EditRecord::route('/{record}/edit'),
    ];
}
```

**Dica**: Desabilitar `recordUrl(null)` na tabela se houver botões de ação explícitos no card mobile.

## Prevenção

- [ ] Preferir navegação por página em mobile para forms complexos
- [ ] Usar `getUrl('edit')` com record binding
- [ ] Testar em viewport 375px (iPhone SE size)

## Referências

- [Filament Edit Record](https://filament.dev/docs/forms/editing-records)
- [Table Actions](https://filament.dev/docs/tables/actions)