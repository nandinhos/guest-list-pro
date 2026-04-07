@props(['cor' => 'azul', 'tamanho' => 'md'])

@php
$cores = match($cor) {
    'azul'    => 'bg-[#EEF2FF] text-[#4D5EF6] ring-1 ring-[#4D5EF6]/20',
    'verde'   => 'bg-[#DCFCE7] text-[#15803D] ring-1 ring-[#4ADE80]/30',
    'vermelho'=> 'bg-[#FEF2F2] text-[#DC2626] ring-1 ring-[#F87171]/30',
    'cinza'   => 'bg-[#F1F5F9] text-[#718096] ring-1 ring-[#718096]/20',
    'amarelo' => 'bg-[#FEFCE8] text-[#A16207] ring-1 ring-[#FDE047]/30',
    default   => 'bg-[#EEF2FF] text-[#4D5EF6] ring-1 ring-[#4D5EF6]/20',
};
$tam = match($tamanho) {
    'sm' => 'px-2 py-0.5 text-[10px]',
    'lg' => 'px-3 py-1 text-sm',
    default => 'px-2.5 py-0.5 text-xs',
};
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center font-semibold rounded-lg {$cores} {$tam}"]) }}>
    {{ $slot }}
</span>
