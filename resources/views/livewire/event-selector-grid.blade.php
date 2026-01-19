<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
    @forelse($this->events as $event)
        <button
            type="button"
            wire:click="selectEvent({{ $event->id }})"
            wire:key="event-{{ $event->id }}"
            class="event-card group text-left overflow-hidden rounded-xl
                   border border-gray-200 dark:border-gray-700
                   bg-white dark:bg-gray-800
                   shadow-sm hover:shadow-lg
                   transition-all duration-300 focus:outline-none
                   focus:ring-2 focus:ring-primary-500 focus:ring-offset-2
                   dark:focus:ring-offset-gray-900"
        >
            {{-- Banner --}}
            <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-700">
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
                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                @endif
            </div>

            {{-- Info --}}
            <div class="p-4 space-y-2">
                <h3 class="font-semibold text-base text-gray-900 dark:text-gray-100 truncate">
                    {{ $event->name }}
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>
                        {{ $event->date->format('d/m/Y') }} &bull;
                        {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
                    </span>
                </p>
                @if($event->location)
                    <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center gap-2 truncate">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="truncate">{{ $event->location }}</span>
                    </p>
                @endif
            </div>

            {{-- Indicador de hover --}}
            <div class="absolute inset-x-0 bottom-0 h-1 bg-primary-500 opacity-0
                        transition-opacity duration-300 group-hover:opacity-100"></div>
        </button>
    @empty
        <div class="col-span-full text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <p class="text-gray-500 dark:text-gray-400">Nenhum evento disponível para você.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                Entre em contato com o administrador para ser atribuído a um evento.
            </p>
        </div>
    @endforelse
</div>
