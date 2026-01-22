<div class="flex flex-col space-y-0.5">
    <div class="flex items-center space-x-1 text-xs text-gray-500">
        <x-heroicon-m-identification class="w-4 h-4 text-gray-400 shrink-0"/>
        <span>{{ $record->document ?? 'Sem documento' }}</span>
    </div>
</div>
