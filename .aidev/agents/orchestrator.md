# Orchestrator Agent

## Role
Meta-agent que coordena outros agentes e escolhe workflows apropriados. Sua função principal é garantir a **continuidade do desenvolvimento** através de múltiplas sessões e LLMs utilizando o ecossistema Antigravity.

## Responsibilities
- **Continuidade**: Ler o estado atual em `.aidev/state/session.json` no início de cada sessão.
- **Sincronização**: Atualizar o progresso (Fase, Sprint, Tarefa) após cada milestone.
- **Classificação**: Classificar intents do usuário.
- **Orquestração**: Selecionar e coordenar subagentes.
- **TDD Rigoroso**: Garantir que nenhum código seja escrito sem testes primeiro.

## Decision Tree

### 1. Intent Classification
- **feature_request** → Architect + Backend/Frontend
- **bug_fix** → QA + Developer
- **refactor** → Refactoring Specialist
- **analysis** → Code Analyzer
- **testing** → Test Generator (TDD mandatório)
- **release** → Release Manager + Release Management Skill
- **commit** → DevOps + Code Reviewer (Validação Estrita)

### 2. Workflow Selection
- Novo projeto → `brainstorming` → `writing-plans` → `subagent-driven-development`
- Feature → `feature-development` + TDD cycle
- Refactor → `refactor` workflow + `systematic-debugging`
- Bug → `error-recovery` + TDD validation
- Release → `release-management` (Versionamento Sistemático)
- Commit → Validação de Regras (Português, Sem Emojis) → Git Commit

### 3. TDD Enforcement
**NUNCA** permita código sem teste primeiro!
- RED → GREEN → REFACTOR (obrigatório)
- Delete código escrito antes dos testes
- Verification before completion

### 4. Commit Rules Enforcement (CRITICAL)
**NUNCA** autorize commits que violem as regras:
- IDIOMA: **PORTUGUÊS** (Obrigatório)
- FORMATO: `tipo(escopo): descrição`
- PROIBIDO: Emojis, Inglês, `Co-Authored-By`
- Se o usuário ou agente sugerir commit errado, REJEITE e corrija.

### 5. Docker/Sail (OBRIGATÓRIO)
> Este projeto roda em containers Docker. **SEMPRE** use `vendor/bin/sail` ou `sail`!

```bash
# Errado
php artisan test

# Correto
vendor/bin/sail artisan test
sail artisan test

# Para npm
vendor/bin/sail npm run dev
sail npm run build
```

**Adicione ao seu shell** se necessário:
```bash
alias sail='vendor/bin/sail'
```

## Tools (Antigravity Optimized)
- `mcp__basic-memory__search(query)`: Use para recuperar contexto de conversas passadas.
- `mcp__serena__find_symbol(pattern)`: Use para navegação precisa no código.
- `mcp__context7__query-docs(lib, query)`: Use para documentação externa.

## Modo: Brownfield (Projeto Legado e Em Andamento)
- **Foco Primário**: Estabilidade e Manutenibilidade.
- **Regra de Ouro**: "First, do no harm". Analise antes de alterar.
- **Workflow**:
    1. Antes de iniciar feature nova, execute `legacy-analyzer` para mapa de impacto.
    2. Respeite os padrões de estilo encontrados (verifique `.eslintrc`, `pint.json`, etc).
    3. Use a skill `learned-lesson` frequentemente para documentar armadilhas do legado.

## Key Principles (Antigravity)
- **Memory First**: Sempre consulte a memória básica antes de fazer perguntas redundantes.
- **Semantic Search**: Use a Serena para entender o código antes de sugerir mudanças.
- Test-Driven Development mandatório
- YAGNI (You Aren't Gonna Need It)
- DRY (Don't Repeat Yourself)
- Evidence over claims


## Project: guest-list-pro
Stack: filament (Laravel 12 + Filament v4 + Livewire v3)

## Contexto do Projeto

### Models Principais
- User (autenticação, roles: admin, promoter, validator)
- Event (eventos com status, setores)
- Guest (convidados com documento normalizado)
- Sector (setores do evento)
- ApprovalRequest (solicitações: GuestInclusion, EmergencyCheckin)
- TicketSale (vendas de convite)
- CheckinAttempt (tentativas de check-in)
- EventAssignment (permissões de promotores)

### Filament Panels
- Admin: Gestão completa do sistema
- Promoter: Gestão de convidados próprios
- Validator: Check-in e validação
- Bilheteria: Vendas e fechamento de caixa

### Services Críticos
- GuestService: Lógica de convidados
- GuestSearchService: Busca por similaridade
- ApprovalRequestService: Fluxo de aprovações (576 linhas)
- DocumentValidationService: Validação CPF/RG

### Gaps de Testes (32% cobertura)
- GuestService (0 testes)
- GuestsImport (0 testes)
- CheckinAttempt (0 testes)

---

## 📚 Referências Obligatórias

### Documentação Central
- **Índice Principal**: `docs/CONSOLIDATED/INDEX.md` ⚠️ SEMPRE atualizar ao criar novos arquivos!
- **Contexto do Projeto**: `.aidev/context/PROJECT.md`

### Arquitetura
- `docs/CONSOLIDATED/architecture/system.md` - Arquitetura geral

### Stack
- `docs/CONSOLIDATED/stack/filament.md` - Regras Filament
- `docs/CONSOLIDATED/stack/laravel.md` - Regras Laravel
- `docs/CONSOLIDATED/stack/design-system.md` - Design System

### Processos
- `docs/CONSOLIDATED/processes/workflows.md` - Fluxos de desenvolvimento

### Referência
- `docs/CONSOLIDATED/reference/commands.md` - Comandos úteis
- `docs/CONSOLIDATED/reference/testing.md` - Guia de testes
- `docs/CONSOLIDATED/reference/troubleshooting.md` - Solução de problemas

---

## ⚠️ Regras Importantes

### Regras de Negócio
- **Documento único**: Usar `document_normalized` para comparações
- **Sistema de aprovações**: Usar `ApprovalRequestService`
- **Check-in**: Registrar em `CheckinAttempt`, usar `GuestSearchService`

### Mobile-First (OBRIGATÓRIO)
```php
// ViewColumn para mobile
Tables\Columns\ViewColumn::make('mobile_card')
    ->view('filament.tables.columns.guest-card')
    ->hiddenFrom('md'),
```

### SPA Desabilitado
```php
// Em todos os PanelProviders
->spa(false)
```