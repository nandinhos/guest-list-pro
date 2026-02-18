<div class="w-full max-w-6xl mx-auto">
    <!-- Logo Section -->
    <div class="text-center mb-12 animate-fade-in-down">
        <div class="inline-flex items-center gap-3 mb-3">
            <div class="p-3 glass-subtle rounded-2xl shadow-xl">
                <x-heroicon-s-ticket class="w-10 h-10 text-[var(--color-brand-admin-600)] dark:text-[var(--color-brand-admin-400)]" />
            </div>
        </div>
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-gradient-admin mb-6">
            Transforme a Gestão do seu Evento <br class="hidden md:block" /> com GuestListPro
        </h1>
        <p class="text-lg md:text-xl text-surface-secondary max-w-3xl mx-auto mb-10 leading-relaxed">
            A plataforma ultra-moderna que redefine o controle de acesso. 
            Do check-in instantâneo via QR Code à inteligência de dados em tempo real, 
            garantimos o sucesso da sua produção com segurança e sofisticação.
        </p>
        
        <!-- CTA Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="/login" 
               class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-gradient-to-r from-[var(--color-brand-admin-500)] to-[var(--color-brand-admin-600)] hover:from-[var(--color-brand-admin-600)] hover:to-[var(--color-brand-admin-700)] text-white font-bold rounded-2xl shadow-2xl hover:shadow-admin-glow transition-all duration-300 hover:-translate-y-1">
                <x-heroicon-o-bolt class="w-6 h-6" />
                Começar Agora
            </a>
            <a href="#features" 
               class="inline-flex items-center justify-center gap-3 px-10 py-5 glass-subtle hover:bg-[var(--glass-bg)] text-surface-primary font-bold rounded-2xl shadow-xl transition-all duration-300 hover:-translate-y-1 border border-[var(--glass-border)]">
                <x-heroicon-o-sparkles class="w-6 h-6" />
                Explorar Soluções
            </a>
        </div>
    </div>

    <!-- Dashboard Preview / Nano Banana Pro Image -->
    <div class="mt-20 relative animate-fade-in-up">
        <div class="absolute -inset-1 bg-gradient-to-r from-[var(--color-brand-admin-500)] to-[var(--color-brand-validator-500)] rounded-[2.5rem] blur opacity-25 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
        <div class="relative glass-card rounded-[2rem] p-3 md:p-6 shadow-2xl overflow-hidden border border-[var(--glass-border)]">
            <div class="aspect-video md:aspect-[21/9] rounded-2xl overflow-hidden shadow-inner relative group">
                <!-- Dashboard Preview -->
                <div class="relative group">
                    <div class="absolute -inset-4 bg-gradient-to-r from-[var(--color-brand-admin-500)]/20 to-[var(--color-brand-promoter-500)]/20 rounded-[2.5rem] blur-2xl opacity-50 group-hover:opacity-100 transition duration-1000"></div>
                    <div class="glass-card p-2 rounded-[2rem] relative overflow-hidden">
                        <img src="{{ asset('assets/images/hero-dashboard-professional.png') }}" 
                             alt="GuestListPro Dashboard" 
                             class="rounded-3xl shadow-2xl w-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
