<div class="flex flex-col justify-between min-h-[200px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Nome e Tipo --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col flex-1 min-w-0 pr-2">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                Solicitação #{{ $getRecord()->id }}
            </span>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 break-words leading-tight">
                {{ $getRecord()->guest_name }}
            </h3>
        </div>
        
        @php
            $type = $getRecord()->type;
            $typeColor = $type->value === 'emergency_checkin' ? 'warning' : 'primary';
        @endphp
        <x-filament::badge :color="$typeColor" size="xs" class="font-bold shrink-0">
            {{ $type->getLabel() }}
        </x-filament::badge>
    </div>

    {{-- Body: Detalhes da Solicitação --}}
    <div class="py-2 space-y-1.5">
        <div class="flex items-center space-x-1.5 text-[11px] text-gray-500 dark:text-gray-400">
            <x-filament::icon icon="heroicon-m-identification" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
            <span class="truncate">{{ $getRecord()->guest_document ?? 'Sem Documento' }}</span>
            @if($getRecord()->sector)
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="text-primary-600 dark:text-primary-400 font-medium">{{ $getRecord()->sector->name }}</span>
            @endif
        </div>

        <div class="flex items-center space-x-1.5 text-[10px] text-gray-400 dark:text-gray-500">
            <x-filament::icon icon="heroicon-m-user" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
            <span class="truncate">Por: <span class="text-gray-600 dark:text-gray-300">{{ $getRecord()->requester->name }}</span></span>
        </div>

        @if($getRecord()->isPending() && $getRecord()->hasExistingGuest())
            @php $existing = $getRecord()->findExistingGuest(); @endphp
            <div class="flex items-center gap-1.5 p-2 bg-warning-50 dark:bg-warning-500/10 border border-warning-100 dark:border-warning-500/20 rounded-lg">
                <x-filament::icon icon="heroicon-m-exclamation-triangle" class="w-3.5 h-3.5 text-warning-600 shrink-0"/>
                <p class="text-[9px] text-warning-700 dark:text-warning-400 leading-tight">
                    <strong>Duplicidade:</strong> {{ $existing->promoter?->name ?? 'N/A' }} ({{ $existing->sector?->name ?? 'N/A' }})
                </p>
            </div>
        @endif
    </div>

    {{-- Footer: Status e Ações --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Status</span>
            @php
                $status = $getRecord()->status;
                $statusColor = match($status->value) {
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'cancelled', 'expired' => 'gray',
                    default => 'gray',
                };
            @endphp
            <x-filament::badge :color="$statusColor" size="xs" class="font-bold">
                {{ $status->getLabel() }}
            </x-filament::badge>
        </div>

        <div class="flex items-center gap-1.5">
             @if($getRecord()->isPending())
                @if($getRecord()->hasExistingGuest() && !$getRecord()->existingGuestInSameSector())
                    <button
                        type="button"
                        wire:click="mountTableAction('approveWithSectorUpdate', {{ $getRecord()->id }})"
                        class="p-2 text-info-600 hover:text-white dark:text-info-400 bg-info-50 dark:bg-info-500/10 hover:bg-info-600 dark:hover:bg-info-500 rounded-lg transition-all"
                        title="Atualizar Setor"
                    >
                        <x-filament::icon icon="heroicon-m-arrows-right-left" class="h-4 w-4" />
                    </button>
                @endif
                <button
                    type="button"
                    wire:click="mountTableAction('reject', {{ $getRecord()->id }})"
                    class="p-2 text-danger-600 hover:text-white dark:text-danger-400 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-600 dark:hover:bg-danger-500 rounded-lg transition-all"
                    title="Rejeitar"
                >
                    <x-filament::icon icon="heroicon-m-x-mark" class="h-4 w-4" />
                </button>
                <button
                    type="button"
                    wire:click="mountTableAction('approve', {{ $getRecord()->id }})"
                    class="p-2 text-success-600 hover:text-white dark:text-success-400 bg-success-50 dark:bg-success-500/10 hover:bg-success-600 dark:hover:bg-success-500 rounded-lg transition-all"
                    title="Aprovar"
                >
                    <x-filament::icon icon="heroicon-m-check" class="h-4 w-4" />
                </button>
            @elseif($getRecord()->canBeReconsidered())
                <button
                    type="button"
                    wire:click="mountTableAction('reconsider', {{ $getRecord()->id }})"
                    class="p-2 text-warning-600 hover:text-white dark:text-warning-400 bg-warning-50 dark:bg-warning-500/10 hover:bg-warning-600 dark:hover:bg-warning-500 rounded-lg transition-all"
                    title="Reconsiderar"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" />
                </button>
            @elseif($getRecord()->canBeReverted())
                <button
                    type="button"
                    wire:click="mountTableAction('revert', {{ $getRecord()->id }})"
                    class="p-2 text-danger-600 hover:text-white dark:text-danger-400 bg-danger-50 dark:bg-danger-500/10 hover:bg-danger-600 dark:hover:bg-danger-500 rounded-lg transition-all"
                    title="Reverter"
                >
                    <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="h-4 w-4" />
                </button>
            @endif
            
            <button
                type="button"
                wire:click="mountTableAction('view', {{ $getRecord()->id }})"
                class="p-2 text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-all"
                title="Ver Detalhes"
            >
                <x-filament::icon icon="heroicon-m-eye" class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>
