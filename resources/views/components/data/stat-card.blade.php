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
                        @if($changeType === 'up')
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" /></svg>
                        @elseif($changeType === 'down')
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" /></svg>
                        @else
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" /></svg>
                        @endif
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
                    @svg($icon, 'w-6 h-6')
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
