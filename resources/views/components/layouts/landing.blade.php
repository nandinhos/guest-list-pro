<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full antialiased selection:bg-indigo-500 selection:text-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Guest List Pro') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-950 text-white font-[Outfit] overflow-hidden">
    
    <div class="relative min-h-screen flex flex-col justify-center items-center p-6 isolate">
        
        <!-- Background Effects -->
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-900/20 via-slate-950 to-slate-950"></div>
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[400px] bg-indigo-600/20 blur-[120px] rounded-full opacity-30 pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 w-[800px] h-[400px] bg-emerald-600/10 blur-[120px] rounded-full opacity-20 pointer-events-none"></div>

        {{ $slot }}

        <footer class="mt-16 text-center text-slate-600 text-xs">
            &copy; {{ date('Y') }} Guest List Pro. Todos os direitos reservados.
        </footer>
    </div>

</body>
</html>
