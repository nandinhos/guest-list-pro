<div class="flex flex-col gap-3 p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-100 dark:border-gray-800 shadow-sm">
    {{-- Header: Hora e Status --}}
    <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-800 pb-2">
        <span class="text-xs text-gray-400 dark:text-gray-500 flex items-center gap-1">
            <x-filament::icon icon="heroicon-m-clock" class="h-3 w-3" />
            {{ $getRecord()->created_at->format('d/m H:i') }}
        </span>
        @php
            $result = $getRecord()->result;
            $color = match($result) {
                'already_checked_in' => 'warning',
                'error' => 'danger',
                'estorno' => 'info',
                default => 'gray',
            };
            $label = match($result) {
                'already_checked_in' => 'Já fez check-in',
                'error' => 'Erro',
                'estorno' => 'Estorno',
                default => $result,
            };
        @endphp
        <x-filament::badge :color="$color" size="sm">
            {{ $label }}
        </x-filament::badge>
    </div>

    {{-- Body: Validador e Convidado --}}
    <div class="space-y-1">
        <div class="flex flex-col">
            <span class="text-xs text-gray-500 dark:text-gray-400">Validador</span>
            <span class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-tight">
                {{ $getRecord()->validator?->name ?? '-' }}
            </span>
        </div>
        
        @if($getRecord()->guest)
            <div class="flex flex-col mt-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Convidado Alvo</span>
                <span class="text-sm text-gray-900 dark:text-gray-100">
                    {{ $getRecord()->guest->name }}
                </span>
            </div>
        @endif
    </div>

    {{-- Footer: IP e Evento (se visível) --}}
    <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-gray-800 text-xs text-gray-400 dark:text-gray-500">
        <span>IP: {{ $getRecord()->ip_address ?? '-' }}</span>
        @if(!session('selected_event_id') && $getRecord()->event)
            <span class="truncate max-w-[120px]">{{ $getRecord()->event->name }}</span>
        @endif
    </div>
</div>
