<div class="flex flex-col justify-between min-h-[170px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Nome e Função --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col flex-1 min-w-0 pr-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Permissão #{{ $getRecord()->id }}
            </span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 break-words leading-tight">
                {{ $getRecord()->user->name }}
            </h3>
        </div>
        
        @php
            $role = \App\Enums\UserRole::tryFrom($getRecord()->role);
            $roleColor = $role?->getColor() ?? 'gray';
        @endphp
        <x-filament::badge :color="$roleColor" size="xs" class="font-bold shrink-0 uppercase tracking-tighter">
            {{ $role?->getLabel() ?? $getRecord()->role }}
        </x-filament::badge>
    </div>

    {{-- Body: Evento e Setor --}}
    <div class="py-2 space-y-2">
        <div class="flex flex-col gap-1">
            <div class="flex items-center space-x-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                <span class="truncate font-medium text-gray-600 dark:text-gray-300">{{ $getRecord()->event?->name }}</span>
            </div>
            @if($getRecord()->sector)
                <div class="flex items-center space-x-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                    <x-filament::icon icon="heroicon-m-map-pin" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                    <span class="truncate text-primary-600 dark:text-primary-400 font-bold">{{ $getRecord()->sector->name }}</span>
                </div>
            @endif
        </div>


    </div>

    {{-- Footer: Datas e Ações --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Validade</span>
            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                {{ $getRecord()->start_time?->format('d/m') ?? 'Início' }} - {{ $getRecord()->end_time?->format('d/m') ?? 'Fim' }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="mountTableAction('quickEdit', {{ $getRecord()->id }})"
                class="p-2 text-info-600 hover:text-white dark:text-info-400 bg-info-50 dark:bg-info-500/10 hover:bg-info-600 rounded-xl transition-all border border-info-100 dark:border-info-500/20 shadow-sm"
                title="Editar Cota"
            >
                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
            </button>

            <button
                type="button"
                wire:click="mountTableAction('delete', {{ $getRecord()->id }})"
                class="p-2 text-danger-600 hover:text-white dark:text-danger-400 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-600 rounded-xl transition-all border border-danger-100 dark:border-danger-500/20 shadow-sm"
                title="Excluir"
            >
                <x-filament::icon icon="heroicon-m-trash" class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>
