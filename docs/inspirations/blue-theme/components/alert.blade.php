@props(['tipo' => 'info', 'titulo' => null])

@php
$estilos = match($tipo) {
    'sucesso' => ['bg' => 'bg-[#F0FFF4]', 'borda' => 'border-[#4ADE80]', 'texto' => 'text-[#15803D]', 'icone' => '✓'],
    'erro'    => ['bg' => 'bg-[#FFF5F5]', 'borda' => 'border-[#F87171]', 'texto' => 'text-[#DC2626]', 'icone' => '✕'],
    'aviso'   => ['bg' => 'bg-[#FFFBEB]', 'borda' => 'border-[#FDE047]', 'texto' => 'text-[#A16207]', 'icone' => '⚠'],
    default   => ['bg' => 'bg-[#EEF2FF]', 'borda' => 'border-[#4D5EF6]', 'texto' => 'text-[#4D5EF6]', 'icone' => 'ℹ'],
};
@endphp

<div {{ $attributes->merge(['class' => "flex gap-3 p-4 rounded-xl border-l-4 {$estilos['bg']} {$estilos['borda']}"]) }}>
    <span class="text-base leading-none mt-0.5 {{ $estilos['texto'] }}">{{ $estilos['icone'] }}</span>
    <div>
        @if($titulo)
            <p class="text-sm font-semibold {{ $estilos['texto'] }}">{{ $titulo }}</p>
        @endif
        <div class="text-sm {{ $estilos['texto'] }} {{ $titulo ? 'mt-0.5 opacity-80' : '' }}">{{ $slot }}</div>
    </div>
</div>
