<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full antialiased"
      x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage)) }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — {{ config('app.name', 'Guest List Pro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-surface-base text-surface-primary selection:bg-[var(--color-brand-admin-500)]/30 antialiased overflow-hidden">

    <!-- Background animado idêntico à landing -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[var(--color-brand-admin-500)]/10 blur-[100px] animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-[var(--color-brand-promoter-500)]/10 blur-[100px] animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full bg-[var(--color-brand-validator-500)]/5 blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Conteúdo centralizado -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-12">

        <!-- Logo -->
        <div class="mb-10 text-center animate-fade-in-up">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                <div class="p-2.5 glass shadow-xl rounded-2xl group-hover:scale-105 transition-all duration-300 group-hover:shadow-admin-glow">
                    <x-heroicon-s-ticket class="w-8 h-8 text-[var(--color-brand-admin-500)]" />
                </div>
                <span class="text-3xl font-bold font-display tracking-tight text-gradient-admin transition-all duration-300 group-hover:tracking-normal">GuestListPro</span>
            </a>
        </div>

        <!-- Slot do componente -->
        <main class="w-full max-w-md animate-fade-in-up" style="animation-delay: 150ms;">
            {{ $slot }}
        </main>

    </div>

    @livewireScripts
</body>
</html>
