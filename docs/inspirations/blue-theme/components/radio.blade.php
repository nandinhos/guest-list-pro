@props([
    'rotulo'     => '',
    'descricao'  => '',
    'nome'       => '',
    'valor'      => '',
    'selecionado' => false,
    'desativado' => false,
])
@php
$uid = 'radio-' . uniqid();
@endphp

<div {{ $attributes->merge(['class' => 'flex items-start gap-3']) }}>

    <div class="relative flex-shrink-0 mt-0.5">
        <input type="radio"
               id="{{ $uid }}"
               name="{{ $nome }}"
               value="{{ $valor }}"
               @if($selecionado) checked @endif
               @if($desativado) disabled @endif
               class="sr-only peer">

        <label for="{{ $uid }}"
               class="w-4 h-4 rounded-full border-2 flex items-center justify-center transition-all duration-150
                      {{ $desativado ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}
                      peer-checked:border-[#4D5EF6] peer-checked:bg-white border-[#E2E8F0] bg-white hover:border-[#4D5EF6]">
            <span class="w-2 h-2 rounded-full bg-[#4D5EF6] scale-0 peer-checked:scale-100 transition-transform duration-150 hidden"></span>
        </label>

        {{-- Custom dot overlay (Alpine approach) --}}
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
            <div class="w-2 h-2 rounded-full bg-[#4D5EF6] transition-all duration-150 {{ $selecionado ? 'opacity-100 scale-100' : 'opacity-0 scale-0' }}"
                 id="{{ $uid }}-dot"></div>
        </div>
    </div>

    @if($rotulo || $descricao)
    <label for="{{ $uid }}" class="{{ $desativado ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }} flex flex-col">
        @if($rotulo)
            <span class="text-sm font-medium text-[#1A202C] leading-none mb-0.5">{{ $rotulo }}</span>
        @endif
        @if($descricao)
            <span class="text-xs text-[#718096]">{{ $descricao }}</span>
        @endif
    </label>
    @endif
</div>

<script>
(function() {
    var input = document.getElementById('{{ $uid }}');
    var dot   = document.getElementById('{{ $uid }}-dot');
    if (!input || !dot) { return; }
    var updateDot = function() {
        if (input.checked) {
            dot.classList.remove('opacity-0', 'scale-0');
            dot.classList.add('opacity-100', 'scale-100');
        } else {
            dot.classList.remove('opacity-100', 'scale-100');
            dot.classList.add('opacity-0', 'scale-0');
        }
    };
    input.addEventListener('change', updateDot);
    // Also update when any radio in same group changes
    var group = document.querySelectorAll('input[name="{{ $nome }}"]');
    group.forEach(function(r) { r.addEventListener('change', updateDot); });
    updateDot();
})();
</script>
