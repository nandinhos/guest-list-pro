@props(['record', 'editUrl' => null])

<div class="flex justify-between items-stretch gap-3 w-full p-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm relative overflow-hidden">
    <!-- Left Column: Infos + Status -->
    <div class="flex flex-col space-y-3 flex-1 min-w-0 justify-between">
        
        <!-- Name -->
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight">
            {{ $record->name }}
        </span>

        <!-- Guest Details -->
        <div class="flex flex-col space-y-1">
            <div class="flex items-center space-x-1 text-xs text-gray-500">
                <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
                <span class="font-medium truncate">{{ $record->document ?? 'S/D' }}</span>
            </div>
            <div class="flex items-center space-x-1">
                <x-heroicon-m-calendar class="w-4 h-4 text-gray-400 shrink-0"/>
                <span class="text-[10px] text-gray-600 dark:text-gray-400 truncate">
                    {{ $record->event?->name }}
                    <span class="text-gray-300 dark:text-gray-600 mx-1">|</span>
                    <span class="font-medium text-indigo-600 dark:text-indigo-400">
                        {{ $record->sector?->name }}
                    </span>
                </span>
            </div>
            <div class="flex items-center space-x-1">
                <x-heroicon-m-user class="w-4 h-4 text-gray-400 shrink-0"/>
                <span class="text-[10px] text-gray-500 truncate">
                    Promoter: <span class="font-medium">{{ $record->promoter?->name ?? 'N/A' }}</span>
                </span>
            </div>
        </div>

        <!-- Status and Validator -->
        <div class="flex flex-col space-y-1.5 min-w-0">
             @if($record->is_checked_in)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-success-500/10 text-success-700 w-fit">
                    <x-heroicon-m-check-circle class="w-3 h-3 mr-1 shrink-0"/>
                    Check-in: {{ $record->checked_in_at?->format('d/m/Y H:i') }}
                </span>
            @else
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-500/10 text-gray-700 w-fit">
                    <x-heroicon-m-clock class="w-3 h-3 mr-1 shrink-0"/>
                    Pendente
                </span>
            @endif

            <!-- Validator Name -->
            @if($record->is_checked_in && $record->validator)
                <div class="flex items-center text-[10px] text-gray-400 truncate mt-0.5">
                    <x-heroicon-m-user class="w-3 h-3 mr-1 shrink-0"/>
                    <span class="truncate">Validado por: {{ $record->validator->name }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Actions (QR Code + Edit) -->
    <div class="shrink-0 flex flex-col justify-between items-end gap-3 pl-2">
        <!-- Big QR Code Button -->
        <button 
            wire:click="mountTableAction('downloadQr', {{ $record->id }})"
            class="inline-flex items-center justify-center p-3 text-gray-500 hover:text-indigo-600 bg-gray-50 hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-indigo-500/10 rounded-xl transition-colors border border-gray-200 hover:border-indigo-200 dark:border-gray-700 dark:hover:border-indigo-800 shadow-sm"
            title="Download QR"
        >
            <x-heroicon-o-qr-code class="w-7 h-7 stroke-2"/>
        </button>
        
        <!-- Edit Button -->
        @if($editUrl)
            <a
                href="{{ $editUrl }}"
                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors border border-indigo-100 dark:border-indigo-900 w-full whitespace-nowrap"
            >
                <x-heroicon-m-pencil-square class="w-3.5 h-3.5 mr-1 shrink-0"/>
                Editar
            </a>
        @else
            <button
                wire:click="mountTableAction('edit', {{ $record->id }})"
                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors border border-indigo-100 dark:border-indigo-900 w-full whitespace-nowrap"
            >
                <x-heroicon-m-pencil-square class="w-3.5 h-3.5 mr-1 shrink-0"/>
                Editar
            </button>
        @endif
    </div>
</div>
