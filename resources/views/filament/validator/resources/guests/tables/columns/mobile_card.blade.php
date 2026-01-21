<div class="flex flex-col space-y-2 py-2">
    <!-- Info Block -->
    <div class="space-y-1">
        <div class="flex items-center justify-between">
            <span class="font-bold text-sm truncate max-w-[200px]">
                {{ $getRecord()->name }}
            </span>
            @if($getRecord()->is_checked_in)
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-500/10 text-success-700">
                    <x-heroicon-m-check-circle class="w-3 h-3 mr-1"/>
                    {{ $getRecord()->checked_in_at?->format('H:i') }}
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-500/10 text-warning-700">
                    <x-heroicon-m-clock class="w-3 h-3 mr-1"/>
                    Pendente
                </span>
            @endif
        </div>

        <div class="flex items-center space-x-2 text-xs text-gray-500">
            <x-heroicon-m-identification class="w-3 h-3"/>
            <span>{{ $getRecord()->document ?? 'S/D' }}</span>
        </div>

        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-700">
                {{ $getRecord()->sector->name ?? 'Geral' }}
            </span>
            @if($getRecord()->is_checked_in && $getRecord()->validator)
                <span class="text-[10px] text-gray-400 flex items-center">
                    <x-heroicon-m-user class="w-3 h-3 mr-0.5"/>
                    {{ $getRecord()->validator->name }}
                </span>
            @endif
        </div>
    </div>

    <!-- Actions Block -->
    <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-800">
        @if($getRecord()->is_checked_in)
            <button 
                wire:click="mountTableAction('undoCheckIn', {{ $getRecord()->id }})"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-warning-700 bg-warning-500/10 rounded-lg hover:bg-warning-500/20 transition-colors"
            >
                <x-heroicon-m-arrow-path class="w-3 h-3 mr-1.5"/>
                Estornar
            </button>
        @else
            <button 
                wire:click="mountTableAction('checkIn', {{ $getRecord()->id }})"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-success-600 rounded-lg hover:bg-success-700 transition-colors shadow-sm"
            >
                <x-heroicon-m-check-circle class="w-3 h-3 mr-1.5"/>
                ENTRADA
            </button>
        @endif
    </div>
</div>
