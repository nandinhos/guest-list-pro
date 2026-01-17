@props([
    'icon' => 'heroicon-o-inbox',
    'title' => 'Nenhum item encontrado',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-6 text-center']) }}>
    {{-- Ícone --}}
    <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
        <x-dynamic-component :component="$icon" class="w-8 h-8 text-slate-400 dark:text-slate-500" />
    </div>

    {{-- Título --}}
    <h3 class="text-lg font-semibold text-slate-900 dark:text-white mb-1">
        {{ $title }}
    </h3>

    {{-- Descrição --}}
    @if($description)
        <p class="text-sm text-slate-500 dark:text-slate-400 max-w-sm mb-6">
            {{ $description }}
        </p>
    @endif

    {{-- Action Button ou Slot --}}
    @isset($action)
        {{ $action }}
    @else
        @if($actionLabel && $actionUrl)
            <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-indigo-500 rounded-xl shadow-lg shadow-indigo-500/25 hover:shadow-xl hover:shadow-indigo-500/30 transition-all duration-200">
                <x-heroicon-o-plus class="w-4 h-4" />
                {{ $actionLabel }}
            </a>
        @endif
    @endisset
</div>
