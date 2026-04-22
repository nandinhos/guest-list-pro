<div class="event-card group text-left overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg transition-all duration-300 h-full flex flex-col relative">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Banner --}}
    <div class="aspect-[21/9] w-full overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0 relative">
        @if($record->banner_display_url)
            <img
                src="{{ $record->banner_display_url }}"
                alt="{{ $record->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
            >
        @else
            <div class="w-full h-full flex items-center justify-center">
                <x-filament::icon icon="heroicon-m-photo" class="w-12 h-12 text-gray-300 dark:text-gray-600" />
            </div>
        @endif

        {{-- Status Overlay --}}
        @if($record->status)
            <div class="absolute top-3 right-3">
                <x-filament::badge :color="$record->status->getColor() ?? 'gray'" size="xs" class="font-bold backdrop-blur-md bg-white/80 dark:bg-gray-900/80 shadow-sm">
                    {{ $record->status->getLabel() ?? ucfirst($record->status->value) }}
                </x-filament::badge>
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="p-4 flex-grow flex flex-col justify-between">
        <div class="space-y-3">
            <div>
                <h3 class="font-bold text-base text-gray-900 dark:text-gray-100 leading-tight line-clamp-1 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" title="{{ $record->name }}">
                    {{ $record->name }}
                </h3>
                <div class="flex items-center gap-1.5 mt-1.5">
                    <x-filament::icon icon="heroicon-m-calendar" class="w-3.5 h-3.5 text-primary-500 shrink-0"/>
                    <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-400">
                        {{ $record->date->format('d/M/Y') }} <span class="text-gray-300 dark:text-gray-600 mx-1">|</span> {{ \Carbon\Carbon::parse($record->start_time)->format('H:i') }}
                    </span>
                </div>
            </div>

            <div class="space-y-1.5">
                @if($record->location)
                    <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                        <x-filament::icon icon="heroicon-m-map-pin" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                        <span class="truncate">{{ $record->location }}</span>
                    </div>
                @endif

                <div class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1.5">
                    <x-filament::icon icon="heroicon-m-users" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                    <span>{{ $record->guests_count ?? $record->guests()->count() }} Convidados</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end w-full mt-5 pt-3 border-t border-gray-50 dark:border-gray-800/50">
            <button
                type="button"
                wire:click="mountTableAction('edit', {{ $record->id }})"
                class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 rounded-xl hover:bg-primary-600 hover:text-white dark:hover:bg-primary-500 transition-all duration-300 border border-transparent hover:shadow-md active:scale-95"
            >
                <x-filament::icon icon="heroicon-m-pencil-square" class="w-4 h-4 mr-2"/>
                Configurar Evento
            </button>
        </div>
    </div>
</div>
