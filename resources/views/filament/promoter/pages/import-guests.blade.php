<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Importar Convidados
            </x-slot>
            <x-slot name="description">
                Adicione convidados à sua lista através de arquivos Excel ou texto.
            </x-slot>
            <x-slot name="headerEnd">
                {{ $this->downloadTemplateAction }}
            </x-slot>

            <x-filament::tabs label="Método de Importação">
                <x-filament::tabs.item
                    :active="$activeTab === 'file'"
                    wire:click="$set('activeTab', 'file')"
                    icon="heroicon-m-document-text"
                >
                    Arquivo Excel/CSV
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'text'"
                    wire:click="$set('activeTab', 'text')"
                    icon="heroicon-m-clipboard-document-list"
                >
                    Colar Texto
                </x-filament::tabs.item>
            </x-filament::tabs>

            <div class="mt-6">
                {{-- Aba: Arquivo --}}
                <div x-show="$wire.activeTab === 'file'" class="space-y-6">
                    <form wire:submit="import" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-filament::input.wrapper label="Setor de Destino" required>
                                <x-filament::input.select wire:model="sectorId" required>
                                    <option value="">Selecione o setor...</option>
                                    @foreach($this->sectors as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>

                            <x-filament::input.wrapper label="Arquivo (.xlsx, .csv)">
                                <x-filament::input wire:model="file" type="file" accept=".xlsx,.xls,.csv" />
                            </x-filament::input.wrapper>
                        </div>

                        <div class="flex justify-end">
                            <x-filament::button type="submit" icon="heroicon-m-check-circle" wire:loading.attr="disabled">
                                Importar Arquivo
                            </x-filament::button>
                        </div>
                    </form>
                </div>

                {{-- Aba: Texto --}}
                <div x-show="$wire.activeTab === 'text'" class="space-y-6" x-cloak>
                    <form wire:submit="importFromText" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                            <x-filament::input.wrapper label="Setor de Destino" required>
                                <x-filament::input.select wire:model="textSectorId" required>
                                    <option value="">Selecione o setor...</option>
                                    @foreach($this->sectors as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </x-filament::input.select>
                            </x-filament::input.wrapper>

                            <div>
                                <x-guest-import.delimiter-selector model="delimiter" />
                            </div>
                        </div>

                        <x-filament::input.wrapper label="Conteúdo da Lista">
                            <textarea 
                                wire:model.live.debounce.500ms="textContent" 
                                rows="10"
                                class="fi-input block w-full border-none bg-transparent focus:ring-0 text-base text-gray-950 dark:text-white sm:text-sm sm:leading-6"
                                placeholder="Nome, Documento (opcional)&#10;João Silva, 123456789&#10;Maria Oliveira"
                            ></textarea>
                        </x-filament::input.wrapper>

                        <x-guest-import.preview-table :items="$this->parsedPreview" />

                        <div class="flex justify-end">
                            <x-filament::button type="submit" icon="heroicon-m-sparkles" :disabled="empty($this->textContent)">
                                Importar {{ count($this->parsedPreview) }} Convidados
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
