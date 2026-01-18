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

    // Variantes - usando classes do design system
    $variants = [
        'default' => 'badge-default',
        'success' => 'badge-success',
        'warning' => 'badge-warning',
        'danger' => 'badge-danger',
        'info' => 'badge-info',
        'primary' => 'badge-primary',
    ];

    // Classes do dot - usando design system
    $dotClasses = [
        'default' => 'badge-dot-default',
        'success' => 'badge-dot-success',
        'warning' => 'badge-dot-warning',
        'danger' => 'badge-dot-danger',
        'info' => 'badge-dot-info',
        'primary' => 'badge-dot-primary',
    ];

    // Classes base
    $baseClasses = 'inline-flex items-center gap-1.5 font-medium rounded-full';

    // Montar classes finais
    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Dot Indicator --}}
    @if($dot)
        <span class="{{ $dotSizes[$size] ?? $dotSizes['md'] }} {{ $dotClasses[$variant] ?? $dotClasses['default'] }} rounded-full animate-pulse"></span>
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
