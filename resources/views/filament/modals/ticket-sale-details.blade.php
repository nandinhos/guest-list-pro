@if($record)
<div class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Venda</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">#{{ $record->id }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Evento</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->event?->name ?? '-' }}</p>
        </div>
    </div>

    <div>
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Comprador</p>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $record->buyer_name }}</p>
        <p class="text-xs text-gray-500">{{ $record->buyer_document ?: 'Sem documento' }}</p>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Tipo de Ingresso</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->ticketType?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Setor</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->sector?->name ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Valor</p>
            <p class="text-lg font-bold text-primary-600 dark:text-primary-400">
                R$ {{ number_format($record->value, 2, ',', '.') }}
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Forma de Pagamento</p>
            <p class="text-sm text-gray-900 dark:text-white">
                @if($record->payment_method)
                    @php
                        $method = \App\Enums\PaymentMethod::tryFrom($record->payment_method);
                    @endphp
                    @if($method)
                        <x-filament::badge :color="$method->getColor()">
                            {{ $method->getLabel() }}
                        </x-filament::badge>
                    @else
                        {{ $record->payment_method }}
                    @endif
                @else
                    -
                @endif
            </p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Vendedor</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->seller?->name ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Data/Hora</p>
            <p class="text-sm text-gray-900 dark:text-white">{{ $record->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    @if($record->is_refunded)
        <div class="p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg border border-danger-200 dark:border-danger-800">
            <div class="flex items-center gap-2 mb-2">
                <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="h-5 w-5 text-danger-600" />
                <p class="text-sm font-semibold text-danger-700 dark:text-danger-400">Estornado</p>
            </div>
            <p class="text-xs text-danger-600 dark:text-danger-400">
                Motivo: {{ $record->refund_reason ?? 'Não informado' }}
            </p>
        </div>
    @endif

    @if($record->refundRequest && $record->refundRequest->isPending())
        <div class="p-3 bg-warning-50 dark:bg-warning-900/20 rounded-lg border border-warning-200 dark:border-warning-800">
            <div class="flex items-center gap-2 mb-2">
                <x-filament::icon icon="heroicon-m-clock" class="h-5 w-5 text-warning-600" />
                <p class="text-sm font-semibold text-warning-700 dark:text-warning-400">Estorno Pendente</p>
            </div>
            <p class="text-xs text-warning-600 dark:text-warning-400">
                Solicitado em: {{ $record->refundRequest->created_at->format('d/m/Y H:i') }}
            </p>
        </div>
    @endif
</div>
@else
<p class="text-center text-gray-500">Informações da venda não disponíveis.</p>
@endif