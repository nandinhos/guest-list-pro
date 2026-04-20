# Lição: Injeção de Bibliotecas JS em Painéis Filament

**Data**: 2026-02-21
**Stack**: Filament + Livewire + Alpine.js
**Tags**: frontend|javascript|filament

## Contexto

Carregar bibliotecas externas (ex: `html5-qrcode`) dentro de componentes Livewire em modais causa erros de `undefined` no Alpine.js devido a corrida de carregamento.

**Ambiente**: Filament Admin Panel com bibliotecas JS customizadas
**Frequência**: Baixa (bibliotecas JS especiais)
**Impacto**: Alto — feature JavaScript completamente quebrada

## Problema

```php
// ERRADO: injetar script dentro do modal Livewire
Modal::make()
    ->body(function () {
        return view('scanner-modal', [
            'script' => '<script src="html5-qrcode.js"></script>'
        ]);
    })
```

Scripts injection dentro de Livewire components causa:
- Alpine.js não encontra biblioteca
- Erro "undefined is not a function"
- Modal abre mas feature não funciona

## Causa Raiz

Livewire re-renders component dentro do contexto Alpine existente. Quando script é injetado via view, Alpine.js já inicializou e não vê o novo script.

## Solução

Registrar scripts essenciais no `HEAD` do painel via `renderHook` no `PanelProvider`:

```php
// PanelProvider.php
use Filament\Support\Enums\PanelRenderHook;

public function panel(Panel $panel): Panel
{
    return $panel
        ->renderHook(PanelRenderHook::HEAD_END, fn() => $this->getLibraryScripts());
}

// Método helper
protected function getLibraryScripts(): string
{
    return <<<HTML
        <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
        <script src="custom-init.js"></script>
    HTML;
}
```

## Prevenção

- [ ] Nunca injetar scripts via Livewire view/template
- [ ] Usar `renderHook(PanelRenderHook::HEAD_END)` para scripts globais
- [ ] Carregar bibliotecas antes do Alpine inicializar

## Referências

- [Filament Panel Provider](https://filament.dev/docs/panels/provider)
- [Filament Render Hooks](https://filament.dev/docs/support/render-hooks)