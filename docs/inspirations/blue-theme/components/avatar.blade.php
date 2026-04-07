@props(['nome' => 'User', 'src' => null, 'tamanho' => 'md', 'online' => false])

@php
$tam = match($tamanho) {
    'xs' => 'w-6 h-6 text-[10px]',
    'sm' => 'w-8 h-8 text-xs',
    'lg' => 'w-12 h-12 text-base',
    'xl' => 'w-16 h-16 text-xl',
    default => 'w-10 h-10 text-sm',
};
$iniciais = collect(explode(' ', $nome))->take(2)->map(fn($p) => strtoupper($p[0]))->implode('');
$cores = ['bg-[#4D5EF6]', 'bg-[#4ADE80]', 'bg-[#F87171]', 'bg-purple-400', 'bg-amber-400'];
$cor = $cores[crc32($nome) % count($cores)];
@endphp

<div class="relative inline-flex" {{ $attributes }}>
    @if($src)
        <img src="{{ $src }}" alt="{{ $nome }}" class="{{ $tam }} rounded-full object-cover border-2 border-white">
    @else
        <div class="{{ $tam }} {{ $cor }} rounded-full flex items-center justify-center font-bold text-white border-2 border-white">
            {{ $iniciais }}
        </div>
    @endif
    @if($online)
        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#4ADE80] border-2 border-white rounded-full"></span>
    @endif
</div>
