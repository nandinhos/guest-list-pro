@if($record->ticketSale)
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Venda</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">#{{ $record->ticketSale->id }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Evento</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->ticketSale->event?->name ?? '-' }}</p>
        </div>
    </div>

    <div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Comprador</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->ticketSale->buyer_name }}</p>
        <p class="text-xs text-gray-500">{{ $record->ticketSale->buyer_document ?: 'Sem documento' }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Valor</p>
            <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                R$ {{ number_format($record->ticketSale->value, 2, ',', '.') }}
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Forma de Pagamento</p>
            <p class="text-sm text-gray-900 dark:text-white">
                @if($record->ticketSale->payment_method)
                    @php
                        $method = \App\Enums\PaymentMethod::tryFrom($record->ticketSale->payment_method);
                    @endphp
                    @if($method)
                        <x-filament::badge :color="$method->getColor()">
                            {{ $method->getLabel() }}
                        </x-filament::badge>
                    @else
                        {{ $record->ticketSale->payment_method }}
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
    </div>

    <div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Motivo do Estorno</p>
        <p class="text-sm text-gray-900 dark:text-white p-2 bg-gray-50 dark:bg-gray-800 rounded">
            {{ $record->reason }}
        </p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Solicitante</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->requester?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data da Solicitação</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @if($record->status !== 'pending')
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Revisor</p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->reviewer?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data da Revisão</p>
                    <p class="text-sm text-gray-900 dark:text-white">{{ $record->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</p>
                </div>
            </div>
            @if($record->review_notes)
                <div class="mt-3">
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Observações</p>
                    <p class="text-sm text-gray-900 dark:text-white p-2 bg-gray-50 dark:bg-gray-800 rounded">
                        {{ $record->review_notes }}
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>
@else
<p class="text-center text-gray-500">Informações da venda não disponíveis.</p>
@endif