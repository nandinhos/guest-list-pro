<div>
    <!-- Theme Toggle -->
    <button @click="darkMode = !darkMode"
            class="fixed top-6 right-6 z-50 p-2 rounded-full glass-subtle hover:bg-[var(--glass-bg)] transition-all shadow-lg group">
        <div class="relative w-6 h-6">
            <x-heroicon-o-sun x-show="!darkMode" class="w-6 h-6 absolute inset-0 rotate-0 transition-transform duration-500 group-hover:rotate-180 text-[var(--color-warning-500)]" />
            <x-heroicon-o-moon x-show="darkMode" class="w-6 h-6 absolute inset-0 rotate-0 transition-transform duration-500 group-hover:-rotate-12 group-hover:scale-110 text-[var(--color-brand-admin-400)]" />
        </div>
    </button>
    <!-- Logo -->
    <div class="mb-12 text-center relative z-10 animate-fade-in-down">
        <div class="inline-flex items-center gap-3 mb-2">
            <div class="p-2 glass-subtle rounded-xl shadow-xl">
                <x-heroicon-s-ticket class="w-8 h-8 text-[var(--color-brand-admin-600)] dark:text-[var(--color-brand-admin-400)]" />
            </div>
            <span class="text-3xl font-bold tracking-tight text-gradient-admin">
                Guest List Pro
            </span>
        </div>
        <p class="text-surface-secondary text-sm font-medium tracking-wide uppercase opacity-80">
            Sistema de Gestão de Eventos
        </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 max-w-6xl w-full relative z-10 px-4">

        <!-- ADMIN CARD -->
        <a href="/admin" class="role-card role-card-admin">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon">
                    <x-heroicon-o-shield-check class="w-8 h-8" />
                </div>
                <h3 class="role-card-title">Administrador</h3>
                <p class="role-card-description">
                    Gestão completa de eventos, setores, usuários e relatórios gerenciais avançados.
                </p>
                <span class="role-card-tag">
                    Acessar Painel &rarr;
                </span>
            </div>
        </a>

        <!-- PROMOTER CARD -->
        <a href="/promoter" class="role-card role-card-promoter">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon">
                    <x-heroicon-o-user-group class="w-8 h-8" />
                </div>
                <h3 class="role-card-title">Promoter</h3>
                <p class="role-card-description">
                    Cadastre seus convidados, gerencie listas VIP e acompanhe suas cotas em tempo real.
                </p>
                <span class="role-card-tag">
                    Gerenciar Listas &rarr;
                </span>
            </div>
        </a>

        <!-- VALIDATOR CARD -->
        <a href="/validator" class="role-card role-card-validator">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon">
                    <x-heroicon-o-qr-code class="w-8 h-8" />
                </div>
                <h3 class="role-card-title">Validador</h3>
                <p class="role-card-description">
                    Terminal rápido para check-in de convidados na portaria do evento.
                </p>
                <span class="role-card-tag">
                    Abrir Terminal &rarr;
                </span>
            </div>
        </a>

        <!-- BILHETERIA CARD -->
        <a href="/bilheteria" class="role-card role-card-bilheteria">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon">
                    <x-heroicon-o-ticket class="w-8 h-8" />
                </div>
                <h3 class="role-card-title">Bilheteria</h3>
                <p class="role-card-description">
                    Venda de ingressos, controle de bilhetes e gestão de entradas.
                </p>
                <span class="role-card-tag">
                    Acessar Bilheteria &rarr;
                </span>
            </div>
        </a>

    </div>
</div>
