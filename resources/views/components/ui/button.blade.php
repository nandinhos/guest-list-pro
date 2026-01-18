@props([
    'variant' => 'primary', // primary, secondary, ghost, danger, success, warning
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'iconRight' => null,
    'loading' => false,
    'disabled' => false,
    'href' => null,
    'type' => 'button',
])

@php
    // Tamanhos
    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs gap-1.5',
        'md' => 'px-4 py-2 text-sm gap-2',
        'lg' => 'px-6 py-3 text-base gap-2.5',
    ];

    // Variantes - usando classes do design system
    $variants = [
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        'ghost' => 'btn-ghost',
        'danger' => 'btn-danger',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
    ];

    // Classes base
    $baseClasses = 'inline-flex items-center justify-center font-medium rounded-xl focus:outline-none focus-ring disabled:opacity-50 disabled:cursor-not-allowed';

    // Montar classes finais
    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp

@if($href && !$disabled)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && !$loading)
            @svg($icon, 'w-4 h-4 shrink-0')
        @endif
        @if($loading)
            <svg class="animate-spin w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight && !$loading)
            @svg($iconRight, 'w-4 h-4 shrink-0')
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        {{ $attributes->merge(['class' => $classes]) }}
        @if($disabled || $loading) disabled @endif
    >
        @if($icon && !$loading)
            @svg($icon, 'w-4 h-4 shrink-0')
        @endif
        @if($loading)
            <svg class="animate-spin w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @endif
        <span>{{ $slot }}</span>
        @if($iconRight && !$loading)
            @svg($iconRight, 'w-4 h-4 shrink-0')
        @endif
    </button>
@endif
