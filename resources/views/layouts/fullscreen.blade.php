<x-filament-panels::layout.base :livewire="$livewire">
    <div class="fi-layout flex min-h-screen w-full flex-col">
        {{-- Topbar minimalista (opcional, apenas logout ou branding) --}}
        <div class="fi-topbar sticky top-0 z-20 flex h-16 items-center px-4 bg-white/80 dark:bg-gray-900/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
             <div class="flex items-center gap-x-4">
                @if ($homeUrl = filament()->getHomeUrl())
                    <a href="{{ $homeUrl }}" class="flex items-center gap-x-2">
                        {{-- Logo ou Nome --}}
                        <span class="text-lg font-bold tracking-tight text-gray-950 dark:text-white sm:text-xl truncate max-w-[160px] sm:max-w-none">
                            {{ filament()->getBrandName() }}
                        </span>
                    </a>
                @endif
             </div>

             <div class="ml-auto flex items-center gap-x-4">
                {{-- User Menu / Logout --}}
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200 truncate max-w-[80px] sm:max-w-[120px]">
                        {{ auth()->user()->name }}
                    </span>
                    <form action="{{ filament()->getLogoutUrl() }}" method="post">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            Sair
                        </button>
                    </form>
                </div>
             </div>
        </div>

        {{-- Main Content Area --}}
        <main class="flex-grow w-full px-4 py-8 md:px-6 lg:px-8">
            <div class="mx-auto max-w-7xl">
                {{ $slot }}
            </div>
        </main>
        
        {{-- Footer se necessário --}}
    </div>
</x-filament-panels::layout.base>
