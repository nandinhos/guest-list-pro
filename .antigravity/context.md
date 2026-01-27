# 🧠 Project Dynamics — guest-list-pro

---

## 1. Propósito deste documento

Este documento define a dinâmica geral do projeto, a organização da documentação,
a arquitetura conceitual e o papel de cada agente de IA envolvido no desenvolvimento.

Ele deve ser lido **antes de qualquer geração de código**.

Este arquivo é a fonte inicial de contexto para:
- Orquestração de agentes de IA
- Alinhamento arquitetural
- Prevenção de decisões conflitantes
- Garantia de padrão profissional

---

## 2. Visão resumida do sistema

O projeto consiste em:

1. **Sistema de Gestão de Convidados**
   - Cadastro com validação de duplicatas por documento
   - Importação em massa via Excel (GuestsImport)
   - Check-in com busca por similaridade (GuestSearchService)
   - Normalização de documentos para comparações

2. **Sistema de Aprovações**
   - Solicitações de inclusão de convidados (GuestInclusion)
   - Check-in emergencial (EmergencyCheckin)
   - Fluxo: Pending → Approved/Rejected
   - Notificações automáticas para requesters

3. **Bilheteria**
   - Venda de ingressos (Convite Amigo)
   - Fechamento de caixa
   - Controle por horário e operador

4. **Painéis Filament**
   - Admin: Gestão completa do sistema
   - Promoter: Gestão de convidados próprios
   - Validator: Check-in e validação
   - Bilheteria: Vendas e fechamento de caixa

### Stack Tecnológica

| Camada | Tecnologia |
|--------|------------|
| Backend | Laravel 12, PHP 8.5 |
| Frontend | Filament v4, Livewire v3, Alpine.js |
| Database | SQLite (dev), MySQL (prod) |
| Infrastructure | Docker/Sail |
| CSS | Tailwind v4, Design System Premium |

---

## 3. Estrutura lógica do repositório

```
guest-list-pro/
├── .antigravity/              # Governança de IA
│   ├── agents/                # Definição dos agentes
│   ├── rules/                 # Regras por tecnologia
│   ├── docs/                  # Documentação de arquitetura
│   ├── workflows/             # Fluxos de trabalho
│   └── context.md             # Este arquivo
├── app/
│   ├── Enums/                 # Enums de domínio
│   ├── Filament/              # Painéis, Resources, Widgets
│   │   ├── Admin/             # Painel Admin
│   │   ├── Promoter/          # Painel Promoter
│   │   ├── Validator/         # Painel Validator
│   │   └── Bilheteria/        # Painel Bilheteria
│   ├── Models/                # 8 entidades principais
│   ├── Observers/             # Activity logging
│   └── Services/              # Lógica de negócio
├── database/
│   ├── factories/             # Factories para testes
│   ├── migrations/            # 22 migrations
│   └── seeders/               # Dados de desenvolvimento
├── resources/
│   ├── css/                   # Estilos customizados
│   └── views/                 # Blade views
└── tests/
    ├── Feature/               # Testes de integração
    └── Unit/                  # Testes unitários
```

---

## 4. Princípios Fundamentais

> **Documentação vem antes do código.**
> Nenhuma camada deve ser implementada sem o documento correspondente existir.

> **Sempre utilizar os MCPs disponíveis para todas as tarefas.**

> **Sempre validar antes de implementar.**

> **Sempre refinar o código antes de finalizar.**

> **Mobile-first:** Usar `ViewColumn` para cards mobile, esconder colunas via `visibleFrom('md')`.

> **SPA desabilitado:** Manter `->spa()` desabilitado para evitar erros de JS.

---

## 5. Fluxo de Desenvolvimento

1. [x] Definir requisitos (PRD)
2. [x] Definir domínio (ERD)
3. [x] Definir contratos (interfaces)
4. [x] Definir infraestrutura
5. [x] Implementar features core
6. [ ] Otimizar performance
7. [ ] Aumentar cobertura de testes
8. [ ] Documentar arquitetura

Cada etapa possui documentos próprios dentro da pasta `docs/`.

---

## 6. Agentes de IA — Definição e Responsabilidades

### 6.1 Architect Agent

**Responsabilidade:** Definir e proteger a arquitetura do sistema.

**Pode:**
- Criar contratos (interfaces)
- Definir camadas e responsabilidades
- Sugerir padrões e abstrações
- Validar decisões técnicas

**Não pode:**
- Implementar detalhes de UI
- Criar código final sem contratos definidos

---

### 6.2 Backend Agent

**Responsabilidade:** Implementar domínio, serviços e persistência.

**Pode:**
- Criar models e repositories
- Implementar serviços definidos nos contracts
- Criar APIs internas
- Implementar regras de negócio

**Não pode:**
- Alterar contratos sem aprovação do Architect Agent
- Criar dependência direta com UI

---

### 6.3 Frontend Agent

**Responsabilidade:** Criar a experiência visual.

**Pode:**
- Criar páginas e componentes
- Implementar interações
- Criar previews reais dos componentes

**Não pode:**
- Criar regras de negócio
- Criar persistência direta
- Alterar domínio

---

### 6.4 DevOps Agent

**Responsabilidade:** Infraestrutura, build e ambiente.

**Pode:**
- Criar configurações de ambiente
- Definir pipeline de build
- Gerenciar containers

**Não pode:**
- Alterar código de negócio
- Criar dependências não documentadas

---

### 6.5 QA Agent

**Responsabilidade:** Garantir qualidade, consistência e aderência ao PRD.

**Pode:**
- Validar contratos
- Identificar inconsistências
- Sugerir melhorias
- Escrever testes

**Não pode:**
- Implementar código de produção
- Alterar arquitetura

---

## 7. Regras Gerais para Todos os Agentes

- Seguir padrões de código definidos em `.antigravity/rules/`
- Não criar código sem contrato ou documento base
- Não duplicar responsabilidades
- Preferir composição a herança
- Priorizar clareza sobre complexidade
- Código deve ser legível antes de ser "esperto"
- **Sincronização de Documentação:** É mandatório atualizar os arquivos `.antigravity/context.md` e `.antigravity/session-state.md` ao final de cada fase ou grande tarefa.

---

## 8. Convenções Importantes

### Linguagem
- Código: inglês
- Documentação: português
- Comentários: português (quando necessário)

### Versionamento
- Versionamento semântico
- Mudanças estruturais devem ser documentadas

---

## 9. Estado Atual da Implementação

### ✅ Concluído
- [x] CRUD de Events, Sectors, Users
- [x] Sistema de Aprovações completo (GuestInclusion, EmergencyCheckin)
- [x] 4 Painéis Filament (Admin, Promoter, Validator, Bilheteria)
- [x] 12+ Widgets de Dashboard
- [x] Design System Premium com Tailwind v4
- [x] Importação de Guests via Excel
- [x] Check-in com detecção de duplicidade
- [x] Sistema de Bilheteria com fechamento de caixa
- [x] Activity logging com Spatie
- [x] Sprint 5 de segurança e performance

### ⏳ Em Progresso
- [ ] Otimização de performance (índices de banco)
- [ ] Aumento de cobertura de testes (32% → 70%)
- [ ] Refatoração de código duplicado (ImportGuests)

### 📋 Backlog
- [ ] Cache em widgets (PromoterPerformanceChart)
- [ ] Queue para imports grandes
- [ ] Bulk operations otimizadas
- [ ] Extrair ImportGuestsBase (Admin/Promoter)
- [ ] Decompor ApprovalRequestService (576 linhas)

---

## 10. Métricas do Projeto

### Codebase
| Métrica | Valor |
|---------|-------|
| Models | 8 entidades principais |
| Filament Resources | 10 resources |
| Services | 4 services críticos |
| Widgets | 12+ widgets |
| Migrations | 22 migrations |
| Arquivos PHP | ~112 arquivos |

### Testes
| Métrica | Valor |
|---------|-------|
| Arquivos de teste | 7 |
| Total de testes | 46 |
| Cobertura estimada | ~32% |

### Gaps de Testes Críticos
- GuestService (0 testes)
- GuestsImport (0 testes)
- CheckinAttempt (0 testes)
- DocumentValidationService (0 testes)

---

## 11. Objetivo Final

Ao final do projeto, o sistema deve:

- Gerenciar convidados de eventos com controle de duplicidade
- Permitir aprovações hierárquicas de inclusões
- Executar check-in rápido e confiável
- Gerar relatórios de bilheteria precisos
- Manter performance < 500ms em operações críticas
- Ter cobertura de testes > 70%
- Seguir padrões PSR-12 e boas práticas Laravel

---

**Este documento é vivo e deve ser atualizado conforme o projeto evolui.**
