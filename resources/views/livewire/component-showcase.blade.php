<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-slate-900 dark:text-white mb-2">Design System Showcase</h1>
        <p class="text-slate-500 dark:text-slate-400">Componentes do Guest List Pro</p>
    </div>

    {{-- Buttons Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Buttons</h2>
        <div class="flex flex-wrap gap-4 items-center">
            <x-ui.button variant="primary">Primary</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button variant="danger">Danger</x-ui.button>
            <x-ui.button variant="primary" icon="heroicon-o-plus">Com Ícone</x-ui.button>
            <x-ui.button variant="primary" loading>Loading</x-ui.button>
            <x-ui.button variant="primary" size="sm">Small</x-ui.button>
            <x-ui.button variant="primary" size="lg">Large</x-ui.button>
        </div>
    </section>

    {{-- Cards Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.card variant="default">
                <h3 class="font-semibold text-slate-900 dark:text-white">Default</h3>
                <p class="text-sm text-slate-500">Conteúdo do card</p>
            </x-ui.card>
            <x-ui.card variant="glass">
                <h3 class="font-semibold text-slate-900 dark:text-white">Glass</h3>
                <p class="text-sm text-slate-500">Glassmorphism</p>
            </x-ui.card>
            <x-ui.card variant="elevated">
                <h3 class="font-semibold text-slate-900 dark:text-white">Elevated</h3>
                <p class="text-sm text-slate-500">Sombra elevada</p>
            </x-ui.card>
            <x-ui.card variant="bordered" hover>
                <h3 class="font-semibold text-slate-900 dark:text-white">Bordered + Hover</h3>
                <p class="text-sm text-slate-500">Com efeito hover</p>
            </x-ui.card>
        </div>
    </section>

    {{-- Badges Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Badges</h2>
        <div class="flex flex-wrap gap-3 items-center">
            <x-ui.badge variant="default">Default</x-ui.badge>
            <x-ui.badge variant="primary">Primary</x-ui.badge>
            <x-ui.badge variant="success">Success</x-ui.badge>
            <x-ui.badge variant="warning">Warning</x-ui.badge>
            <x-ui.badge variant="danger">Danger</x-ui.badge>
            <x-ui.badge variant="info">Info</x-ui.badge>
            <x-ui.badge variant="success" dot>Com Dot</x-ui.badge>
            <x-ui.badge variant="primary" removable>Removable</x-ui.badge>
        </div>
    </section>

    {{-- Inputs Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Inputs</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
            <x-ui.input label="E-mail" name="email" type="email" placeholder="seu@email.com" icon="heroicon-o-envelope" />
            <x-ui.input label="Senha" name="password" type="password" required hint="Mínimo 8 caracteres" />
            <x-ui.input label="Campo com erro" name="error_field" error="Este campo é obrigatório" />
            <x-ui.input label="Buscar" name="search" placeholder="Pesquisar..." icon="heroicon-o-magnifying-glass" />
        </div>
    </section>

    {{-- Stat Cards Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Stat Cards</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-data.stat-card label="Total de Eventos" value="42" change="+12%" changeType="up" icon="heroicon-o-calendar" iconColor="indigo" />
            <x-data.stat-card label="Convidados" value="1,234" change="+5%" changeType="up" icon="heroicon-o-users" iconColor="purple" />
            <x-data.stat-card label="Check-ins" value="892" change="-3%" changeType="down" icon="heroicon-o-check-circle" iconColor="emerald" />
            <x-data.stat-card label="Pendentes" value="342" icon="heroicon-o-clock" iconColor="amber" />
        </div>
    </section>

    {{-- Alerts Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Alerts</h2>
        <div class="space-y-4 max-w-2xl">
            <x-feedback.alert type="info" title="Informação">Esta é uma mensagem informativa.</x-feedback.alert>
            <x-feedback.alert type="success" title="Sucesso!" dismissible>Operação realizada com sucesso.</x-feedback.alert>
            <x-feedback.alert type="warning">Atenção: verifique os dados antes de continuar.</x-feedback.alert>
            <x-feedback.alert type="danger" title="Erro" dismissible>Ocorreu um erro ao processar sua solicitação.</x-feedback.alert>
        </div>
    </section>

    {{-- Skeletons Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Skeletons</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-slate-500 mb-2">Text (3 lines)</p>
                <x-feedback.skeleton type="text" :lines="3" />
            </div>
            <div>
                <p class="text-sm text-slate-500 mb-2">Avatar</p>
                <x-feedback.skeleton type="avatar" />
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-slate-500 mb-2">Card</p>
                <x-feedback.skeleton type="card" />
            </div>
        </div>
    </section>

    {{-- Empty State Section --}}
    <section class="mb-12">
        <h2 class="text-2xl font-semibold text-slate-900 dark:text-white mb-6">Empty State</h2>
        <x-ui.card variant="bordered">
            <x-feedback.empty-state 
                icon="heroicon-o-users"
                title="Nenhum convidado encontrado"
                description="Adicione o primeiro convidado para começar a gerenciar sua lista."
                actionLabel="Adicionar Convidado"
                actionUrl="#"
            />
        </x-ui.card>
    </section>

    {{-- Back Link --}}
    <div class="text-center">
        <x-ui.button variant="secondary" href="/" icon="heroicon-o-arrow-left">Voltar ao Início</x-ui.button>
    </div>
</div>
