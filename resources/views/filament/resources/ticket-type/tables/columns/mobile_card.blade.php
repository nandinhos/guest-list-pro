<div class="flex flex-col justify-between min-h-[180px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Nome e Ações --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col max-w-[75%]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Tipo de Ingresso #{{ $getRecord()->id }}
            </span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $getRecord()->name }}
            </h3>
            <div class="flex items-center gap-1 mt-0.5">
                <x-filament::icon icon="heroicon-m-calendar-days" class="w-3 h-3 text-gray-400 shrink-0"/>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate font-medium">
                    {{ $getRecord()->event?->name ?? 'Evento não definido' }}
                </span>
            </div>
        </div>
        
        <div class="flex items-center gap-1">
            <button
                type="button"
                wire:click="mountTableAction('edit', {{ $getRecord()->id }})"
                class="p-2 text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-all"
                title="Editar"
            >
                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Body: Descrição, Setores e Vendas --}}
    <div class="py-2 space-y-2">
        @if($getRecord()->description)
            <p class="text-[10px] text-gray-500 dark:text-gray-400 line-clamp-2 italic bg-gray-50 dark:bg-gray-800/40 p-2 rounded-lg border border-gray-100/50 dark:border-gray-800/50">
                "{{ $getRecord()->description }}"
            </p>
        @endif

        @php
            $sectorPrices = $getRecord()->sectorPrices->sortBy(fn ($sp) => $sp->sector->name ?? '');
        @endphp
        @if($sectorPrices->count() > 0)
            <div class="flex flex-col gap-0.5">
                <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Setores</span>
                @foreach($sectorPrices as $sp)
                    @php
                        $soldCount = $getRecord()->ticketSales->where('sector_id', $sp->sector_id)->count();
                    @endphp
                    <div class="flex items-center gap-2 text-[10px] py-0.5 px-1.5 rounded bg-gray-50 dark:bg-gray-800/30">
                        <span class="w-16 text-gray-600 dark:text-gray-400 truncate">{{ $sp->sector->name ?? 'N/A' }}</span>
                        @if($soldCount > 0)
                            <span class="text-gray-400 dark:text-gray-500 text-[9px]">({{ $soldCount }})</span>
                        @endif
                        <span class="text-primary-600 dark:text-primary-400 font-medium ml-auto">R$ {{ number_format($sp->price, 2, ',', '.') }}</span>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="flex items-center gap-2 bg-gray-50/50 dark:bg-gray-800/30 p-2 rounded-xl mt-1">
            <div class="flex flex-col">
                <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Vendas</span>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                        {{ number_format($getRecord()->ticket_sales_count ?? 0, 0, ',', '.') }}
                    </span>
                    <span class="text-[9px] text-gray-400 uppercase tracking-widest font-medium">unid</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Footer: Status --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex gap-1.5">
            @if($getRecord()->is_active)
                <x-filament::badge color="success" size="xs" class="font-bold uppercase tracking-tighter">
                    ATIVO
                </x-filament::badge>
            @else
                <x-filament::badge color="danger" size="xs" class="font-bold uppercase tracking-tighter">
                    INATIVO
                </x-filament::badge>
            @endif

            @if($getRecord()->is_visible)
                <x-filament::badge color="info" size="xs" class="font-bold uppercase tracking-tighter">
                    VISÍVEL
                </x-filament::badge>
            @else
                <x-filament::badge color="gray" size="xs" class="font-bold uppercase tracking-tighter">
                    OCULTO
                </x-filament::badge>
            @endif
        </div>

        <div class="flex items-center">
             <x-filament::badge color="gray" size="xs" class="font-bold tracking-tighter">
                ID: {{ $getRecord()->id }}
            </x-filament::badge>
        </div>
    </div>
</div>

