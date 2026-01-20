<div class="flex items-center gap-4">
    {{-- Evento Selecionado (ESQUERDA) --}}
    @if($this->selectedEvent)
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg
                    bg-primary-50 dark:bg-primary-900/20
                    border border-primary-200 dark:border-primary-700/50
                    text-sm">
            {{-- Ícone de calendário --}}
            <svg class="w-4 h-4 text-primary-500 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>

            {{-- Nome do evento --}}
            <span class="font-medium text-primary-700 dark:text-primary-300 max-w-[150px] truncate">
                {{ $this->selectedEvent->name }}
            </span>

            {{-- Botão de trocar --}}
            <button
                type="button"
                wire:click="changeEvent"
                class="ml-1 p-1 rounded-md
                       text-primary-400 hover:text-primary-600
                       dark:text-primary-500 dark:hover:text-primary-300
                       hover:bg-primary-100 dark:hover:bg-primary-800/50
                       transition-colors duration-150"
                title="Trocar Evento"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </button>
        </div>

        {{-- Separador visual --}}
        <div class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>
    @endif

    {{-- User Info com Dropdown (DIREITA) --}}
    <x-filament::dropdown placement="bottom-end">
        <x-slot name="trigger">
            <button type="button" class="flex items-center gap-2 px-3 py-1.5 rounded-lg
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           transition-colors duration-150 focus:outline-none">
                {{-- Avatar --}}
                <x-filament::avatar
                    :src="filament()->getUserAvatarUrl(auth()->user())"
                    :attributes="\Filament\Support\prepare_inherited_attributes(
                        new \Illuminate\View\ComponentAttributeBag([
                            'class' => 'w-8 h-8',
                        ])
                    )"
                />

                {{-- Nome do usuário --}}
                <span class="font-medium text-sm text-gray-700 dark:text-gray-200 max-w-[120px] truncate hidden sm:inline">
                    {{ auth()->user()?->name ?? 'Usuário' }}
                </span>

                {{-- Chevron indicando dropdown --}}
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>
        </x-slot>

        {{-- Dropdown Content - User Menu Items --}}
        <x-filament::dropdown.list>
            {{-- Theme Switcher --}}
            <x-filament-panels::theme-switcher />

            <x-filament::dropdown.list.item
                icon="heroicon-o-arrow-right-on-rectangle"
                tag="form"
                :action="filament()->getLogoutUrl()"
                method="post"
            >
                Sair
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
</div>
