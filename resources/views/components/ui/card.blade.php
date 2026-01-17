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

    // Variantes
    $variants = [
        'default' => 'bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm',
        'glass' => 'bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 shadow-lg',
        'elevated' => 'bg-white dark:bg-slate-800 shadow-xl shadow-slate-200/50 dark:shadow-slate-900/50 border border-slate-100 dark:border-slate-700',
        'bordered' => 'bg-transparent border-2 border-slate-200 dark:border-slate-700',
    ];

    // Hover effect
    $hoverClasses = $hover ? 'transition-all duration-300 hover:-translate-y-1 hover:shadow-xl cursor-pointer' : '';

    // Classes base
    $baseClasses = 'rounded-2xl overflow-hidden';

    // Montar classes finais
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($paddings[$padding] ?? $paddings['md']) . ' ' . $hoverClasses;
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{-- Header Slot --}}
    @isset($header)
        <div class="border-b border-slate-100 dark:border-slate-700 pb-4 mb-4 -mx-4 px-4 sm:-mx-6 sm:px-6 -mt-4 pt-4 sm:-mt-6 sm:pt-6">
            {{ $header }}
        </div>
    @endisset

    {{-- Content (Default Slot) --}}
    {{ $slot }}

    {{-- Footer Slot --}}
    @isset($footer)
        <div class="border-t border-slate-100 dark:border-slate-700 pt-4 mt-4 -mx-4 px-4 sm:-mx-6 sm:px-6 -mb-4 pb-4 sm:-mb-6 sm:pb-6 bg-slate-50/50 dark:bg-slate-900/50">
            {{ $footer }}
        </div>
    @endisset
</div>
