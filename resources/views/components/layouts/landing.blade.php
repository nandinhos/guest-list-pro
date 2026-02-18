<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="h-full antialiased"
      x-data="{
          darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage)),
          currentSection: 'hero'
      }"
      x-init="$watch('darkMode', val => localStorage.setItem('theme', val ? 'dark' : 'light')); 
          const observer = new IntersectionObserver((entries) => {
              entries.forEach(entry => {
                  if (entry.isIntersecting) {
                      currentSection = entry.target.id;
                  }
              });
          }, { threshold: 0.3 });
          document.querySelectorAll('section').forEach(section => observer.observe(section));"
      :class="{ 'dark': darkMode }">
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
            background: rgba(99, 102, 241, 0.3);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(99, 102, 241, 0.5);
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
        
        /* Gradient Text */
        .text-gradient-admin {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #ec4899 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Glow Effect */
        .glow-pulse {
            animation: glow-pulse 2s ease-in-out infinite;
        }
        
        @keyframes glow-pulse {
            0%, 100% { box-shadow: 0 0 20px rgba(99, 102, 241, 0.3); }
            50% { box-shadow: 0 0 40px rgba(99, 102, 241, 0.6); }
        }
    </style>
</head>
<body class="h-full landing-bg text-surface-primary font-[Outfit] overflow-x-hidden">

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 glass-subtle border-b border-[var(--glass-border)]">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 glass-subtle rounded-xl">
                    <x-heroicon-s-ticket class="w-6 h-6 text-[var(--color-brand-admin-600)] dark:text-[var(--color-brand-admin-400)]" />
                </div>
                <span class="font-bold text-lg">Guest List Pro</span>
            </div>
            
            <div class="hidden md:flex items-center gap-6">
                <a href="#features" class="text-sm font-medium hover:text-[var(--color-brand-admin-500)] transition-colors">Funcionalidades</a>
                <a href="#benefits" class="text-sm font-medium hover:text-[var(--color-brand-admin-500)] transition-colors">Benefícios</a>
                <a href="#panéis" class="text-sm font-medium hover:text-[var(--color-brand-admin-500)] transition-colors">Painéis</a>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="/login" class="px-4 py-2 bg-[var(--color-brand-admin-500)] hover:bg-[var(--color-brand-admin-600)] text-white text-sm font-medium rounded-lg transition-colors">
                    Entrar
                </a>
                <button @click="darkMode = !darkMode"
                        class="p-2 rounded-lg glass-subtle hover:bg-[var(--glass-bg)] transition-colors">
                    <x-heroicon-o-sun x-show="!darkMode" class="w-5 h-5" />
                    <x-heroicon-o-moon x-show="darkMode" class="w-5 h-5" />
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="py-8 border-t border-[var(--glass-border)] mt-20">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-surface-muted text-sm">
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
