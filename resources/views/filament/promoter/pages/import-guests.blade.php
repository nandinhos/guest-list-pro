<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Cabeçalho --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between p-6 glass-card rounded-3xl border border-white/5 shadow-xl">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gradient-admin">
                    Importar Convidados
                </h2>
                <p class="mt-1 text-sm text-surface-secondary">
                    Gerencie sua lista de convidados via arquivo Excel ou colando texto.
                </p>
            </div>
            <div class="flex items-center gap-3">
                {{ $this->downloadTemplateAction }}
            </div>
        </div>

        {{-- Tabs de Navegação --}}
        <div x-data="{ activeTab: @entangle('activeTab') }" class="space-y-6">
            <div class="flex p-1.5 glass-subtle rounded-2xl border border-white/5 w-fit">
                <button type="button" @click="activeTab = 'file'"
                    :class="activeTab === 'file' ? 'bg-[var(--color-brand-admin-500)] text-white shadow-lg' : 'text-surface-secondary hover:text-surface-primary hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2">
                    <x-heroicon-o-document-plus class="w-4 h-4" />
                    Arquivo Excel/CSV
                </button>
                <button type="button" @click="activeTab = 'text'"
                    :class="activeTab === 'text' ? 'bg-[var(--color-brand-admin-500)] text-white shadow-lg' : 'text-surface-secondary hover:text-surface-primary hover:bg-white/5'"
                    class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300 flex items-center gap-2">
                    <x-heroicon-o-clipboard-document-list class="w-4 h-4" />
                    Colar Lista de Texto
                </button>
            </div>

            {{-- Aba: Upload de Arquivo --}}
            <div x-show="activeTab === 'file'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                <div class="glass-card p-8 rounded-3xl border border-white/5 shadow-2xl overflow-hidden relative group">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-[var(--color-brand-admin-500)]/10 rounded-full blur-3xl group-hover:bg-[var(--color-brand-admin-500)]/20 transition-all duration-700"></div>
                    
                    <form wire:submit="import" class="relative z-10 space-y-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-surface-primary flex items-center gap-2">
                                    <x-heroicon-o-tag class="w-4 h-4 text-[var(--color-brand-admin-500)]" />
                                    Setor de Destino
                                </label>
                                <select wire:model="sectorId" class="block w-full rounded-xl border-white/10 bg-white/5 text-surface-primary focus:border-[var(--color-brand-admin-500)] focus:ring-[var(--color-brand-admin-500)] transition-all">
                                    <option value="" class="bg-gray-900">Selecione o setor...</option>
                                    @foreach($this->sectors as $id => $name)
                                        <option value="{{ $id }}" class="bg-gray-900">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-bold text-surface-primary flex items-center gap-2">
                                    <x-heroicon-o-cloud-arrow-up class="w-4 h-4 text-[var(--color-brand-admin-500)]" />
                                    Selecione o Arquivo
                                </label>
                                <input type="file" wire:model="file" accept=".xlsx,.xls,.csv"
                                    class="block w-full text-xs text-surface-secondary file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[var(--color-brand-admin-500)]/10 file:text-[var(--color-brand-admin-500)] hover:file:bg-[var(--color-brand-admin-500)]/20 transition-all cursor-pointer" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-4 border-t border-white/5">
                            <p class="text-[10px] text-surface-secondary uppercase tracking-widest font-bold italic">
                                * Colunas esperadas: Nome, Documento, Email
                            </p>
                            <x-filament::button type="submit" size="lg" icon="heroicon-o-check-circle" class="rounded-xl shadow-admin-glow" wire:loading.attr="disabled">
                                Iniciar Importação
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Aba: Colar Texto --}}
            <div x-show="activeTab === 'text'" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4">
                <div class="glass-card p-8 rounded-3xl border border-white/5 shadow-2xl">
                    <form wire:submit="importFromText" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-surface-primary">Setor de Destino</label>
                                <select wire:model="textSectorId" class="block w-full rounded-xl border-white/10 bg-white/5 text-surface-primary focus:border-[var(--color-brand-admin-500)] transition-all">
                                    <option value="" class="bg-gray-900">Selecione o setor...</option>
                                    @foreach($this->sectors as $id => $name)
                                        <option value="{{ $id }}" class="bg-gray-900">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="flex items-end">
                                <x-guest-import.delimiter-selector model="delimiter" />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-bold text-surface-primary">Conteúdo da Lista</label>
                            <textarea wire:model.live.debounce.300ms="textContent" rows="10"
                                class="block w-full rounded-2xl border-white/10 bg-white/5 text-surface-primary font-mono text-sm focus:border-[var(--color-brand-admin-500)] transition-all placeholder:text-surface-secondary/30"
                                placeholder="João Silva, 123.456.789-00
Maria Santos, 987.654.321-00"></textarea>
                        </div>

                        {{-- Preview Component --}}
                        <x-guest-import.preview-table :items="$this->parsedPreview" />

                        <div class="flex justify-end pt-6 border-t border-white/5">
                            <x-filament::button type="submit" size="lg" icon="heroicon-o-sparkles" class="rounded-xl shadow-admin-glow" :disabled="empty($this->textContent)">
                                Importar {{ count($this->parsedPreview) }} Convidados
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
