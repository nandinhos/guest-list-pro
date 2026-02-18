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
                class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-gradient-to-r from-[var(--color-brand-admin-500)] to-[var(--color-brand-admin-600)] hover:from-[var(--color-brand-admin-600)] hover:to-[var(--color-brand-admin-700)] text-white font-semibold rounded-xl shadow-lg hover:shadow-admin-glow transition-all duration-300 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>
                    <x-heroicon-m-arrow-right-on-rectangle class="w-5 h-5 inline-block mr-1" />
                    Entrar
                </span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Autenticando...
                </span>
            </button>

        </form>
    </div>

    <!-- Badges de painéis disponíveis -->
    <div class="mt-6 flex flex-wrap justify-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 glass-subtle rounded-full text-xs text-surface-muted border border-[var(--glass-border)]">
            <x-heroicon-m-shield-check class="w-3.5 h-3.5 text-red-400" />
            Admin
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 glass-subtle rounded-full text-xs text-surface-muted border border-[var(--glass-border)]">
            <x-heroicon-m-user-group class="w-3.5 h-3.5 text-yellow-400" />
            Promoter
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 glass-subtle rounded-full text-xs text-surface-muted border border-[var(--glass-border)]">
            <x-heroicon-m-check-badge class="w-3.5 h-3.5 text-green-400" />
            Validador
        </span>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 glass-subtle rounded-full text-xs text-surface-muted border border-[var(--glass-border)]">
            <x-heroicon-m-ticket class="w-3.5 h-3.5 text-orange-400" />
            Bilheteria
        </span>
    </div>
</div>
