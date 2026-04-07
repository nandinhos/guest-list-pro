@props([
    'itens' => [],
])

<nav {{ $attributes->merge(['class' => 'flex items-center min-w-0']) }} aria-label="Breadcrumb">
    <ol class="flex items-center gap-1 flex-wrap min-w-0">
        @foreach($itens as $item)
            @php $ultimo = $loop->last; @endphp
            <li class="flex items-center gap-1 min-w-0">
                @if(!$loop->first)
                    {{-- Separador --}}
                    <svg class="w-3.5 h-3.5 text-[#E2E8F0] flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                @endif

                @if($ultimo || empty($item['href']))
                    <span class="text-sm font-semibold text-[#1A202C] truncate max-w-[140px] sm:max-w-none"
                          aria-current="{{ $ultimo ? 'page' : 'false' }}">
                        {{ $item['rotulo'] }}
                    </span>
                @else
                    <a href="{{ $item['href'] }}"
                       class="text-sm font-medium text-[#718096] hover:text-[#4D5EF6] transition-colors truncate max-w-[120px] sm:max-w-none">
                        {{ $item['rotulo'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
