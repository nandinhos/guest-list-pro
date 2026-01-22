<div class="flex flex-col space-y-3 py-1.5 w-full overflow-hidden">
    <!-- Row 1: Nome do Evento e Status -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->name }}
        </span>
        <div class="flex items-center space-x-2 shrink-0">
             @if($getRecord()->is_active)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-success-500/10 text-success-700">
                    Ativo
                </span>
            @else
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-danger-500/10 text-danger-700">
                    Inativo
                </span>
            @endif
        </div>
    </div>

    <!-- Row 2: Data e Info -->
    <div class="flex flex-col space-y-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-calendar class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="font-medium truncate">{{ $getRecord()->date?->format('d/m/Y') ?? 'S/D' }}</span>
        </div>
        <div class="flex items-center space-x-1 text-gray-500">
            <x-heroicon-m-users class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="text-[10px] truncate">
                Total de Convidados: <span class="font-bold">{{ $getRecord()->guests_count ?? $getRecord()->guests()->count() }}</span>
            </span>
        </div>
    </div>

    <!-- Row 3: Ações -->
    <div class="flex items-center justify-end w-full pt-1 border-t border-gray-100 dark:border-gray-800">
        <button
            wire:click="mountTableAction('edit', {{ $getRecord()->id }})"
            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors"
        >
            <x-heroicon-m-pencil-square class="w-4 h-4 mr-1.5"/>
            Editar
        </button>
    </div>
</div>
