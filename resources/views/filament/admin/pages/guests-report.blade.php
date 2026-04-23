<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Section --}}
        <x-filament::section variant="bordered" class="overflow-hidden">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-funnel" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Filtros</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selecione o evento para gerar o relatório</p>
                    </div>
                </div>
            </x-slot>
            <form wire:submit.prevent>
                {{ $this->form }}
            </form>
        </x-filament::section>

        {{-- Report Content --}}
        @if($this->reportData && $this->reportData->isNotEmpty())
            {{-- Stats Cards - Mobile First Grid --}}
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg hover:shadow-primary-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                            <p class="mt-1 lg:mt-2 text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $this->totals['grand_total'] }}</p>
                        </div>
                        <div class="p-2 rounded-lg bg-primary-500/10">
                            <x-filament::icon icon="heroicon-o-ticket" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg hover:shadow-primary-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">PISTA</p>
                            <p class="mt-1 lg:mt-2 text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $this->totals['pista_total'] }}</p>
                        </div>
                        <div class="p-2 rounded-lg bg-blue-500/10">
                            <x-filament::icon icon="heroicon-o-user-group" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg hover:shadow-primary-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">BACKSTAGE</p>
                            <p class="mt-1 lg:mt-2 text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $this->totals['backstage_total'] }}</p>
                        </div>
                        <div class="p-2 rounded-lg bg-purple-500/10">
                            <x-filament::icon icon="heroicon-o-star" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg hover:shadow-primary-500/10">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">Validação</p>
                            @php $totalValidated = $this->totals['grand_total'] > 0 ? round(($this->totals['grand_validated'] / $this->totals['grand_total']) * 100) : 0; @endphp
                            <p class="mt-1 lg:mt-2 text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white">{{ $totalValidated }}%</p>
                        </div>
                        <div class="p-2 rounded-lg bg-success-500/10">
                            <x-filament::icon icon="heroicon-o-check-badge" class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Table View --}}
            <x-filament::section variant="bordered" class="overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-primary-500/10">
                            <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Detalhamento por Promotor</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Lista completa de cortesias por responsável</p>
                        </div>
                    </div>
                </x-slot>

                <div class="overflow-x-auto -m-5 mt-0">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">RESPONSÁVEL</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">SETOR</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">TOTAL</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">CHECK-INS</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">% POR SETOR</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @php $lastPromoter = null; @endphp
                            @foreach($this->reportData as $row)
                                @if($row['promoter_name'] !== $lastPromoter)
                                    <tr class="transition-colors duration-150 hover:bg-primary-50/50 dark:hover:bg-primary-900/10">
                                        <td rowspan="2" class="px-4 py-3 align-middle">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-bold">
                                                    {{ strtoupper(substr($row['promoter_name'], 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-gray-900 dark:text-white">{{ $row['promoter_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium text-blue-600 dark:text-blue-400">PISTA</td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $row['pista_total'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                                {{ $row['pista_validated'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">
                                            {{ $row['pista_total'] > 0 ? round(($row['pista_validated'] / $row['pista_total']) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                    <tr class="transition-colors duration-150 hover:bg-primary-50/50 dark:hover:bg-primary-900/10">
                                        <td class="px-4 py-3 text-center font-medium text-purple-600 dark:text-purple-400">BACKSTAGE</td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">{{ $row['backstage_total'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                                {{ $row['backstage_validated'] }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300">
                                            {{ $row['backstage_total'] > 0 ? round(($row['backstage_validated'] / $row['backstage_total']) * 100, 1) : 0 }}%
                                        </td>
                                    </tr>
                                    @php $lastPromoter = $row['promoter_name']; @endphp
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t-2 border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/70">
                                <td rowspan="3" class="px-4 py-3 align-middle">
                                    <span class="font-bold text-gray-900 dark:text-white">TOTAL GERAL</span>
                                </td>
                                <td class="px-4 py-3 text-center font-semibold text-blue-600 dark:text-blue-400">PISTA</td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $this->totals['pista_total'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-400">
                                        {{ $this->totals['pista_validated'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">
                                    {{ $this->totals['pista_total'] > 0 ? round(($this->totals['pista_validated'] / $this->totals['pista_total']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            <tr class="border-t border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800/70">
                                <td class="px-4 py-3 text-center font-semibold text-purple-600 dark:text-purple-400">BACKSTAGE</td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">{{ $this->totals['backstage_total'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                                        {{ $this->totals['backstage_validated'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-gray-900 dark:text-white">
                                    {{ $this->totals['backstage_total'] > 0 ? round(($this->totals['backstage_validated'] / $this->totals['backstage_total']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                            <tr class="border-t-2 border-gray-300 dark:border-gray-600 bg-primary-50 dark:bg-primary-900/20">
                                <td class="px-4 py-3 text-center font-bold text-primary-700 dark:text-primary-300">TOTAL</td>
                                <td class="px-4 py-3 text-center font-bold text-xl text-primary-600 dark:text-primary-400">{{ $this->totals['grand_total'] }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                                        {{ $this->totals['grand_validated'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-xl text-primary-600 dark:text-primary-400">
                                    {{ $this->totals['grand_total'] > 0 ? round(($this->totals['grand_validated'] / $this->totals['grand_total']) * 100, 1) : 0 }}%
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </x-filament::section>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-12 lg:py-16">
                <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                    <x-filament::icon icon="heroicon-o-document-chart-bar" class="w-10 w-10 lg:w-12 h-12 text-gray-400 dark:text-gray-600" />
                </div>
                <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white mb-1">Nenhum dado disponível</h3>
                <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm px-4">
                    Selecione um evento no filtro acima para visualizar o relatório de cortesias.
                </p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
