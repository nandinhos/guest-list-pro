<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full antialiased"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage))
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light'))"
      :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Guest List Pro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full landing-bg text-surface-primary font-[Outfit] overflow-hidden transition-colors duration-300">

    <div class="relative min-h-screen flex flex-col justify-center items-center p-6 isolate">

        <!-- Background Effects -->
        <div class="absolute inset-0 -z-10 landing-gradient-top transition-colors duration-500"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] landing-glow-admin blur-[120px] rounded-full opacity-30 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[800px] h-[400px] landing-glow-validator blur-[120px] rounded-full opacity-20 pointer-events-none"></div>

        {{ $slot }}

        <footer class="mt-16 text-center text-surface-muted text-xs">
            &copy; {{ date('Y') }} Guest List Pro. Todos os direitos reservados.
        </footer>
    </div>

@livewireScripts
</body>
</html>
