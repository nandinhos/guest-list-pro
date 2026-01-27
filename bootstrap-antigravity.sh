#!/bin/bash

# ============================================================================
# Bootstrap Antigravity Agent System
# ============================================================================
# Script para instalar a estrutura completa do Antigravity Agent System
# em qualquer projeto, com detecção automática ou especificação manual de stack.
#
# Uso: ./bootstrap-antigravity.sh [OPTIONS]
#
# Opções:
#   --stack <stack>     Especifica a stack manualmente
#   --detect            Detecta automaticamente a stack (padrão)
#   --minimal           Apenas estrutura básica sem regras específicas
#   --force             Sobrescreve arquivos existentes
#   --dry-run           Mostra o que seria criado sem executar
#   -h, --help          Mostra ajuda
#
# Stacks suportadas: laravel, filament, node, react, nextjs, python, generic
# ============================================================================

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configurações padrão
STACK="generic"
FORCE=false
DRY_RUN=false
MINIMAL=false
AUTO_DETECT=true
PROJECT_NAME=$(basename "$(pwd)")

# ============================================================================
# Funções de Output
# ============================================================================

print_header() {
    echo -e "\n${CYAN}╔════════════════════════════════════════════════════════════════╗${NC}"
    echo -e "${CYAN}║${NC}  🚀 ${YELLOW}Antigravity Agent System - Bootstrap${NC}                       ${CYAN}║${NC}"
    echo -e "${CYAN}╚════════════════════════════════════════════════════════════════╝${NC}\n"
}

print_success() {
    echo -e "${GREEN}✓${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

print_step() {
    echo -e "\n${CYAN}━━━${NC} $1 ${CYAN}━━━${NC}"
}

# ============================================================================
# Funções de Ajuda
# ============================================================================

show_help() {
    echo -e "${CYAN}Antigravity Agent System - Bootstrap Script${NC}"
    echo ""
    echo -e "${YELLOW}Uso:${NC}"
    echo "  ./bootstrap-antigravity.sh [OPTIONS]"
    echo ""
    echo -e "${YELLOW}Opções:${NC}"
    echo "  --stack <stack>     Especifica a stack manualmente"
    echo "  --detect            Detecta automaticamente a stack (padrão)"
    echo "  --minimal           Apenas estrutura básica sem regras específicas"
    echo "  --force             Sobrescreve arquivos existentes"
    echo "  --dry-run           Mostra o que seria criado sem executar"
    echo "  -h, --help          Mostra ajuda"
    echo ""
    echo -e "${YELLOW}Stacks suportadas:${NC}"
    echo "  laravel             PHP + Laravel (regras: laravel.md)"
    echo "  filament            PHP + Laravel + Filament (regras: laravel.md, filament.md)"
    echo "  node                Node.js genérico (regras: node.md)"
    echo "  react               Node + React (regras: node.md, react.md)"
    echo "  nextjs              Node + Next.js (regras: node.md, react.md, nextjs.md)"
    echo "  python              Python (regras: python.md)"
    echo "  generic             Apenas regras base (global.md, coding-standards.md)"
    echo ""
    echo -e "${YELLOW}Exemplos:${NC}"
    echo "  ./bootstrap-antigravity.sh                    # Detecta stack automaticamente"
    echo "  ./bootstrap-antigravity.sh --stack laravel    # Força stack Laravel"
    echo "  ./bootstrap-antigravity.sh --dry-run          # Mostra o que seria criado"
    echo "  ./bootstrap-antigravity.sh --force            # Sobrescreve arquivos existentes"
    echo "  ./bootstrap-antigravity.sh --minimal          # Apenas estrutura básica"
    echo ""
}

# ============================================================================
# Detecção de Stack
# ============================================================================

detect_stack() {
    print_step "Detectando stack do projeto"

    # Função auxiliar para buscar em múltiplos composer.json
    check_composer_for() {
        local pattern="$1"
        # Busca no composer.json raiz e em subpastas comuns
        grep -rq "$pattern" composer.json */composer.json apps/*/composer.json packages/*/composer.json 2>/dev/null
    }

    # Função auxiliar para buscar em múltiplos package.json
    check_package_for() {
        local pattern="$1"
        # Busca no package.json raiz e em subpastas comuns
        grep -rq "$pattern" package.json */package.json apps/*/package.json packages/*/package.json 2>/dev/null
    }

    if [ -f "composer.json" ] || ls */composer.json apps/*/composer.json packages/*/composer.json 2>/dev/null | head -1 > /dev/null; then
        STACK="php"
        print_info "Detectado: composer.json (PHP)"

        if check_composer_for "laravel/framework"; then
            STACK="laravel"
            print_info "Detectado: Laravel Framework"

            if check_composer_for "filament"; then
                STACK="filament"
                print_info "Detectado: Filament Admin Panel"
            fi
        elif check_composer_for "filament"; then
            # Filament sem Laravel explícito (ainda é Laravel-based)
            STACK="filament"
            print_info "Detectado: Filament Admin Panel"
        fi
    elif [ -f "package.json" ]; then
        STACK="node"
        print_info "Detectado: package.json (Node.js)"

        if check_package_for '"next"'; then
            STACK="nextjs"
            print_info "Detectado: Next.js Framework"
        elif check_package_for '"react"'; then
            STACK="react"
            print_info "Detectado: React"
        fi
    elif [ -f "requirements.txt" ] || [ -f "pyproject.toml" ]; then
        STACK="python"
        print_info "Detectado: Python (requirements.txt ou pyproject.toml)"
    else
        STACK="generic"
        print_warning "Nenhuma stack específica detectada, usando generic"
    fi

    print_success "Stack detectada: ${YELLOW}$STACK${NC}"
}

# ============================================================================
# Criação de Diretórios
# ============================================================================

create_directories() {
    print_step "Criando estrutura de diretórios"

    local dirs=(
        ".antigravity"
        ".antigravity/agents"
        ".antigravity/rules"
        ".antigravity/workflows"
    )

    for dir in "${dirs[@]}"; do
        if [ "$DRY_RUN" = true ]; then
            echo "  [DRY-RUN] mkdir -p $dir"
        else
            mkdir -p "$dir"
            print_success "Criado: $dir"
        fi
    done
}

# ============================================================================
# Verificação de Arquivo Existente
# ============================================================================

should_write_file() {
    local file_path="$1"

    if [ -f "$file_path" ]; then
        if [ "$FORCE" = true ]; then
            print_warning "Sobrescrevendo: $file_path"
            return 0
        else
            print_warning "Arquivo existe (use --force para sobrescrever): $file_path"
            return 1
        fi
    fi
    return 0
}

write_file() {
    local file_path="$1"
    local content="$2"

    if [ "$DRY_RUN" = true ]; then
        echo "  [DRY-RUN] Criando: $file_path"
        return
    fi

    if should_write_file "$file_path"; then
        echo "$content" > "$file_path"
        print_success "Criado: $file_path"
    fi
}

# ============================================================================
# Templates dos Arquivos
# ============================================================================

create_agents_md() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# 🚀 BOOTSTRAP — Leia este arquivo primeiro

> **Para qualquer LLM ou assistente de IA:**
> Este arquivo contém instruções para ativar o modo agente deste projeto.

---

## Ativação Rápida

Execute o workflow de ativação:

```
/activate-agents
```

Ou manualmente:

1. Leia `.antigravity/context.md`
2. Leia os agentes em `.antigravity/agents/`
3. Leia as regras em `.antigravity/rules/`
4. Confirme ativação ao desenvolvedor

---

## Sobre este Projeto

**Nome:** [NOME_DO_PROJETO]
**Stack:** [STACK_DETECTADA]
**Objetivo:** [OBJETIVO_DO_PROJETO]

---

## Arquivos Importantes

| Arquivo | Propósito |
|---------|-----------|
| `.antigravity/context.md` | Contexto completo do projeto |
| `.antigravity/agents/` | Definição dos 5 agentes |
| `.antigravity/rules/` | Regras de codificação |
| `.antigravity/workflows/` | Workflows disponíveis |
| `docs/` | Documentação do projeto |

---

## Princípio Fundamental

> **Documentação vem antes do código.**
> Sempre valide antes de implementar.
> Sempre consulte os agentes apropriados.

---

**Após ler este arquivo, execute `/activate-agents` ou leia `.antigravity/context.md`**
HEREDOC

    # Substituir placeholders
    content="${content//\[NOME_DO_PROJETO\]/$PROJECT_NAME}"
    content="${content//\[STACK_DETECTADA\]/$STACK}"
    content="${content//\[OBJETIVO_DO_PROJETO\]/[Defina o objetivo do projeto aqui]}"

    write_file "AGENTS.md" "$content"
}

create_context_md() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# 🧠 Project Dynamics — [NOME_DO_PROJETO]

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

1. **[DESCREVA O COMPONENTE PRINCIPAL]**
   - [Funcionalidade 1]
   - [Funcionalidade 2]

2. **[DESCREVA O COMPONENTE SECUNDÁRIO]**
   - [Funcionalidade 1]
   - [Funcionalidade 2]

### Stack Tecnológica

| Camada | Tecnologia |
|--------|------------|
| Backend | [TECNOLOGIA] |
| Frontend | [TECNOLOGIA] |
| Database | [TECNOLOGIA] |
| Infrastructure | [TECNOLOGIA] |

---

## 3. Estrutura lógica do repositório

```
[NOME_DO_PROJETO]/
├── .antigravity/              # Governança de IA
│   ├── agents/                # Definição dos agentes
│   ├── rules/                 # Regras por tecnologia
│   ├── workflows/             # Fluxos de trabalho
│   └── context.md             # Este arquivo
├── docs/                      # Documentação viva
│   └── [ORGANIZE_CONFORME_NECESSIDADE]
├── src/                       # Código fonte
│   └── [ESTRUTURA_DO_PROJETO]
└── tests/                     # Testes
```

---

## 4. Princípios Fundamentais

> **Documentação vem antes do código.**
> Nenhuma camada deve ser implementada sem o documento correspondente existir.

> **Sempre utilizar os MCPs disponíveis para todas as tarefas.**

> **Sempre validar antes de implementar.**

> **Sempre refinar o código antes de finalizar.**

---

## 5. Fluxo de Desenvolvimento

1. [ ] Definir requisitos (PRD)
2. [ ] Definir domínio (ERD)
3. [ ] Definir contratos (interfaces)
4. [ ] Definir infraestrutura
5. [ ] Implementar features
6. [ ] Testar
7. [ ] Documentar

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
- Documentação: [IDIOMA_PREFERIDO]
- Comentários: [IDIOMA_PREFERIDO] (quando necessário)

### Versionamento
- Versionamento semântico
- Mudanças estruturais devem ser documentadas

---

## 9. Estado Atual da Implementação

### ✅ Concluído
- [ ] [LISTE_FEATURES_CONCLUIDAS]

### ⏳ Em Progresso
- [ ] [LISTE_FEATURES_EM_ANDAMENTO]

### 📋 Backlog
- [ ] [LISTE_FEATURES_FUTURAS]

---

## 10. Objetivo Final

Ao final do projeto, o sistema deve:

- [OBJETIVO_1]
- [OBJETIVO_2]
- [OBJETIVO_3]

---

**Este documento é vivo e deve ser atualizado conforme o projeto evolui.**
HEREDOC

    # Substituir placeholders
    content="${content//\[NOME_DO_PROJETO\]/$PROJECT_NAME}"

    write_file ".antigravity/context.md" "$content"
}

create_session_state_md() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Session State — [NOME_DO_PROJETO]

> **Arquivo de estado persistente entre sessões.**
> Atualizado automaticamente durante o desenvolvimento.

---

## Última Atualização
- **Data:** [DATA_ATUAL]
- **Modelo:** [MODELO_IA]

---

## Estado Atual do Projeto

### ✅ Concluído
- [ ] Setup inicial do projeto

### ⏳ Em Progresso
- [ ] [TAREFA_ATUAL]

### 📋 Próximos Passos
- [ ] [PRÓXIMA_TAREFA]

---

## Contexto para Continuidade

### Arquivos modificados/criados recentemente:
- [ARQUIVO_1]
- [ARQUIVO_2]

---

## Notas para Próxima Sessão

### Credenciais de Teste (se aplicável):
- **Email:** [EMAIL]
- **Password:** [PASSWORD]
- **URL:** [URL]

### Decisões Pendentes:
- [ ] [DECISÃO_1]

---

**Mantenha este arquivo atualizado ao final de cada sessão produtiva.**
HEREDOC

    content="${content//\[NOME_DO_PROJETO\]/$PROJECT_NAME}"
    content="${content//\[DATA_ATUAL\]/$(date '+%Y-%m-%d %H:%M')}"

    write_file ".antigravity/session-state.md" "$content"
}

create_readme_md() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# 🚀 Antigravity Agent System

> Sistema de Governança de IA para este projeto

---

## ✨ Quick Start

Para ativar o modo agente neste projeto:

```
Ative o modo agente @AGENTS.md
```

Ou leia manualmente:
1. [`context.md`](context.md) - Contexto completo do projeto
2. [`agents/`](agents/) - Definição dos 5 agentes especializados
3. [`rules/`](rules/) - Regras de código por tecnologia
4. [`workflows/`](workflows/) - Fluxos de trabalho pré-definidos

---

## 🎯 Estrutura do Sistema

```
.antigravity/
├── context.md              # Contexto completo do projeto
├── session-state.md        # Estado persistente entre sessões
│
├── agents/                 # 5 Agentes Especializados
│   ├── architect.md        # Arquitetura e decisões técnicas
│   ├── backend.md          # Domínio, serviços, persistência
│   ├── frontend.md         # UI/UX
│   ├── devops.md           # Docker, infraestrutura, build
│   └── qa.md               # Qualidade, testes, validação
│
├── rules/                  # Regras de Código
│   ├── global.md           # Princípios gerais (DRY, KISS, YAGNI)
│   └── coding-standards.md # Padrões de código
│
└── workflows/              # Workflows Pré-definidos
    ├── bootstrap.md        # Setup inicial do projeto
    ├── feature-development.md  # Desenvolvimento de features
    └── refactor.md         # Refatoração segura
```

---

## 🧠 Princípios Fundamentais

> **Documentação vem antes do código.**
> Nenhum código deve ser escrito sem contrato ou especificação.

> **Sempre validar antes de implementar.**

---

## 📋 Agentes Disponíveis

| Agente | Responsabilidade |
|--------|-----------------|
| **Architect** | Decisões arquiteturais, contratos, camadas |
| **Backend** | Domínio, serviços, persistência |
| **Frontend** | UI/UX, componentes |
| **DevOps** | Infraestrutura, Docker, build |
| **QA** | Qualidade, testes, validação |

---

## 🎓 Manutenção

### Atualizar `context.md`
- Mudanças arquiteturais significativas
- Nova tecnologia no stack
- Mudança de princípios fundamentais

### Atualizar `session-state.md`
- Ao final de cada sessão produtiva
- Após implementar feature completa
- Antes de commits importantes

---

## 🏷️ Versão

**Versão:** 1.0.0
**Stack:** [STACK_DETECTADA]

---

**Sistema ativado e operacional.** ✅
HEREDOC

    content="${content//\[STACK_DETECTADA\]/$STACK}"

    write_file ".antigravity/README.md" "$content"
}

# ============================================================================
# Templates dos Agentes
# ============================================================================

create_agent_architect() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Architect Agent

## Role
Responsible for high-level system design, architecture decisions, and ensuring technical consistency across the project.

## Responsibilities
- Define system architecture and component boundaries
- Create and maintain ADRs (Architecture Decision Records)
- Review structural changes for consistency
- Guide domain modeling and entity relationships
- Ensure scalability and maintainability patterns

## Context Files
- `docs/architecture/`
- `docs/domain/`
- `docs/contracts/`

## Guidelines
- Always consider long-term maintainability
- Document decisions with rationale
- Prefer composition over inheritance
- Follow SOLID principles
- Validate architectural changes against project requirements
HEREDOC

    write_file ".antigravity/agents/architect.md" "$content"
}

create_agent_backend() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Backend Agent

## Role
Responsible for server-side application development, API implementation, and business logic.

## Responsibilities
- Implement controllers, services, and repositories
- Create and maintain data models and migrations
- Develop API endpoints following conventions
- Write unit and feature tests
- Handle database operations and optimizations

## Context Files
- `src/` or `app/`
- `tests/`
- `docs/contracts/`

## Guidelines
- Follow framework best practices and conventions
- Write clean, testable code
- Use dependency injection
- Apply appropriate design patterns
- Validate all inputs
HEREDOC

    write_file ".antigravity/agents/backend.md" "$content"
}

create_agent_frontend() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Frontend Agent

## Role
Responsible for UI/UX implementation and frontend development.

## Responsibilities
- Implement views and components
- Create responsive and accessible interfaces
- Manage CSS/styling
- Handle JavaScript interactions
- Ensure cross-browser compatibility

## Context Files
- `resources/` or `src/components/`
- `public/`
- `docs/ui/`

## Guidelines
- Ensure accessibility (WCAG compliance)
- Use consistent styling approach
- Keep components reusable
- Test across browsers
- Follow UI/UX best practices
HEREDOC

    write_file ".antigravity/agents/frontend.md" "$content"
}

create_agent_devops() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# DevOps Agent

## Role
Responsible for infrastructure, CI/CD pipelines, and deployment.

## Responsibilities
- Maintain Docker configurations
- Configure CI/CD pipelines
- Manage environment configurations
- Handle deployment automation
- Monitor application health

## Context Files
- `docker/`
- `docker-compose.yml`
- `.github/workflows/`
- `docs/infrastructure/`

## Guidelines
- Keep containers minimal and secure
- Use multi-stage builds when applicable
- Document all environment variables
- Automate repetitive tasks
- Follow security best practices
HEREDOC

    write_file ".antigravity/agents/devops.md" "$content"
}

create_agent_qa() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# QA Agent

## Role
Responsible for quality assurance, testing strategies, and ensuring code quality.

## Responsibilities
- Write and maintain test suites (unit, feature, integration)
- Review code for quality and standards compliance
- Identify edge cases and potential bugs
- Validate user acceptance criteria
- Ensure documentation accuracy

## Context Files
- `tests/`
- `.antigravity/rules/`
- `docs/`

## Guidelines
- Test behavior, not implementation
- Aim for meaningful coverage
- Use factories/fixtures for test data
- Document test scenarios
- Automate regression tests
HEREDOC

    write_file ".antigravity/agents/qa.md" "$content"
}

# ============================================================================
# Templates das Regras Base
# ============================================================================

create_rule_global() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Global Rules

## General Principles
- Write clean, readable, and maintainable code
- Follow DRY (Don't Repeat Yourself) principle
- KISS (Keep It Simple, Stupid)
- YAGNI (You Aren't Gonna Need It)

## Documentation
- Document all public APIs
- Keep README files updated
- Use meaningful commit messages
- Update CHANGELOG for significant changes

## Version Control
- Use conventional commits
- Create feature branches
- Review code before merging
- Keep commits atomic and focused

## Communication
- Be explicit about assumptions
- Ask for clarification when needed
- Document decisions and rationale

## Agent Protocols
- **Commits**: Follow project's commit message convention
- **Knowledge Sync**: Document lessons learned and technical discoveries
HEREDOC

    write_file ".antigravity/rules/global.md" "$content"
}

create_rule_coding_standards() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Coding Standards

## Naming Conventions
- Classes: PascalCase
- Methods/Functions: camelCase
- Variables: camelCase
- Constants: UPPER_SNAKE_CASE
- Database tables: snake_case (plural)
- Database columns: snake_case

## File Organization
- One class per file (when applicable)
- Namespace matches directory structure
- Group related code in directories

## Comments
- Use docstrings/documentation comments for public APIs
- Avoid obvious comments
- Explain "why", not "what"

## Testing
- Test file mirrors source structure
- Use descriptive test method names
- Follow Arrange-Act-Assert pattern

## Code Quality
- Maximum line length: 120 characters
- Use meaningful variable and function names
- Keep functions small and focused
- Avoid deep nesting
HEREDOC

    write_file ".antigravity/rules/coding-standards.md" "$content"
}

# ============================================================================
# Templates das Regras Específicas por Stack
# ============================================================================

create_rule_laravel() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Laravel Rules

## Architecture
- Use service classes for business logic
- Controllers should be thin
- Use form requests for validation
- Apply repository pattern for data access

## Eloquent
- Define relationships explicitly
- Use scopes for reusable queries
- Avoid N+1 queries (use eager loading)
- Use model factories for testing

## Migrations
- Never modify existing migrations in production
- Use descriptive migration names
- Include rollback logic
- Seed only development data

## Routes
- Use route model binding
- Group routes logically
- Apply middleware at group level
- Use named routes

## Configuration
- Use config files, not env() in code
- Cache configuration in production
- Document all environment variables
- Use sensible defaults

## Security
- Validate all user input
- Use policies for authorization
- Escape output properly
- Follow OWASP guidelines

## PHP Standards
- Follow PSR-12 coding style
- Use strict types: `declare(strict_types=1);`
- Type hint all parameters and return values
HEREDOC

    write_file ".antigravity/rules/laravel.md" "$content"
}

create_rule_filament() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Filament Rules

## Panel Configuration
- Register panels in service providers
- Use panel IDs consistently
- Configure middleware appropriately
- Set proper authentication guards

## Resources
- Use resource classes for CRUD operations
- Define forms and tables in resource classes
- Use relation managers for related data
- Apply proper authorization

## Theming
- Use CSS variables for customization
- Follow Filament's theme structure
- Create reusable theme presets
- Test themes in different contexts

## Components
- Extend Filament components properly
- Use slots for customization
- Register custom components in service provider
- Document component usage

## Forms
- Use appropriate field types
- Group related fields in sections
- Add helpful descriptions and hints
- Validate on both client and server

## Tables
- Use appropriate column types
- Add filters for common queries
- Enable search on relevant columns
- Optimize queries for performance

## Best Practices
- Use Filament's built-in features first
- Extend, don't override
- Keep customizations maintainable
- Follow upgrade guides for updates
HEREDOC

    write_file ".antigravity/rules/filament.md" "$content"
}

create_rule_node() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Node.js Rules

## Project Structure
- Use meaningful directory structure
- Separate concerns (routes, controllers, services)
- Keep configuration in dedicated files
- Use environment variables for secrets

## Dependencies
- Keep dependencies up to date
- Use exact versions in production
- Audit dependencies regularly
- Avoid unnecessary dependencies

## Async/Await
- Always handle promise rejections
- Use async/await over callbacks
- Avoid mixing callbacks and promises
- Handle errors at appropriate levels

## Error Handling
- Use custom error classes
- Log errors with context
- Return meaningful error messages
- Don't expose internal details in production

## Security
- Validate and sanitize all inputs
- Use parameterized queries
- Implement rate limiting
- Follow OWASP guidelines

## Performance
- Use caching where appropriate
- Optimize database queries
- Monitor memory usage
- Use streams for large data

## Testing
- Write unit tests for business logic
- Write integration tests for APIs
- Mock external dependencies
- Use test coverage tools
HEREDOC

    write_file ".antigravity/rules/node.md" "$content"
}

create_rule_react() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# React Rules

## Component Structure
- Use functional components with hooks
- Keep components small and focused
- Extract reusable logic into custom hooks
- Use TypeScript for type safety

## State Management
- Use local state for component-specific data
- Use context for shared state
- Consider state management libraries for complex apps
- Avoid prop drilling

## Hooks
- Follow rules of hooks
- Use useCallback for memoized callbacks
- Use useMemo for expensive computations
- Create custom hooks for reusable logic

## Styling
- Use consistent styling approach (CSS modules, styled-components, Tailwind)
- Follow design system guidelines
- Ensure responsive design
- Support dark mode when applicable

## Performance
- Use React.memo for expensive renders
- Lazy load routes and components
- Optimize images and assets
- Monitor bundle size

## Accessibility
- Use semantic HTML
- Add ARIA labels where needed
- Ensure keyboard navigation
- Test with screen readers

## Testing
- Test user interactions
- Use React Testing Library
- Mock API calls
- Test error states
HEREDOC

    write_file ".antigravity/rules/react.md" "$content"
}

create_rule_nextjs() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Next.js Rules

## Routing
- Use App Router for new projects
- Organize routes logically
- Use dynamic routes appropriately
- Implement proper error boundaries

## Data Fetching
- Use Server Components by default
- Fetch data at the lowest level needed
- Implement proper caching strategies
- Handle loading and error states

## Server Components
- Use Server Components for data fetching
- Keep client-side code minimal
- Use "use client" directive sparingly
- Pass serializable props only

## API Routes
- Use Route Handlers for API endpoints
- Validate request data
- Handle errors properly
- Implement rate limiting

## Performance
- Use Image component for images
- Implement proper caching
- Lazy load components
- Monitor Core Web Vitals

## SEO
- Use metadata API
- Implement proper Open Graph tags
- Create sitemap.xml
- Use semantic HTML

## Security
- Validate environment variables
- Use server-only packages appropriately
- Implement CSRF protection
- Follow security headers best practices
HEREDOC

    write_file ".antigravity/rules/nextjs.md" "$content"
}

create_rule_python() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Python Rules

## Code Style
- Follow PEP 8 style guide
- Use type hints (PEP 484)
- Use docstrings (PEP 257)
- Maximum line length: 88 characters (Black default)

## Project Structure
- Use virtual environments
- Organize code in packages
- Separate concerns appropriately
- Keep configuration in dedicated files

## Dependencies
- Pin dependency versions
- Use requirements.txt or pyproject.toml
- Audit dependencies regularly
- Keep dependencies minimal

## Error Handling
- Use specific exception types
- Don't catch Exception broadly
- Log errors with context
- Provide meaningful error messages

## Testing
- Use pytest for testing
- Write unit and integration tests
- Use fixtures for test data
- Aim for high test coverage

## Documentation
- Document all public APIs
- Use type hints as documentation
- Keep README updated
- Document configuration options

## Performance
- Profile before optimizing
- Use generators for large datasets
- Consider async for I/O-bound tasks
- Cache expensive computations

## Security
- Validate all inputs
- Use parameterized queries
- Don't store secrets in code
- Follow OWASP guidelines
HEREDOC

    write_file ".antigravity/rules/python.md" "$content"
}

# ============================================================================
# Templates dos Workflows
# ============================================================================

create_workflow_bootstrap() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Bootstrap Workflow

## Purpose
Initial project setup and environment configuration.

## Steps

### 1. Clone Repository
```bash
git clone <repository-url>
cd <project-name>
```

### 2. Copy Environment File
```bash
cp .env.example .env
```

### 3. Install Dependencies
Install dependencies according to your stack:

**PHP/Laravel:**
```bash
composer install
```

**Node.js:**
```bash
npm install
```

**Python:**
```bash
pip install -r requirements.txt
```

### 4. Configure Environment
- Update `.env` with appropriate values
- Generate application keys if needed
- Configure database connection

### 5. Setup Database
Run migrations and seeders if applicable.

### 6. Build Assets
Build frontend assets if applicable.

### 7. Verify Installation
- Access the application
- Check that all features work correctly

## Troubleshooting
- Check logs for errors
- Ensure all dependencies are installed
- Verify environment configuration
- Check file permissions
HEREDOC

    write_file ".antigravity/workflows/bootstrap.md" "$content"
}

create_workflow_feature_development() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Feature Development Workflow

## Purpose
Standard workflow for implementing new features.

## Steps

### 1. Create Feature Branch
```bash
git checkout -b feature/<feature-name>
```

### 2. Understand Requirements
- Review related documentation
- Identify affected components
- List acceptance criteria

### 3. Plan Implementation
- Define interfaces/contracts first
- Break down into small tasks
- Identify dependencies

### 4. Implement
- Write tests first (TDD when possible)
- Implement the feature
- Follow coding standards
- Keep commits atomic

### 5. Test
Run all tests to ensure nothing is broken.

### 6. Review
- Self-review code changes
- Ensure documentation is updated
- Verify all tests pass

### 7. Submit
```bash
git push origin feature/<feature-name>
```
- Create pull request
- Request code review

## Checklist
- [ ] Tests written and passing
- [ ] Documentation updated
- [ ] Coding standards followed
- [ ] No breaking changes (or documented)
HEREDOC

    write_file ".antigravity/workflows/feature-development.md" "$content"
}

create_workflow_refactor() {
    local content
    read -r -d '' content << 'HEREDOC' || true
# Refactor Workflow

## Purpose
Safe refactoring of existing code while maintaining functionality.

## Steps

### 1. Identify Scope
- Define what needs refactoring
- Understand current behavior
- Document existing tests

### 2. Ensure Test Coverage
- Run existing tests
- Add missing tests for current behavior
- All tests must pass before refactoring

### 3. Create Refactor Branch
```bash
git checkout -b refactor/<scope-description>
```

### 4. Refactor Incrementally
- Make small, focused changes
- Run tests after each change
- Commit frequently

### 5. Verify Behavior
- Ensure no behavioral changes
- Compare before/after outputs if applicable

### 6. Review Changes
- Check code quality improvements
- Verify performance implications
- Ensure readability improved

### 7. Update Documentation
- Update affected documentation
- Add ADR if architectural change

## Golden Rules
- Never refactor and add features simultaneously
- Tests must pass at every step
- If unsure, discuss first
HEREDOC

    write_file ".antigravity/workflows/refactor.md" "$content"
}

# ============================================================================
# Função Principal de Criação
# ============================================================================

create_all_files() {
    print_step "Criando arquivos do Antigravity Agent System"

    # Arquivo principal na raiz
    create_agents_md

    # Arquivos no .antigravity/
    create_context_md
    create_session_state_md
    create_readme_md

    # Agentes
    print_step "Criando definições dos agentes"
    create_agent_architect
    create_agent_backend
    create_agent_frontend
    create_agent_devops
    create_agent_qa

    # Regras base (sempre criadas)
    print_step "Criando regras base"
    create_rule_global
    create_rule_coding_standards

    # Regras específicas por stack (se não minimal)
    if [ "$MINIMAL" = false ]; then
        print_step "Criando regras específicas para stack: $STACK"

        case "$STACK" in
            "laravel")
                create_rule_laravel
                ;;
            "filament")
                create_rule_laravel
                create_rule_filament
                ;;
            "node")
                create_rule_node
                ;;
            "react")
                create_rule_node
                create_rule_react
                ;;
            "nextjs")
                create_rule_node
                create_rule_react
                create_rule_nextjs
                ;;
            "python")
                create_rule_python
                ;;
            "generic"|*)
                print_info "Stack genérica: apenas regras base criadas"
                ;;
        esac
    fi

    # Workflows
    print_step "Criando workflows"
    create_workflow_bootstrap
    create_workflow_feature_development
    create_workflow_refactor
}

# ============================================================================
# Sumário Final
# ============================================================================

print_summary() {
    print_step "Instalação Concluída"

    echo -e "\n${GREEN}Antigravity Agent System instalado com sucesso!${NC}\n"

    echo -e "${YELLOW}Stack:${NC} $STACK"
    echo -e "${YELLOW}Projeto:${NC} $PROJECT_NAME"

    echo -e "\n${CYAN}Estrutura criada:${NC}"
    echo "  AGENTS.md                    # Bootstrap na raiz"
    echo "  .antigravity/"
    echo "  ├── context.md               # Contexto do projeto"
    echo "  ├── session-state.md         # Estado entre sessões"
    echo "  ├── README.md                # Guia rápido"
    echo "  ├── agents/                  # 5 agentes especializados"
    echo "  │   ├── architect.md"
    echo "  │   ├── backend.md"
    echo "  │   ├── frontend.md"
    echo "  │   ├── devops.md"
    echo "  │   └── qa.md"
    echo "  ├── rules/                   # Regras de código"
    echo "  │   ├── global.md"
    echo "  │   └── coding-standards.md"

    if [ "$MINIMAL" = false ]; then
        case "$STACK" in
            "laravel")
                echo "  │   └── laravel.md"
                ;;
            "filament")
                echo "  │   ├── laravel.md"
                echo "  │   └── filament.md"
                ;;
            "node")
                echo "  │   └── node.md"
                ;;
            "react")
                echo "  │   ├── node.md"
                echo "  │   └── react.md"
                ;;
            "nextjs")
                echo "  │   ├── node.md"
                echo "  │   ├── react.md"
                echo "  │   └── nextjs.md"
                ;;
            "python")
                echo "  │   └── python.md"
                ;;
        esac
    fi

    echo "  └── workflows/               # Workflows pré-definidos"
    echo "      ├── bootstrap.md"
    echo "      ├── feature-development.md"
    echo "      └── refactor.md"

    echo -e "\n${CYAN}Próximos passos:${NC}"
    echo -e "  1. Edite ${YELLOW}AGENTS.md${NC} com informações do projeto"
    echo -e "  2. Preencha os placeholders em ${YELLOW}.antigravity/context.md${NC}"
    echo -e "  3. Adicione ${YELLOW}.antigravity/${NC} e ${YELLOW}AGENTS.md${NC} ao git"
    echo -e "  4. Diga ao seu assistente de IA: ${CYAN}\"Ative o modo agente @AGENTS.md\"${NC}"

    echo -e "\n${GREEN}Done!${NC} 🚀\n"
}

# ============================================================================
# Parse de Argumentos
# ============================================================================

parse_args() {
    while [[ $# -gt 0 ]]; do
        case $1 in
            --stack)
                STACK="$2"
                AUTO_DETECT=false
                shift 2
                ;;
            --detect)
                AUTO_DETECT=true
                shift
                ;;
            --minimal)
                MINIMAL=true
                shift
                ;;
            --force)
                FORCE=true
                shift
                ;;
            --dry-run)
                DRY_RUN=true
                shift
                ;;
            -h|--help)
                show_help
                exit 0
                ;;
            *)
                print_error "Opção desconhecida: $1"
                show_help
                exit 1
                ;;
        esac
    done

    # Validar stack se especificada manualmente
    if [ "$AUTO_DETECT" = false ]; then
        case "$STACK" in
            "laravel"|"filament"|"node"|"react"|"nextjs"|"python"|"generic")
                ;;
            *)
                print_error "Stack inválida: $STACK"
                echo "Stacks válidas: laravel, filament, node, react, nextjs, python, generic"
                exit 1
                ;;
        esac
    fi
}

# ============================================================================
# Main
# ============================================================================

main() {
    print_header

    parse_args "$@"

    if [ "$DRY_RUN" = true ]; then
        print_warning "Modo DRY-RUN: nenhum arquivo será criado"
    fi

    if [ "$AUTO_DETECT" = true ]; then
        detect_stack
    else
        print_info "Stack especificada manualmente: $STACK"
    fi

    create_directories
    create_all_files

    if [ "$DRY_RUN" = false ]; then
        print_summary
    else
        echo -e "\n${YELLOW}Modo DRY-RUN concluído. Execute sem --dry-run para criar os arquivos.${NC}\n"
    fi
}

main "$@"
