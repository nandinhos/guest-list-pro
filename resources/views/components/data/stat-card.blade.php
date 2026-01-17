@props([
    'label' => '',
    'value' => '0',
    'change' => null, // Ex: '+12%' ou '-5%'
    'changeType' => 'neutral', // up, down, neutral
    'icon' => null,
    'iconColor' => 'indigo', // indigo, purple, emerald, amber, red
])

@php
    // Cores do ícone
    $iconColors = [
        'indigo' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400',
        'purple' => 'bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400',
        'emerald' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400',
        'amber' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-400',
        'red' => 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400',
    ];

    // Cores do indicador de mudança
    $changeColors = [
        'up' => 'text-emerald-600 dark:text-emerald-400 bg-emerald-100 dark:bg-emerald-900/50',
        'down' => 'text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/50',
        'neutral' => 'text-slate-600 dark:text-slate-400 bg-slate-100 dark:bg-slate-700',
    ];

    // Ícones de mudança
    $changeIcons = [
        'up' => 'heroicon-m-arrow-trending-up',
        'down' => 'heroicon-m-arrow-trending-down',
        'neutral' => 'heroicon-m-minus',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700 shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-0.5']) }}>
    <div class="flex items-start justify-between">
        {{-- Conteúdo Principal --}}
        <div class="flex-1">
            {{-- Label --}}
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">
                {{ $label }}
            </p>
            
            {{-- Valor --}}
            <p class="text-3xl font-bold text-slate-900 dark:text-white tracking-tight">
                {{ $value }}
            </p>

            {{-- Indicador de Mudança --}}
            @if($change)
                <div class="flex items-center gap-1.5 mt-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {{ $changeColors[$changeType] ?? $changeColors['neutral'] }}">
                        <x-dynamic-component :component="$changeIcons[$changeType] ?? $changeIcons['neutral']" class="w-3.5 h-3.5" />
                        {{ $change }}
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">vs. período anterior</span>
                </div>
            @endif
        </div>

        {{-- Ícone --}}
        @if($icon)
            <div class="shrink-0 ml-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $iconColors[$iconColor] ?? $iconColors['indigo'] }}">
                    <x-dynamic-component :component="$icon" class="w-6 h-6" />
                </div>
            </div>
        @endif
    </div>

    {{-- Footer Slot --}}
    @isset($footer)
        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700">
            {{ $footer }}
        </div>
    @endisset
</div>
