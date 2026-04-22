@php
    $record = $getRecord();
    $isCheckedIn = $record->is_checked_in;
    $promoterName = $record->promoter?->name ?? 'N/A';
    $sectorName = $record->sector?->name ?? 'Sem Setor';
    $eventName = $record->event?->name ?? 'Sem Evento';
    $checkedInAt = $record->checked_in_at;
@endphp

<div class="flex flex-col justify-between min-h-[190px] w-full p-4 bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 hover:shadow-xl hover:translate-y-[-2px] relative overflow-hidden group">
    <!-- Glow effect on hover -->
    <div class="absolute -right-12 -top-12 w-32 h-32 bg-primary-500/5 rounded-full blur-3xl group-hover:bg-primary-500/15 transition-all duration-700"></div>
    <div class="absolute -left-12 -bottom-12 w-32 h-32 bg-secondary-500/5 rounded-full blur-3xl group-hover:bg-secondary-500/10 transition-all duration-700"></div>

    {{-- Header: Nome e Badge de Status --}}
    <div class="flex justify-between items-start gap-3">
        <div class="flex flex-col flex-1 min-w-0">
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="text-[9px] font-bold uppercase tracking-widest text-primary-600 dark:text-primary-400 opacity-80">
                    CONVIDADO
                </span>
                @if($record->isCompanion())
                    <span class="px-1.5 py-0.5 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[8px] font-black uppercase rounded-full border border-amber-100 dark:border-amber-500/20">
                        +1
                    </span>
                @endif
            </div>
            <h3 class="text-base font-black text-gray-900 dark:text-gray-100 line-clamp-1 leading-tight group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                {{ $record->name }}
            </h3>
            <div class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500 mt-0.5">
                <x-filament::icon icon="heroicon-m-identification" class="w-3 h-3 shrink-0"/>
                <span class="text-[10px] font-medium tracking-tight truncate">{{ $record->document ?: 'SEM DOCUMENTO' }}</span>
            </div>
        </div>
        
        <div class="flex items-center gap-2 shrink-0">
            @if($isCheckedIn)
                <div class="p-2.5 bg-success-50 dark:bg-success-500/10 rounded-2xl border border-success-100 dark:border-success-500/20 shadow-sm" title="Check-in Realizado">
                    <x-filament::icon icon="heroicon-m-check-badge" class="h-6 w-6 text-success-600 dark:text-success-400 animate-pulse-slow" />
                </div>
            @else
                <button
                    type="button"
                    wire:click="mountTableAction('checkIn', {{ $record->id }})"
                    class="p-2.5 text-gray-400 hover:text-success-600 dark:text-gray-500 dark:hover:text-success-400 bg-gray-50 dark:bg-gray-800/50 hover:bg-success-50 dark:hover:bg-success-500/10 rounded-2xl border border-transparent hover:border-success-200 dark:hover:border-success-500/30 transition-all duration-300 shadow-sm group/btn"
                    title="Realizar Check-in"
                >
                    <x-filament::icon icon="heroicon-m-check-circle" class="h-6 w-6 group-hover/btn:scale-110 transition-transform" />
                </button>
            @endif
        </div>
    </div>

    {{-- Body: Detalhes --}}
    <div class="py-4 grid grid-cols-2 gap-3">
        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Setor / Local</span>
            <div class="flex items-center gap-1.5">
                <div class="w-1.5 h-1.5 rounded-full bg-info-500 shadow-[0_0_5px_rgba(var(--info-500),0.5)]"></div>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">{{ $sectorName }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-1">
            <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Promoter</span>
            <div class="flex items-center gap-1.5">
                <x-filament::icon icon="heroicon-m-user-circle" class="w-3.5 h-3.5 text-gray-400 shrink-0"/>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate">{{ $promoterName }}</span>
            </div>
        </div>
    </div>

    {{-- Footer: Status Bar e Ações --}}
    <div class="flex justify-between items-center mt-auto pt-4 border-t border-gray-50 dark:border-gray-800/50">
        <div class="flex flex-col">
            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">Status</span>
            @if($isCheckedIn)
                <div class="flex items-center gap-2">
                    <span class="text-xs font-black text-success-600 dark:text-success-400">PRESENTE</span>
                    <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500">{{ $checkedInAt?->format('H:i') }}</span>
                </div>
            @else
                <span class="text-xs font-black text-amber-500 dark:text-amber-400">PENDENTE</span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            @if($isCheckedIn)
                <button
                    type="button"
                    wire:click="mountTableAction('undoCheckIn', {{ $record->id }})"
                    class="p-2 text-warning-600 hover:text-white dark:text-warning-400 bg-warning-50 dark:bg-warning-500/10 hover:bg-warning-600 dark:hover:bg-warning-500 rounded-xl transition-all shadow-sm group/undo border border-warning-100 dark:border-warning-500/20"
                    title="Estornar Check-in"
                >
                    <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4 group-hover/undo:rotate-180 transition-transform duration-700" />
                </button>
            @endif
            
            <button
                type="button"
                wire:click="mountTableAction('edit', {{ $record->id }})"
                class="p-2 text-primary-600 hover:text-white dark:text-primary-400 bg-primary-50 dark:bg-primary-500/10 hover:bg-primary-600 dark:hover:bg-primary-500 rounded-xl transition-all shadow-sm border border-primary-100 dark:border-primary-500/20"
                title="Editar"
            >
                <x-filament::icon icon="heroicon-m-pencil-square" class="h-4 w-4" />
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }
    .animate-pulse-slow {
        animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
