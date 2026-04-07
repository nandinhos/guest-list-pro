@props([
    'pagina'     => 1,
    'total'      => 0,
    'por_pagina' => 15,
    'rota'       => '',
])
@php
$totalPaginas = $por_pagina > 0 ? (int) ceil($total / $por_pagina) : 1;
$inicio       = $total > 0 ? (($pagina - 1) * $por_pagina) + 1 : 0;
$fim          = min($pagina * $por_pagina, $total);

// Gera o range de páginas com elipsis
$paginas = [];
if ($totalPaginas <= 7) {
    $paginas = range(1, $totalPaginas);
} else {
    $paginas[] = 1;
    if ($pagina > 3) {
        $paginas[] = '…';
    }
    foreach (range(max(2, $pagina - 1), min($totalPaginas - 1, $pagina + 1)) as $p) {
        $paginas[] = $p;
    }
    if ($pagina < $totalPaginas - 2) {
        $paginas[] = '…';
    }
    $paginas[] = $totalPaginas;
}

$btnBase    = 'w-8 h-8 flex items-center justify-center rounded-lg text-xs font-semibold transition-colors';
$btnAtivo   = "{$btnBase} bg-[#4D5EF6] text-white";
$btnNormal  = "{$btnBase} text-[#718096] bg-white border border-[#E2E8F0] hover:bg-[#EEF2FF] hover:text-[#4D5EF6]";
$btnDisable = "{$btnBase} text-[#A0AEC0] bg-[#F8F9FB] border border-[#E2E8F0] opacity-40 cursor-not-allowed";
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-between gap-4 flex-wrap']) }}>
    <p class="text-xs text-[#718096]">
        @if($total > 0)
            Mostrando <span class="font-semibold text-[#1A202C]">{{ $inicio }}</span>–<span class="font-semibold text-[#1A202C]">{{ $fim }}</span> de <span class="font-semibold text-[#1A202C]">{{ $total }}</span> resultados
        @else
            Nenhum resultado encontrado
        @endif
    </p>

    @if($totalPaginas > 1)
    <div class="flex items-center gap-1">
        {{-- Anterior --}}
        @if($pagina > 1)
            <a href="{{ route($rota, ['pagina' => $pagina - 1]) }}"
               class="{{ $btnNormal }}">←</a>
        @else
            <span class="{{ $btnDisable }}">←</span>
        @endif

        {{-- Números --}}
        @foreach($paginas as $p)
            @if($p === '…')
                <span class="w-8 h-8 flex items-center justify-center text-xs text-[#718096]">…</span>
            @elseif($p === $pagina)
                <span class="{{ $btnAtivo }}">{{ $p }}</span>
            @else
                <a href="{{ route($rota, ['pagina' => $p]) }}"
                   class="{{ $btnNormal }}">{{ $p }}</a>
            @endif
        @endforeach

        {{-- Próximo --}}
        @if($pagina < $totalPaginas)
            <a href="{{ route($rota, ['pagina' => $pagina + 1]) }}"
               class="{{ $btnNormal }}">→</a>
        @else
            <span class="{{ $btnDisable }}">→</span>
        @endif
    </div>
    @endif
</div>
