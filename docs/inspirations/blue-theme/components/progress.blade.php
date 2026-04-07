@props(['valor' => 0, 'max' => 100, 'cor' => 'azul', 'label' => null, 'mostrarValor' => true])

@php
$percentual = min(100, max(0, ($valor / $max) * 100));
$cores = match($cor) {
    'verde'   => 'bg-[#4ADE80]',
    'vermelho'=> 'bg-[#F87171]',
    'cinza'   => 'bg-[#718096]',
    default   => 'bg-[#4D5EF6]',
};
@endphp

<div {{ $attributes }}>
    @if($label || $mostrarValor)
        <div class="flex justify-between items-center mb-1.5">
            @if($label)
                <span class="text-xs font-medium text-[#718096]">{{ $label }}</span>
            @endif
            @if($mostrarValor)
                <span class="text-xs font-bold text-[#1A202C]">{{ round($percentual) }}%</span>
            @endif
        </div>
    @endif
    <div class="h-2 bg-[#E9ECF1] rounded-full overflow-hidden">
        <div class="{{ $cores }} h-full rounded-full transition-all duration-700 ease-out" style="width: {{ $percentual }}%"></div>
    </div>
</div>
