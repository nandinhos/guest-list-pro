@props(['componentKey', 'exampleKey'])

@php
    $demos = [
        'button' => [
            'Básico' => 'button-basic',
            'Com Ícone' => 'button-icon',
            'Variantes' => 'button-variants',
            'Loading' => 'button-loading',
            'Link' => 'button-link',
        ],
        'card' => [
            'Básico' => 'card-basic',
            'Glass' => 'card-glass',
            'Com Hover' => 'card-hover',
            'Com Header/Footer' => 'card-header-footer',
        ],
        'badge' => [
            'Variantes' => 'badge-variants',
            'Com Dot' => 'badge-dot',
            'Removível' => 'badge-removable',
        ],
        'input' => [
            'Básico' => 'input-basic',
            'Com Ícone' => 'input-icon',
            'Com Erro' => 'input-error',
            'Com Dica' => 'input-hint',
        ],
        'stat-card' => [
            'Básico' => 'stat-card-basic',
            'Com Variação' => 'stat-card-change',
            'Variação Negativa' => 'stat-card-negative',
        ],
        'alert' => [
            'Info' => 'alert-info',
            'Sucesso' => 'alert-success',
            'Aviso' => 'alert-warning',
            'Erro' => 'alert-error',
        ],
        'skeleton' => [
            'Texto' => 'skeleton-text',
            'Avatar' => 'skeleton-avatar',
            'Card' => 'skeleton-card',
            'Linha de Tabela' => 'skeleton-table',
        ],
        'empty-state' => [
            'Básico' => 'empty-state-basic',
            'Com Ação' => 'empty-state-action',
        ],
    ];
    
    $demoFile = $demos[$componentKey][$exampleKey] ?? null;
@endphp

@if($demoFile)
    @include('components.docs.demos.' . $demoFile)
@else
    <span class="text-surface-muted">Demo não disponível</span>
@endif
