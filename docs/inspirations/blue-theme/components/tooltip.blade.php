@props([
    'texto'    => '',
    'posicao'  => 'top',
    'variante' => 'escuro',
])
@php
$bgClass = match($variante) {
    'azul'  => 'bg-[#4D5EF6] text-white',
    default => 'bg-[#1A202C] text-white',
};

$positionClasses = match($posicao) {
    'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
    'left'   => 'right-full top-1/2 -translate-y-1/2 mr-2',
    'right'  => 'left-full top-1/2 -translate-y-1/2 ml-2',
    default  => 'bottom-full left-1/2 -translate-x-1/2 mb-2',
};

$arrowWrap = match($posicao) {
    'bottom' => 'top-0 left-1/2 -translate-x-1/2 -translate-y-full',
    'left'   => 'right-0 top-1/2 -translate-y-1/2 translate-x-full',
    'right'  => 'left-0 top-1/2 -translate-y-1/2 -translate-x-full',
    default  => 'bottom-0 left-1/2 -translate-x-1/2 translate-y-full',
};

$arrowDir = match($posicao) {
    'bottom' => 'border-b-[#1A202C] border-t-transparent border-l-transparent border-r-transparent border-[5px] border-b-[' . ($variante === 'azul' ? '#4D5EF6' : '#1A202C') . ']',
    'left'   => 'border-l-[#1A202C] border-r-transparent border-t-transparent border-b-transparent border-[5px]',
    'right'  => 'border-r-[#1A202C] border-l-transparent border-t-transparent border-b-transparent border-[5px]',
    default  => 'border-t-[#1A202C] border-b-transparent border-l-transparent border-r-transparent border-[5px]',
};
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex']) }}
     x-data="{ visivel: false }"
     @mouseenter="visivel = true"
     @mouseleave="visivel = false"
     @focusin="visivel = true"
     @focusout="visivel = false">

    {{ $slot }}

    <div x-show="visivel"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-50 pointer-events-none {{ $positionClasses }}"
         role="tooltip">
        <div class="{{ $bgClass }} text-xs font-semibold px-3 py-1.5 rounded-lg whitespace-nowrap"
             style="box-shadow: 0 4px 16px rgba(0,0,0,0.15);">
            {{ $texto }}
        </div>
        {{-- Seta --}}
        <span class="absolute {{ $arrowWrap }} w-0 h-0 {{ $arrowDir }}"></span>
    </div>
</div>
