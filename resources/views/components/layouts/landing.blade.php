<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Guest List Pro') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700|inter:400,500,600" rel="stylesheet" />

    <!-- Smooth Scroll -->
    <script src="https://cdn.jsdelivr.net/npm/lucide@latest/dist/umd/lucide.js"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] { display: none !important; }
        
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(77, 94, 246, 0.3);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(77, 94, 246, 0.5);
        }
        
        /* Animation Classes */
        .fade-in-section {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.8s ease-out, transform 0.8s ease-out;
        }
        
        .fade-in-section.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .stagger-1 { transition-delay: 0.1s; }
        .stagger-2 { transition-delay: 0.2s; }
        .stagger-3 { transition-delay: 0.3s; }
        .stagger-4 { transition-delay: 0.4s; }
        
        /* Floating Animation */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</head>
<body class="h-full blue-landing-bg text-surface-primary font-[Outfit] overflow-x-hidden">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 blue-nav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="p-1.5 sm:p-2 bg-[var(--blue-theme-accent)]/10 rounded-xl">
                    <x-heroicon-s-ticket class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--blue-theme-accent)]" />
                </div>
                <span class="font-bold text-base sm:text-lg tracking-tight text-[var(--blue-theme-dark)]">Guest List Pro</span>
            </div>
            
            <div class="hidden md:flex items-center gap-6">
                <a href="#features" class="text-sm font-medium text-[var(--blue-theme-muted)] hover:text-[var(--blue-theme-accent)] transition-colors">Funcionalidades</a>
                <a href="#benefits" class="text-sm font-medium text-[var(--blue-theme-muted)] hover:text-[var(--blue-theme-accent)] transition-colors">Benefícios</a>
                <a href="#paineis" class="text-sm font-medium text-[var(--blue-theme-muted)] hover:text-[var(--blue-theme-accent)] transition-colors">Painéis</a>
            </div>
            
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="/login" class="px-3 py-1.5 sm:px-4 sm:py-2 bg-[var(--blue-theme-accent)] hover:bg-[var(--blue-theme-accent)]/90 text-white text-sm font-medium rounded-lg transition-colors">
                    Entrar
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-8 border-t border-[var(--blue-theme-border)] mt-20">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-sm text-[var(--blue-theme-muted)]">
                &copy; {{ date('Y') }} Guest List Pro. Todos os direitos reservados.
            </p>
        </div>
    </footer>

    <!-- Intersection Observer Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.fade-in-section').forEach(section => {
                observer.observe(section);
            });
        });
    </script>

    @livewireScripts
</body>
</html>
