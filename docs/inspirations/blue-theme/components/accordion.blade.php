@props([
    'titulo'   => '',
    'aberto'   => false,
    'variante' => 'default',
])
@php
$wrapClass = match($variante) {
    'flush'    => 'border-b border-[#E2E8F0]',
    'separado' => 'bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden',
    default    => 'bg-white rounded-xl border border-[#E2E8F0]',
};
$btnPad  = $variante === 'flush' ? 'px-0 py-4' : 'px-5 py-4';
$bodyPad = $variante === 'flush' ? 'px-0 pb-4' : 'px-5 pb-5';
@endphp

<div {{ $attributes->merge(['class' => $wrapClass]) }}
     x-data="{ aberto: @js($aberto) }">

    {{-- Trigger --}}
    <button type="button"
            @click="aberto = !aberto"
            :aria-expanded="aberto"
            class="w-full flex items-center justify-between {{ $btnPad }} text-left gap-4 focus:outline-none focus:ring-2 focus:ring-[#4D5EF6]/30 focus:ring-inset {{ $variante !== 'flush' ? 'rounded-xl' : '' }}">

        <span class="text-sm font-semibold text-[#1A202C]">{{ $titulo }}</span>

        {{-- Ícone + que rotaciona --}}
        <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-lg text-[#718096] hover:text-[#4D5EF6] transition-colors"
              :class="aberto ? 'text-[#4D5EF6]' : ''">
            <svg class="w-4 h-4 transition-transform duration-200"
                 :class="{ 'rotate-45': aberto }"
                 viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
        </span>
    </button>

    {{-- Body com animação --}}
    <div x-show="aberto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2">
        <div class="{{ $bodyPad }} text-sm text-[#718096] leading-relaxed">
            {{ $slot }}
        </div>
    </div>
</div>
