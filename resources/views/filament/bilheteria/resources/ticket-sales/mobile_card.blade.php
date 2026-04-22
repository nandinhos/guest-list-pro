<div class="flex flex-col gap-3 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
    {{-- Header: ID e Hora + Ações --}}
    <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-2">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">
            #{{ $getRecord()->id }}
        </span>
        <div class="flex items-center gap-2">
            {{-- Botão Ver Detalhes (icon only) --}}
            <button
                type="button"
                wire:click="mountTableAction('viewDetails', {{ $getRecord()->id }})"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md transition-colors"
            >
                <x-filament::icon icon="heroicon-m-eye" class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Body: Comprador e Convidado --}}
    <div class="space-y-1">
        <div class="flex flex-col">
            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight">
                {{ $getRecord()->buyer_name }}
            </span>
            <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ $getRecord()->buyer_document ?: 'Sem documento' }}
            </span>
        </div>

        @if($getRecord()->guest)
            <div class="flex items-center gap-2 mt-1">
                <x-filament::badge color="gray" size="sm">
                    {{ $getRecord()->guest?->sector?->name }}
                </x-filament::badge>
            </div>
        @endif
    </div>

    {{-- Footer: Valor, Pagamento e Estorno --}}
    <div class="flex justify-between items-end pt-2 border-t border-gray-50 dark:border-gray-800">
        <div class="flex flex-col">
            <span class="text-xs text-gray-400 dark:text-gray-500">Valor</span>
            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
                R$ {{ number_format($getRecord()->value, 2, ',', '.') }}
            </span>
        </div>

        <div class="flex flex-col items-end gap-1">
            @php
                $paymentMethod = \App\Enums\PaymentMethod::tryFrom($getRecord()->payment_method);
            @endphp
            @if($paymentMethod)
                <x-filament::badge :color="$paymentMethod->getColor()" size="sm">
                    {{ $paymentMethod->getLabel() }}
                </x-filament::badge>
            @endif

            @if($getRecord()->is_refunded)
                <x-filament::badge color="danger" size="sm">
                    Estornado
                </x-filament::badge>
            @elseif($getRecord()->refundRequest && $getRecord()->refundRequest->isPending())
                <x-filament::badge color="warning" size="sm">
                    Pendente
                </x-filament::badge>
            @else
                {{-- Botão Solicitar Estorno (icon only) --}}
                <button
                    type="button"
                    wire:click="mountTableAction('requestRefund', {{ $getRecord()->id }})"
                    class="p-1.5 text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300 hover:bg-warning-500/10 rounded-md transition-colors"
                    title="Solicitar Estorno"
                >
                    <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="h-4 w-4" />
                </button>
            @endif
        </div>
    </div>
</div>