# Licao: Integridade de Variaveis em Componentes Blade (@props)

**Data**: 2026-02-21
**Stack**: Laravel 12, Blade
**Tags**: [bug, quality, clean-code]

## Contexto
Implementação de um componente compartilhado para cartões mobile onde uma variável condicional (`$editUrl`) foi introduzida para distinguir entre navegação por Link ou via Livewire.

## Problema
O erro `ErrorException: Undefined variable $editUrl` ocorreu ao renderizar a página, interrompendo o funcionamento para todos os usuários.

## Causa Raiz
### Analise (5 Whys)
1. **Por que falhou?** Variável `$editUrl` não encontrada.
2. **Por que?** O Blade não reconheceu o parâmetro passado pelo componente pai.
3. **Por que?** O componente dependia de uma variável que não estava explicitamente definida como propriedade.
4. **Por que?** O desenvolvedor assumiu que passar o atributo no componente pai (`:editUrl="..."`) seria suficiente.
5. **Por que?** Componentes anônimos do Blade exigem a diretiva `@props` para mapear atributos para variáveis locais da view.

## Solucao
### Correcao Aplicada
Declarar todas as variáveis esperadas e seus valores padrão na primeira linha do arquivo Blade:

```blade
@props(['record', 'editUrl' => null])

<!-- Uso seguro da variável -->
@if($editUrl)
    <a href="{{ $editUrl }}">...</a>
@endif
```

### Por Que Funciona
O `@props` garante que o Blade intercepte os atributos passados e os instancie como variáveis disponíveis no escopo do arquivo, evitando erros de "variável indefinida".

## Prevencao
Como evitar no futuro:
- [ ] Nunca criar componentes Blade sem definir `@props`.
- [ ] Definir valores padrão (ex: `=> null` ou `=> []`) para evitar quebras se o pai esquecer de passar o dado.
- [ ] Rodar um teste de fumaça (acesso à página) após alterar contratos de componentes.

## Referencias
- [Documentação Laravel Blade Components](https://laravel.com/docs/11.x/blade#components)
- [Guest Mobile Card](file:///home/nandodev/projects/guest-list-pro/resources/views/components/guest-mobile-card.blade.php)
