@props(['model'])

<div class="flex flex-wrap gap-2 items-center">
    <span class="text-xs font-medium text-gray-500 dark:text-gray-400 mr-2">Delimitador:</span>
    
    <div class="flex p-1 bg-gray-100 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10">
        @foreach([
            'newline' => 'Linha',
            'comma' => ',',
            'semicolon' => ';',
            'tab' => 'Tab'
        ] as $value => $label)
            <label class="cursor-pointer">
                <input type="radio" wire:model.live="{{ $model }}" value="{{ $value }}" class="hidden peer">
                <span class="px-3 py-1 rounded-md text-[10px] font-bold transition-all peer-checked:bg-white dark:peer-checked:bg-gray-800 peer-checked:text-primary-600 dark:peer-checked:text-primary-400 peer-checked:shadow-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    {{ $label }}
                </span>
            </label>
        @endforeach
    </div>
</div>
