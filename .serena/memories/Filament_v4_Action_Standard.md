# Standard: Filament v4 Table Actions

## Contexto
Na migração ou desenvolvimento com Filament v4, as ações de tabela e página foram unificadas para melhorar a consistência.

## Lições Aprendidas
1. **Namespace Unificado**: Use `Filament\Actions\Action` em vez dos namespaces específicos de tabela/página da v3 para a maioria dos casos.
2. **Record Actions vs Header Actions**: 
   - No `table()`, use `recordActions()` para botões que aparecem em cada linha.
   - Use `headerActions()` para botões globais no topo da tabela.
3. **Download de Arquivos**: Use `response()->streamDownload()` dentro do método `action()` para gerar e baixar arquivos dinamicamente (ex: QR Codes em SVG).

## Exemplo Recomendado
```php
use Filament\Actions\Action;

// Dentro de configure(Table $table)
->actions([
    Action::make('download')
        ->icon('heroicon-o-arrow-down-tray')
        ->action(fn ($record) => response()->streamDownload(...)),
])
```

## Evitar
- Tentar usar `Filament\Tables\Actions\Action` que pode gerar erros de "Class not found" em tempo de execução dependendo da configuração do painel.
- Esquecer de importar a Facade correta se estiver usando bibliotecas externas (ex: `SimpleSoftwareIO\QrCode\Facades\QrCode`).
