<div>
    <!-- Logo -->
    <div class="mb-12 text-center relative z-10 animate-fade-in-down">
        <div class="inline-flex items-center gap-3 mb-2">
            <div class="p-2 bg-white/10 rounded-xl backdrop-blur-md border border-white/10 shadow-xl">
                <x-heroicon-s-ticket class="w-8 h-8 text-indigo-400" />
            </div>
            <span class="text-3xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-white/60">
                Guest List Pro
            </span>
        </div>
        <p class="text-slate-400 text-sm font-medium tracking-wide uppercase opacity-80">
            Sistema de Gestão de Eventos
        </p>
    </div>

    <!-- Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl w-full relative z-10 px-4">
        
        <!-- ADMIN CARD -->
        <a href="/admin" class="group relative p-1 rounded-3xl bg-gradient-to-b from-white/10 to-white/5 hover:from-indigo-500/50 hover:to-indigo-600/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-indigo-500/20">
            <div class="absolute inset-0 rounded-3xl bg-slate-950/80 backdrop-blur-xl transition-colors duration-300 group-hover:bg-slate-950/60"></div>
            
            <div class="relative h-full p-8 flex flex-col items-center text-center rounded-[20px] border border-white/5 group-hover:border-indigo-500/30 transition-colors">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/20 flex items-center justify-center mb-6 text-indigo-400 group-hover:bg-indigo-500 group-hover:text-white transition-all duration-300 shadow-inner group-hover:shadow-indigo-500/50">
                    <x-heroicon-o-shield-check class="w-8 h-8" />
                </div>
                
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-indigo-200">Administrador</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-6 group-hover:text-slate-200">
                    Gestão completa de eventos, setores, usuários e relatórios gerenciais avançados.
                </p>
                
                <span class="mt-auto py-2 px-4 rounded-full text-xs font-semibold bg-white/5 text-slate-300 border border-white/5 group-hover:bg-indigo-500/20 group-hover:border-indigo-500/30 group-hover:text-indigo-200 transition-all">
                    Acessar Painel &rarr;
                </span>
            </div>
        </a>

        <!-- PROMOTER CARD -->
        <a href="/promoter" class="group relative p-1 rounded-3xl bg-gradient-to-b from-white/10 to-white/5 hover:from-purple-500/50 hover:to-purple-600/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-purple-500/20">
            <div class="absolute inset-0 rounded-3xl bg-slate-950/80 backdrop-blur-xl transition-colors duration-300 group-hover:bg-slate-950/60"></div>
            
            <div class="relative h-full p-8 flex flex-col items-center text-center rounded-[20px] border border-white/5 group-hover:border-purple-500/30 transition-colors">
                <div class="w-16 h-16 rounded-2xl bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400 group-hover:bg-purple-500 group-hover:text-white transition-all duration-300 shadow-inner group-hover:shadow-purple-500/50">
                    <x-heroicon-o-user-group class="w-8 h-8" />
                </div>
                
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-purple-200">Promoter</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-6 group-hover:text-slate-200">
                    Cadastre seus convidados, gerencie listas VIP e acompanhe suas cotas em tempo real.
                </p>
                
                <span class="mt-auto py-2 px-4 rounded-full text-xs font-semibold bg-white/5 text-slate-300 border border-white/5 group-hover:bg-purple-500/20 group-hover:border-purple-500/30 group-hover:text-purple-200 transition-all">
                    Gerenciar Listas &rarr;
                </span>
            </div>
        </a>

        <!-- VALIDATOR CARD -->
        <a href="/validator" class="group relative p-1 rounded-3xl bg-gradient-to-b from-white/10 to-white/5 hover:from-emerald-500/50 hover:to-emerald-600/50 transition-all duration-300 hover:scale-[1.02] hover:shadow-2xl hover:shadow-emerald-500/20">
            <div class="absolute inset-0 rounded-3xl bg-slate-950/80 backdrop-blur-xl transition-colors duration-300 group-hover:bg-slate-950/60"></div>
            
            <div class="relative h-full p-8 flex flex-col items-center text-center rounded-[20px] border border-white/5 group-hover:border-emerald-500/30 transition-colors">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 flex items-center justify-center mb-6 text-emerald-400 group-hover:bg-emerald-500 group-hover:text-white transition-all duration-300 shadow-inner group-hover:shadow-emerald-500/50">
                    <x-heroicon-o-qr-code class="w-8 h-8" />
                </div>
                
                <h3 class="text-xl font-semibold text-white mb-2 group-hover:text-emerald-200">Validador</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-6 group-hover:text-slate-200">
                    Terminal rápido para check-in de convidados na portaria do evento.
                </p>
                
                <span class="mt-auto py-2 px-4 rounded-full text-xs font-semibold bg-white/5 text-slate-300 border border-white/5 group-hover:bg-emerald-500/20 group-hover:border-emerald-500/30 group-hover:text-emerald-200 transition-all">
                    Abrir Terminal &rarr;
                </span>
            </div>
        </a>

    </div>
</div>
