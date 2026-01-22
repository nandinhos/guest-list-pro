<div class="flex flex-col space-y-3 py-3 w-full">
    <!-- Row 1: Nome do Convidado e Setor -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->guest_name }}
        </span>
        <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/10 text-blue-700 ml-auto">
            {{ $getRecord()->sector?->name ?? 'Geral' }}
        </span>
    </div>

    <!-- Row 2: Documento e Data -->
    <div class="flex flex-col space-y-0.5 pb-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="font-medium truncate">{{ $getRecord()->guest_document ?? 'S/D' }}</span>
        </div>
        <div class="flex items-center space-x-1 text-gray-400">
            <x-heroicon-m-clock class="w-4 h-4 shrink-0"/>
            <span class="text-[10px] font-medium">{{ $getRecord()->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Row 3: Status Badge e Ações -->
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
                    <x-heroicon-m-user class="w-3 h-3 mr-1 shrink-0"/>
                    <span class="truncate">{{ $getRecord()->reviewer->name }}</span>
                </div>
            @endif
        </div>

        <!-- Right: Ações -->
        <div class="shrink-0 pl-1 flex space-x-2">
            <!-- Botão Detalhes -->
            <button
                wire:click="mountTableAction('view', '{{ $getRecord()->id }}')"
                class="inline-flex items-center justify-center px-3 py-2 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors whitespace-nowrap dark:bg-gray-700 dark:text-gray-300"
            >
                <x-heroicon-m-eye class="w-4 h-4"/>
            </button>

            <!-- Botão Cancelar (apenas pendentes) -->
            @if($getRecord()->isPending())
                <button
                    wire:click="mountTableAction('cancel', '{{ $getRecord()->id }}')"
                    class="inline-flex items-center justify-center px-3 py-2 text-xs font-bold text-danger-700 bg-danger-500/10 rounded-lg hover:bg-danger-500/20 transition-colors whitespace-nowrap"
                >
                    <x-heroicon-m-x-mark class="w-4 h-4"/>
                </button>
            @endif
        </div>
    </div>
</div>
