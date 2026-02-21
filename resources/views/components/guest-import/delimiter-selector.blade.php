@props(['model'])

<div class="flex flex-wrap gap-4 items-center mb-6 p-4 glass-subtle rounded-2xl border border-white/5">
    <span class="text-sm font-medium text-surface-secondary">Delimitador:</span>
    
    <label class="inline-flex items-center group cursor-pointer">
        <input type="radio" wire:model.live="{{ $model }}" value="newline" class="hidden peer">
        <span class="px-4 py-2 rounded-xl text-xs font-bold transition-all border border-transparent peer-checked:bg-[var(--color-brand-admin-500)] peer-checked:text-white bg-white/5 text-surface-secondary hover:bg-white/10 group-hover:scale-105">
            Um por linha
        </span>
    </label>

    <label class="inline-flex items-center group cursor-pointer">
        <input type="radio" wire:model.live="{{ $model }}" value="comma" class="hidden peer">
        <span class="px-4 py-2 rounded-xl text-xs font-bold transition-all border border-transparent peer-checked:bg-[var(--color-brand-admin-500)] peer-checked:text-white bg-white/5 text-surface-secondary hover:bg-white/10 group-hover:scale-105">
            Vírgula (,)
        </span>
    </label>

    <label class="inline-flex items-center group cursor-pointer">
        <input type="radio" wire:model.live="{{ $model }}" value="semicolon" class="hidden peer">
        <span class="px-4 py-2 rounded-xl text-xs font-bold transition-all border border-transparent peer-checked:bg-[var(--color-brand-admin-500)] peer-checked:text-white bg-white/5 text-surface-secondary hover:bg-white/10 group-hover:scale-105">
            Ponto e Vírgula (;)
        </span>
    </label>

    <label class="inline-flex items-center group cursor-pointer">
        <input type="radio" wire:model.live="{{ $model }}" value="tab" class="hidden peer">
        <span class="px-4 py-2 rounded-xl text-xs font-bold transition-all border border-transparent peer-checked:bg-[var(--color-brand-admin-500)] peer-checked:text-white bg-white/5 text-surface-secondary hover:bg-white/10 group-hover:scale-105">
            Tabulação
        </span>
    </label>
</div>
