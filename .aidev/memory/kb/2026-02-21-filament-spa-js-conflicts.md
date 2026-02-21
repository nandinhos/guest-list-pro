# Licao: Conflitos de Redeclaração JS no Modo SPA do Filament

**Data**: 2026-02-21
**Stack**: Laravel 12 + Filament v4 + Livewire v3
**Tags**: [bug, filament, js, spa, stability]

## Contexto
O projeto estava configurado com `->spa(true)` nos `PanelProviders`. Ao navegar entre páginas, o Livewire tenta fazer o swap do conteúdo sem recarregar o cabeçalho completamente.

## Problema
Sintoma: Erro no console `Uncaught SyntaxError: Identifier 'loadDarkMode' has already been declared`.
Causa: O Filament injeta scripts globais (como o de dark mode) que usam `const` ou `let`. Ao navegar via SPA, o script é reexecutado e tenta redeclarar a mesma variável na memória do navegador.

## Causa Raiz
### Analise (5 Whys)
1. **Por que falhou?** O JS parou de funcionar e lançou erro de sintaxe.
2. **Por que?** Porque tentou declarar `loadDarkMode` duas vezes.
3. **Por que?** Porque o script está no layout e foi reexecutado pelo Livewire Navigate.
4. **Por que?** O modo SPA estava ativo e o script usa `const`.
5. **Por que?** Configuracao de SPA estava ativa contra as regras do projeto.

## Solucao
### Correcao Aplicada
Desabilitar o modo SPA em todos os `PanelProviders`:
```php
// app/Providers/Filament/AdminPanelProvider.php
return $panel
    ->id('admin')
    ->spa(false) // Desabilitado para estabilidade
    ...
```

### Por Que Funciona
Com `spa(false)`, cada navegação força um recarregamento completo da página, garantindo que o estado do JS seja limpo e as declarações ocorram apenas uma vez.

## Prevencao
- [ ] Checklist: Sempre manter `->spa(false)` em projetos com muitos scripts injetados.
- [ ] Usar verificações de existência para variáveis globais: `window.myVar = window.myVar || ...`

## Referencias
- Documentacao Filament: Single Page Applications (SPA)
- Error: Identifier already declared
