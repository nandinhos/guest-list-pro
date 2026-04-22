@php
    $record = $getRecord();
    $paymentMethod = \App\Enums\PaymentMethod::tryFrom($record->payment_method);
    $buyerName = $record->buyer_name ?? 'N/A';
    $buyerDocument = $record->buyer_document ?? 'Sem Documento';
    $ticketTypeName = $record->ticketType?->name ?? 'N/A';
    $sectorName = $record->sector?->name ?? 'N/A';
    $value = $record->value ?? 0;
    $createdAt = $record->created_at;
    $isRefunded = $record->is_refunded;
    $isPendingRefund = $record->refundRequest && $record->refundRequest->isPending();
@endphp

<div class="flex flex-col justify-between min-h-[190px] w-full p-4 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-xl hover:translate-y-[-2px] relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-12 -top-12 w-32 h-32 bg-primary-500/5 rounded-full blur-3xl group-hover:bg-primary-500/15 transition-all duration-700"></div>
    <div class="absolute -left-12 -bottom-12 w-32 h-32 bg-secondary-500/5 rounded-full blur-3xl group-hover:bg-secondary-500/10 transition-all duration-700"></div>

    {{-- Header: ID e Ações --}}
    <div class="flex justify-between items-start gap-3">
        <div class="flex flex-col flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="text-[9px] font-bold uppercase tracking-widest text-primary-600 dark:text-primary-400 opacity-80">
                    VENDA #{{ $record->id }}
                </span>
                @if($isRefunded)
                    <span class="px-1.5 py-0.5 bg-danger-50 dark:bg-danger-500/10 text-danger-600 dark:text-danger-400 text-[8px] font-black uppercase rounded-full border border-danger-100 dark:border-danger-500/20">
                        ESTORNADA
                    </span>
                @elseif($isPendingRefund)
                    <span class="px-1.5 py-0.5 bg-warning-50 dark:bg-warning-500/10 text-warning-600 dark:text-warning-400 text-[8px] font-black uppercase rounded-full border border-warning-100 dark:border-warning-500/20 animate-pulse">
                        PENDENTE
                    </span>
                @endif
            </div>
            <h3 class="text-base font-black text-gray-900 dark:text-gray-100 line-clamp-1 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $buyerName }}
            </h3>
            <div class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500 mt-0.5">
                <x-filament::icon icon="heroicon-m-calendar" class="w-3 h-3 shrink-0"/>
                <span class="text-[10px] font-medium tracking-tight truncate">{{ format_datetime($createdAt) }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                wire:click="mountTableAction('viewDetails', {{ $record->id }})"
                class="p-2.5 text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-2xl border border-transparent hover:border-primary-200 dark:hover:border-primary-500/30 transition-all duration-300 shadow-sm"
                title="Ver Detalhes"
            >
                <x-filament::icon icon="heroicon-m-eye" class="h-5 w-5" />
            </button>
        </div>
    </div>

    {{-- Body: Detalhes do Ingresso --}}
    <div class="py-4 grid grid-cols-2 gap-3">
        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Documento</span>
            <div class="flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-m-identification" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">{{ $buyerDocument }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tipo / Setor</span>
            <div class="flex items-center gap-1">
                <span class="text-[10px] font-black text-info-600 dark:text-info-400 bg-info-50 dark:bg-info-500/10 px-1.5 py-0.5 rounded-md border border-info-100 dark:border-info-500/20 truncate">
                    {{ $ticketTypeName }}
                </span>
            </div>
        </div>
    </div>

    {{-- Footer: Valor e Pagamento --}}
    <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Total Pago</span>
            <span class="text-lg font-black text-primary-600 dark:text-primary-500 leading-none">
                R$ {{ number_format($value, 2, ',', '.') }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            @if($paymentMethod)
                <div class="flex flex-col items-end mr-1">
                    <span class="text-[8px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-tighter mb-0.5">Pagamento</span>
                    <x-filament::badge :color="$paymentMethod->getColor()" size="xs" class="font-black uppercase tracking-tighter">
                        {{ $paymentMethod->getLabel() }}
                    </x-filament::badge>
                </div>
            @endif

            @if(!$isRefunded && !$isPendingRefund)
                <button
                    type="button"
                    wire:click="mountTableAction('requestRefund', {{ $record->id }})"
                    class="p-2 text-warning-600 hover:text-white dark:text-warning-400 bg-warning-50 dark:bg-warning-500/10 hover:bg-warning-600 dark:hover:bg-warning-500 rounded-xl transition-all shadow-sm group/undo border border-warning-100 dark:border-warning-500/20"
                    title="Solicitar Estorno"
                >
                    <x-filament::icon icon="heroicon-m-arrow-uturn-left" class="h-4 w-4 group-hover/undo:rotate-[-45deg] transition-transform duration-300" />
                </button>
            @endif
        </div>
    </div>
</div>