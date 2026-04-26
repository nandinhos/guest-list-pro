<x-filament-panels::page>
    {{ $this->form }}

    @if (!$this->selectedEventId)
        <div class="flex items-center justify-center py-12 text-gray-400 dark:text-gray-600">
            <p class="text-sm">Selecione um evento para visualizar o relatório.</p>
        </div>
    @else
        {{-- Resumo de Totais --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-primary-600">{{ $this->totais['excursoes'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Excursões</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-warning-600">{{ $this->totais['veiculos'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Veículos</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 text-center">
                <p class="text-3xl font-bold text-success-600">{{ $this->totais['monitores'] }}</p>
                <p class="text-sm text-gray-500 mt-1">Monitores</p>
            </div>
        </div>

        {{-- Seção: Excursões --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-map" class="h-5 w-5 text-primary-500" />
                <h2 class="font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Excursões ({{ $this->totais['excursoes'] }})
                </h2>
            </div>
            @if ($this->excursoes->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-400 text-center">Nenhuma excursão encontrada.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">NOME</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">RESPONSÁVEL</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-600 dark:text-gray-400">VEÍCULOS</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-600 dark:text-gray-400">MONITORES</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">CADASTRADO EM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($this->excursoes as $excursao)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $excursao->nome }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $excursao->criadoPor->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-400">
                                            {{ $excursao->veiculos->count() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                            {{ $excursao->veiculos->flatMap->monitores->count() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $excursao->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Seção: Veículos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-truck" class="h-5 w-5 text-warning-500" />
                <h2 class="font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Veículos ({{ $this->totais['veiculos'] }})
                </h2>
            </div>
            @if ($this->veiculos->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-400 text-center">Nenhum veículo encontrado.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">TIPO</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">PLACA</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">EXCURSÃO</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">RESPONSÁVEL</th>
                                <th class="px-4 py-2 text-center font-medium text-gray-600 dark:text-gray-400">MONITORES</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">CADASTRADO EM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($this->veiculos as $veiculo)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $veiculo->tipo->label() }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $veiculo->placa ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $veiculo->excursao->nome }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $veiculo->excursao->criadoPor->name }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-400">
                                            {{ $veiculo->monitores->count() }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $veiculo->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Seção: Monitores --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-user-group" class="h-5 w-5 text-success-500" />
                <h2 class="font-semibold text-sm text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                    Monitores ({{ $this->totais['monitores'] }})
                </h2>
            </div>
            @if ($this->monitores->isEmpty())
                <p class="px-4 py-6 text-sm text-gray-400 text-center">Nenhum monitor encontrado.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">NOME</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">DOCUMENTO</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">VEÍCULO</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">EXCURSÃO</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">RESPONSÁVEL</th>
                                <th class="px-4 py-2 text-left font-medium text-gray-600 dark:text-gray-400">CADASTRADO EM</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($this->monitores as $monitor)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $monitor->nome }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                        <span class="uppercase font-medium">{{ $monitor->document_type->getLabel() }}</span>:
                                        {{ $monitor->document_number }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        @if ($monitor->veiculo)
                                            {{ $monitor->veiculo->tipo->label() }}
                                            @if ($monitor->veiculo->placa) · {{ $monitor->veiculo->placa }} @endif
                                        @else
                                            <span class="text-gray-400">Sem veículo</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $monitor->veiculo?->excursao?->nome ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $monitor->criadoPor->name }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 text-xs">{{ $monitor->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</x-filament-panels::page>
