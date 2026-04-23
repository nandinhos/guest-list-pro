<div class="flex flex-col space-y-3 py-3 px-1 w-full">
    <div class="flex items-start justify-between w-full gap-2">
        <span class="font-bold text-sm text-gray-950 dark:text-white flex-1 min-w-0 break-words leading-tight">
            {{ $getRecord()->tipo->label() }}
        </span>
        <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-brand-excursionista-500/10 text-brand-excursionista-700 whitespace-nowrap">
            {{ $getRecord()->placa ?? '—' }}
        </span>
    </div>

    <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
        <x-heroicon-m-user-group class="w-4 h-4 text-gray-400 shrink-0"/>
        <span class="truncate">
            @if($getRecord()->monitores->isEmpty())
                Nenhum monitor
            @else
                {{ $getRecord()->monitores->count() }} monitor(es)
            @endif
        </span>
    </div>
</div>
