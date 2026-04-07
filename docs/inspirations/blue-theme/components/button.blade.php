@props(['variante' => 'primario', 'tipo' => 'button', 'desativado' => false, 'tamanho' => 'md'])

@php
$base = 'inline-flex items-center justify-center font-medium transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2';
$tamanhos = match($tamanho) {
    'sm' => 'px-3 py-1.5 text-xs rounded-xl gap-1.5',
    'lg' => 'px-6 py-3 text-base rounded-2xl gap-2.5',
    default => 'px-5 py-2 text-sm rounded-xl gap-2',
};
$classes = match($variante) {
    'primario'   => "{$base} {$tamanhos} bg-[#4D5EF6] text-white hover:bg-[#3d4ee6] focus:ring-[#4D5EF6]/50 shadow-sm",
    'secundario' => "{$base} {$tamanhos} bg-white text-[#1A202C] border border-[#E2E8F0] hover:bg-[#F8F9FB] focus:ring-[#4D5EF6]/30 shadow-sm",
    'fantasma'   => "{$base} {$tamanhos} bg-transparent text-[#718096] hover:bg-[#E9ECF1] hover:text-[#1A202C] focus:ring-[#4D5EF6]/20",
    'perigo'     => "{$base} {$tamanhos} bg-[#F87171] text-white hover:bg-[#ef5656] focus:ring-[#F87171]/50 shadow-sm",
    'sucesso'    => "{$base} {$tamanhos} bg-[#4ADE80] text-[#1A202C] hover:bg-[#38c96c] focus:ring-[#4ADE80]/50 shadow-sm",
    default      => "{$base} {$tamanhos} bg-[#4D5EF6] text-white hover:bg-[#3d4ee6] focus:ring-[#4D5EF6]/50 shadow-sm",
};
@endphp

<button
    type="{{ $tipo }}"
    @if($desativado) disabled @endif
    {{ $attributes->merge(['class' => $classes . ($desativado ? ' opacity-50 cursor-not-allowed' : '')]) }}
>
    {{ $slot }}
</button>
