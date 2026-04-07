@props([
    'rotulo'      => 'Opções',
    'itens'       => [],
    'alinhamento' => 'esquerda',
    'variante'    => 'secundario',
])
@php
$base     = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 px-4 py-2 text-sm rounded-xl gap-2';
$btnClass = match($variante) {
    'primario' => "{$base} bg-[#4D5EF6] text-white hover:bg-[#3d4ee6] focus:ring-[#4D5EF6]/50 shadow-sm",
    'fantasma' => "{$base} bg-transparent text-[#718096] hover:bg-[#E9ECF1] hover:text-[#1A202C] focus:ring-[#4D5EF6]/30",
    default    => "{$base} bg-white text-[#1A202C] border border-[#E2E8F0] hover:bg-[#F8F9FB] focus:ring-[#4D5EF6]/30 shadow-sm",
};
$menuAlign = $alinhamento === 'direita' ? 'right-0' : 'left-0';
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-block']) }} x-data="{ aberto: false }" @click.away="aberto = false">
    <button type="button"
            @click="aberto = !aberto"
            :aria-expanded="aberto"
            class="{{ $btnClass }}">
        {{ $rotulo }}
        <svg class="w-4 h-4 transition-transform duration-150" :class="{ 'rotate-180': aberto }"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
    </button>

    <div x-show="aberto"
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute z-50 mt-2 min-w-[10rem] {{ $menuAlign }} bg-white rounded-xl border border-[#E2E8F0] shadow-lg py-1"
         style="box-shadow: 0 8px 32px rgba(0,0,0,0.10);"
         @click.stop>
        @foreach($itens as $item)
            @if(!empty($item['divisor']))
                <hr class="my-1 border-[#E2E8F0]">
            @elseif(!empty($item['href']))
                <a href="{{ $item['href'] }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-[#1A202C] hover:bg-[#EEF2FF] hover:text-[#4D5EF6] transition-colors">
                    @if(!empty($item['icone']))
                        <span class="text-base leading-none">{{ $item['icone'] }}</span>
                    @endif
                    {{ $item['label'] }}
                </a>
            @else
                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-sm text-[#1A202C] hover:bg-[#EEF2FF] hover:text-[#4D5EF6] transition-colors text-left">
                    @if(!empty($item['icone']))
                        <span class="text-base leading-none">{{ $item['icone'] }}</span>
                    @endif
                    {{ $item['label'] }}
                </button>
            @endif
        @endforeach
    </div>
</div>
