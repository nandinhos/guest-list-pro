@php
    $typeLabel = $record->type->getLabel();
    $typeIcon = $record->type->getIcon();
    $statusLabel = $record->status->getLabel();
    $statusColor = $record->status->getColor();
@endphp

<div class="space-y-4">
    {{-- Header com Status --}}
    <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2">
            <x-filament::icon :icon="$typeIcon" class="w-5 h-5 text-gray-500" />
            <span class="font-medium">{{ $typeLabel }}</span>
        </div>
        <x-filament::badge :color="$statusColor" size="lg">{{ $statusLabel }}</x-filament::badge>
    </div>

    {{-- Dados do Convidado --}}
    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
        <div class="text-lg font-bold mb-2">{{ $record->guest_name }}</div>
        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Doc:</span>
                <span class="font-medium">{{ $record->guest_document ?? '-' }}</span>
                @if($record->guest_document_type)
                    <span class="text-xs text-gray-400">({{ $record->guest_document_type->getLabel() }})</span>
                @endif
            </div>
            <div class="flex items-center gap-2">
                <span class="text-gray-500">Setor:</span>
                <x-filament::badge color="info" size="sm">{{ $record->sector?->name ?? '-' }}</x-filament::badge>
            </div>
            @if($record->guest_email)
            <div class="col-span-2 flex items-center gap-2">
                <span class="text-gray-500">Email:</span>
                <span class="font-medium">{{ $record->guest_email }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Info da Solicitacao --}}
    <div class="grid grid-cols-2 gap-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Solicitante:</span>
            <span class="font-medium">{{ $record->requester->name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Cargo:</span>
            <span class="font-medium">{{ $record->requester->role->getLabel() }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Evento:</span>
            <span class="font-medium">{{ $record->event->name }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Data:</span>
            <span class="font-medium">{{ $record->created_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>

    {{-- Alerta de Duplicidade --}}
    @if($record->isPending() && $record->hasExistingGuest())
        @php $existing = $record->findExistingGuest(); @endphp
        <div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
            <div class="flex items-start gap-2">
                <x-heroicon-m-exclamation-triangle class="w-5 h-5 text-warning-600 shrink-0"/>
                <div>
                    <div class="text-sm font-semibold text-warning-800 dark:text-warning-200">Convidado já cadastrado!</div>
                    <div class="text-xs text-warning-700 dark:text-warning-300 mt-1">
                        Este documento já está na lista de <strong>{{ $existing->promoter?->name ?? 'N/A' }}</strong>,
                        setor <strong>{{ $existing->sector?->name ?? 'N/A' }}</strong>.
                        @if($existing->sector_id === $record->sector_id)
                            <span class="block mt-1 text-danger-600 dark:text-danger-400 font-medium">⛔ Mesmo setor - rejeição recomendada.</span>
                        @else
                            <span class="block mt-1 text-info-600 dark:text-info-400 font-medium">ℹ️ Setor diferente - pode aprovar atualizando o setor.</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Observacoes do Solicitante --}}
    @if($record->requester_notes)
    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
        <div class="text-xs font-semibold text-yellow-800 dark:text-yellow-200 uppercase mb-1">Observacoes</div>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $record->requester_notes }}</p>
    </div>
    @endif

    {{-- Revisao --}}
    @if($record->reviewed_at)
    <div class="p-3 {{ $record->isApproved() ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }} rounded-lg border">
        <div class="flex items-center justify-between mb-2">
            <div class="text-xs font-semibold {{ $record->isApproved() ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200' }} uppercase">
                {{ $record->isApproved() ? 'Aprovado' : 'Rejeitado' }}
            </div>
            <span class="text-xs text-gray-500">{{ $record->reviewed_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="text-sm">
            <span class="text-gray-500">Por:</span>
            <span class="font-medium">{{ $record->reviewer?->name ?? '-' }}</span>
        </div>
        @if($record->reviewer_notes)
        <div class="mt-2 pt-2 border-t {{ $record->isApproved() ? 'border-green-200 dark:border-green-700' : 'border-red-200 dark:border-red-700' }}">
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $record->reviewer_notes }}</p>
        </div>
        @endif
        @if($record->guest_id)
        <div class="mt-2 text-xs text-gray-500">
            Guest criado: <span class="font-medium">#{{ $record->guest_id }}</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Botao Reconsiderar (apenas para admin visualizando rejeitados/cancelados) --}}
    @if($record->canBeReconsidered() && auth()->user()->role === \App\Enums\UserRole::ADMIN)
    <div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-warning-800 dark:text-warning-200">Reconsiderar esta solicitacao?</div>
                <div class="text-xs text-warning-600 dark:text-warning-400">A solicitacao voltara para o status Pendente.</div>
            </div>
            <button
                type="button"
                wire:click="mountTableAction('reconsider', '{{ $record->id }}')"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-warning-700 bg-warning-500/10 rounded-lg hover:bg-warning-500/20 transition-colors"
            >
                <x-heroicon-m-arrow-path class="w-4 h-4 mr-2"/>
                Reconsiderar
            </button>
        </div>
    </div>
    @endif

    {{-- Botão Reverter (apenas para admin visualizando aprovados) --}}
    @if($record->canBeReverted() && auth()->user()->role === \App\Enums\UserRole::ADMIN)
    <div class="p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
        <div class="flex items-center justify-between">
            <div>
                <div class="text-sm font-medium text-danger-800 dark:text-danger-200">Reverter esta aprovação?</div>
                <div class="text-xs text-danger-600 dark:text-danger-400">O convidado será excluído e a solicitação voltará para Pendente.</div>
            </div>
            <button
                type="button"
                wire:click="mountTableAction('revert', '{{ $record->id }}')"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-danger-700 bg-danger-500/10 rounded-lg hover:bg-danger-500/20 transition-colors"
            >
                <x-heroicon-m-arrow-uturn-left class="w-4 h-4 mr-2"/>
                Reverter
            </button>
        </div>
    </div>
    @endif

    {{-- Footer com metadados --}}
    <div class="text-xs text-gray-400 flex justify-between pt-2 border-t border-gray-200 dark:border-gray-700">
        <span>ID: #{{ $record->id }}</span>
        <span>IP: {{ $record->ip_address ?? '-' }}</span>
    </div>
</div>
