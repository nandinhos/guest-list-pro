<div class="flex flex-col">
    <span class="font-medium text-gray-900 dark:text-gray-100">
        {{ $user }}
    </span>
    <span class="text-xs text-gray-500 dark:text-gray-400" title="{{ $date->format('d/m/Y H:i:s') }}">
        {{ $date->diffForHumans() }}
    </span>
</div>
