# Lição: Validação de Enums no Filament

**Data**: 2026-02-21
**Stack**: PHP 8+ Laravel + Filament + Enums
**Tags**: backend|filament|php

## Contexto

Ao usar `Get $get` em Selects com Enums no Filament, o valor retornado pode ser a *instância* do Enum ou a *string*, dependendo do contexto.

**Ambiente**: Filament Schema Components
**Frequência**: Média (forms com Select Enum)
**Impacto**: Alto — comparação falha silenciosamente

## Problema

```php
// Supondo $get('type') retorna valor do select
$type = $get('type');

// comparação falha se type é instância do Enum
if ($type === 'some_value') { // NUNCA executa se for Enum
    // ...
}
```

## Causa Raiz

Filament pode retornar:
- `MyEnum::value` (string) — quando vem do form submit
- `MyEnum` (instância) — quando vem do model ou estado interno

## Solução

Sempre validar o tipo antes de comparar ou operar:

```php
$type = $get('type');

// Validação defensiva
$enum = $type instanceof MyEnum ? $type : MyEnum::tryFrom($type ?? '');

// Agora pode comparar com segurança
if ($enum?->value === 'some_value') {
    // ...
}

// Ou usar match pattern
return match (true) {
    $type instanceof MyEnum => $type->value,
    is_string($type) => MyEnum::tryFrom($type)?->value,
    default => null,
};
```

## Prevenção

- [ ] Sempre usar `instanceof` para verificar tipo antes de comparar
- [ ] Usar `tryFrom()` para conversão segura de string para Enum
- [ ] Adicionar type hint quando possível

## Referências

- [PHP Enums](https://www.php.net/manual/en/language.enumerations.backed.php)
- [Filament Get](https://filament.dev/docs/forms/get)