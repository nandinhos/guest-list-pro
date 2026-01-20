<div class="flex flex-col">
    <div class="flex items-center gap-2">
        @php
            $color = match ($event) {
                'created' => 'success',
                'updated' => 'warning',
                'deleted' => 'danger',
                'system' => 'info',
                default => 'gray',
            };
            $label = match ($event) {
                'created' => 'Criou',
                'updated' => 'Atualizou',
                'deleted' => 'Removeu',
                'system' => 'Ação',
                default => ucfirst($event ?? 'Ação'),
            };
        @endphp
        <span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-1.5 py-0.5 fi-color-custom fi-color-{{ $color }} text-custom-600 bg-custom-50 ring-custom-600/10 dark:text-custom-400 dark:bg-custom-400/10 dark:ring-custom-400/30"
            style="
                --c-50: var(--{{ $color }}-50);
                --c-400: var(--{{ $color }}-400);
                --c-600: var(--{{ $color }}-600);
            ">
            {{ $label }}
        </span>
        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
            {{ $subject_type }}
        </span>
    </div>
    @if(!($is_system_log ?? false) && $subject_id)
        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ $entity_label ?? 'Item' }} #{{ $subject_id }}
        </span>
    @elseif($is_system_log ?? false)
        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            {{ $entity_label ?? 'Sistema' }}
        </span>
    @endif
</div>
