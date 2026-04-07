@props([
    'eventos' => [],
])
@php
$dotCores = [
    'azul'     => 'bg-[#4D5EF6] ring-[#EEF2FF]',
    'verde'    => 'bg-[#4ADE80] ring-[#DCFCE7]',
    'vermelho' => 'bg-[#F87171] ring-[#FEF2F2]',
    'cinza'    => 'bg-[#718096] ring-[#F1F5F9]',
];
@endphp

<div {{ $attributes->merge(['class' => 'relative']) }}>

    {{-- Linha vertical --}}
    <div class="absolute left-[7px] top-2 bottom-2 w-px bg-[#E2E8F0]"></div>

    <ul class="space-y-6 pl-6">
        @foreach($eventos as $i => $evento)
            @php
            $cor       = $evento['cor'] ?? 'azul';
            $dotClass  = $dotCores[$cor] ?? $dotCores['azul'];
            $delay     = ($i * 100);
            @endphp
            <li class="relative animate-slide-up" style="animation-delay: {{ $delay }}ms;">

                {{-- Dot --}}
                <span class="absolute -left-6 top-1 w-3.5 h-3.5 rounded-full ring-4 {{ $dotClass }} flex-shrink-0"
                      style="box-shadow: 0 0 0 3px white;"></span>

                <div class="flex flex-col sm:flex-row sm:items-start sm:gap-4">
                    {{-- Data --}}
                    <time class="text-[10px] font-bold text-[#718096] uppercase tracking-wider sm:w-28 sm:text-right flex-shrink-0 mt-0.5 mb-0.5 sm:mb-0">
                        {{ $evento['data'] }}
                    </time>

                    {{-- Conteúdo --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            @if(!empty($evento['icone']))
                                <span class="text-base leading-none">{{ $evento['icone'] }}</span>
                            @endif
                            <h4 class="text-sm font-semibold text-[#1A202C] truncate">{{ $evento['titulo'] }}</h4>
                        </div>
                        @if(!empty($evento['descricao']))
                            <p class="text-xs text-[#718096] leading-relaxed">{{ $evento['descricao'] }}</p>
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
</div>
