# TicketTypeResource com Preços por Setor - Filament Repeater

**Data:** 2026-04-19
**Tags:** filament, forms, repeater, bug-fix

## Contexto

Criação do TicketTypeResource para admin configurar preços por setor. O formulário usa Repeater para permitir múltiplos preços customizados.

## Problema

O Select dentro do Repeater usava `../event_id` para acessar o campo do formulário pai, mas isso não funciona dentro do contexto aninhado do Repeater.

**Caminho correto:** `../../event_id` (sobe dois níveis)

```php
// ERRADO - dentro do Repeater, ../event_id não resolve
$eventId = $get('../event_id');

// CORRETO - sobe dois níveis para alcançar o formulário
$eventId = $get('../../event_id');
```

## Outra Issue

Tentativa de usar `allowDuplicates(false)` e `disableOptionsWhenSelected()` no Filament - esses métodos não existem nessa versão do Filament.

## Solução

1. Usar path correto `../../event_id` para acessar campos do formulário
2. Não usar métodos inexistentes - deixar a constraint de unique do banco tratar duplicatas

## Arquivos Criados

- `app/Filament/Resources/TicketType/TicketTypeResource.php`
- `app/Filament/Resources/TicketType/Pages/ListTicketTypes.php`
- `app/Filament/Resources/TicketType/Pages/CreateTicketType.php`
- `app/Filament/Resources/TicketType/Pages/EditTicketType.php`
- `app/Filament/Resources/TicketType/Schemas/TicketTypeForm.php`
- `app/Filament/Resources/TicketType/Tables/TicketTypesTable.php`