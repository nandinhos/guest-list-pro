@props([
    'icon' => null,
    'title' => 'Nenhum item encontrado',
    'description' => null,
    'actionLabel' => null,
    'actionUrl' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-12 px-6 text-center']) }}>
    {{-- Ícone --}}
    @if($icon)
        <div class="w-16 h-16 rounded-2xl stat-icon-admin flex items-center justify-center mb-4">
            @svg($icon, 'w-8 h-8')
        </div>
    @else
        <div class="w-16 h-16 rounded-2xl stat-icon-admin flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" /></svg>
        </div>
    @endif

    {{-- Título --}}
    <h3 class="text-lg font-semibold text-surface-primary mb-1">
        {{ $title }}
    </h3>

    {{-- Descrição --}}
    @if($description)
        <p class="text-sm text-surface-secondary max-w-sm mb-6">
            {{ $description }}
        </p>
    @endif

    {{-- Action Button ou Slot --}}
    @isset($action)
        {{ $action }}
    @else
        @if($actionLabel && $actionUrl)
            <a href="{{ $actionUrl }}" class="btn-primary inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-xl">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ $actionLabel }}
            </a>
        @endif
    @endisset
</div>
