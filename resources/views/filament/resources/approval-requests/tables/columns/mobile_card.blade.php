<div class="flex flex-col space-y-3 py-1.5 w-full overflow-hidden">
    <!-- Row 1: Nome do Convidado e Tipo -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->guest_name }}
        </span>
        @php
            $type = $getRecord()->type;
            $typeColor = $type->value === 'emergency_checkin' ? 'warning' : 'primary';
        @endphp
        <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-500/10 text-{{ $typeColor }}-700 ml-auto">
            {{ $type->getLabel() }}
        </span>
    </div>

    <!-- Row 2: Documento, Setor e Solicitante -->
    <div class="flex flex-col space-y-0.5 pb-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="font-medium truncate">{{ $getRecord()->guest_document ?? 'S/D' }}</span>
            @if($getRecord()->sector)
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-500/10 text-blue-700">
                    {{ $getRecord()->sector->name }}
                </span>
            @endif
        </div>
        <div class="flex items-center space-x-1 text-indigo-600 dark:text-indigo-400">
            <x-heroicon-m-user class="w-4 h-4 shrink-0"/>
            <span class="text-[10px] font-medium truncate">
                {{ $getRecord()->requester->name }}
                <span class="text-gray-400">({{ $getRecord()->requester->role->getLabel() }})</span>
            </span>
        </div>
        <div class="flex items-center space-x-1 text-gray-400">
            <x-heroicon-m-clock class="w-4 h-4 shrink-0"/>
            <span class="text-[10px] font-medium">{{ $getRecord()->created_at->format('d/m/Y H:i') }}</span>
        </div>

        {{-- Alerta de Duplicidade --}}
        @if($getRecord()->isPending() && $getRecord()->hasExistingGuest())
            @php $existing = $getRecord()->findExistingGuest(); @endphp
            <div class="flex items-center space-x-1 text-warning-700 dark:text-warning-400 bg-warning-50 dark:bg-warning-900/20 px-2 py-1 rounded-md mt-1">
                <x-heroicon-m-exclamation-triangle class="w-4 h-4 shrink-0"/>
                <span class="text-[10px] font-medium truncate">
                    Já existe: {{ $existing->promoter?->name ?? 'N/A' }} ({{ $existing->sector?->name ?? 'N/A' }})
                </span>
            </div>
        @endif
    </div>

    <!-- Row 3: Status Badge e Acoes -->
    <div class="flex items-end justify-between gap-3 w-full">
        <!-- Left: Status e Revisor -->
        <div class="flex flex-col space-y-1.5 min-w-0 flex-1">
            <!-- Status Badge -->
            @php
                $status = $getRecord()->status;
                $statusConfig = match($status->value) {
                    'pending' => ['icon' => 'heroicon-m-clock', 'class' => 'bg-warning-500/10 text-warning-700', 'label' => 'Pendente'],
                    'approved' => ['icon' => 'heroicon-m-check-circle', 'class' => 'bg-success-500/10 text-success-700', 'label' => 'Aprovado'],
                    'rejected' => ['icon' => 'heroicon-m-x-circle', 'class' => 'bg-danger-500/10 text-danger-700', 'label' => 'Rejeitado'],
                    'cancelled' => ['icon' => 'heroicon-m-minus-circle', 'class' => 'bg-gray-500/10 text-gray-700', 'label' => 'Cancelado'],
                    'expired' => ['icon' => 'heroicon-m-exclamation-circle', 'class' => 'bg-gray-500/10 text-gray-700', 'label' => 'Expirado'],
                    default => ['icon' => 'heroicon-m-question-mark-circle', 'class' => 'bg-gray-500/10 text-gray-700', 'label' => $status->getLabel()],
                };
            @endphp
            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold {{ $statusConfig['class'] }} w-fit whitespace-nowrap">
                <x-dynamic-component :component="$statusConfig['icon']" class="w-3.5 h-3.5 mr-1"/>
                {{ $statusConfig['label'] }}
            </span>

            <!-- Revisor (se aprovado/rejeitado) -->
            @if($getRecord()->reviewer)
                <div class="flex items-center text-[10px] text-gray-400 truncate">
                    <x-heroicon-m-shield-check class="w-3 h-3 mr-1 shrink-0"/>
                    <span class="truncate">{{ $getRecord()->reviewer->name }}</span>
                </div>
            @endif
        </div>

        <!-- Right: Acoes -->
        <div class="shrink-0 pl-1 flex space-x-2">
            <!-- Botao Detalhes -->
            <button
                wire:click="mountTableAction('view', '{{ $getRecord()->id }}')"
                class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors whitespace-nowrap dark:bg-gray-700 dark:text-gray-300"
            >
                <x-heroicon-m-eye class="w-4 h-4"/>
            </button>

            <!-- Botao Aprovar (apenas pendentes) -->
            @if($getRecord()->isPending())
                <button
                    wire:click="mountTableAction('approve', '{{ $getRecord()->id }}')"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-white bg-success-600 rounded-lg hover:bg-success-700 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-check class="w-4 h-4"/>
                </button>
                <button
                    wire:click="mountTableAction('reject', '{{ $getRecord()->id }}')"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-danger-700 bg-danger-500/10 rounded-lg hover:bg-danger-500/20 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-x-mark class="w-4 h-4"/>
                </button>

                <!-- Botão Atualizar Setor (duplicado em outro setor) -->
                @if($getRecord()->hasExistingGuest() && !$getRecord()->existingGuestInSameSector())
                    <button
                        wire:click="mountTableAction('approveWithSectorUpdate', '{{ $getRecord()->id }}')"
                        class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-info-700 bg-info-500/10 rounded-lg hover:bg-info-500/20 transition-colors whitespace-nowrap"
                        title="Atualizar Setor"
                    >
                        <x-heroicon-m-arrows-right-left class="w-4 h-4"/>
                    </button>
                @endif
            @endif

            <!-- Botao Reconsiderar (rejeitados/cancelados) -->
            @if($getRecord()->canBeReconsidered())
                <button
                    wire:click="mountTableAction('reconsider', '{{ $getRecord()->id }}')"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-warning-700 bg-warning-500/10 rounded-lg hover:bg-warning-500/20 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-arrow-path class="w-4 h-4"/>
                </button>
            @endif

            <!-- Botão Reverter (aprovados que podem ser revertidos) -->
            @if($getRecord()->canBeReverted())
                <button
                    wire:click="mountTableAction('revert', '{{ $getRecord()->id }}')"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-danger-700 bg-danger-500/10 rounded-lg hover:bg-danger-500/20 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-arrow-uturn-left class="w-4 h-4"/>
                </button>
            @endif
        </div>
    </div>
</div>
