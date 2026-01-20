<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cabeçalho --}}
        <div class="fi-ta-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Importar Convidados via Excel/CSV
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Faça upload de um arquivo Excel ou CSV com a lista de convidados.
                </p>
            </div>

            {{ $this->downloadTemplateAction }}
        </div>

        {{-- Formulário --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-content p-6">
                <form wire:submit="import" class="space-y-6">
                    {{-- Seleção de Setor --}}
                    <div>
                        <label for="sectorId" class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Setor de Destino <span class="text-danger-600">*</span>
                            </span>
                        </label>
                        <select
                            wire:model="sectorId"
                            id="sectorId"
                            class="mt-1.5 block w-full rounded-lg border-gray-300 shadow-sm
                                   focus:border-primary-500 focus:ring-primary-500
                                   dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Selecione um setor...</option>
                            @foreach($this->sectors as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Upload de Arquivo --}}
                    <div>
                        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                                Arquivo Excel/CSV <span class="text-danger-600">*</span>
                            </span>
                        </label>
                        <div class="mt-1.5">
                    <input
                                type="file"
                                wire:model="file"
                                accept=".xlsx,.xls,.csv"
                                class="block w-full text-sm text-gray-500
                                       file:mr-4 file:py-2 file:px-4
                                       file:rounded-lg file:border-0
                                       file:text-sm file:font-semibold
                                       file:bg-primary-50 file:text-primary-700
                                       hover:file:bg-primary-100
                                       dark:file:bg-primary-900/20 dark:file:text-primary-400"
                            />
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Formatos aceitos: .xlsx, .xls, .csv | Colunas esperadas: Nome, Documento, Email (opcional)
                        </p>
                    </div>

                    {{-- Indicador de loading --}}
                    <div wire:loading wire:target="filePath" class="text-sm text-gray-500">
                        <svg class="animate-spin inline-block h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Carregando arquivo...
                    </div>

                    {{-- Botão de Importar --}}
                    <div class="flex justify-end">
                        <x-filament::button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="import"
                        >
                            <svg wire:loading wire:target="import" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Importar Convidados
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Instruções --}}
        <div class="rounded-xl bg-blue-50 p-4 dark:bg-blue-900/20">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">
                        Instruções de Importação
                    </h3>
                    <div class="mt-2 text-sm text-blue-700 dark:text-blue-400">
                        <ul class="list-disc pl-5 space-y-1">
                            <li>A primeira linha deve conter os cabeçalhos: <strong>Nome</strong>, <strong>Documento</strong>, <strong>Email</strong></li>
                            <li>Documentos duplicados serão automaticamente ignorados</li>
                            <li>O campo Email é opcional</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
