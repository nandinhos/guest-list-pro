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

    // Classes base usando tokens do design system
    $inputClasses = 'block w-full rounded-xl border-0 bg-[var(--glass-bg-strong)] py-2.5 shadow-sm ring-1 ring-inset transition-all placeholder:text-surface-muted focus:ring-2 focus:ring-inset disabled:bg-[var(--color-surface-100)] disabled:cursor-not-allowed sm:text-sm sm:leading-6';

    // Ajusta padding baseado em ícones
    $paddingLeft = $icon ? 'pl-10' : 'pl-3';
    $paddingRight = $iconRight ? 'pr-10' : 'pr-3';

    // Ring color baseado em estado - usando tokens
    $ringColor = $error
        ? 'ring-[var(--color-danger-500)]/30 focus:ring-[var(--color-danger-500)]'
        : 'ring-[var(--glass-border)] focus:ring-[var(--color-brand-admin-500)]';

    // Text color
    $textColor = 'text-[var(--color-surface-900)]';
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full']) }}>
    {{-- Label --}}
    @if($label)
        <label for="{{ $inputId }}" class="block text-sm font-medium text-surface-primary mb-1.5">
            {{ $label }}
            @if($required)
                <span class="text-[var(--color-danger-500)]">*</span>
            @endif
        </label>
    @endif

    {{-- Input Container --}}
    <div class="relative">
        {{-- Icon Left --}}
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <x-dynamic-component :component="$icon" class="h-5 w-5 text-surface-secondary" />
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
            {{ $attributes->except('class')->merge(['class' => $inputClasses . ' ' . $paddingLeft . ' ' . $paddingRight . ' ' . $ringColor . ' ' . $textColor]) }}
        />

        {{-- Icon Right --}}
        @if($iconRight)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                <x-dynamic-component :component="$iconRight" class="h-5 w-5 text-surface-secondary" />
            </div>
        @endif
    </div>

    {{-- Error Message --}}
    @if($error)
        <p class="mt-1.5 text-sm text-[var(--color-danger-600)] flex items-center gap-1">
            <x-heroicon-m-exclamation-circle class="w-4 h-4 shrink-0" />
            {{ $error }}
        </p>
    @endif

    {{-- Hint Text --}}
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-surface-secondary">
            {{ $hint }}
        </p>
    @endif
</div>
