@props([
    'titulo'  => '',
    'tamanho' => 'md',
    'aberto'  => 'aberto',
])
@php
$maxWidth = match($tamanho) {
    'sm' => 'max-w-sm',
    'lg' => 'max-w-2xl',
    'xl' => 'max-w-4xl',
    default => 'max-w-lg',
};
@endphp

<div {{ $attributes }}
     x-show="{{ $aberto }}"
     x-trap.noscroll="{{ $aberto }}"
     @keydown.escape.window="{{ $aberto }} = false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="display: none;">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-[#1A202C]/50 backdrop-blur-sm"
         @click="{{ $aberto }} = false"></div>

    {{-- Container --}}
    <div class="relative w-full {{ $maxWidth }} bg-white rounded-2xl border border-[#E2E8F0] overflow-hidden"
         style="box-shadow: 0 20px 60px rgba(0,0,0,0.15);"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E2E8F0]">
            <h3 class="text-base font-bold text-[#1A202C]">{{ $titulo }}</h3>
            <button type="button"
                    @click="{{ $aberto }} = false"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-[#718096] hover:bg-[#E9ECF1] hover:text-[#1A202C] transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 text-sm text-[#1A202C]">
            {{ $slot }}
        </div>

        {{-- Footer --}}
        @if(isset($footer))
            <div class="px-6 py-4 bg-[#F8F9FB] border-t border-[#E2E8F0] flex items-center justify-end gap-3">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
