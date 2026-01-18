<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="scroll-smooth"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage))
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Guest List Pro') }} - Design System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600|jetbrains-mono:400,500" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen landing-bg text-surface-primary font-[Outfit] transition-colors duration-300">

    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 landing-gradient-top transition-colors duration-500"></div>
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] landing-glow-admin blur-[120px] rounded-full opacity-20 pointer-events-none"></div>
    <div class="fixed bottom-0 right-0 w-[800px] h-[400px] landing-glow-validator blur-[120px] rounded-full opacity-15 pointer-events-none"></div>

    {{ $slot }}

@livewireScripts
</body>
</html>
