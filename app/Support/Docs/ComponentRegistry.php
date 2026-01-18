<?php

namespace App\Support\Docs;

/**
 * Registro centralizado dos componentes do Design System.
 * Contém metadados, props e exemplos de código para documentação.
 */
class ComponentRegistry
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            'button' => self::button(),
            'card' => self::card(),
            'badge' => self::badge(),
            'input' => self::input(),
            'stat-card' => self::statCard(),
            'alert' => self::alert(),
            'skeleton' => self::skeleton(),
            'empty-state' => self::emptyState(),
        ];
    }

    public static function button(): array
    {
        return [
            'name' => 'Button',
            'tag' => 'x-ui.button',
            'category' => 'UI',
            'description' => 'Botão interativo com suporte a múltiplas variantes, tamanhos, ícones e estados de loading. Pode ser usado como botão ou link.',
            'props' => [
                'variant' => [
                    'type' => 'string',
                    'default' => 'primary',
                    'options' => ['primary', 'secondary', 'ghost', 'danger'],
                    'description' => 'Estilo visual do botão',
                ],
                'size' => [
                    'type' => 'string',
                    'default' => 'md',
                    'options' => ['sm', 'md', 'lg'],
                    'description' => 'Tamanho do botão',
                ],
                'icon' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Ícone Heroicon à esquerda (ex: heroicon-o-plus)',
                ],
                'iconRight' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Ícone Heroicon à direita',
                ],
                'loading' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Exibe spinner de loading e desabilita o botão',
                ],
                'disabled' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Desabilita o botão',
                ],
                'href' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'URL para navegação (transforma em link)',
                ],
            ],
            'examples' => [
                'Básico' => '<x-ui.button variant="primary">
    Salvar
</x-ui.button>',
                'Com Ícone' => '<x-ui.button variant="primary" icon="heroicon-o-plus">
    Adicionar Item
</x-ui.button>',
                'Variantes' => '<x-ui.button variant="primary">Primary</x-ui.button>
<x-ui.button variant="secondary">Secondary</x-ui.button>
<x-ui.button variant="ghost">Ghost</x-ui.button>
<x-ui.button variant="danger">Danger</x-ui.button>',
                'Loading' => '<x-ui.button variant="primary" loading>
    Processando...
</x-ui.button>',
                'Link' => '<x-ui.button variant="secondary" href="/dashboard">
    Ir para Dashboard
</x-ui.button>',
            ],
        ];
    }

    public static function card(): array
    {
        return [
            'name' => 'Card',
            'tag' => 'x-ui.card',
            'category' => 'UI',
            'description' => 'Container flexível para agrupar conteúdo relacionado. Suporta variantes visuais, efeitos de hover e slots para header/footer.',
            'props' => [
                'variant' => [
                    'type' => 'string',
                    'default' => 'default',
                    'options' => ['default', 'glass', 'elevated', 'bordered'],
                    'description' => 'Estilo visual do card',
                ],
                'hover' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Adiciona efeito de elevação no hover',
                ],
                'padding' => [
                    'type' => 'string',
                    'default' => 'md',
                    'options' => ['none', 'sm', 'md', 'lg'],
                    'description' => 'Espaçamento interno',
                ],
            ],
            'examples' => [
                'Básico' => '<x-ui.card>
    <h3 class="font-semibold">Título</h3>
    <p>Conteúdo do card</p>
</x-ui.card>',
                'Glass' => '<x-ui.card variant="glass">
    <h3 class="font-semibold">Glassmorphism</h3>
    <p>Efeito translúcido moderno</p>
</x-ui.card>',
                'Com Hover' => '<x-ui.card variant="bordered" hover>
    <h3 class="font-semibold">Card Interativo</h3>
    <p>Passe o mouse para ver o efeito</p>
</x-ui.card>',
                'Com Header/Footer' => '<x-ui.card>
    <x-slot name="header">
        <h3 class="font-bold">Título do Card</h3>
    </x-slot>
    
    <p>Conteúdo principal aqui.</p>
    
    <x-slot name="footer">
        <x-ui.button size="sm">Ação</x-ui.button>
    </x-slot>
</x-ui.card>',
            ],
        ];
    }

    public static function badge(): array
    {
        return [
            'name' => 'Badge',
            'tag' => 'x-ui.badge',
            'category' => 'UI',
            'description' => 'Rótulo compacto para destacar status, categorias ou contadores. Suporta indicador de ponto e botão de remoção.',
            'props' => [
                'variant' => [
                    'type' => 'string',
                    'default' => 'default',
                    'options' => ['default', 'primary', 'success', 'warning', 'danger', 'info'],
                    'description' => 'Cor/estilo do badge',
                ],
                'size' => [
                    'type' => 'string',
                    'default' => 'md',
                    'options' => ['sm', 'md', 'lg'],
                    'description' => 'Tamanho do badge',
                ],
                'dot' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Exibe indicador de ponto colorido',
                ],
                'removable' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Exibe botão X para remoção',
                ],
            ],
            'examples' => [
                'Variantes' => '<x-ui.badge variant="default">Default</x-ui.badge>
<x-ui.badge variant="primary">Primary</x-ui.badge>
<x-ui.badge variant="success">Success</x-ui.badge>
<x-ui.badge variant="warning">Warning</x-ui.badge>
<x-ui.badge variant="danger">Danger</x-ui.badge>
<x-ui.badge variant="info">Info</x-ui.badge>',
                'Com Dot' => '<x-ui.badge variant="success" dot>
    Online
</x-ui.badge>',
                'Removível' => '<x-ui.badge variant="primary" removable>
    Tag Removível
</x-ui.badge>',
            ],
        ];
    }

    public static function input(): array
    {
        return [
            'name' => 'Input',
            'tag' => 'x-ui.input',
            'category' => 'UI',
            'description' => 'Campo de entrada de texto com suporte a label, ícones, validação e estados de erro.',
            'props' => [
                'label' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Label do campo',
                ],
                'name' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Atributo name do input',
                ],
                'type' => [
                    'type' => 'string',
                    'default' => 'text',
                    'description' => 'Tipo do input (text, email, password, etc)',
                ],
                'placeholder' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Texto placeholder',
                ],
                'icon' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Ícone à esquerda',
                ],
                'error' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Mensagem de erro',
                ],
                'hint' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Texto de ajuda abaixo do campo',
                ],
                'required' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Marca o campo como obrigatório',
                ],
                'disabled' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Desabilita o campo',
                ],
            ],
            'examples' => [
                'Básico' => '<x-ui.input 
    label="E-mail" 
    name="email" 
    type="email" 
    placeholder="seu@email.com" 
/>',
                'Com Ícone' => '<x-ui.input 
    label="Buscar" 
    name="search" 
    placeholder="Pesquisar..." 
    icon="heroicon-o-magnifying-glass" 
/>',
                'Com Erro' => '<x-ui.input 
    label="Nome" 
    name="name" 
    error="Este campo é obrigatório" 
/>',
                'Com Dica' => '<x-ui.input 
    label="Senha" 
    name="password" 
    type="password" 
    required 
    hint="Mínimo 8 caracteres" 
/>',
            ],
        ];
    }

    public static function statCard(): array
    {
        return [
            'name' => 'Stat Card',
            'tag' => 'x-data.stat-card',
            'category' => 'Data',
            'description' => 'Card para exibir métricas e estatísticas com ícone, valor principal e indicador de variação.',
            'props' => [
                'label' => [
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Título/label da métrica',
                ],
                'value' => [
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Valor principal a exibir',
                ],
                'change' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Texto de variação (ex: +12%)',
                ],
                'changeType' => [
                    'type' => 'string',
                    'default' => 'neutral',
                    'options' => ['up', 'down', 'neutral'],
                    'description' => 'Direção da variação (afeta cor)',
                ],
                'icon' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Ícone Heroicon',
                ],
                'iconColor' => [
                    'type' => 'string',
                    'default' => 'indigo',
                    'options' => ['indigo', 'purple', 'emerald', 'amber', 'rose'],
                    'description' => 'Cor do ícone',
                ],
            ],
            'examples' => [
                'Básico' => '<x-data.stat-card 
    label="Total de Vendas" 
    value="R$ 45.231" 
/>',
                'Com Variação' => '<x-data.stat-card 
    label="Usuários Ativos" 
    value="1,234" 
    change="+12%" 
    changeType="up" 
    icon="heroicon-o-users" 
    iconColor="indigo" 
/>',
                'Variação Negativa' => '<x-data.stat-card 
    label="Cancelamentos" 
    value="23" 
    change="-5%" 
    changeType="down" 
    icon="heroicon-o-x-circle" 
    iconColor="rose" 
/>',
            ],
        ];
    }

    public static function alert(): array
    {
        return [
            'name' => 'Alert',
            'tag' => 'x-feedback.alert',
            'category' => 'Feedback',
            'description' => 'Mensagem de alerta para feedback ao usuário. Suporta diferentes tipos e pode ser dispensável.',
            'props' => [
                'type' => [
                    'type' => 'string',
                    'default' => 'info',
                    'options' => ['info', 'success', 'warning', 'danger'],
                    'description' => 'Tipo/cor do alerta',
                ],
                'title' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Título do alerta (opcional)',
                ],
                'dismissible' => [
                    'type' => 'boolean',
                    'default' => 'false',
                    'description' => 'Permite fechar o alerta',
                ],
            ],
            'examples' => [
                'Info' => '<x-feedback.alert type="info">
    Esta é uma mensagem informativa.
</x-feedback.alert>',
                'Sucesso' => '<x-feedback.alert type="success" title="Sucesso!">
    Operação realizada com sucesso.
</x-feedback.alert>',
                'Aviso' => '<x-feedback.alert type="warning" dismissible>
    Atenção: verifique os dados antes de continuar.
</x-feedback.alert>',
                'Erro' => '<x-feedback.alert type="danger" title="Erro" dismissible>
    Ocorreu um erro ao processar sua solicitação.
</x-feedback.alert>',
            ],
        ];
    }

    public static function skeleton(): array
    {
        return [
            'name' => 'Skeleton',
            'tag' => 'x-feedback.skeleton',
            'category' => 'Feedback',
            'description' => 'Placeholder animado para indicar carregamento de conteúdo. Suporta diferentes formatos.',
            'props' => [
                'type' => [
                    'type' => 'string',
                    'default' => 'text',
                    'options' => ['text', 'card', 'avatar', 'table-row'],
                    'description' => 'Formato do skeleton',
                ],
                'lines' => [
                    'type' => 'integer',
                    'default' => '3',
                    'description' => 'Número de linhas (para type=text)',
                ],
            ],
            'examples' => [
                'Texto' => '<x-feedback.skeleton type="text" :lines="3" />',
                'Avatar' => '<x-feedback.skeleton type="avatar" />',
                'Card' => '<x-feedback.skeleton type="card" />',
                'Linha de Tabela' => '<x-feedback.skeleton type="table-row" />',
            ],
        ];
    }

    public static function emptyState(): array
    {
        return [
            'name' => 'Empty State',
            'tag' => 'x-feedback.empty-state',
            'category' => 'Feedback',
            'description' => 'Mensagem para estados vazios com ícone, título, descrição e ação opcional.',
            'props' => [
                'icon' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Ícone Heroicon',
                ],
                'title' => [
                    'type' => 'string',
                    'required' => true,
                    'description' => 'Título principal',
                ],
                'description' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Descrição/instrução',
                ],
                'actionLabel' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'Texto do botão de ação',
                ],
                'actionUrl' => [
                    'type' => 'string',
                    'default' => 'null',
                    'description' => 'URL do botão de ação',
                ],
            ],
            'examples' => [
                'Básico' => '<x-feedback.empty-state
    icon="heroicon-o-document"
    title="Nenhum documento"
    description="Você ainda não tem documentos."
/>',
                'Com Ação' => '<x-feedback.empty-state
    icon="heroicon-o-users"
    title="Nenhum convidado encontrado"
    description="Adicione o primeiro convidado para começar."
    actionLabel="Adicionar Convidado"
    actionUrl="/guests/create"
/>',
            ],
        ];
    }
}
