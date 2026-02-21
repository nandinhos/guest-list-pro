@props(['record'])

<div class="flex flex-col space-y-3 p-1">
    <!-- Row 1: Nome do Convidado -->
    <div class="flex justify-between items-start gap-2 w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $record->name }}
        </span>
    </div>

    <!-- Row 2: Info e QR Code -->
    <div class="flex justify-between items-center gap-2">
        <!-- Guest Details -->
        <div class="flex flex-col space-y-1 flex-1 min-w-0">
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

        <!-- Big QR Code Button -->
        <div class="shrink-0 flex items-center justify-center pl-2">
            <button 
                wire:click="mountTableAction('downloadQr', {{ $record->id }})"
                class="inline-flex items-center justify-center p-2.5 text-gray-500 hover:text-indigo-600 bg-white hover:bg-indigo-50 dark:bg-gray-800 dark:hover:bg-indigo-500/10 rounded-xl transition-colors border border-gray-200 hover:border-indigo-200 dark:border-gray-700 dark:hover:border-indigo-800 shadow-sm"
                title="Download QR"
            >
                <x-heroicon-o-qr-code class="w-7 h-7 stroke-2"/>
            </button>
        </div>
    </div>

    <!-- Row 3: Status, Date and Actions -->
    <div class="flex items-end justify-between gap-3 w-full pt-1">
        <!-- Status Badge -->
        <div class="flex flex-col space-y-1.5 min-w-0 flex-1">
             @if($record->is_checked_in)
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-success-500/10 text-success-700 w-fit">
                    <x-heroicon-m-check-circle class="w-3 h-3 mr-1"/>
                    Check-in: {{ $record->checked_in_at?->format('d/m/Y H:i') }}
                </span>
            @else
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-500/10 text-gray-700 w-fit">
                    <x-heroicon-m-clock class="w-3 h-3 mr-1"/>
                    Pendente
                </span>
            @endif

            <!-- Validator Name -->
            @if($record->is_checked_in && $record->validator)
                <div class="flex items-center text-[10px] text-gray-400 truncate">
                    <x-heroicon-m-user class="w-3 h-3 mr-1 shrink-0"/>
                    <span class="truncate">Validado por: {{ $record->validator->name }}</span>
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="shrink-0 flex items-center gap-2">
            <button
                wire:click="mountTableAction('edit', {{ $record->id }})"
                class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors border border-indigo-100 dark:border-indigo-900"
            >
                <x-heroicon-m-pencil-square class="w-4 h-4 mr-1.5"/>
                Editar
            </button>
        </div>
    </div>
</div>
