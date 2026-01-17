@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'iconRight' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'disabled' => false,
])

@php
    $inputId = $name ?? 'input-' . uniqid();
    
    $inputClasses = 'block w-full rounded-xl border-0 bg-white dark:bg-slate-800 py-2.5 text-slate-900 dark:text-white shadow-sm ring-1 ring-inset transition-all duration-200 placeholder:text-slate-400 dark:placeholder:text-slate-500 focus:ring-2 focus:ring-inset disabled:bg-slate-50 dark:disabled:bg-slate-900 disabled:text-slate-500 disabled:cursor-not-allowed sm:text-sm sm:leading-6';
    
    // Ajusta padding baseado em ícones
    $paddingLeft = $icon ? 'pl-10' : 'pl-3';
    $paddingRight = $iconRight ? 'pr-10' : 'pr-3';
    
    // Ring color baseado em estado
    $ringColor = $error 
        ? 'ring-red-300 dark:ring-red-500/50 focus:ring-red-500' 
        : 'ring-slate-200 dark:ring-slate-700 focus:ring-indigo-500 dark:focus:ring-indigo-400';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    {{-- Input Container --}}
    <div class="relative">
        {{-- Icon Left --}}
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <x-dynamic-component :component="$icon" class="h-5 w-5 text-slate-400" />
            </div>
        @endif

        {{-- Input --}}
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $inputId }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->except('class')->merge(['class' => $inputClasses . ' ' . $paddingLeft . ' ' . $paddingRight . ' ' . $ringColor]) }}
        />

        {{-- Icon Right --}}
        @if($iconRight)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <x-dynamic-component :component="$iconRight" class="h-5 w-5 text-slate-400" />
            </div>
        @endif
    </div>

    {{-- Error Message --}}
    @if($error)
        <p class="mt-1.5 text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
            <x-heroicon-m-exclamation-circle class="w-4 h-4 shrink-0" />
            {{ $error }}
        </p>
    @endif

    {{-- Hint Text --}}
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">
            {{ $hint }}
        </p>
    @endif
</div>
