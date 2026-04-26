<x-filament-panels::page>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Upload Section --}}
        <x-filament::section variant="bordered" class="overflow-hidden">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-primary-500/10">
                        <x-filament::icon icon="heroicon-o-document-arrow-up" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Importar Lista de Excursões</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Upload arquivo .md com dados de excursões</p>
                    </div>
                </div>
            </x-slot>

            <form wire:submit.prevent class="space-y-4">
                {{ $this->form }}
            </form>

            @if($showPreview || $showResult)
                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700">
                    <x-filament::button type="button" color="gray" wire:click="resetForm">
                        Nova Importação
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>

        {{-- Preview Section --}}
        @if($showPreview && !empty($preview))
            <x-filament::section variant="bordered" class="overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-info-500/10">
                            <x-filament::icon icon="heroicon-o-eye" class="w-5 h-5 text-info-600 dark:text-info-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Preview da Importação</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $previewSummary['total_entries'] ?? 0 }} monitores encontrados
                            </p>
                        </div>
                    </div>
                </x-slot>

                {{-- Event Selector --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Selecione o evento para importação:
                    </label>
                    <select
                        wire:model.live="selectedEventId"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">-- Selecione um evento --</option>
                        @foreach(\App\Models\Event::all(['id','name']) as $event)
                            <option value="{{ $event->id }}">{{ $event->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Excursões</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ $previewSummary['excursoes_total'] ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                        <p class="text-xs text-green-600 dark:text-green-400 font-medium">Monitores</p>
                        <p class="text-2xl font-bold text-green-700 dark:text-green-300">
                            {{ $previewSummary['monitors_total'] ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                        <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">Veículos s/ Excursão</p>
                        <p class="text-2xl font-bold text-orange-700 dark:text-orange-300">
                            {{ $previewSummary['vehicles_no_type'] ?? 0 }}
                        </p>
                    </div>
                </div>

                {{-- Vehicles by Type --}}
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Veículos por tipo:</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($previewSummary['vehicles_by_type'] ?? [] as $type => $count)
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ \App\Enums\TipoVeiculo::from($type)->label() }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Excursions List --}}
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Excursões encontradas:</h4>
                    <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                        @foreach($previewSummary['excursoes'] ?? [] as $excursao)
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-purple-100 dark:bg-purple-900/30 text-xs font-medium text-purple-700 dark:text-purple-300">
                                {{ $excursao }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Preview Table --}}
                <div class="overflow-x-auto -m-5 mt-0 max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Monitor</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Excursão</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Veículo</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Documento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach(array_slice($preview, 0, 50) as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item['monitor'] }}</td>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $item['excursao'] ?? '—' }}</td>
                                    <td class="px-3 py-2">
                                        @if($item['vehicle_type'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700">
                                                {{ $item['vehicle_type']->label() }} {{ $item['vehicle_code'] ? ' - ' . $item['vehicle_code'] : '' }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $item['document_number'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if(count($preview) > 50)
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-2">
                        Mostrando 50 de {{ count($preview) }} entradas
                    </p>
                @endif

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                    <x-filament::button type="button" color="success" wire:click="import" :disabled="!$selectedEventId">
                        Confirmar Importação
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- Result Section --}}
        @if($showResult)
            <x-filament::section variant="bordered" class="overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-success-500/10">
                            <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 text-success-600 dark:text-success-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Resultado da Importação</h3>
                        </div>
                    </div>
                </x-slot>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $importResult['monitores_created'] ?? 0 }}</p>
                        <p class="text-sm text-success-700 dark:text-success-300">Monitores importados</p>
                    </div>
                    <div class="bg-info-50 dark:bg-info-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-info-600 dark:text-info-400">{{ $importResult['veiculos_created'] ?? 0 }}</p>
                        <p class="text-sm text-info-700 dark:text-info-300">Veículos criados</p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $importResult['excursoes_created'] ?? 0 }}</p>
                        <p class="text-sm text-purple-700 dark:text-purple-300">Excursões criadas</p>
                    </div>
                    <div class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $importResult['skipped_duplicates'] ?? 0 }}</p>
                        <p class="text-sm text-warning-700 dark:text-warning-300">Duplicados ignorados</p>
                    </div>
                </div>

                @if(($importResult['orphan_veiculos'] ?? 0) > 0)
                    <div class="mb-4 p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                        <p class="text-sm text-orange-700 dark:text-orange-300">
                            <strong>{{ $importResult['orphan_veiculos'] }}</strong> veículo(s) criado(s) sem excursão ( vans/ônibus avulsos).
                        </p>
                    </div>
                @endif

                @if(!empty($importResult['errors']))
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-danger-700 dark:text-danger-300 mb-2">Erros:</h4>
                        <div class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-3 max-h-48 overflow-y-auto">
                            @foreach($importResult['errors'] as $error)
                                <div class="text-sm text-danger-700 dark:text-danger-300 py-1">
                                    {{ $error }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                    <x-filament::button type="button" color="gray" wire:click="resetForm">
                        Nova Importação
                    </x-filament::button>
                </div>
            </x-filament::section>
        @endif

        {{-- Help Section --}}
        <x-filament::section variant="bordered" class="overflow-hidden">
            <x-slot name="header">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-gray-500/10">
                        <x-filament::icon icon="heroicon-o-question-mark-circle" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Formato do Arquivo</h3>
                    </div>
                </div>
            </x-slot>

            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2">Estrutura esperada</p>
                    <div class="rounded-xl bg-gray-900 dark:bg-gray-950 p-4 text-xs font-mono leading-relaxed overflow-x-auto">
                        <div class="text-gray-500"># Bloco de Monitor #</div>
                        <div class="text-yellow-400">ônibus / van / microônibus</div>
                        <div class="text-gray-300">codigo-veiculo</div>
                        <div class="text-purple-400">Monitor: Nome do Monitor</div>
                        <div class="text-gray-300">Nome da Excursão</div>
                        <div class="text-gray-500">12345678901</div>
                        <div class="text-gray-600 mt-2">[bloco vazio separa monitores]</div>
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>