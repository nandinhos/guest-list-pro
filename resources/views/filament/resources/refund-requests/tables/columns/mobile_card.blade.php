<div class="flex flex-col justify-between min-h-[170px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Comprador e Botão Detalhes --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col max-w-[70%]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Solicitação #{{ $getState()->id }}
            </span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1">
                {{ $getState()->ticketSale?->buyer_name ?? 'N/A' }}
            </h3>
            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                {{ $getState()->ticketSale?->buyer_document ?? 'Sem documento' }}
            </span>
        </div>
        
        <div class="flex items-center gap-1">
            <button
                type="button"
                wire:click="mountTableAction('view', {{ $getState()->id }})"
                class="p-2 text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-all"
                title="Ver Detalhes"
            >
                <x-filament::icon icon="heroicon-m-eye" class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Body: Evento e Motivo --}}
    <div class="py-2">
        <div class="flex items-center gap-1.5 mb-1">
            <x-filament::icon icon="heroicon-m-calendar" class="h-3 w-3 text-gray-400" />
            <span class="text-[11px] font-medium text-gray-600 dark:text-gray-300 line-clamp-1">
                {{ $getState()->ticketSale?->event?->name ?? 'Evento não encontrado' }}
            </span>
        </div>
        <div class="flex items-start gap-1.5 bg-gray-50 dark:bg-gray-800/40 p-2 rounded-lg border border-gray-100 dark:border-gray-700/50">
            <x-filament::icon icon="heroicon-m-chat-bubble-left-right" class="h-3 w-3 text-gray-400 mt-0.5 shrink-0" />
            <p class="text-[11px] text-gray-500 dark:text-gray-400 italic line-clamp-2">
                "{{ $getState()->reason }}"
            </p>
        </div>
    </div>

    {{-- Footer: Valor e Ações de Aprovação/Rejeição --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Valor</span>
            <span class="text-base font-black text-primary-600 dark:text-primary-500 leading-none">
                R$ {{ number_format($getState()->ticketSale?->value ?? 0, 2, ',', '.') }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @if($getState()->isPending())
                <button
                    type="button"
                    wire:click="mountTableAction('reject', {{ $getState()->id }})"
                    class="flex items-center gap-1 px-2.5 py-1.5 bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 rounded-lg text-[10px] font-bold hover:bg-danger-100 dark:hover:bg-danger-500/20 transition-all border border-danger-200 dark:border-danger-500/30"
                >
                    <x-filament::icon icon="heroicon-m-x-circle" class="h-3.5 w-3.5" />
                    REJEITAR
                </button>
                <button
                    type="button"
                    wire:click="mountTableAction('approve', {{ $getState()->id }})"
                    class="flex items-center gap-1 px-2.5 py-1.5 bg-success-50 dark:bg-success-500/10 text-success-600 dark:text-success-400 rounded-lg text-[10px] font-bold hover:bg-success-100 dark:hover:bg-success-500/20 transition-all border border-success-200 dark:border-success-500/30"
                >
                    <x-filament::icon icon="heroicon-m-check-circle" class="h-3.5 w-3.5" />
                    APROVAR
                </button>
            @else
                <x-filament::badge :color="$getState()->status->getColor()" size="xs" class="font-bold">
                    {{ $getState()->status->getLabel() }}
                </x-filament::badge>
            @endif
        </div>
    </div>
</div>