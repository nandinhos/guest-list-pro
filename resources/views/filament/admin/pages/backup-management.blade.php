<x-filament-panels::page>
    <div x-data="{
        showModal: false,
        modalTitle: '',
        modalMessage: '',
        modalAction: null,
        modalActionParams: {},
        modalContent: 'confirm'
    }">
        {{-- Modal --}}
        <div x-show="showModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center"
             x-on:keydown.escape.window="showModal = false">
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm"
                 x-on:click="showModal = false"></div>
            <div class="relative z-10 w-full max-w-md mx-4 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden"
                 x-show="showModal"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                {{-- Header --}}
                <div class="p-6 pb-4">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center"
                             :class="modalContent === 'processing' ? 'bg-warning-100 dark:bg-warning-900/30' : 'bg-red-100 dark:bg-red-900/30'">
                            <template x-if="modalContent === 'processing'">
                                <div class="w-6 h-6 border-2 border-warning-500 border-t-transparent rounded-full animate-spin"></div>
                            </template>
                            <template x-if="modalContent !== 'processing' && modalAction === 'resetDatabase'">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </template>
                            <template x-if="modalContent !== 'processing' && modalAction === 'delete'">
                                <x-filament::icon icon="heroicon-o-trash" class="w-6 h-6 text-red-600 dark:text-red-400" />
                            </template>
                            <template x-if="modalContent !== 'processing' && modalAction === 'restore'">
                                <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-6 h-6 text-warning-600 dark:text-warning-400" />
                            </template>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="modalTitle"></h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-show="modalContent !== 'processing'">Não feche esta janela durante o processo</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-show="modalContent === 'processing'">Processando, aguarde...</p>
                        </div>
                    </div>
                </div>

                {{-- Content --}}
                <div class="px-6 pb-6">
                    {{-- Confirm state (idle) --}}
                    <template x-if="modalContent === 'confirm'">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6" x-text="modalMessage"></p>
                            <div class="flex gap-3 justify-end">
                                <button x-on:click="showModal = false"
                                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    Cancelar
                                </button>
                                <button x-on:click="modalAction === 'resetDatabase' ? (modalContent = 'processing', $wire.resetDatabase()) : (showModal = false, modalActionParams.filename ? $wire[modalAction](modalActionParams.filename) : $wire[modalAction]())"
                                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors"
                                        :class="modalAction === 'delete' || modalAction === 'resetDatabase' ? 'bg-red-600 hover:bg-red-700' : 'bg-warning-600 hover:bg-warning-700'">
                                    Confirmar
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Processing state --}}
                    <template x-if="modalContent === 'processing'">
                        <div class="text-center py-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-warning-100 dark:bg-warning-900/30 mb-4">
                                <div class="w-8 h-8 border-3 border-warning-500 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                            <p class="text-base font-semibold text-gray-900 dark:text-white mb-1">Processando...</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Zerando banco de dados. Aguarde.</p>
                            <div class="mt-4 flex justify-center gap-1">
                                <div class="w-2 h-2 rounded-full bg-warning-500 animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 rounded-full bg-warning-500 animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 rounded-full bg-warning-500 animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

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
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $backup['size'] }}</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.backups.download', ['filename' => $backup['filename']]) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                    <x-filament::icon icon="heroicon-o-arrow-down" class="w-4 h-4" />
                                    Baixar
                                </a>
                                <button
                                    type="button"
                                    x-on:click="showModal = true; modalTitle = 'Restaurar Backup'; modalMessage = 'Tem certeza que deseja restaurar o backup \'{{ $backup['filename'] }}\'? Os dados atuais serão substituídos.'; modalAction = 'restore'; modalContent = 'confirm'; modalActionParams = { filename: '{{ $backup['filename'] }}' }"
                                    class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-warning-700 dark:text-warning-300 bg-warning-100 dark:bg-warning-900/30 rounded-lg hover:bg-warning-200 dark:hover:bg-warning-900/50 transition-colors">
                                    <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-4 h-4" />
                                    Restaurar
                                </button>
                                <button
                                    type="button"
                                    x-on:click="showModal = true; modalTitle = 'Excluir Backup'; modalMessage = 'Tem certeza que deseja excluir o backup \'{{ $backup['filename'] }}\'? Esta ação não pode ser desfeita.'; modalAction = 'delete'; modalContent = 'confirm'; modalActionParams = { filename: '{{ $backup['filename'] }}' }"
                                    class="flex-1 inline-flex items-center justify-center gap-1 px-3 py-2 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                    <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />
                                    Excluir
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Desktop Table View (hidden below lg) --}}
                <div class="hidden lg:block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3 font-medium">Nome</th>
                                <th class="px-4 py-3 font-medium">Tamanho</th>
                                <th class="px-4 py-3 font-medium">Data</th>
                                <th class="px-4 py-3 font-medium text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                            @foreach($this->backups as $index => $backup)
                                <tr class="{{ $index === 0 ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            @if($index === 0)
                                                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400">
                                                    Novo
                                                </span>
                                            @endif
                                            <span class="font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ $backup['filename'] }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $backup['size'] }}</td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $backup['modified'] }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.backups.download', ['filename' => $backup['filename']]) }}"
                                               class="inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                                                <x-filament::icon icon="heroicon-o-arrow-down" class="w-4 h-4" />
                                                Baixar
                                            </a>
                                            <button
                                                type="button"
                                                x-on:click="showModal = true; modalTitle = 'Restaurar Backup'; modalMessage = 'Tem certeza que deseja restaurar o backup \'{{ $backup['filename'] }}\'? Os dados atuais serão substituídos.'; modalAction = 'restore'; modalContent = 'confirm'; modalActionParams = { filename: '{{ $backup['filename'] }}' }"
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-warning-700 dark:text-warning-300 bg-warning-100 dark:bg-warning-900/30 rounded-lg hover:bg-warning-200 dark:hover:bg-warning-900/50 transition-colors">
                                                <x-filament::icon icon="heroicon-o-arrow-uturn-left" class="w-4 h-4" />
                                                Restaurar
                                            </button>
                                            <button
                                                type="button"
                                                x-on:click="showModal = true; modalTitle = 'Excluir Backup'; modalMessage = 'Tem certeza que deseja excluir o backup \'{{ $backup['filename'] }}\'? Esta ação não pode ser desfeita.'; modalAction = 'delete'; modalContent = 'confirm'; modalActionParams = { filename: '{{ $backup['filename'] }}' }"
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors">
                                                <x-filament::icon icon="heroicon-o-trash" class="w-4 h-4" />
                                                Excluir
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Empty State --}}
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 mb-4">
                        <x-filament::icon icon="heroicon-o-cloud-arrow-up" class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">Nenhum backup encontrado</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Crie seu primeiro backup para proteger seus dados.</p>
                </div>
            @endif

            {{-- Danger Zone --}}
            @if(app()->environment(['local', 'development']))
                <x-filament::section icon="heroicon-o-exclamation-triangle" icon-color="danger" class="mt-6">
                    <x-slot name="heading">Zona de Perigo</x-slot>
                    <x-slot name="description">Estas ações são irreversíveis. Tenha certeza antes de prosseguir.</x-slot>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Zerar Banco de Dados</p>
                            <p class="text-xs text-red-600 dark:text-red-400 mt-0.5">Esta ação não pode ser desfeita. Um backup automático será criado antes do reset.</p>
                        </div>

                        <button
                            type="button"
                            x-on:click="showModal = true; modalTitle = 'Zerar Banco de Dados'; modalMessage = 'Esta ação não pode ser desfeita. Todos os dados serão apagados e o banco será recriado com um usuário admin padrão.'; modalAction = 'resetDatabase'; modalContent = 'confirm'"
                            class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 dark:bg-red-700 dark:hover:bg-red-600 dark:focus:ring-red-400">
                            <x-filament::icon icon="heroicon-o-trash" class="w-5 h-5" />
                            Zerar Banco de Dados
                        </button>
                    </div>
                </x-filament::section>
            @endif
        </div>
    </div>
</x-filament-panels::page>
