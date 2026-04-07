@props([
    'abas'     => [],
    'variante' => 'linha',
])
@php
$abaAtiva = collect($abas)->firstWhere('ativo', true)['id'] ?? ($abas[0]['id'] ?? '');
@endphp

<div {{ $attributes }} x-data="{ abaAtiva: '{{ $abaAtiva }}' }">

    {{-- Nav --}}
    <div class="{{ $variante === 'pilula' ? 'flex gap-1 bg-[#E9ECF1] p-1 rounded-xl w-fit' : 'flex border-b border-[#E2E8F0]' }}">
        @foreach($abas as $aba)
            @php
            $btnBase = 'inline-flex items-center gap-2 font-medium text-sm transition-all duration-150 focus:outline-none';
            $btnStyle = $variante === 'pilula'
                ? "{$btnBase} px-4 py-1.5 rounded-lg"
                : "{$btnBase} px-4 py-2.5 -mb-px border-b-2";
            @endphp
            <button type="button"
                    @click="abaAtiva = '{{ $aba['id'] }}'"
                    :class="abaAtiva === '{{ $aba['id'] }}'
                        ? '{{ $variante === 'pilula'
                            ? 'bg-white text-[#4D5EF6] shadow-sm'
                            : 'border-[#4D5EF6] text-[#4D5EF6]' }}'
                        : '{{ $variante === 'pilula'
                            ? 'text-[#718096] hover:text-[#1A202C]'
                            : 'border-transparent text-[#718096] hover:text-[#1A202C] hover:border-[#E2E8F0]' }}'"
                    class="{{ $btnStyle }}">
                @if(!empty($aba['icone']))
                    <span class="text-base leading-none">{{ $aba['icone'] }}</span>
                @endif
                {{ $aba['rotulo'] }}
                @if(!empty($aba['contador']))
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                          :class="abaAtiva === '{{ $aba['id'] }}' ? 'bg-[#EEF2FF] text-[#4D5EF6]' : 'bg-[#E9ECF1] text-[#718096]'">
                        {{ $aba['contador'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Panels --}}
    <div class="mt-4">
        @foreach($abas as $aba)
            @php $slotId = $aba['id']; @endphp
            @if(isset($$slotId))
                <div x-show="abaAtiva === '{{ $aba['id'] }}'"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0">
                    {{ $$slotId }}
                </div>
            @endif
        @endforeach
        {{-- Slot padrão quando não há slots nomeados --}}
        @if($slot->isNotEmpty())
            {{ $slot }}
        @endif
    </div>
</div>
