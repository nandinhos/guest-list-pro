@php
    $sectorPrices = $getRecord()->sectorPrices->sortBy(fn ($sp) => $sp->sector->name ?? '');
@endphp

<div class="flex flex-col gap-0.5 min-w-[160px]">
    @forelse($sectorPrices as $sp)
        @php
            $soldCount = $getRecord()->ticketSales->where('sector_id', $sp->sector_id)->count();
        @endphp
        <div class="flex items-center gap-2 text-xs py-0.5 px-1 rounded bg-gray-50 dark:bg-gray-800/40">
            <span class="w-20 text-gray-700 dark:text-gray-300 truncate">{{ $sp->sector->name ?? 'N/A' }}</span>
            @if($soldCount > 0)
                <span class="text-gray-500 dark:text-gray-400 text-[10px]">({{ $soldCount }})</span>
            @endif
            <span class="text-primary-600 dark:text-primary-400 font-medium whitespace-nowrap ml-auto">R$ {{ number_format($sp->price, 2, ',', '.') }}</span>
        </div>
    @empty
        <span class="text-xs text-gray-400 italic">Sem setores configurados</span>
    @endforelse
</div>