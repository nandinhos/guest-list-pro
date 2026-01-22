# Conflitos de SPA e Erros de JavaScript no Filament

## Problema
O uso de `->spa()` no Filament pode causar erros de redeclaração de JavaScript, especialmente em funções globais como `loadDarkMode`. 
Além disso, plugins como `Chart.js` podem falhar ao tentar executar o método `destroy` em objetos que já foram removidos do DOM de forma inconsistente durante a navegação SPA do Livewire/Filament.

## Erros Comuns
- `Uncaught SyntaxError: Identifier 'loadDarkMode' has already been declared`
- `Uncaught TypeError: Cannot read properties of null (reading 'destroy')` no Chart.js

## Solução
A solução mais estável é desabilitar o modo SPA (`->spa()`) nos `PanelProviders` afetados. Isso garante um recarregamento limpo de todos os scripts e estados globais a cada navegação de página.

## Painéis Corrigidos
- AdminPanelProvider
- PromoterPanelProvider
- BilheteriaPanelProvider
- ValidatorPanelProvider
