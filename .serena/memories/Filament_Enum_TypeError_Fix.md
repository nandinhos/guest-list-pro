# Tratamento de Enums em Selects do Filament e Erros de Tipagem

## Problema
Ao utilizar Enums nativos do PHP em campos `Select` do Filament via `options(DocumentType::class)`, o Filament pode passar a instância do Enum diretamente para os callbacks de `placeholder`, `helperText` ou `rules` em vez do valor bruto (string/int).
Isso causa um `TypeError` se tentarmos chamar `Enum::tryFrom($value)` onde `$value` já é uma instância do Enum.

## Lição Aprendida
Sempre verifique se o valor recebido no callback já é uma instância do Enum antes de tentar convertê-lo:

```php
TextInput::make('document')
    ->placeholder(fn (Get $get) => {
        $type = $get('document_type');
        $enum = $type instanceof DocumentType ? $type : DocumentType::tryFrom($type ?? '');
        return $enum?->getPlaceholder() ?? 'Digite o documento';
    })
```

## Arquivos Afetados (Exemplos)
- `GuestForm.php` (Promoter)
- `GuestsTable.php` (Validator - Emergency Check-in)
