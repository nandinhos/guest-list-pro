@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
])

@php
    // Configuração de cores e ícones por tipo
    $config = [
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'icon' => 'heroicon-o-information-circle',
            'iconColor' => 'text-blue-500 dark:text-blue-400',
            'titleColor' => 'text-blue-800 dark:text-blue-300',
            'textColor' => 'text-blue-700 dark:text-blue-400',
        ],
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'border' => 'border-emerald-200 dark:border-emerald-800',
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'text-emerald-500 dark:text-emerald-400',
            'titleColor' => 'text-emerald-800 dark:text-emerald-300',
            'textColor' => 'text-emerald-700 dark:text-emerald-400',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'border' => 'border-amber-200 dark:border-amber-800',
            'icon' => 'heroicon-o-exclamation-triangle',
            'iconColor' => 'text-amber-500 dark:text-amber-400',
            'titleColor' => 'text-amber-800 dark:text-amber-300',
            'textColor' => 'text-amber-700 dark:text-amber-400',
        ],
        'danger' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'text-red-500 dark:text-red-400',
            'titleColor' => 'text-red-800 dark:text-red-300',
            'textColor' => 'text-red-700 dark:text-red-400',
        ],
    ];

    $c = $config[$type] ?? $config['info'];
@endphp

<div 
    x-data="{ show: true }" 
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    {{ $attributes->merge(['class' => "rounded-xl border p-4 {$c['bg']} {$c['border']}"]) }}
>
    <div class="flex gap-3">
        {{-- Ícone --}}
        <div class="shrink-0">
            <x-dynamic-component :component="$c['icon']" class="w-5 h-5 {{ $c['iconColor'] }}" />
        </div>

        {{-- Conteúdo --}}
        <div class="flex-1 min-w-0">
            @if($title)
                <h4 class="text-sm font-semibold {{ $c['titleColor'] }} mb-1">
                    {{ $title }}
                </h4>
            @endif
            <div class="text-sm {{ $c['textColor'] }}">
                {{ $slot }}
            </div>
        </div>

        {{-- Botão de Fechar --}}
        @if($dismissible)
            <div class="shrink-0">
                <button 
                    type="button" 
                    @click="show = false"
                    class="p-1 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 transition-colors {{ $c['iconColor'] }}"
                >
                    <x-heroicon-m-x-mark class="w-4 h-4" />
                </button>
            </div>
        @endif
    </div>
</div>
