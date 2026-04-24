<x-filament-panels::page>
    <div class="space-y-4 lg:space-y-6">
        {{-- Header Section with Stats --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-primary-500 to-primary-600 p-4 text-white">
                <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-xs lg:text-sm font-medium text-primary-100">Total de Backups</p>
                        <p class="mt-1 lg:mt-2 text-2xl lg:text-3xl font-bold">{{ count($this->backups) }}</p>
                    </div>
                    <div class="p-2 lg:p-3 rounded-lg bg-white/20 backdrop-blur-sm">
                        <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="w-5 h-5 lg:w-6 lg:h-6 text-white" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">Último Backup</p>
                        @if(count($this->backups) > 0)
                            @php $latest = $this->backups[0]; @endphp
                            <p class="mt-1 lg:mt-2 text-sm lg:text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $latest['modified'] }}</p>
                        @else
                            <p class="mt-1 lg:mt-2 text-sm lg:text-lg font-semibold text-gray-400">Nenhum</p>
                        @endif
                    </div>
                    <div class="p-2 rounded-lg bg-warning-500/10">
                        <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 lg:w-6 lg:h-6 text-warning-600 dark:text-warning-400" />
                    </div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 transition-all duration-200 hover:shadow-lg sm:col-span-2 lg:col-span-1">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs lg:text-sm font-medium text-gray-500 dark:text-gray-400">Espaço em Disco</p>
                        @php
                            $totalSize = 0;
                            foreach($this->backups as $b) {
                                $sizeKb = floatval(str_replace([' KB', ','], ['', '.'], $b['size']));
                                $totalSize += $sizeKb;
                            }
                        @endphp
                        <p class="mt-1 lg:mt-2 text-sm lg:text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($totalSize, 2) }} KB</p>
                    </div>
                    <div class="p-2 rounded-lg bg-info-500/10">
                        <x-filament::icon icon="heroicon-o-chart-bar" class="w-5 h-5 lg:w-6 lg:h-6 text-info-600 dark:text-info-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Backups Content --}}
        @if(count($this->backups) > 0)
            {{-- Mobile Cards View (hidden on lg+) --}}
            <div class="lg:hidden space-y-3">
                @foreach($this->backups as $index => $backup)
                    <div class="rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 p-4 {{ $index === 0 ? 'ring-2 ring-primary-500/30' : '' }}">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $index === 0 ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                <x-filament::icon icon="heroicon-o-circle-stack" class="w-6 h-6" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-gray-900 dark:text-white truncate">{{ $backup['filename'] }}</p>
                                    @if($index === 0)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400 shrink-0">
                                            Mais recente
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-3 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $backup['size'] }}</span>
                                    <span>•</span>
                                    <span>{{ $backup['modified'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button
                                wire:click="downloadBackup('{{ $backup['filename'] }}')"
                                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                                Baixar
                            </button>
                            <button
                                wire:click="restoreBackup('{{ $backup['filename'] }}')"
                                wire:confirm="ATENÇÃO: Isso irá substituir o banco de dados atual pelo backup. Continuar?"
                                class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-warning-600 bg-warning-50 rounded-lg hover:bg-warning-100 dark:bg-warning-900/20 dark:text-warning-400 dark:hover:bg-warning-900/30"
                            >
                                <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-4 h-4" />
                                Restaurar
                            </button>
                            <button
                                wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                wire:confirm="Tem certeza que deseja excluir?"
                                class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                            >
                                <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table View (hidden below lg) --}}
            <x-filament::section variant="bordered" class="hidden lg:block overflow-hidden">
                <x-slot name="header">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-primary-500/10">
                            <x-filament::icon icon="heroicon-o-document-text" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Arquivos de Backup</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Gerencie seus backups do banco de dados</p>
                        </div>
                    </div>
                </x-slot>

                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($this->backups as $index => $backup)
                        <div class="flex items-center justify-between p-4 transition-colors duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $index === 0 ? 'bg-primary-50/30 dark:bg-primary-900/10' : '' }}">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center justify-center w-12 h-12 rounded-xl {{ $index === 0 ? 'bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' }}">
                                    <x-filament::icon icon="heroicon-o-circle-stack" class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $backup['filename'] }}</p>
                                        @if($index === 0)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                                                Mais recente
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-folder" class="w-4 h-4" />
                                            {{ $backup['size'] }}
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-calendar" class="w-4 h-4" />
                                            {{ $backup['modified'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button
                                    wire:click="downloadBackup('{{ $backup['filename'] }}')"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-primary-600 transition-colors bg-primary-50 rounded-lg hover:bg-primary-100 dark:bg-primary-900/20 dark:text-primary-400 dark:hover:bg-primary-900/30"
                                    title="Baixar backup"
                                >
                                    <x-filament::icon icon="heroicon-o-arrow-down-tray" class="w-4 h-4" />
                                    <span class="hidden xl:inline">Baixar</span>
                                </button>
                                <button
                                    wire:click="restoreBackup('{{ $backup['filename'] }}')"
                                    wire:confirm="ATENÇÃO: Isso irá substituir o banco de dados atual. Continuar?"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-warning-600 transition-colors bg-warning-50 rounded-lg hover:bg-warning-100 dark:bg-warning-900/20 dark:text-warning-400 dark:hover:bg-warning-900/30"
                                    title="Restaurar backup"
                                >
                                    <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-4 h-4" />
                                    <span class="hidden xl:inline">Restaurar</span>
                                </button>
                                <button
                                    wire:click="deleteBackup('{{ $backup['filename'] }}')"
                                    wire:confirm="Tem certeza que deseja excluir o backup?"
                                    class="inline-flex items-center justify-center gap-1 px-3 py-2 text-sm font-medium text-red-600 transition-colors bg-red-50 rounded-lg hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                                    title="Excluir backup"
                                >
                                    <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />
                                    <span class="hidden xl:inline">Excluir</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-filament::section>
        @else
            {{-- Empty State --}}
            <x-filament::section variant="bordered" class="overflow-hidden">
                <div class="flex flex-col items-center justify-center py-12 lg:py-16">
                    <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="w-10 h-10 lg:w-12 lg:h-12 text-gray-400 dark:text-gray-600" />
                    </div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900 dark:text-white mb-1">Nenhum backup encontrado</h3>
                    <p class="text-xs lg:text-sm text-gray-500 dark:text-gray-400 text-center max-w-sm px-4 mb-4">
                        Clique no botão "Criar Backup" acima para fazer o primeiro backup do banco de dados.
                    </p>
                    <div class="flex items-center gap-2 text-xs lg:text-sm text-gray-400 dark:text-gray-500">
                        <x-filament::icon icon="heroicon-o-information-circle" class="w-4 h-4" />
                        <span class="hidden sm:inline">Backups são salvos em</span>
                        <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">{{ storage_path('app/backups') }}</code>
                    </div>
                </div>
            </x-filament::section>
        @endif

        {{-- Info Card --}}
        <div class="rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-4">
            <div class="flex gap-3">
                <div class="flex-shrink-0">
                    <x-filament::icon icon="heroicon-o-information-circle" class="h-5 w-5 text-blue-400 dark:text-blue-400 mt-0.5" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-300">Sobre Backups</h3>
                    <p class="mt-1 text-xs lg:text-sm text-blue-700 dark:text-blue-400">
                        Os backups são feitos em formato SQL e incluem toda a estrutura e dados do banco de dados. Recomendamos fazer backup regularmente e antes de qualquer manutenção importante.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
