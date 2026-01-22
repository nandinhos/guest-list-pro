<div class="flex flex-col space-y-3 py-1.5 w-full overflow-hidden">
    <!-- Row 1: Usuario e Data -->
    <div class="flex items-start justify-between w-full">
        <div class="flex flex-col min-w-0 flex-1 pr-2">
            <span class="font-bold text-sm text-gray-950 dark:text-white truncate">
                {{ $getRecord()->causer?->name ?? 'Sistema' }}
            </span>
            <span class="text-[10px] text-gray-500 truncate">{{ $getRecord()->causer?->email }}</span>
        </div>
        <div class="shrink-0 text-right">
            <span class="text-[10px] font-medium text-gray-400">
                {{ $getRecord()->created_at->format('d/m/Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Row 2: Atividade -->
    <div class="flex flex-col space-y-1 bg-gray-50 dark:bg-gray-900/50 p-2 rounded-lg border border-gray-100 dark:border-gray-800">
        <div class="flex items-center space-x-2">
            @php
                $eventColor = match($getRecord()->event) {
                    'created' => 'success',
                    'updated' => 'warning',
                    'deleted' => 'danger',
                    default => 'gray',
                };
                $eventLabel = match($getRecord()->event) {
                    'created' => 'CRIAÇÃO',
                    'updated' => 'ALTERAÇÃO',
                    'deleted' => 'REMOÇÃO',
                    default => strtoupper($getRecord()->event ?? 'SISTEMA'),
                };
            @endphp
            <span class="px-1.5 py-0.5 rounded text-[8px] font-black bg-{{ $eventColor }}-500/10 text-{{ $eventColor }}-700 tracking-tighter shadow-sm">
                {{ $eventLabel }}
            </span>
            <span class="text-[10px] font-bold text-gray-700 dark:text-gray-300">
                @php
                    $subjectName = null;
                    if ($getRecord()->subject) {
                        $subjectName = match (get_class($getRecord()->subject)) {
                            'App\Models\TicketSale' => 'Venda #'.$getRecord()->subject->id,
                            default => $getRecord()->subject->name ?? $getRecord()->subject->title ?? null,
                        };
                    }
                    if (!$subjectName) {
                        $attributes = $getRecord()->properties['attributes'] ?? [];
                        $old = $getRecord()->properties['old'] ?? [];
                        $subjectName = $attributes['name'] ?? $old['name'] ?? 
                                       $attributes['title'] ?? $old['title'] ?? 
                                       class_basename($getRecord()->subject_type ?? 'Sistema');
                    }
                @endphp
                {{ $subjectName }}
            </span>
        </div>
        <p class="text-[10px] text-gray-500 line-clamp-2 italic leading-tight">
            {{ $getRecord()->description }}
        </p>
    </div>

    <!-- Row 3: Ação (Botão Ver) -->
    <div class="flex items-center justify-end w-full pt-1">
        <button
            wire:click="mountTableAction('view', {{ $getRecord()->id }})"
            class="inline-flex items-center justify-center px-4 py-1.5 text-xs font-bold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors dark:bg-gray-800 dark:text-gray-400"
        >
            <x-heroicon-m-eye class="w-4 h-4 mr-1.5"/>
            Ver Detalhes
        </button>
    </div>
</div>
