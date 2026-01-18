<div class="min-h-screen" 
    x-data="{ 
        activeSection: @entangle('activeSection'),
        initScrollSpy() {
            const sections = document.querySelectorAll('.docs-component-section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        this.activeSection = entry.target.id;
                    }
                });
            }, {
                rootMargin: '-20% 0px -70% 0px',
                threshold: 0
            });
            
            sections.forEach(section => observer.observe(section));
        }
    }"
    x-init="$nextTick(() => initScrollSpy())"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Header --}}
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-surface-primary mb-3">Design System</h1>
            <p class="text-lg text-surface-secondary max-w-2xl mx-auto">
                Documentação completa dos componentes do Guest List Pro. 
                Explore, aprenda e copie o código para usar em seus projetos.
            </p>
        </div>

        <div class="flex gap-8">
            {{-- Sidebar Navigation --}}
            <aside class="hidden lg:block w-64 shrink-0">
                <nav class="docs-sidebar glass-card p-4 scrollbar-thin">
                    {{-- Theme Toggle --}}
                    <button 
                        @click="darkMode = !darkMode"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 mb-4 rounded-lg bg-white/5 hover:bg-white/10 transition-all duration-200 text-surface-secondary hover:text-surface-primary"
                    >
                        <span class="text-sm font-medium">Alterar Tema</span>
                        <template x-if="darkMode">
                            <x-heroicon-o-sun class="w-5 h-5" />
                        </template>
                        <template x-if="!darkMode">
                            <x-heroicon-o-moon class="w-5 h-5" />
                        </template>
                    </button>
                    
                    <div class="border-t border-white/10 pt-4 mb-4"></div>
                    
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-surface-secondary mb-4 px-3">
                        Componentes
                    </h2>
                    
                    {{-- UI Components --}}
                    <div class="mb-6">
                        <h3 class="text-xs font-medium text-surface-muted mb-2 px-3">UI</h3>
                        <div class="space-y-1">
                            @foreach(['button' => 'Button', 'card' => 'Card', 'badge' => 'Badge', 'input' => 'Input'] as $key => $name)
                                <a 
                                    href="#{{ $key }}"
                                    @click.prevent="activeSection = '{{ $key }}'; document.getElementById('{{ $key }}').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                                    class="docs-sidebar-link"
                                    :class="{ 'active': activeSection === '{{ $key }}' }"
                                >
                                    {{ $name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Data Components --}}
                    <div class="mb-6">
                        <h3 class="text-xs font-medium text-surface-muted mb-2 px-3">Data</h3>
                        <div class="space-y-1">
                            <a 
                                href="#stat-card"
                                @click.prevent="activeSection = 'stat-card'; document.getElementById('stat-card').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                                class="docs-sidebar-link"
                                :class="{ 'active': activeSection === 'stat-card' }"
                            >
                                Stat Card
                            </a>
                        </div>
                    </div>
                    
                    {{-- Feedback Components --}}
                    <div class="mb-6">
                        <h3 class="text-xs font-medium text-surface-muted mb-2 px-3">Feedback</h3>
                        <div class="space-y-1">
                            @foreach(['alert' => 'Alert', 'skeleton' => 'Skeleton', 'empty-state' => 'Empty State'] as $key => $name)
                                <a 
                                    href="#{{ $key }}"
                                    @click.prevent="activeSection = '{{ $key }}'; document.getElementById('{{ $key }}').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                                    class="docs-sidebar-link"
                                    :class="{ 'active': activeSection === '{{ $key }}' }"
                                >
                                    {{ $name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Back Link --}}
                    <div class="pt-4 border-t border-white/10">
                        <a href="/" class="docs-sidebar-link flex items-center gap-2">
                            <x-heroicon-o-arrow-left class="w-4 h-4" />
                            Voltar ao Início
                        </a>
                    </div>
                </nav>
            </aside>

            {{-- Main Content --}}
            <main class="flex-1 min-w-0 space-y-16">
                @foreach($comps as $key => $comp)
                    <section id="{{ $key }}" class="docs-component-section" wire:key="section-{{ $key }}">
                        {{-- 1. Item Header --}}
                        <div class="mb-6">
                            <div class="flex items-center gap-3 mb-2">
                                <h2 class="text-2xl font-bold text-surface-primary">{{ $comp['name'] }}</h2>
                                <x-ui.badge variant="primary" size="sm">{{ $comp['category'] }}</x-ui.badge>
                            </div>
                            {{-- 2. Descrição --}}
                            <p class="text-surface-secondary">{{ $comp['description'] }}</p>
                        </div>

                        {{-- 3. Propriedades --}}
                        @if(!empty($comp['props']))
                            <div class="mb-8">
                                <h3 class="text-lg font-semibold text-surface-primary mb-4">Propriedades</h3>
                                <x-ui.card variant="bordered" padding="none">
                                    <div class="overflow-x-auto">
                                        <table class="docs-props-table">
                                            <thead>
                                                <tr>
                                                    <th>Prop</th>
                                                    <th>Tipo</th>
                                                    <th>Padrão</th>
                                                    <th>Descrição</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($comp['props'] as $propName => $propDetails)
                                                    <tr>
                                                        <td>
                                                            <span class="prop-name">{{ $propName }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="prop-type">{{ $propDetails['type'] }}</span>
                                                            @if(isset($propDetails['options']))
                                                                <div class="mt-1 text-xs text-surface-muted">
                                                                    {{ implode(' | ', $propDetails['options']) }}
                                                                </div>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <span class="prop-default">{{ $propDetails['default'] ?? '-' }}</span>
                                                        </td>
                                                        <td>{{ $propDetails['description'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </x-ui.card>
                            </div>
                        @endif

                        {{-- 4. Demonstrações --}}
                        <div class="space-y-8">
                            <h3 class="text-lg font-semibold text-surface-primary">Demonstrações</h3>
                            @foreach($comp['examples'] as $exampleName => $exampleCode)
                                <div wire:key="example-{{ $key }}-{{ Str::slug($exampleName) }}">
                                    <h4 class="text-base font-medium text-surface-primary mb-4">{{ $exampleName }}</h4>
                                    
                                    <div class="space-y-4">
                                        {{-- Demo Card --}}
                                        <x-ui.card variant="bordered" class="p-6">
                                            <div class="flex flex-wrap gap-3 items-center">
                                                <x-docs.demo-renderer :componentKey="$key" :exampleKey="$exampleName" />
                                            </div>
                                        </x-ui.card>

                                        {{-- Code Block --}}
                                        <x-docs.code-block
                                            :code="$exampleCode"
                                            language="blade"
                                            :title="$comp['tag']"
                                        />
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Separator --}}
                        <hr class="my-12 border-white/10" />
                    </section>
                @endforeach
            </main>
        </div>
    </div>
</div>
