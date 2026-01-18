@props([
    'label' => '',
    'value' => '0',
    'change' => null, // Ex: '+12%' ou '-5%'
    'changeType' => 'neutral', // up, down, neutral
    'icon' => null,
    'iconColor' => 'admin', // admin, promoter, validator, success, warning, danger
])

@php
    // Cores do ícone - usando classes do design system
    $iconColors = [
        'admin' => 'stat-icon-admin',
        'promoter' => 'stat-icon-promoter',
        'validator' => 'stat-icon-validator',
        'success' => 'stat-icon-success',
        'warning' => 'stat-icon-warning',
        'danger' => 'stat-icon-danger',
        // Compatibilidade com cores antigas
        'indigo' => 'stat-icon-admin',
        'purple' => 'stat-icon-promoter',
        'emerald' => 'stat-icon-validator',
        'amber' => 'stat-icon-warning',
        'red' => 'stat-icon-danger',
    ];

    // Cores do indicador de mudança - usando design system
    $changeColors = [
        'up' => 'badge-success',
        'down' => 'badge-danger',
        'neutral' => 'badge-default',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'glass-card p-6 hover-lift']) }}>
    <div class="flex items-start justify-between">
        {{-- Conteúdo Principal --}}
        <div class="flex-1">
            {{-- Label --}}
            <p class="text-sm font-medium text-[var(--color-surface-200)] mb-1">
                {{ $label }}
            </p>

            {{-- Valor --}}
            <p class="text-3xl font-bold text-[var(--color-surface-900)] tracking-tight">
                {{ $value }}
            </p>

            {{-- Indicador de Mudança --}}
            @if($change)
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $changeColors[$changeType] ?? $changeColors['neutral'] }}">
                        @if($changeType === 'up')
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                        @elseif($changeType === 'down')
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" /></svg>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        @endif
                        {{ $change }}
                    </span>
                    <span class="text-xs text-[var(--color-surface-200)]">vs. período anterior</span>
                </div>
            @endif
        </div>

        {{-- Ícone --}}
        @if($icon)
            <div class="shrink-0 ml-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $iconColors[$iconColor] ?? $iconColors['admin'] }}">
                    @svg($icon, 'w-6 h-6')
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Slot --}}
    @isset($footer)
        <div class="mt-4 pt-4 border-t border-[var(--glass-border)]">
            {{ $footer }}
        </div>
    @endisset
</div>
