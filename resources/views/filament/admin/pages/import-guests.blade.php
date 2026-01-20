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
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                        {{-- Seleção de Evento --}}
                        <div>
                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                Evento <span class="text-danger-600">*</span>
                            </label>
                            <select wire:model.live="eventId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Selecione...</option>
                                @foreach($this->events as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Seleção de Setor --}}
                        <div>
                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                Setor <span class="text-danger-600">*</span>
                            </label>
                            <select wire:model="sectorId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" @if(!$this->eventId) disabled @endif>
                                <option value="">Selecione...</option>
                                @foreach($this->sectors as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Seleção de Promoter --}}
                        <div>
                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                Atribuir ao Promoter <span class="text-danger-600">*</span>
                            </label>
                            <select wire:model="promoterId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="">Selecione...</option>
                                @foreach($this->promoters as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Upload de Arquivo --}}
                    <div>
                        <label class="text-sm font-medium text-gray-950 dark:text-white">
                            Arquivo Excel/CSV <span class="text-danger-600">*</span>
                        </label>
                        <div class="mt-1.5">
                            <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 dark:file:bg-primary-900/20 dark:file:text-primary-400" />
                        </div>
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Colunas esperadas: Nome, Documento, Email (opcional)
                        </p>
                    </div>

                    <div wire:loading wire:target="file" class="text-sm text-gray-500">Carregando...</div>

                    <div class="flex justify-end">
                        <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="import">
                            Importar Convidados
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>
