# Licao: UX Mobile no Filament - Modal vs. Página na Edição

**Data**: 2026-02-21
**Stack**: Filament v4, Livewire v3
**Tags**: [success-pattern, ux, quality]

## Contexto
A ação padrão de "Edit" no Filament em tabelas mobile costuma abrir um Modal. O usuário reportou que isso prejudicava a qualidade visual e a navegabilidade.

## Problema
Modais em telas pequenas (mobile) limitam o espaço de trabalho, podem ter problemas de scroll concorrente e não oferecem uma navegação nativa de "voltar" intuitiva.

## Solucao
### Correcao Aplicada
Substituir o uso de `mountTableAction` (que abre modal) por links diretos para a página de edição do recurso.

1. **Geração da URL no Componente Pai**:
```php
// No mobile_card.blade.php (coluna do Filament)
<x-guest-mobile-card 
    :record="$getRecord()" 
    :editUrl="\App\Filament\Resources\Guests\GuestResource::getUrl('edit', ['record' => $getRecord()])"
/>
```

2. **Uso no Componente Filho**:
```blade
<a href="{{ $editUrl }}" class="...">Editar</a>
```

### Por Que Funciona
Isso aproveita o sistema de rotas de página do Filament Resource, fornecendo uma interface em tela cheia com cabeçalho padrão, botões de ação (Salvar/Cancelar) claros e integração com o histórico de navegação do navegador/celular.

## Prevencao
Como evitar no futuro:
- [ ] Para formulários complexos no mobile, priorizar navegação por página (`getUrl('edit')`) em vez de modais.
- [ ] Desabilitar o clique na linha da tabela (`->recordUrl(null)`) se houver ações explícitas no card para evitar comportamentos conflitantes.

## Referencias
- [GuestResource Edit Page](file:///home/nandodev/projects/guest-list-pro/app/Filament/Resources/Guests/GuestResource.php)
- [Promoter Guest Table](file:///home/nandodev/projects/guest-list-pro/app/Filament/Promoter/Resources/Guests/Tables/GuestsTable.php)
