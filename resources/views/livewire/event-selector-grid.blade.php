<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($this->events as $event)
        <button
            type="button"
            wire:click="selectEvent({{ $event->id }})"
            wire:key="event-{{ $event->id }}"
            class="event-card glass-card hover-lift group text-left overflow-hidden
                   transition-all duration-300 focus:outline-none
                   focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                   dark:focus:ring-offset-gray-900"
        >
            {{-- Banner (60%) --}}
            <div class="aspect-[16/10] overflow-hidden bg-gray-100 dark:bg-gray-800">
                @if($event->banner_display_url)
                    <img
                        src="{{ $event->banner_display_url }}"
                        alt="{{ $event->name }}"
                        class="w-full h-full object-cover transition-transform
                               duration-300 group-hover:scale-105"
                        loading="lazy"
                    >
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <x-heroicon-o-photo class="w-16 h-16 text-gray-400" />
                    </div>
                @endif
            </div>

            {{-- Info (40%) --}}
            <div class="p-4 space-y-2">
                <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 truncate">
                    {{ $event->name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-1">
                    <x-heroicon-m-calendar class="w-4 h-4 flex-shrink-0" />
                    <span>
                        {{ $event->date->format('d/m/Y') }} &bull;
                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
                    </span>
                </p>
                @if($event->location)
                    <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center gap-1 truncate">
                        <x-heroicon-m-map-pin class="w-4 h-4 flex-shrink-0" />
                        <span class="truncate">{{ $event->location }}</span>
                    </p>
                @endif
            </div>

            {{-- Indicador de hover (usa cor primaria do tema) --}}
            <div class="absolute inset-x-0 bottom-0 h-1 bg-primary-500 opacity-0
                        transition-opacity duration-300 group-hover:opacity-100"></div>
        </button>
    @empty
        <div class="col-span-full text-center py-12">
            <x-heroicon-o-calendar-days class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <p class="text-gray-500 dark:text-gray-400">Nenhum evento disponível para você.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                Entre em contato com o administrador para ser atribuído a um evento.
            </p>
        </div>
    @endforelse
</div>
