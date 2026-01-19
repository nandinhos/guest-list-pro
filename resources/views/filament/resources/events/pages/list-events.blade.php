<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @php
            // Buscando eventos ativos e ordenados por data
            $events = \App\Models\Event::query()->orderBy('date')->get();
        @endphp

        @forelse($events as $event)
            <a href="{{ \App\Filament\Resources\Events\Pages\EditEvent::getUrl(['record' => $event]) }}"
               class="event-card group text-left overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
                
                {{-- Banner --}}
                <div class="aspect-video w-full overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0 relative">
                    @if($event->banner_display_url)
                        <img
                            src="{{ $event->banner_display_url }}"
                            alt="{{ $event->name }}"
                            class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                            loading="lazy"
                        >
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                        </div>
                    @endif

                    {{-- Overlay de Edição (opcional, aparece no hover) --}}
                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <span class="px-4 py-2 bg-white/90 dark:bg-gray-900/90 rounded-full text-sm font-medium text-gray-900 dark:text-white shadow-lg backdrop-blur-sm">
                            Editar Evento
                        </span>
                    </div>
                </div>

                {{-- Info --}}
                <div class="p-4 space-y-3 flex-grow flex flex-col">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-gray-100 leading-tight line-clamp-2" title="{{ $event->name }}">
                            {{ $event->name }}
                        </h3>
                    </div>

                    <div class="space-y-1">
                        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                            <svg class="w-4 h-4 flex-shrink-0 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">
                                {{ $event->date->format('d/m/Y') }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 flex items-center gap-2 pl-6">
                            <span>
                                {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }} às {{ \Carbon\Carbon::parse($event->end_time)->format('H:i') }}
                            </span>
                        </p>
                    </div>

                    @if($event->location)
                        <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center gap-2 truncate pt-2 border-t border-gray-100 dark:border-gray-700 mt-auto">
                            <svg class="w-4 h-4 flex-shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="truncate">{{ $event->location }}</span>
                        </p>
                    @endif

                    <div class="pt-2">
                         @php
                            $statusColor = $event->status->getColor() ?? 'gray';
                            $statusLabel = $event->status->getLabel() ?? ucfirst($event->status->value);
                        @endphp
                        <x-filament::badge :color="$statusColor" size="sm">
                            {{ $statusLabel }}
                        </x-filament::badge>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center p-12 text-center bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-4 mb-4">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nenhum evento encontrado</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Comece criando seu primeiro evento.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
