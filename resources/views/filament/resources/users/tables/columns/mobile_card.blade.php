<div class="flex flex-col space-y-3 py-1.5 w-full overflow-hidden">
    <!-- Row 1: Nome e Role -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->name }}
        </span>
        <div class="shrink-0">
            @php
                $role = $getRecord()->role;
                $roleColor = match($role->value) {
                    'admin' => 'danger',
                    'promoter' => 'primary',
                    'validator' => 'success',
                    default => 'gray',
                };
            @endphp
            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-{{ $roleColor }}-500/10 text-{{ $roleColor }}-700 uppercase tracking-wider">
                {{ $role->getLabel() }}
            </span>
        </div>
    </div>

    <!-- Row 2: Email e Status -->
    <div class="flex flex-col space-y-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-envelope class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="truncate">{{ $getRecord()->email }}</span>
        </div>
        <div class="flex items-center space-x-1 text-[10px] text-gray-400">
            <x-heroicon-m-clock class="w-3.5 h-3.5 shrink-0"/>
            <span>Criado em: {{ $getRecord()->created_at->format('d/m/Y') }}</span>
        </div>
    </div>

    <!-- Row 3: Ações -->
    <div class="flex items-center justify-end w-full pt-1 border-t border-gray-100 dark:border-gray-800">
        <button
            wire:click="mountTableAction('edit', {{ $getRecord()->id }})"
            class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-indigo-600 bg-indigo-500/10 rounded-lg hover:bg-indigo-500/20 transition-colors"
        >
            <x-heroicon-m-pencil-square class="w-4 h-4 mr-1.5"/>
            Editar
        </button>
    </div>
</div>
