<div class="w-full max-w-6xl mx-auto px-4 sm:px-6 md:px-8 pt-6 md:pt-16">
    <!-- Logo Section -->
    <div class="text-center mb-12 animate-fade-in-down">
        <div class="inline-flex items-center gap-3 mb-3">
            <div class="p-3 bg-[var(--blue-theme-accent)]/10 rounded-2xl shadow-lg">
                <x-heroicon-s-ticket class="w-10 h-10 text-[var(--blue-theme-accent)]" />
            </div>
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight blue-text-gradient mb-6 leading-tight md:leading-tight">
            Transforme a Gestão do seu Evento <br class="hidden md:block" /> com GuestListPro
        </h1>
        <p class="text-base md:text-xl text-[var(--blue-theme-muted)] max-w-3xl mx-auto mb-10 leading-relaxed px-2 sm:px-0">
            A plataforma ultra-moderna que redefine o controle de acesso. 
            Do check-in instantâneo via QR Code à inteligência de dados em tempo real, 
            garantimos o sucesso da sua produção com segurança e sofisticação.
        </p>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/login" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-6 py-4 md:px-10 md:py-5 blue-btn-primary">
                <x-heroicon-o-bolt class="w-6 h-6" />
                Começar Agora
            </a>
            <a href="#features" 
               class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-6 py-4 md:px-10 md:py-5 blue-btn-secondary">
                <x-heroicon-o-sparkles class="w-6 h-6" />
                Explorar Soluções
            </a>
        </div>
    </div>

    <!-- Dashboard Preview / Nano Banana Pro Image -->
    <div class="mt-20 relative animate-fade-in-up">
        <div class="absolute -inset-1 bg-gradient-to-r from-[var(--blue-theme-accent)] to-[var(--blue-theme-green)] rounded-[2.5rem] blur opacity-25 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
        <div class="relative blue-glass-card-lg p-3 md:p-6 shadow-2xl overflow-hidden">
            <div class="aspect-video md:aspect-[21/9] rounded-2xl overflow-hidden shadow-inner relative group">
                <!-- Dashboard Preview -->
                <div class="relative group">
                    <div class="absolute -inset-4 bg-gradient-to-r from-[var(--blue-theme-accent)]/20 to-[var(--blue-theme-accent)]/20 rounded-[2.5rem] blur-2xl opacity-50 group-hover:opacity-100 transition duration-1000"></div>
                    <div class="blue-glass-card-lg p-2 relative overflow-hidden">
                        <img src="{{ asset('assets/images/hero-dashboard-professional.png') }}" 
                             alt="GuestListPro Dashboard" 
                             class="rounded-3xl shadow-2xl w-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
