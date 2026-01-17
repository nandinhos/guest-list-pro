@props([
    'type' => 'text', // text, card, avatar, table-row
    'lines' => 3,
])

@php
    $baseClasses = 'animate-pulse bg-slate-200 dark:bg-slate-700 rounded';
@endphp

@if($type === 'text')
    <div {{ $attributes->merge(['class' => 'space-y-2']) }}>
        @for($i = 0; $i < $lines; $i++)
            <div class="{{ $baseClasses }} h-4 {{ $i === $lines - 1 ? 'w-3/4' : 'w-full' }}"></div>
        @endfor
    </div>

@elseif($type === 'card')
    <div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200 dark:border-slate-700']) }}>
        <div class="flex items-start gap-4">
            <div class="{{ $baseClasses }} w-12 h-12 rounded-xl shrink-0"></div>
            <div class="flex-1 space-y-2">
                <div class="{{ $baseClasses }} h-4 w-1/3"></div>
                <div class="{{ $baseClasses }} h-6 w-2/3"></div>
            </div>
        </div>
        <div class="mt-4 space-y-2">
            <div class="{{ $baseClasses }} h-3 w-full"></div>
            <div class="{{ $baseClasses }} h-3 w-5/6"></div>
        </div>
    </div>

@elseif($type === 'avatar')
    <div {{ $attributes->merge(['class' => 'flex items-center gap-3']) }}>
        <div class="{{ $baseClasses }} w-10 h-10 rounded-full shrink-0"></div>
        <div class="flex-1 space-y-1.5">
            <div class="{{ $baseClasses }} h-4 w-24"></div>
            <div class="{{ $baseClasses }} h-3 w-32"></div>
        </div>
    </div>

@elseif($type === 'table-row')
    <div {{ $attributes->merge(['class' => 'flex items-center gap-4 py-3 px-4']) }}>
        <div class="{{ $baseClasses }} w-8 h-8 rounded-lg shrink-0"></div>
        <div class="{{ $baseClasses }} h-4 w-32"></div>
        <div class="{{ $baseClasses }} h-4 w-24 ml-auto"></div>
        <div class="{{ $baseClasses }} h-4 w-20"></div>
        <div class="{{ $baseClasses }} h-4 w-16"></div>
    </div>
@endif
