<x-filament-panels::page>
    {{ $this->form }}

    {{-- Abas --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="flex border-b border-gray-200 dark:border-gray-700">
            @foreach ([
                'excursoes' => ['label' => 'Excursões', 'icon' => 'heroicon-o-map'],
                'veiculos'  => ['label' => 'Veículos',  'icon' => 'heroicon-o-truck'],
                'monitores' => ['label' => 'Monitores', 'icon' => 'heroicon-o-user-group'],
            ] as $tab => $info)
                <button
                    wire:click="switchTab('{{ $tab }}')"
                    @class([
                        'flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors border-b-2 -mb-px',
                        'border-primary-500 text-primary-600 dark:text-primary-400' => $activeTab === $tab,
                        'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $activeTab !== $tab,
                    ])
                >
                    <x-filament::icon :icon="$info['icon']" class="h-4 w-4" />
                    {{ $info['label'] }}
                    <span @class([
                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold',
                        'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' => $activeTab === $tab,
                        'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400' => $activeTab !== $tab,
                    ])>
                        {{ $this->getTabCount($tab) }}
                    </span>
                </button>
            @endforeach
        </div>

        <div class="p-0">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
