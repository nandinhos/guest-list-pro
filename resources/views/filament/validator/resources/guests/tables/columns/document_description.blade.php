<div class="flex flex-col space-y-0.5">
    <div class="flex items-center space-x-1 text-xs text-gray-500">
        <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
        <span>{{ $record->document }}</span>
    </div>
    @if($record->promoter)
        <div class="flex items-center space-x-1 text-indigo-600 dark:text-indigo-400">
            <x-heroicon-m-clipboard-document-list class="w-4 h-4 shrink-0"/>
            <span class="text-[10px] font-medium truncate">{{ $record->promoter->name }}</span>
        </div>
    @endif
</div>
