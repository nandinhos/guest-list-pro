<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cabeçalho --}}
        <div class="fi-ta-header flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Importar Convidados via Excel/CSV
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Faça upload de um arquivo ou cole uma lista de texto.
                </p>
            </div>
            {{ $this->downloadTemplateAction }}
        </div>

        {{-- Abas --}}
        <div x-data="{ activeTab: @entangle('activeTab') }">
            <nav class="flex space-x-4 border-b border-gray-200 dark:border-gray-700" aria-label="Tabs">
                <button type="button" @click="activeTab = 'file'"
                    :class="activeTab === 'file' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    📁 Upload de Arquivo
                </button>
                <button type="button" @click="activeTab = 'text'"
                    :class="activeTab === 'text' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    📝 Colar Texto
                </button>
            </nav>

            {{-- Aba: Upload de Arquivo --}}
            <div x-show="activeTab === 'file'" x-cloak class="mt-6">
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

            {{-- Aba: Colar Texto --}}
            <div x-show="activeTab === 'text'" x-cloak class="mt-6">
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- Formulário --}}
                    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="fi-section-content p-6">
                            <form wire:submit="importFromText" class="space-y-6">
                                <div class="grid grid-cols-1 gap-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- Seleção de Evento (Texto) --}}
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                                Evento <span class="text-danger-600">*</span>
                                            </label>
                                            <select wire:model.live="textEventId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Selecione...</option>
                                                @foreach($this->events as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Seleção de Setor (Texto) --}}
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                                Setor <span class="text-danger-600">*</span>
                                            </label>
                                            <select wire:model="textSectorId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white" @if(!$this->textEventId) disabled @endif>
                                                <option value="">Selecione...</option>
                                                @foreach($this->textSectors as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- Seleção de Promoter (Texto) --}}
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="text-sm font-medium text-gray-950 dark:text-white">
                                                Promoter <span class="text-danger-600">*</span>
                                            </label>
                                            <select wire:model="textPromoterId" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="">Selecione...</option>
                                                @foreach($this->promoters as $id => $name)
                                                    <option value="{{ $id }}">{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Delimitador --}}
                                        <div class="col-span-2 md:col-span-1">
                                            <label class="text-sm font-medium text-gray-950 dark:text-white">Delimitador</label>
                                            <select wire:model.live="delimiter" class="mt-1.5 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                                <option value="newline">Nova linha (um por linha)</option>
                                                <option value="comma">Vírgula (nome, documento)</option>
                                                <option value="semicolon">Ponto e vírgula (nome; documento)</option>
                                                <option value="tab">Tab</option>
                                                <option value="pipe">Pipe (nome | documento)</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-950 dark:text-white">Cole a lista de convidados</label>
                                    <textarea wire:model.live.debounce.300ms="textContent" rows="12"
                                        class="mt-1.5 block w-full rounded-lg border-gray-300 font-mono text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        placeholder="João Silva, 123.456.789-00
Maria Santos, 987.654.321-00
Carlos Oliveira"></textarea>
                                </div>

                                <div class="flex justify-end">
                                    <x-filament::button type="submit" wire:loading.attr="disabled" wire:target="importFromText">
                                        Importar {{ count($this->parsedPreview) }} Convidados
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="fi-section-header p-4 border-b border-gray-100 dark:border-gray-800">
                            <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                Preview ({{ count($this->parsedPreview) }} registros)
                            </h3>
                        </div>
                        <div class="fi-section-content p-4 max-h-96 overflow-y-auto">
                            @if(count($this->parsedPreview) > 0)
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-gray-500 dark:text-gray-400">
                                        <tr>
                                            <th class="text-left py-2">#</th>
                                            <th class="text-left py-2">Nome</th>
                                            <th class="text-left py-2">Documento</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @foreach($this->parsedPreview as $row)
                                            <tr>
                                                <td class="py-2 text-gray-400">{{ $row['line'] }}</td>
                                                <td class="py-2 text-gray-900 dark:text-white">{{ $row['name'] }}</td>
                                                <td class="py-2 text-gray-500 dark:text-gray-400">{{ $row['document'] ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-8">
                                    Cole o texto para ver o preview aqui
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
