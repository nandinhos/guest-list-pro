{{-- Componente para exibir nome do convidado com indicador de match parcial --}}
@props(['name', 'document', 'isPartialMatch' => false])

<div class="flex items-center gap-2">
    <div class="flex flex-col">
        <span class="font-medium">{{ $name }}</span>
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $document ?? '-' }}</span>
    </div>
    
    @if($isPartialMatch)
        <span 
            class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-amber-700 bg-amber-100 dark:bg-amber-900/30 dark:text-amber-300 rounded-full" 
            title="Match aproximado - verifique a grafia"
        >
            ~
        </span>
    @endif
</div>
