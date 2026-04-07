@props(['ativo' => false, 'icone' => null, 'href' => '#'])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150 ' . ($ativo ? 'bg-[#4D5EF6] text-white shadow-sm' : 'text-[#718096] hover:bg-[#E9ECF1] hover:text-[#1A202C]')]) }}>
    @if($icone)
        <span class="text-base leading-none">{{ $icone }}</span>
    @endif
    {{ $slot }}
</a>
