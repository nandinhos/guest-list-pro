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
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Importar Lista de Convidados</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Upload arquivo .md ou .txt</p>
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
                                {{ $previewSummary['total'] ?? 0 }} convidados encontrados
                            </p>
                        </div>
                    </div>
                </x-slot>

                {{-- Summary Cards --}}
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3">
                        <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">PISTA</p>
                        <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                            {{ $previewSummary['by_sector']['PISTA'] ?? 0 }}
                        </p>
                    </div>
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3">
                        <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">BACKSTAGE</p>
                        <p class="text-2xl font-bold text-purple-700 dark:text-purple-300">
                            {{ $previewSummary['by_sector']['BACKSTAGE'] ?? 0 }}
                        </p>
                    </div>
                </div>

                {{-- Promoters Summary --}}
                <div class="mb-4">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Por Responsável:</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($previewSummary['by_promoter'] ?? [] as $promoter => $count)
                            <span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-xs font-medium text-gray-700 dark:text-gray-300">
                                {{ $promoter }}: {{ $count }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Preview Table --}}
                <div class="overflow-x-auto -m-5 mt-0 max-h-96 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Responsável</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Setor</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Nome</th>
                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-gray-500">Documento</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($preview as $item)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item['promoter'] }}</td>
                                    <td class="px-3 py-2">
                                        @if($item['sector'] === 'PISTA')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">PISTA</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">BACKSTAGE</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-gray-900 dark:text-gray-100">{{ $item['name'] }}</td>
                                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $item['document'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-4">
                    <x-filament::button type="button" color="success" wire:click="import">
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

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-success-50 dark:bg-success-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-success-600 dark:text-success-400">{{ $importResult['imported'] ?? 0 }}</p>
                        <p class="text-sm text-success-700 dark:text-success-300">Importados</p>
                    </div>
                    <div class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-warning-600 dark:text-warning-400">{{ $importResult['duplicates'] ?? 0 }}</p>
                        <p class="text-sm text-warning-700 dark:text-warning-300">Duplicados</p>
                    </div>
                    <div class="bg-danger-50 dark:bg-danger-900/20 rounded-lg p-4 text-center">
                        <p class="text-3xl font-bold text-danger-600 dark:text-danger-400">{{ count($importResult['errors'] ?? []) }}</p>
                        <p class="text-sm text-danger-700 dark:text-danger-300">Erros</p>
                    </div>
                </div>

                @if(($importResult['promoters_created'] ?? 0) > 0)
                    <div class="mb-4 p-3 bg-info-50 dark:bg-info-900/20 rounded-lg">
                        <p class="text-sm text-info-700 dark:text-info-300">
                            <strong>{{ $importResult['promoters_created'] }}</strong> novo(s) promoter(s) criado(s).
                        </p>
                    </div>
                @endif

                @if(!empty($importResult['warnings']))
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-warning-700 dark:text-warning-300 mb-2">Duplicados (não importados):</h4>
                        <div class="bg-warning-50 dark:bg-warning-900/20 rounded-lg p-3 max-h-48 overflow-y-auto">
                            @foreach($importResult['warnings'] as $warning)
                                <div class="text-sm text-warning-700 dark:text-warning-300 py-1">
                                    <span class="font-medium">{{ $warning['name'] }}</span> -
                                    <span class="font-mono">{{ $warning['document'] }}</span> -
                                    {{ $warning['reason'] }}
                                </div>
                            @endforeach
                        </div>
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

            <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Estrutura do arquivo:</p>
                    <pre class="bg-gray-100 dark:bg-gray-800 rounded-lg p-3 overflow-x-auto text-xs">### Convidados Erick ###
# BACKSTAGE #
Wellington Miranda, 27589191841
Priscilla Stocco, 22010456823

# PISTA #
LUCAS SÓGLIA LAROTONDA, 41813773858</pre>
                </div>

                <div>
                    <p class="font-medium text-gray-700 dark:text-gray-300 mb-1">Regras:</p>
                    <ul class="list-disc list-inside space-y-1">
                        <li><code>### Convidados [NOME] ###</code> - Define o responsável/promotor</li>
                        <li><code># BACKSTAGE #</code> ou <code># PISTA #</code> - Define o setor</li>
                        <li><code>Nome Completo, Documento</code> - Uma linha por convidado</li>
                        <li>CPF: apenas números (11 dígitos)</li>
                        <li>Passaporte: texto livre</li>
                    </ul>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
