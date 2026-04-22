<div class="flex flex-col justify-between min-h-[180px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Nome e Ações --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col max-w-[75%]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Setor #{{ $getRecord()->id }}
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

    {{-- Body: Capacidade e Progresso --}}
    <div class="py-2">
        <div class="flex justify-between items-end mb-1.5">
            <div class="flex flex-col">
                <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Ocupação do Setor</span>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <x-filament::icon icon="heroicon-m-users" class="w-3.5 h-3.5 text-primary-500"/>
                    <span class="text-base font-black text-gray-900 dark:text-gray-100 leading-none">
                        {{ $getRecord()->capacity ?? '∞' }}
                    </span>
                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-widest">Capacidade</span>
                </div>
            </div>
            
            @if($getRecord()->capacity)
                @php
                    $percent = min(100, round(($getRecord()->ticket_sales_count / $getRecord()->capacity) * 100));
                    $barColor = match(true) {
                        $percent >= 90 => 'bg-danger-500',
                        $percent >= 70 => 'bg-warning-500',
                        default => 'bg-success-500',
                    };
                @endphp
                <div class="flex flex-col items-end">
                    <span class="text-[11px] font-black text-gray-700 dark:text-gray-300 leading-none">{{ $percent }}%</span>
                </div>
            @endif
        </div>

        @if($getRecord()->capacity)
            <div class="w-full h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden shadow-inner">
                <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000 shadow-[0_0_8px_rgba(var(--primary-500),0.3)]" style="width: {{ $percent }}%"></div>
            </div>
        @else
             <div class="w-full h-2 bg-gray-50 dark:bg-gray-800/50 rounded-full border border-dashed border-gray-200 dark:border-gray-700"></div>
        @endif
    </div>

    {{-- Footer: Vendas --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Vendas Confirmadas</span>
            <div class="flex items-center gap-1.5 mt-0.5">
                <span class="text-sm font-black text-success-600 dark:text-success-400 leading-none">
                    {{ number_format($getRecord()->ticket_sales_count ?? 0, 0, ',', '.') }}
                </span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium lowercase">ingressos</span>
            </div>
        </div>

        <div class="flex items-center">
             <x-filament::badge color="gray" size="xs" class="font-bold tracking-tighter">
                REF: {{ $getRecord()->id }}
            </x-filament::badge>
        </div>
    </div>
</div>

