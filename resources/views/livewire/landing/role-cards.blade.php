<div id="paineis" class="w-full max-w-6xl mx-auto py-16 scroll-mt-20">
    <div class="text-center mb-16">
        <h2 class="text-3xl md:text-5xl font-extrabold text-gradient-admin mb-6">
            Ecosystem de Gestão
        </h2>
        <p class="text-surface-secondary max-w-2xl mx-auto text-lg">
            Quatro painéis especializados projetados para máxima eficiência operacional.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- ADMIN CARD -->
        <a href="/login?role=admin" class="role-card role-card-admin group">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon group-hover:scale-110 transition-all duration-500">
                    <x-heroicon-o-shield-check class="w-10 h-10" />
                </div>
                <h3 class="role-card-title">Central Admin</h3>
                <p class="role-card-description">
                    Controle total da infraestrutura, auditoria financeira e BI avançado para tomadores de decisão.
                </p>
                <span class="role-card-tag group-hover:translate-x-2 transition-transform">
                    Assumir Comando &rarr;
                </span>
            </div>
        </a>

        <!-- PROMOTER CARD -->
        <a href="/login?role=promoter" class="role-card role-card-promoter group">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon group-hover:scale-110 transition-all duration-500">
                    <x-heroicon-o-sparkles class="w-10 h-10" />
                </div>
                <h3 class="role-card-title">Hub do Promoter</h3>
                <p class="role-card-description">
                    Ferramenta de alta performance para conversão de convidados e gestão autônoma de listas VIP.
                </p>
                <span class="role-card-tag group-hover:translate-x-2 transition-transform">
                    Ativar Listas &rarr;
                </span>
            </div>
        </a>

        <!-- VALIDATOR CARD -->
        <a href="/login?role=validator" class="role-card role-card-validator group">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon group-hover:scale-110 transition-all duration-500">
                    <x-heroicon-o-bolt class="w-10 h-10" />
                </div>
                <h3 class="role-card-title">Terminal Access</h3>
                <p class="role-card-description">
                    Interface otimizada para portaria de alto fluxo, garantindo entrada sem atritos e zero filas.
                </p>
                <span class="role-card-tag group-hover:translate-x-2 transition-transform">
                    Abrir Portaria &rarr;
                </span>
            </div>
        </a>

        <!-- BILHETERIA CARD -->
        <a href="/login?role=bilheteria" class="role-card role-card-bilheteria group">
            <div class="role-card-bg"></div>
            <div class="role-card-content role-card-border">
                <div class="role-card-icon group-hover:scale-110 transition-all duration-500">
                    <x-heroicon-o-banknotes class="w-10 h-10" />
                </div>
                <h3 class="role-card-title">Financial Desk</h3>
                <p class="role-card-description">
                    Módulo especializado em PDV e emissão instantânea, integrado ao fechamento em tempo real.
                </p>
                <span class="role-card-tag group-hover:translate-x-2 transition-transform">
                    Operar Caixa &rarr;
                </span>
            </div>
        </a>
    </div>
</div>
