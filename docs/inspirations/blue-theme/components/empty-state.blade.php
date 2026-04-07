@props([
    'titulo'     => 'Nenhum item encontrado',
    'descricao'  => '',
    'icone'      => '📭',
    'acao'       => '',
    'href_acao'  => '',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-12 px-6']) }}>

    {{-- Ícone --}}
    <div class="w-20 h-20 rounded-2xl bg-[#EEF2FF] border border-[#E2E8F0] flex items-center justify-center mb-5"
         style="box-shadow: 0 4px 20px rgba(77,94,246,0.08);">
        @if(str_starts_with($icone, '<'))
            {!! $icone !!}
        @else
            <span class="text-4xl leading-none">{{ $icone }}</span>
        @endif
    </div>

    {{-- Título --}}
    <h3 class="text-base font-bold text-[#1A202C] mb-1">{{ $titulo }}</h3>

    {{-- Descrição --}}
    @if($descricao)
        <p class="text-sm text-[#718096] max-w-xs mb-5">{{ $descricao }}</p>
    @endif

    {{-- Slot extra --}}
    @if($slot->isNotEmpty())
        <div class="mb-5">{{ $slot }}</div>
    @endif

    {{-- Botão de ação --}}
    @if($acao)
        @if($href_acao)
            <a href="{{ $href_acao }}"
               class="inline-flex items-center gap-2 px-5 py-2 bg-[#4D5EF6] text-white text-sm font-semibold rounded-xl hover:bg-[#3d4ee6] transition-colors"
               style="box-shadow: 0 4px 20px rgba(77,94,246,0.25);">
                {{ $acao }}
            </a>
        @else
            <button type="button"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#4D5EF6] text-white text-sm font-semibold rounded-xl hover:bg-[#3d4ee6] transition-colors"
                    style="box-shadow: 0 4px 20px rgba(77,94,246,0.25);">
                {{ $acao }}
            </button>
        @endif
    @endif

    {{-- Borda dashed decorativa (sutil) --}}
    <div class="absolute inset-4 rounded-2xl border-2 border-dashed border-[#E2E8F0] pointer-events-none -z-10 hidden"></div>
</div>
