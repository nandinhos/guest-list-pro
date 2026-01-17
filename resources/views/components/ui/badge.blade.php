@props([
    'variant' => 'default', // default, success, warning, danger, info, primary
    'size' => 'md', // sm, md, lg
    'dot' => false,
    'removable' => false,
])

@php
    // Tamanhos
    $sizes = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];

    // Tamanhos do dot
    $dotSizes = [
        'sm' => 'w-1.5 h-1.5',
        'md' => 'w-2 h-2',
        'lg' => 'w-2.5 h-2.5',
    ];

    // Variantes - cores
    $variants = [
        'default' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300',
        'success' => 'bg-emerald-100 dark:bg-emerald-900/50 text-emerald-700 dark:text-emerald-400',
        'warning' => 'bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-400',
        'danger' => 'bg-red-100 dark:bg-red-900/50 text-red-700 dark:text-red-400',
        'info' => 'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400',
        'primary' => 'bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400',
    ];

    // Cores do dot
    $dotColors = [
        'default' => 'bg-slate-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
        'primary' => 'bg-indigo-500',
    ];

    // Classes base
    $baseClasses = 'inline-flex items-center gap-1.5 font-medium rounded-full';

    // Montar classes finais
    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Dot Indicator --}}
    @if($dot)
        <span class="{{ $dotSizes[$size] ?? $dotSizes['md'] }} {{ $dotColors[$variant] ?? $dotColors['default'] }} rounded-full animate-pulse"></span>
    @endif

    {{-- Content --}}
    {{ $slot }}

    {{-- Remove Button --}}
    @if($removable)
        <button type="button" class="ml-0.5 -mr-1 p-0.5 rounded-full hover:bg-black/10 dark:hover:bg-white/10 transition-colors">
            <x-heroicon-m-x-mark class="w-3 h-3" />
        </button>
    @endif
</span>
