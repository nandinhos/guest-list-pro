# Lição: Integridade de Variáveis em Componentes Blade (@props)

**Data**: 2026-02-21
**Stack**: Laravel Blade + Components
**Tags**: backend|blade|laravel

## Contexto

Erro de "Undefined variable" ao passar dados para componentes anônimos do Blade. Componentes que não declaram `@props` causam quebra na renderização.

**Ambiente**: Laravel Blade Components
**Frequência**: Média (cada componente anônimo)
**Impacto**: Alto — página quebra completamente

## Problema

```blade
{{-- Componente sem @props --}}
<div>{{ $title }}</div> {{-- ERRO se não passou title --}}

{{-- Chamada --}}
<x-my-component />

{{-- Outro erro --}}
<x-my-component title="OK" /> {{-- Funciona --}}
<x-my-component /> {{-- ERRO! title não definido --}}
```

## Causa Raiz

Blade não sabe quais variáveis esperar se não houver `@props`. Sem declaração, qualquer variável não passada causa erro.

## Solução

Sempre declarar `@props` com defaults:

```blade
{{-- good-component.blade.php --}}
@props(['title' => 'Default Title', 'type' => 'info'])

<div class="alert alert-{{ $type }}">
    {{ $title }}
</div>
```

**Padrão**: `(@props(['var' => default_value]))`

## Prevenção

- [ ] Sempre declarar `@props` em componentes Blade anônimos
- [ ] Fornecer defaults para todas as props
- [ ] Documentar props esperadas no início do arquivo

## Referências

- [Laravel Blade Components](https://laravel.com/docs/11.x/blade#component-method-dependency-injection)
- [Blade Props](https://laravel.com/docs/11.x/blade#components)