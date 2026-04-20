# Lição: SPA Mode — Estabilidade sobre Fluidez

**Data**: 2026-02-21
**Stack**: Filament v4
**Tags**: frontend|filament|spa

## Contexto

Revisitando decisão sobre `->spa(true)` após bugs de redeclaração JavaScript (`loadDarkMode`). O uso de SPA mode provou-se instável para o volume de scripts customizados do projeto.

**Ambiente**: Filament Admin Panels
**Frequência**: Contínua (toda navegação)
**Impacto**: Crítico — erros de sintaxe em produção

## Problema

```php
// PROBLEMA: spa(true) causa erros de redeclaração
// em projetos com múltiplos scripts customizados
public function panel(Panel $panel): Panel
{
    return $panel
        ->spa(true) // ❌ INSTÁVEL
        ->renderHook(...)
```

Erro observado: `Uncaught SyntaxError: Identifier 'loadDarkMode' has already been declared`

## Causa Raiz

SPA mode mantém JavaScript em memória entre navegações. Quando múltiplos componentes Livewire com scripts customizados são carregados, há race condition de declaração.

## Solução

Manter `->spa(false)` em todos os PanelProviders para garantir limpeza total da memória JS a cada navegação:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->spa(false) // ✅ ESTÁVEL
        // ...
}
```

**Resultado**: Navegações completas (full reload) mas sem erros de JavaScript.

## Prevenção

- [ ] Manter `->spa(false)` como padrão
- [ ] Se precisar de SPA, testar extensivamente com todos os scripts customizados
- [ ] Monitorar erros de JavaScript em produção

## Referências

- [Filament SPA Mode](https://filament.dev/docs/admin/spa-mode)