@props(['label' => '', 'valor' => '0', 'tendencia' => null, 'porcentagem' => null, 'icone' => null])

<div class="bg-white rounded-2xl border border-[#E2E8F0] p-5" style="box-shadow: 0 4px 20px rgba(0,0,0,0.05)">
    <div class="flex items-start justify-between mb-3">
        <p class="text-xs font-semibold text-[#718096] uppercase tracking-wider">{{ $label }}</p>
        @if($icone)
            <div class="w-8 h-8 rounded-xl bg-[#EEF2FF] flex items-center justify-center text-[#4D5EF6] text-sm">
                {{ $icone }}
            </div>
        @endif
    </div>
    <p class="text-3xl font-bold text-[#1A202C] leading-none mb-2">{{ $valor }}</p>
    @if($tendencia !== null && $porcentagem !== null)
        <div class="flex items-center gap-1.5">
            <span class="text-xs font-semibold {{ $tendencia === 'up' ? 'text-[#4ADE80]' : 'text-[#F87171]' }}">
                {{ $tendencia === 'up' ? '↑' : '↓' }} {{ $porcentagem }}
            </span>
            <span class="text-xs text-[#718096]">vs mês anterior</span>
        </div>
    @endif
</div>
