@props([
    'variant' => 'default', // default, glass, elevated, bordered
    'hover' => false,
    'padding' => 'md', // none, sm, md, lg
])

@php
    // Paddings
    $paddings = [
        'none' => '',
        'sm' => 'p-3',
        'md' => 'p-4 sm:p-6',
        'lg' => 'p-6 sm:p-8',
    ];

    // Variantes - usando tokens do design system
    $variants = [
        'default' => 'bg-[var(--glass-bg-strong)] border border-[var(--glass-border)] shadow-[var(--shadow-card)]',
        'glass' => 'glass-card',
        'elevated' => 'bg-[var(--glass-bg-strong)] shadow-[var(--shadow-elevated)] border border-[var(--glass-border)]',
        'bordered' => 'bg-transparent border-2 border-[var(--glass-border)]',
    ];

    // Hover effect - usando classe hover-lift do design system
    $hoverClasses = $hover ? 'hover-lift cursor-pointer' : '';

    // Classes base
    $baseClasses = 'rounded-2xl overflow-hidden';

    // Montar classes finais
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($paddings[$padding] ?? $paddings['md']) . ' ' . $hoverClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Header Slot --}}
    @isset($header)
        <div class="border-b border-[var(--glass-border)] pb-4 mb-4 -mx-4 px-4 sm:-mx-6 sm:px-6 -mt-4 pt-4 sm:-mt-6 sm:pt-6">
            {{ $header }}
        </div>
    @endisset

    {{-- Content (Default Slot) --}}
    {{ $slot }}

    {{-- Footer Slot --}}
    @isset($footer)
        <div class="border-t border-[var(--glass-border)] pt-4 mt-4 -mx-4 px-4 sm:-mx-6 sm:px-6 -mb-4 pb-4 sm:-mb-6 sm:pb-6 bg-[var(--color-surface-50)]/50">
            {{ $footer }}
        </div>
    @endisset
</div>
