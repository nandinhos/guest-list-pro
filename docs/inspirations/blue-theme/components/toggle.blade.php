@props([
    'rotulo'    => '',
    'descricao' => '',
    'ativo'     => false,
    'cor'       => 'azul',
    'nome'      => '',
    'tamanho'   => 'md',
])
@php
$bgAtivo = match($cor) {
    'verde' => 'bg-[#4ADE80]',
    'cinza' => 'bg-[#718096]',
    default => 'bg-[#4D5EF6]',
};

[$trackW, $trackH, $thumbS, $thumbTranslate] = match($tamanho) {
    'sm' => ['w-8',  'h-4',  'w-3 h-3',  'translate-x-4'],
    'lg' => ['w-14', 'h-7',  'w-5 h-5',  'translate-x-7'],
    default => ['w-11', 'h-6', 'w-4 h-4', 'translate-x-5'],
};

$uid = 'toggle-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3']) }}
     x-data="{ ligado: @js($ativo) }">

    {{-- Track --}}
    <button type="button"
            role="switch"
            :aria-checked="ligado"
            @click="ligado = !ligado"
            :class="ligado ? '{{ $bgAtivo }}' : 'bg-[#E2E8F0]'"
            class="relative inline-flex shrink-0 {{ $trackW }} {{ $trackH }} rounded-full cursor-pointer transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-[#4D5EF6]/50 focus:ring-offset-2">
        <span class="pointer-events-none inline-block {{ $thumbS }} bg-white rounded-full shadow-sm transform transition duration-200 ease-in-out translate-x-1 mt-[{{ $tamanho === 'lg' ? '4px' : ($tamanho === 'sm' ? '2px' : '4px') }}]"
              :class="ligado ? '{{ $thumbTranslate }}' : 'translate-x-1'"
              style="margin-top: {{ $tamanho === 'lg' ? '4px' : '4px' }}; margin-top: {{ $tamanho === 'sm' ? '2px' : '4px' }};">
        </span>
    </button>

    {{-- Input hidden --}}
    <input type="hidden" name="{{ $nome }}" :value="ligado ? '1' : '0'">

    {{-- Label --}}
    @if($rotulo || $descricao)
    <div class="flex flex-col">
        @if($rotulo)
            <span class="text-sm font-medium text-[#1A202C] leading-none mb-0.5 cursor-pointer"
                  @click="ligado = !ligado">{{ $rotulo }}</span>
        @endif
        @if($descricao)
            <span class="text-xs text-[#718096]">{{ $descricao }}</span>
        @endif
    </div>
    @endif
</div>
