@props([
    'rotulo'         => '',
    'descricao'      => '',
    'marcado'        => false,
    'indeterminate'  => false,
    'nome'           => '',
    'valor'          => '1',
    'desativado'     => false,
])
@php
$uid = 'cb-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3']) }}
     x-data="{ marcado: @js($marcado), indeterminate: @js($indeterminate) }"
     x-init="$nextTick(() => { if (indeterminate) { $refs.cbInput.indeterminate = true } })">

    <div class="relative flex-shrink-0 mt-0.5">
        <input type="checkbox"
               id="{{ $uid }}"
               name="{{ $nome }}"
               value="{{ $valor }}"
               x-ref="cbInput"
               x-model="marcado"
               @change="indeterminate = false; $refs.cbInput.indeterminate = false"
               @if($desativado) disabled @endif
               class="sr-only peer">

        <div @click="if (!{{ $desativado ? 'true' : 'false' }}) { marcado = !marcado; indeterminate = false; $refs.cbInput.indeterminate = false }"
             :class="{
                 'bg-[#4D5EF6] border-[#4D5EF6]': marcado || indeterminate,
                 'bg-white border-[#E2E8F0] hover:border-[#4D5EF6]': !marcado && !indeterminate,
                 'opacity-50 cursor-not-allowed': {{ $desativado ? 'true' : 'false' }},
                 'cursor-pointer': !{{ $desativado ? 'true' : 'false' }},
             }"
             class="w-4 h-4 rounded flex items-center justify-center border-2 transition-all duration-150">

            {{-- Check icon --}}
            <svg x-show="marcado && !indeterminate"
                 class="w-2.5 h-2.5 text-white" viewBox="0 0 12 12" fill="none">
                <path d="M2 6L5 9L10 3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{-- Indeterminate icon --}}
            <svg x-show="indeterminate"
                 class="w-2.5 h-2.5 text-white" viewBox="0 0 12 12" fill="none">
                <path d="M2 6H10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    @if($rotulo || $descricao)
    <label for="{{ $uid }}" class="{{ $desativado ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} flex flex-col">
        @if($rotulo)
            <span class="text-sm font-medium text-[#1A202C] leading-none mb-0.5">{{ $rotulo }}</span>
        @endif
        @if($descricao)
            <span class="text-xs text-[#718096]">{{ $descricao }}</span>
        @endif
    </label>
    @endif
</div>
