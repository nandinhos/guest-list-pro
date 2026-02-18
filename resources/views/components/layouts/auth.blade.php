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
<body class="h-full bg-surface-base text-surface-primary overflow-hidden">

    <!-- Background animado idêntico à landing -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full bg-[var(--color-brand-admin-500)]/10 blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 rounded-full bg-[var(--color-brand-promoter-500)]/10 blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-[var(--color-brand-validator-500)]/5 blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>

    <!-- Conteúdo centralizado -->
    <div class="relative z-10 min-h-screen flex flex-col items-center justify-center px-4 py-12">

        <!-- Logo -->
        <a href="{{ route('home') }}" class="flex items-center gap-3 mb-10 group">
            <div class="p-2.5 glass-subtle rounded-xl shadow-lg group-hover:scale-105 transition-transform duration-200">
                <x-heroicon-s-ticket class="w-8 h-8 text-[var(--color-brand-admin-500)]" />
            </div>
            <span class="text-2xl font-bold text-gradient-admin">GuestListPro</span>
        </a>

        <!-- Slot do componente -->
        {{ $slot }}

    </div>

    @livewireScripts
</body>
</html>
