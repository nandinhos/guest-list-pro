<div class="relative">
    <!-- Background Effects -->
    <div class="fixed inset-0 -z-10 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 landing-gradient-top transition-colors duration-500"></div>
        
        <!-- Animated Orbs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-[var(--color-brand-admin-500)]/20 rounded-full blur-[120px] animate-float"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-[var(--color-brand-promoter-500)]/20 rounded-full blur-[100px] animate-float" style="animation-delay: -2s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 w-64 h-64 bg-[var(--color-brand-validator-500)]/15 rounded-full blur-[80px] animate-float" style="animation-delay: -4s;"></div>
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
            <div class="glass-card inline-block p-10 rounded-3xl fade-in-section">
                <h3 class="text-3xl md:text-4xl font-bold text-gradient-admin mb-4">
                    Pronto para começar?
                </h3>
                <p class="text-surface-secondary mb-8 max-w-md mx-auto">
                    Acesse o sistema agora e revolucione a gestão dos seus eventos.
                </p>
                <a href="/login" 
                   class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-gradient-to-r from-[var(--color-brand-admin-500)] to-[var(--color-brand-admin-600)] hover:from-[var(--color-brand-admin-600)] hover:to-[var(--color-brand-admin-700)] text-white font-semibold rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 hover:scale-105">
                    <x-heroicon-o-arrow-right-on-rectangle class="w-6 h-6" />
                    Entrar Agora
                </a>
            </div>
        </div>
    </section>
</div>
