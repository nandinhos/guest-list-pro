<div class="flex flex-col gap-3 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
    {{-- Header: ID e Hora --}}
    <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-2">
        <span class="text-xs font-medium text-gray-400 dark:text-gray-500">
            #{{ $getRecord()->id }}
        </span>
        <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
            <x-filament::icon icon="heroicon-m-clock" class="h-3 w-3" />
            {{ $getRecord()->created_at->format('H:i') }}
        </span>
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

    {{-- Footer: Valor e Pagamento --}}
    <div class="flex justify-between items-end pt-2 border-t border-gray-50 dark:border-gray-800">
        <div class="flex flex-col">
            <span class="text-xs text-gray-400 dark:text-gray-500">Valor</span>
            <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
                R$ {{ number_format($getRecord()->value, 2, ',', '.') }}
            </span>
        </div>
        
        <div>
            @php
                $paymentMethod = \App\Enums\PaymentMethod::tryFrom($getRecord()->payment_method);
            @endphp
            @if($paymentMethod)
                <x-filament::badge :color="$paymentMethod->getColor()" size="sm">
                    {{ $paymentMethod->getLabel() }}
                </x-filament::badge>
            @endif
        </div>
    </div>
</div>
