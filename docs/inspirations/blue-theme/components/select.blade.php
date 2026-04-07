@props([
    'rotulo'     => '',
    'opcoes'     => [],
    'selecionado' => '',
    'nome'       => '',
    'erro'       => '',
    'ajuda'      => '',
    'desativado' => false,
])
@php
$uid         = 'select-' . uniqid();
$borderClass = $erro
    ? 'border-[#F87171] focus:ring-[#F87171]/30 focus:border-[#F87171]'
    : 'border-[#E2E8F0] focus:ring-[#4D5EF6]/30 focus:border-[#4D5EF6]';
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>

    @if($rotulo)
        <label for="{{ $uid }}" class="block text-xs font-semibold text-[#1A202C] uppercase tracking-wider">
            {{ $rotulo }}
        </label>
    @endif

    <div class="relative">
        <select id="{{ $uid }}"
                name="{{ $nome }}"
                @if($desativado) disabled @endif
                class="w-full appearance-none bg-white {{ $borderClass }} border rounded-xl text-sm text-[#1A202C] px-3 py-2 pr-9
                       focus:outline-none focus:ring-2 transition-all duration-150
                       {{ $desativado ? 'opacity-50 cursor-not-allowed bg-[#F8F9FB]' : '' }}
                       placeholder-[#A0AEC0]">
            @foreach($opcoes as $opcao)
                <option value="{{ $opcao['valor'] }}" {{ $selecionado === (string) $opcao['valor'] ? 'selected' : '' }}>
                    {{ $opcao['rotulo'] }}
                </option>
            @endforeach
        </select>

        {{-- Chevron SVG --}}
        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
            <svg class="w-4 h-4 text-[#718096]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </div>
    </div>

    @if($erro)
        <p class="text-xs text-[#DC2626] flex items-center gap-1">
            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            {{ $erro }}
        </p>
    @elseif($ajuda)
        <p class="text-xs text-[#718096]">{{ $ajuda }}</p>
    @endif
</div>
