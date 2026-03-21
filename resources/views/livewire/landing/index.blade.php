<div class="relative">
    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 bg-[var(--blue-theme-bg)]"></div>
        
        <!-- Animated Orbs - Blue Theme -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 blue-glow-orb-1 animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 blue-glow-orb-2 animate-float" style="animation-delay: -2s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-64 h-64 blue-glow-orb-3 animate-float" style="animation-delay: -4s;"></div>
    </div>

    <!-- Hero Section -->
    <livewire:landing.hero />

    <!-- Features Section -->
    <livewire:landing.features />

    <!-- Benefits Section -->
    <livewire:landing.benefits />

    <!-- Role Cards Section -->
    <livewire:landing.role-cards />

    <!-- CTA Final -->
    <section class="py-24">
        <div class="w-full max-w-4xl mx-auto px-6 text-center">
            <div class="blue-glass-card-lg inline-block p-6 sm:p-10 fade-in-section mx-4 sm:mx-0">
                <h3 class="text-3xl md:text-4xl font-bold blue-text-gradient mb-4">
                    Pronto para começar?
                </h3>
                <p class="text-[var(--blue-theme-muted)] mb-8 max-w-md mx-auto">
                    Acesse o sistema agora e revolucione a gestão dos seus eventos.
                </p>
                <a href="/login" 
                   class="inline-flex items-center justify-center gap-3 px-6 py-4 md:px-10 md:py-5 blue-btn-primary">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6" />
                    Entrar Agora
                </a>
            </div>
        </div>
    </section>
</div>
