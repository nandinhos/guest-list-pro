<div class="flex flex-col space-y-3 py-3 w-full">
    <!-- Row 1: Name and Sector -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->name }}
        </span>
        <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-700 ml-auto">
            {{ $getRecord()->sector->name ?? 'Geral' }}
        </span>
    </div>

    <!-- Row 2: Document & List -->
    <div class="flex flex-col space-y-0.5 pb-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="font-medium truncate">{{ $getRecord()->document ?? 'S/D' }}</span>
        </div>
        @if($getRecord()->promoter)
            <div class="flex items-center space-x-1 text-indigo-600 dark:text-indigo-400">
                <x-heroicon-m-clipboard-document-list class="w-4 h-4 shrink-0"/>
                <span class="text-[10px] font-medium truncate">{{ $getRecord()->promoter->name }}</span>
            </div>
        @endif
    </div>

    <!-- Row 3: Status/Validator (Left) and Action (Right) -->
    <div class="flex items-end justify-between gap-3 w-full">
        <!-- Left Column: Status & Validator -->
        <div class="flex flex-col space-y-1.5 min-w-0 flex-1">
            <!-- Status Badge -->
             @if($getRecord()->is_checked_in)
                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-success-500/10 text-success-700 w-fit whitespace-nowrap">
                    <x-heroicon-m-check-circle class="w-3.5 h-3.5 mr-1 pt-0.5"/>
                    {{ $getRecord()->checked_in_at?->format('d/m/Y \à\s H:i') }}
                </span>
            @else
                <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-warning-500/10 text-warning-700 w-fit whitespace-nowrap">
                    <x-heroicon-m-clock class="w-3.5 h-3.5 mr-1 pt-0.5"/>
                    Pendente
                </span>
            @endif

            <!-- Validator Name -->
            @if($getRecord()->is_checked_in && $getRecord()->validator)
                <div class="flex items-center text-[10px] text-gray-400 truncate">
                    <x-heroicon-m-user class="w-3 h-3 mr-1 shrink-0"/>
                    <span class="truncate">{{ $getRecord()->validator->name }}</span>
                </div>
            @endif
        </div>

        <!-- Right Column: Action Button -->
        <div class="shrink-0 pl-1">
             @if($getRecord()->is_checked_in)
                <button 
                    wire:click="mountTableAction('undoCheckIn', {{ $getRecord()->id }})"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-warning-700 bg-warning-500/10 rounded-lg border border-warning-200 hover:bg-warning-500/20 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-arrow-path class="w-4 h-4 mr-1.5"/>
                    Estornar
                </button>
            @else
                <button 
                    wire:click="mountTableAction('checkIn', {{ $getRecord()->id }})"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-white bg-success-600 rounded-lg hover:bg-success-700 transition-colors shadow-sm whitespace-nowrap"
                >
                    <x-heroicon-m-check-circle class="w-4 h-4 mr-1.5"/>
                    ENTRADA
                </button>
            @endif
        </div>
    </div>
</div>
