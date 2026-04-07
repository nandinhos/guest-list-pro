@props(['label' => null, 'erro' => null, 'prefixo' => null, 'sufixo' => null, 'placeholder' => ''])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label class="block text-xs font-semibold text-[#1A202C] mb-1.5 uppercase tracking-wider">
            {{ $label }}
        </label>
    @endif
    <div class="relative flex items-center">
        @if($prefixo)
            <span class="absolute left-3 text-[#718096] text-sm">{{ $prefixo }}</span>
        @endif
        <input
            {{ $attributes->except(['class', 'label', 'erro', 'prefixo', 'sufixo', 'placeholder'])->merge([
                'placeholder' => $placeholder,
                'class' => 'w-full bg-white border border-[#E2E8F0] rounded-xl text-sm text-[#1A202C] placeholder-[#A0AEC0] focus:outline-none focus:ring-2 focus:ring-[#4D5EF6]/30 focus:border-[#4D5EF6] transition py-2 ' . ($prefixo ? 'pl-8 pr-3' : ($sufixo ? 'pl-3 pr-8' : 'px-3')),
            ]) }}
        >
        @if($sufixo)
            <span class="absolute right-3 text-[#718096] text-sm">{{ $sufixo }}</span>
        @endif
    </div>
    @if($erro)
        <p class="mt-1 text-xs text-[#F87171]">{{ $erro }}</p>
    @endif
</div>
