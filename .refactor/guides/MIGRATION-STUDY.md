# Guia para Estudo de Migração

## Visão Geral

Este guia descreve como usar a pipeline ia-refactor para embasar estudos de migração de sistemas legados Laravel + Filament.

## O que é Migração de Sistema?

Migração de sistema é o processo de mover um sistema de uma plataforma/tecnologia para outra. No contexto desta pipeline, focamos em:

- **Migração de versão Laravel** (ex: 5.1 → 12)
- **Modernização de stack** (ex: Laravel + Blade → Laravel + Livewire + Filament)
- **Refatoração** (ex: Código legado → Código modularizado)

## Por que usar a Pipeline?

A pipeline fornece:

1. **Visibilidade completa** — Mapa do sistema atual
2. **Risco identificado** — Problemas documentados
3. **Domínio extraído** — Entidades e regras claras
4. **Plano de ação** — Prioridades e fases definidas

## Fluxo de Estudo de Migração

### Passo 1: Entender o Sistema Atual

Use o **Project Scanner** para mapear a estrutura:

```bash
php artisan refactor:scan
```

**Output esperado**: `analysis/project-structure.json`

**Perguntas respondidas**:
- Quais modelos existem?
- Quais recursos Filament estão em uso?
- Quais serviços contêm lógica de negócio?

### Passo 2: Mapear Comportamentos

Use o **Runtime Behavior Mapper** para cada feature crítica:

```bash
php artisan refactor:map --action "create guest"
php artisan refactor:map --action "approve guest"
php artisan refactor:map --action "list events"
```

**Output esperado**: `flows/*.json`

**Perguntas respondidas**:
- Quais fluxos existem?
- Quais serviços são usados?
- Quais side-effects ocorrem?

### Passo 3: Analisar Código

Use o **Laravel Analyzer** para arquivos críticos:

```bash
php artisan refactor:analyze --file app/Services/GuestService.php
```

**Output esperado**: `analysis/*.json`

**Perguntas respondidas**:
- Quaisviolações de SRP existem?
- Onde validações estão localizadas?
- Quais Facades estão sendo usadas?

### Passo 4: Extrair Domínio

Use o **Domain Extractor** para identificar entidades:

```bash
php artisan refactor:extract-domain
```

**Output esperado**: `domains/*.json`

**Perguntas respondidas**:
- Quais são as entidades de negócio?
- Quais são as regras de negócio?
- Quais são os relacionamentos?

### Passo 5: Detectar Riscos

Use o **Risk Detector** para identificar problemas:

```bash
php artisan refactor:detect-risks
```

**Output esperado**: `risks/*.json`

**Perguntas respondidas**:
- Quais são os riscos críticos?
- Quais são os riscos altos?
- Onde estão as duplicações?

### Passo 6: Criar Plano

Use o **Refactor Planner** para gerar um plano:

```bash
php artisan refactor:plan
```

**Output esperado**: `decisions/refactor-plan.json`

**Perguntas respondidas**:
- Qual a estratégia de migração?
- Quais são as fases?
- Quais são as prioridades?

## Relação com Estudo de Migração

### Análise de Requisitos

| Pipeline Output | Uso na Migração |
|----------------|-----------------|
| `project-structure.json` | Inventário de código |
| `flows/*.json` | Mapeamento de funcionalidades |
| `domains/*.json` | Definição de entidades |
| `risks/*.json` | Riscos aaddressar |

### Planejamento

| Pipeline Output | Uso no Planejamento |
|----------------|---------------------|
| `decisions/refactor-plan.json` | Roadmap de migração |
| Prioridades | Sequence de implementação |
| Fases | Sprint planning |

### Documentação

| Pipeline Output | Uso na Documentação |
|----------------|---------------------|
| `domains/*.json` | Modelo de domínio |
| `flows/*.json` | Documentação de features |
| `risks/*.json` | Lista de tech debt |

## Exemplo Prático: Migração guest-list-pro

### Contexto

Sistema de gestão de convidados com:
- Laravel 12 + Filament v4 + Livewire v3
- Controle de duplicidade
- Sistema de aprovações
- Integração com bilheteria

### Passo 1: Scan

```bash
php artisan refactor:scan
```

**Resultado**:
- 2 Models: Guest, Event
- 2 Resources: GuestResource, EventResource
- 1 Service: GuestService

### Passo 2: Map Flows

```bash
php artisan refactor:map --action "create guest"
```

**Resultado**:
- Rota: POST /admin/guests
- Controller: GuestResource\Pages\CreateGuest
- Service: GuestService::create
- Event: GuestCreated
- Job: SendGuestNotification

### Passo 3: Analyze

```bash
php artisan refactor:analyze --file app/Services/GuestService.php
```

**Resultado**:
- SRP violation: Enviando email no service
- Facade abuse: Mail facade usado diretamente

### Passo 4: Extract Domain

```bash
php artisan refactor:extract-domain
```

**Resultado**:
- Entities: Guest, Event
- Rules: No duplicate email, Pending to approved
- Invariants: Guest must have contact
- Relationships: Guest ↔ Event (many-to-many)

### Passo 5: Detect Risks

```bash
php artisan refactor:detect-risks
```

**Resultado**:
- Critical: Missing transaction (create + email)
- High: Duplicate validation
- Medium: Job no retry

### Passo 6: Plan

```bash
php artisan refactor:plan
```

**Resultado**:
- Strategy: Strangler Fig
- Phase 1: Fix critical issues (1 week)
- Phase 2: New Guest module (3 weeks)
- Phase 3: New Event module (3 weeks)
- Phase 4: Decommission legacy (1 week)

## Checklist de Migração

### Pré-Migração

- [ ] Executar Project Scanner
- [ ] Mapear todos os fluxos críticos
- [ ] Analisar código de serviços
- [ ] Extrair domínios
- [ ] Detectar riscos
- [ ] Criar plano de refatoração

### Riscos a Addressar

- [ ] Validações centralizadas
- [ ] Transactions em operações críticas
- [ ] Jobs com retry configurado
- [ ] Dependências circulares resolvidas

### Documentação

- [ ] Modelo de domínio documentado
- [ ] Fluxos mapeados
- [ ] Riscos e Mitigações registrados

## Ferramentas de Suporte

### Leitura de Outputs

```bash
# Ver estrutura do projeto
cat .refactor/analysis/project-structure.json | jq

# Ver fluxos
ls -la .refactor/flows/

# Ver riscos
cat .refactor/risks/*.json | jq '.details.risk_level'

# Ver plano
cat .refactor/decisions/refactor-plan.json | jq '.details.phases'
```

### Índices

```bash
# Ver índice global
cat .refactor/index.json | jq
```

## Próximos Passos

1. **Executar a pipeline** — Gerar todos os outputs
2. **Revisar riscos** — Entender problemas críticos
3. **Definir estratégia** — Escolher abordagem de migração
4. **Planejar fases** — Sequenciar implementação
5. **Documentar decisões** — Registrar rationale

---

*Última atualização: 2026-04-08*
