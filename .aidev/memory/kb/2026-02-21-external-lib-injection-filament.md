# Licao: Injeção de Bibliotecas JS Externas em Painéis Filament

**Data**: 2026-02-21
**Stack**: Laravel 12 + Filament v4 + Alpine.js
**Tags**: [bug, filament, js, performance]

## Contexto
Necessidade de carregar bibliotecas JS de terceiros (ex: `html5-qrcode`) apenas em painéis específicos para uso em modais Livewire.

## Problema
Injetar a tag `<script>` diretamente na view do componente Livewire dentro do modal resulta em erros de `ReferenceError: X is not defined` no Alpine.js, pois o `init()` do Alpine dispara antes do script externo terminar de carregar e registrar a variável global.

## Causa Raiz
### Analise (5 Whys)
1. **Por que a lib nao funciona?** A variavel global nao existe no momento do init.
2. **Por que nao existe?** O script ainda esta baixando/processando.
3. **Por que o Alpine disparou antes?** Porque o script esta no corpo do modal injetado via AJAX pelo Livewire.
4. **Por que nao foi carregado antes?** Foi injetado de forma "lazy" dentro do componente.
5. **Por que?** Tentativa de otimizar o carregamento, mas ignorando o ciclo de vida do navegador.

## Solucao
### Correcao Aplicada
Registrar o script no `HEAD` do painel via `renderHook` no `PanelProvider`.
```php
// app/Providers/Filament/ValidatorPanelProvider.php
->renderHook(
    \Filament\View\PanelsRenderHook::HEAD_END,
    fn (): string => '<script src="https://unpkg.com/html5-qrcode"></script>'
)
```

### Por Que Funciona
Isso garante que a biblioteca seja carregada assim que o usuário entra no painel, ficando disponível globalmente na memória antes que qualquer modal ou componente Alpine seja inicializado.

## Prevencao
- [ ] Checklist: Bibliotecas JS essenciais para features do painel devem ser carregadas via `renderHook` no `HEAD`.
- [ ] Usar `async/await` no Alpine para verificar a existência da lib como segunda camada de proteção.

## Referencias
- Filament Docs: Render Hooks
- Alpine.js: Lifecycle hooks
