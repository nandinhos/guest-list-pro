<div class="w-full max-w-md">
    <!-- Card principal -->
    <div class="glass-card rounded-3xl p-8 shadow-2xl border border-[var(--glass-border)]">

        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-surface-primary mb-1">Bem-vindo de volta</h1>
            <p class="text-sm text-surface-secondary">Entre com suas credenciais para acessar o painel</p>
        </div>

        <!-- Formulário -->
        <form wire:submit="authenticate" class="space-y-5" novalidate>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-surface-secondary mb-1.5">
                    E-mail
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <x-heroicon-o-envelope class="w-5 h-5 text-surface-muted" />
                    </div>
                    <input
                        id="email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        placeholder="seu@email.com"
                        class="w-full pl-10 pr-4 py-3 glass-subtle rounded-xl border border-[var(--glass-border)] text-surface-primary placeholder-surface-muted focus:outline-none focus:ring-2 focus:ring-[var(--color-brand-admin-500)]/50 focus:border-[var(--color-brand-admin-500)] transition-all duration-200 bg-transparent @error('email') border-red-500 focus:ring-red-500/50 @enderror"
                    >
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                        <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Senha -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-sm font-medium text-surface-secondary">
                        Senha
                    </label>
                    <a href="#" class="text-xs text-[var(--color-brand-admin-400)] hover:text-[var(--color-brand-admin-300)] transition-colors duration-200">
                        Esqueci minha senha
                    </a>
                </div>
                <div class="relative" x-data="{ show: false }">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <x-heroicon-o-lock-closed class="w-5 h-5 text-surface-muted" />
                    </div>
                    <input
                        id="password"
                        :type="show ? 'text' : 'password'"
                        wire:model="password"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="w-full pl-10 pr-12 py-3 glass-subtle rounded-xl border border-[var(--glass-border)] text-surface-primary placeholder-surface-muted focus:outline-none focus:ring-2 focus:ring-[var(--color-brand-admin-500)]/50 focus:border-[var(--color-brand-admin-500)] transition-all duration-200 bg-transparent @error('password') border-red-500 focus:ring-red-500/50 @enderror"
                    >
                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-surface-muted hover:text-surface-secondary transition-colors duration-200">
                        <x-heroicon-o-eye class="w-5 h-5" x-show="!show" />
                        <x-heroicon-o-eye-slash class="w-5 h-5" x-show="show" x-cloak />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                        <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5 flex-shrink-0" />
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Lembrar-me -->
            <div class="flex items-center gap-2.5">
                <input
                    id="remember"
                    type="checkbox"
                    wire:model="remember"
                    class="w-4 h-4 rounded border-[var(--glass-border)] bg-transparent text-[var(--color-brand-admin-500)] focus:ring-[var(--color-brand-admin-500)]/50 cursor-pointer"
                >
                <label for="remember" class="text-sm text-surface-secondary cursor-pointer select-none">
                    Lembrar-me por 30 dias
                </label>
            </div>

            <!-- Botão de submit -->
            <button
                type="submit"
                class="w-full relative group overflow-hidden flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-[var(--color-brand-admin-500)] to-[var(--color-brand-admin-600)] hover:from-[var(--color-brand-admin-600)] hover:to-[var(--color-brand-admin-700)] text-white font-semibold rounded-xl shadow-lg hover:shadow-admin-glow transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                wire:loading.attr="disabled"
            >
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-500"></div>
                <span class="relative flex items-center justify-center gap-2" wire:loading.remove>
                    <x-heroicon-m-arrow-right-on-rectangle class="w-5 h-5" />
                    Entrar no Painel
                </span>
                <span wire:loading class="relative flex items-center justify-center gap-3">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Autenticando...
                </span>
            </button>
        </form>
    </div>

    <!-- Badges Elegantes -->
    <div class="mt-8 flex flex-wrap justify-center gap-4 animate-fade-in-up" style="animation-delay: 300ms;">
        <div class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-wider text-surface-muted/60">
            Acesso disponível
            <div class="h-px w-8 bg-[var(--glass-border)]"></div>
        </div>
        <div class="flex flex-wrap justify-center gap-1.5">
            <span class="px-2.5 py-1 glass-subtle rounded-lg text-[10px] font-bold text-surface-primary border border-[var(--glass-border)] flex items-center gap-1.5 shadow-sm transform hover:scale-105 transition-transform duration-200">
                <span class="w-1.5 h-1.5 rounded-full bg-red-400 shadow-[0_0_8px_rgba(248,113,113,0.8)]"></span>
                Admin
            </span>
            <span class="px-2.5 py-1 glass-subtle rounded-lg text-[10px] font-bold text-surface-primary border border-[var(--glass-border)] flex items-center gap-1.5 shadow-sm transform hover:scale-105 transition-transform duration-200">
                <span class="w-1.5 h-1.5 rounded-full bg-yellow-400 shadow-[0_0_8px_rgba(250,204,21,0.8)]"></span>
                Promoter
            </span>
            <span class="px-2.5 py-1 glass-subtle rounded-lg text-[10px] font-bold text-surface-primary border border-[var(--glass-border)] flex items-center gap-1.5 shadow-sm transform hover:scale-105 transition-transform duration-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                Validador
            </span>
            <span class="px-2.5 py-1 glass-subtle rounded-lg text-[10px] font-bold text-surface-primary border border-[var(--glass-border)] flex items-center gap-1.5 shadow-sm transform hover:scale-105 transition-transform duration-200">
                <span class="w-1.5 h-1.5 rounded-full bg-orange-400 shadow-[0_0_8_rgba(251,146,60,0.8)]"></span>
                Bilheteria
            </span>
        </div>
    </div>
</div>
