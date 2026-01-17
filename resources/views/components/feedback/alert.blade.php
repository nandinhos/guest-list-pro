@props([
    'type' => 'info', // info, success, warning, danger
    'title' => null,
    'dismissible' => false,
])

@php
    // Configuração de cores por tipo
    $config = [
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-900/20',
            'border' => 'border-blue-200 dark:border-blue-800',
            'iconColor' => 'text-blue-500 dark:text-blue-400',
            'titleColor' => 'text-blue-800 dark:text-blue-300',
            'textColor' => 'text-blue-700 dark:text-blue-400',
        ],
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-900/20',
            'border' => 'border-emerald-200 dark:border-emerald-800',
            'iconColor' => 'text-emerald-500 dark:text-emerald-400',
            'titleColor' => 'text-emerald-800 dark:text-emerald-300',
            'textColor' => 'text-emerald-700 dark:text-emerald-400',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-900/20',
            'border' => 'border-amber-200 dark:border-amber-800',
            'iconColor' => 'text-amber-500 dark:text-amber-400',
            'titleColor' => 'text-amber-800 dark:text-amber-300',
            'textColor' => 'text-amber-700 dark:text-amber-400',
        ],
        'danger' => [
            'bg' => 'bg-red-50 dark:bg-red-900/20',
            'border' => 'border-red-200 dark:border-red-800',
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
            @if($type === 'info')
                <svg class="w-5 h-5 {{ $c['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
            @elseif($type === 'success')
                <svg class="w-5 h-5 {{ $c['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            @elseif($type === 'warning')
                <svg class="w-5 h-5 {{ $c['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            @else
                <svg class="w-5 h-5 {{ $c['iconColor'] }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            @endif
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
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        @endif
    </div>
</div>
