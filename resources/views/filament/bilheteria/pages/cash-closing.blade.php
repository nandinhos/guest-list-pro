<x-filament-panels::page>
    <form wire:submit.prevent>
        {{ $this->form }}
    </form>

    <div class="mt-6">
        {{-- Resumo por Forma de Pagamento --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach($this->salesByPaymentMethod as $method => $data)
                <x-filament::section>
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg" style="background-color: rgba(var(--{{ $data['color'] }}-500), 0.1);">
                            <x-filament::icon
                                :icon="$data['icon']"
                                class="h-6 w-6"
                                style="color: rgb(var(--{{ $data['color'] }}-500));"
                            />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $data['label'] }}
                            </p>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                R$ {{ number_format($data['total'], 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $data['count'] }} {{ $data['count'] === 1 ? 'venda' : 'vendas' }}
                            </p>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>

        {{-- Total Geral --}}
        <x-filament::section class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Geral</p>
                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        R$ {{ number_format($this->totalSales, 2, ',', '.') }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total de Vendas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $this->totalCount }}
                    </p>
                </div>
            </div>
        </x-filament::section>

        {{-- Lista de Vendas --}}
        <x-filament::section>
            <x-slot name="heading">
                Detalhamento das Vendas
            </x-slot>

            @if($this->sales->isEmpty())
                <div class="text-center py-8">
                    <x-filament::icon
                        icon="heroicon-o-ticket"
                        class="h-12 w-12 mx-auto text-gray-400 dark:text-gray-500"
                    />
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Nenhuma venda encontrada no periodo selecionado.
                    </p>
                </div>
            @else
                {{-- Mobile View: Card-based --}}
                <div class="grid grid-cols-1 gap-4 md:hidden">
                    @foreach($this->sales as $sale)
                        <div class="flex flex-col gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
                            <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-700 pb-2">
                                <span class="text-xs font-medium text-gray-400">#{{ $sale->id }}</span>
                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                    <x-filament::icon icon="heroicon-m-clock" class="h-3 w-3" />
                                    {{ $sale->created_at->format('H:i') }}
                                </span>
                            </div>
                            
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $sale->buyer_name }}</span>
                                <span class="text-xs text-gray-500">{{ $sale->buyer_document ?: 'Sem documento' }}</span>
                                <span class="text-xs text-gray-400 mt-1">Vendedor: {{ $sale->seller?->name ?? '-' }}</span>
                            </div>

                            <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-gray-700">
                                <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
                                    R$ {{ number_format($sale->value, 2, ',', '.') }}
                                </span>
                                
                                @php
                                    $paymentMethod = \App\Enums\PaymentMethod::tryFrom($sale->payment_method);
                                @endphp
                                @if($paymentMethod)
                                    <x-filament::badge :color="$paymentMethod->getColor()" size="sm">
                                        {{ $paymentMethod->getLabel() }}
                                    </x-filament::badge>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop View: Traditional Table --}}
                <div class="hidden md:block">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">#</th>
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Hora</th>
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Comprador</th>
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Documento</th>
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Pagamento</th>
                                    <th class="text-left py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Vendedor</th>
                                    <th class="text-right py-3 px-2 font-medium text-gray-500 dark:text-gray-400">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach($this->sales as $sale)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-300">{{ $sale->id }}</td>
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-300">{{ $sale->created_at->format('H:i') }}</td>
                                        <td class="py-3 px-2 text-gray-900 dark:text-white font-medium">{{ $sale->buyer_name }}</td>
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-300">{{ $sale->buyer_document }}</td>
                                        <td class="py-3 px-2">
                                            @php
                                                $paymentMethod = \App\Enums\PaymentMethod::tryFrom($sale->payment_method);
                                            @endphp
                                            @if($paymentMethod)
                                                <x-filament::badge :color="$paymentMethod->getColor()">
                                                    {{ $paymentMethod->getLabel() }}
                                                </x-filament::badge>
                                            @else
                                                {{ $sale->payment_method }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-2 text-gray-600 dark:text-gray-300">{{ $sale->seller?->name ?? '-' }}</td>
                                        <td class="py-3 px-2 text-right font-semibold text-gray-900 dark:text-white">
                                            R$ {{ number_format($sale->value, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-300 dark:border-gray-600">
                                    <td colspan="6" class="py-3 px-2 text-right font-semibold text-gray-700 dark:text-gray-200">
                                        Total:
                                    </td>
                                    <td class="py-3 px-2 text-right font-bold text-lg text-primary-600 dark:text-primary-400">
                                        R$ {{ number_format($this->totalSales, 2, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
