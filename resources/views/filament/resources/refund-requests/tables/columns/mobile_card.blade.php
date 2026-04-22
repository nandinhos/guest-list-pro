<div class="flex flex-col gap-3 p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm">
    <div class="flex justify-between items-center border-b border-gray-50 dark:border-gray-700 pb-2">
        <span class="text-xs font-medium text-gray-400">#{{ $getState()->id }}</span>
        <span class="text-xs text-gray-400 flex items-center gap-1">
            <x-filament::icon icon="heroicon-m-clock" class="h-3 w-3" />
            {{ $getState()->created_at->format('H:i') }}
        </span>
    </div>

    <div class="flex flex-col">
        <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $getState()->ticketSale?->buyer_name ?? '-' }}</span>
        <span class="text-xs text-gray-500">{{ $getState()->ticketSale?->buyer_document ?: 'Sem documento' }}</span>
    </div>

    <div class="flex justify-between items-center pt-2 border-t border-gray-50 dark:border-gray-700">
        <span class="text-sm font-bold text-primary-600 dark:text-primary-400">
            R$ {{ number_format($getState()->ticketSale?->value ?? 0, 2, ',', '.') }}
        </span>
        <x-filament::badge :color="match($getState()->status->value) { 'pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', default => 'gray' }" size="sm">
            {{ $getState()->status->getLabel() }}
        </x-filament::badge>
    </div>

    <div class="text-xs text-gray-400">
        Motivo: {{ \Illuminate\Support\Str::limit($getState()->reason, 40) }}
    </div>
</div>