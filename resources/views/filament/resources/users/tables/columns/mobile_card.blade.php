<div class="flex flex-col justify-between min-h-[180px] w-full p-4 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-md relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-10 -top-10 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all duration-500"></div>

    {{-- Header: Nome e Ações --}}
    <div class="flex justify-between items-start">
        <div class="flex flex-col max-w-[75%]">
            <div class="flex items-center gap-1.5">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                    Usuário #{{ $getRecord()->id }}
                </span>
                @if($getRecord()->is_active)
                    <span class="flex h-1.5 w-1.5 rounded-full bg-success-500 shadow-[0_0_5px_rgba(34,197,94,0.5)]" title="Usuário Ativo"></span>
                @else
                    <span class="flex h-1.5 w-1.5 rounded-full bg-danger-500 shadow-[0_0_5px_rgba(239,68,68,0.5)]" title="Usuário Inativo"></span>
                @endif
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 line-clamp-1 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $getRecord()->name }}
            </h3>
            <div class="flex items-center gap-1 mt-0.5">
                <x-filament::icon icon="heroicon-m-envelope" class="w-3 h-3 text-gray-400 shrink-0"/>
                <span class="text-[10px] text-gray-500 dark:text-gray-400 truncate font-medium">
                    {{ $getRecord()->email }}
                </span>
            </div>
        </div>
        
        <div class="flex items-center gap-1">
            <button
                type="button"
                wire:click="mountTableAction('edit', {{ $getRecord()->id }})"
                class="p-2 text-gray-400 hover:text-primary-600 dark:text-gray-500 dark:hover:text-primary-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-primary-50 dark:hover:bg-primary-500/10 rounded-lg transition-all"
                title="Editar"
            >
                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
            </button>
        </div>
    </div>

    {{-- Body: Perfil --}}
    <div class="py-2">
        <div class="flex flex-col gap-1.5">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Nível de Acesso</span>
            @php
                $role = $getRecord()->role;
                $roleColor = match($role?->value) {
                    'admin' => 'danger',
                    'promoter' => 'primary',
                    'validator' => 'success',
                    default => 'gray',
                };
            @endphp
            @if($role)
                <div class="flex">
                    <x-filament::badge :color="$roleColor" size="xs" class="font-bold uppercase tracking-tighter">
                        {{ $role->getLabel() }}
                    </x-filament::badge>
                </div>
            @endif
        </div>
    </div>

    {{-- Footer: Status e Cadastro --}}
    <div class="flex justify-between items-center mt-auto pt-3 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Status da Conta</span>
            <div class="mt-0.5">
                @if($getRecord()->is_active)
                    <x-filament::badge color="success" size="xs" class="font-bold">
                        ATIVO
                    </x-filament::badge>
                @else
                    <x-filament::badge color="danger" size="xs" class="font-bold">
                        INATIVO
                    </x-filament::badge>
                @endif
            </div>
        </div>

        <div class="flex flex-col items-end">
            <span class="text-[9px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-tighter">Cadastrado em</span>
            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-bold">
                {{ $getRecord()->created_at->format('d/m/Y') }}
            </span>
        </div>
    </div>
</div>

