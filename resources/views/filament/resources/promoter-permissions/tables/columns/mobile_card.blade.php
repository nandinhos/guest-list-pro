<div class="flex flex-col space-y-3 py-1.5 w-full overflow-hidden">
    <!-- Row 1: Usuario e Role -->
    <div class="flex items-start justify-between w-full">
        <span class="font-bold text-sm text-gray-950 dark:text-white break-words pr-2 leading-tight flex-1 min-w-0">
            {{ $getRecord()->user->name }}
        </span>
        @php
            $role = \App\Enums\UserRole::tryFrom($getRecord()->role);
            $roleColor = $role?->getColor() ?? 'gray';
        @endphp
        <span class="inline-flex shrink-0 items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-{{ $roleColor }}-500/10 text-{{ $roleColor }}-700 uppercase">
            {{ $role?->getLabel() ?? $getRecord()->role }}
        </span>
    </div>

    <!-- Row 2: Evento e Setor -->
    <div class="flex flex-col space-y-1">
        <div class="flex items-center space-x-1 text-xs text-gray-500">
            <x-heroicon-m-calendar class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="font-medium truncate">{{ $getRecord()->event?->name }}</span>
            @if($getRecord()->sector)
                <span class="text-gray-300 dark:text-gray-600">|</span>
                <span class="truncate text-indigo-600 dark:text-indigo-400 font-medium">{{ $getRecord()->sector->name }}</span>
            @endif
        </div>
        <div class="flex items-center space-x-1 text-gray-500">
            <x-heroicon-m-users class="w-4 h-4 text-gray-400 shrink-0"/>
            <span class="text-[10px] truncate">
                Limite: <span class="font-bold">{{ $getRecord()->guest_limit ?? 'Ilimitado' }}</span>
            </span>
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
