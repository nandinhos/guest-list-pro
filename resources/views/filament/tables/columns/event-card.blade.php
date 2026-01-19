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
    <div class="p-4 space-y-2 flex-grow flex flex-col">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-base text-gray-900 dark:text-gray-100 truncate flex-grow" title="{{ $record->name }}">
                {{ $record->name }}
            </h3>
            @if($record->status)
                @php
                    $statusColor = $record->status->getColor() ?? 'gray';
                    $statusLabel = $record->status->getLabel() ?? ucfirst($record->status->value);
                @endphp
                <x-filament::badge :color="$statusColor">
                    {{ $statusLabel }}
                </x-filament::badge>
            @endif
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>
                {{ $record->date->format('d/m/Y') }} &bull;
                {{ \Carbon\Carbon::parse($record->start_time)->format('H:i') }} -
                {{ \Carbon\Carbon::parse($record->end_time)->format('H:i') }}
            </span>
        </p>

        @if($record->location)
            <p class="text-sm text-gray-500 dark:text-gray-500 flex items-center gap-2 truncate">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="truncate">{{ $record->location }}</span>
            </p>
        @endif
    </div>
</div>
