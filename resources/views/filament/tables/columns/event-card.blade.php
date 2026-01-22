<div class="event-card group text-left overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm hover:shadow-lg transition-all duration-300 h-full flex flex-col">
    {{-- Banner --}}
    <div class="aspect-video w-full overflow-hidden bg-gray-100 dark:bg-gray-700 shrink-0">
        @if($record->banner_display_url)
            <img
                src="{{ $record->banner_display_url }}"
                alt="{{ $record->name }}"
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
    </div>

    {{-- Info --}}
    <div class="p-4 flex-grow flex flex-col justify-between">
        <div class="space-y-2">
            <div class="flex items-start justify-between gap-2">
                <h3 class="font-bold text-sm text-gray-900 dark:text-gray-100 leading-tight flex-grow" title="{{ $record->name }}">
                    {{ $record->name }}
                </h3>
                @if($record->status)
                    @php
                        $statusColor = $record->status->getColor() ?? 'gray';
                        $statusLabel = $record->status->getLabel() ?? ucfirst($record->status->value);
                    @endphp
                    <span class="inline-flex shrink-0 items-center px-2 py-0.5 rounded text-[10px] font-medium bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-700">
                        {{ $statusLabel }}
                    </span>
                @endif
            </div>

            <p class="text-[10px] text-gray-600 dark:text-gray-400 flex items-center gap-1.5">
                <x-heroicon-m-calendar class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                <span>
                    {{ $record->date->format('d/m/Y') }} &bull;
                    {{ \Carbon\Carbon::parse($record->start_time)->format('H:i') }}
                </span>
            </p>

            @if($record->location)
                <p class="text-[10px] text-gray-500 dark:text-gray-500 flex items-center gap-1.5 truncate">
                    <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                    <span class="truncate">{{ $record->location }}</span>
                </p>
            @endif

            <p class="text-[10px] text-gray-400 flex items-center gap-1.5 pt-1">
                <x-heroicon-m-users class="w-3.5 h-3.5 shrink-0"/>
                <span>{{ $record->guests_count ?? $record->guests()->count() }} Convidados</span>
            </p>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end w-full mt-4 pt-3 border-t border-gray-100 dark:border-gray-800">
            <button
                wire:click="mountTableAction('edit', {{ $record->id }})"
                class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors"
            >
                <x-heroicon-m-pencil-square class="w-4 h-4 mr-1.5"/>
                Editar
            </button>
        </div>
    </div>
</div>
