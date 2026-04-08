# Pipeline ia-Refactor — Fluxo de Execução

## Visão Geral

Este documento descreve o fluxo de execução da pipeline, detalhando como cada skill interage com as outras e como os dados fluem entre elas.

## Sequência de Execução

### Etapa 1: Project Scanner

**Objetivo**: Mapear toda a estrutura do projeto Laravel

**Input**: Root do projeto (`/home/nandodev/projects/guest-list-pro`)

**Ações executadas**:
1. varrer diretórios do projeto
2. identificar arquivos por tipo (models, controllers, resources, jobs, events, services, providers)
3. classificar arquivos em categorias
4. gerar estrutura de metadados

**Output**: `analysis/project-structure.json`

```json
{
  "models": ["app/Models/Guest.php", "app/Models/Event.php"],
  "controllers": [],
  "resources": ["app/Filament/Resources/GuestResource.php"],
  "jobs": [],
  "events": [],
  "services": [],
  "unknown": []
}
```

**Dependências**: Nenhuma (primeira etapa)

---

### Etapa 2: Runtime Behavior Mapper

**Objetivo**: Mapear comportamento real da aplicação baseado em execução

**Input**: Ação do usuário (ex: "create guest", "approve guest")

**Ações executadas**:
1. identificar rota correspondente
2. identificar controller/resource responsável
3. mapear cadeia de execução:
   - methods chamados
   - services utilizados
   - models afetados
   - jobs disparados
   - events disparados
4. identificar side-effects (emails, logs, integrações externas)

**Output**: `flows/{feature}.json`

```json
{
  "id": "flow-create-guest",
  "steps": [
    {
      "layer": "controller",
      "action": "GuestResource\\Pages\\CreateGuest::create",
      "file": "app/Filament/Resources/GuestResource/Pages/CreateGuest.php"
    },
    {
      "layer": "service",
      "action": "GuestService::create",
      "file": "app/Services/GuestService.php"
    }
  ],
  "side_effects": ["mail: GuestCreatedNotification"],
  "dependencies": ["app/Models/Guest.php"]
}
```

**Dependências**: Project Scanner (etapa 1)

---

### Etapa 3: Laravel Analyzer

**Objetivo**: Analisar código Laravel com contexto pré-mapeado

**Input**: Lista de arquivos identificados nas etapas anteriores

**Ações executadas**:
1. analisar responsabilidades de cada arquivo
2. detectar violações de SRP (Single Responsibility Principle)
3. identificar acoplamento entre classes
4. detectar uso indevido de Facades
5. identificar regras de validação no lugar errado
6. mapear dependências

**Output**: `analysis/{file}.json`

```json
{
  "file": "app/Services/GuestService.php",
  "responsibilities": ["criar convidado", "validar duplicata"],
  "violations": [
    {
      "type": "SRP",
      "description": "Service também envia email",
      "suggestion": "Extrair para Notification class"
    }
  ],
  "facades_used": ["Mail", "Log"],
  "coupling": ["Guest", "Event"],
  "validation_location": "controller"
}
```

**Dependências**: Project Scanner + Runtime Behavior Mapper (etapas 1 + 2)

---

### Etapa 4: Domain Extractor

**Objetivo**: Transformar código em domínio de negócio

**Input**: flows + analysis das etapas anteriores

**Ações executadas**:
1. identificar entidades de negócio (Guest, Event, Ticket)
2. identificar regras de negócio (validações, autorizações)
3. identificar invariantes (regras que devem ser sempre verdadeiras)
4. mapear relacionamentos entre entidades

**Output**: `domains/{domain}.json`

```json
{
  "domain": "guest-management",
  "entities": [
    {
      "id": "entity-guest",
      "name": "Guest",
      "attributes": ["name", "email", "phone", "status"],
      "repository": "app/Repositories/GuestRepository.php"
    }
  ],
  "rules": [
    {
      "id": "rule-no-duplicate-email",
      "description": "Email deve ser único por evento",
      "enforcement": "database unique constraint + application validation"
    }
  ],
  "invariants": [
    "guest deve ter pelo menos um contato (email ou phone)",
    "guest aprovado não pode ser removido"
  ],
  "relationships": [
    {
      "from": "Guest",
      "to": "Event",
      "type": "many-to-many",
      "pivot": "guest_event"
    }
  ]
}
```

**Dependências**: Todas as etapas anteriores

---

### Etapa 5: Risk Detector

**Detectar riscos arquiteturais

**Input**: análise completa (todas as etapas anteriores)

**Ações executadas**:
1. detectar regras duplicadas
2. identificar escrita concorrente
3. verificar falta de transactions
4. identificar jobs sem controle de retries
5. detectar dependências circulares

**Output**: `risks/{context}.json`

```json
{
  "risk_level": "high",
  "issues": [
    {
      "type": "duplicate-validation",
      "description": "Validação de email duplicado em 3 lugares",
      "impact": "Manutenção difícil, potencial inconsistency",
      "locations": [
        "app/Http/Controllers/GuestController.php",
        "app/Services/GuestService.php",
        "app/Models/Guest.php"
      ],
      "suggestion": "Centralizar em GuestValidator ou Policy"
    },
    {
      "type": "missing-transaction",
      "description": "Criação de guest + envio de email sem transaction",
      "impact": "Email enviado mas guest não foi criado",
      "suggestion": "Wrap em DB::transaction()"
    }
  ]
}
```

**Dependências**: Todas as etapas anteriores

---

### Etapa 6: Refactor Planner

**Objetivo**: Planejar reconstrução do sistema

**Input**: domains + risks

**Ações executadas**:
1. definir estrutura de módulos
2. determinar separação por domínio
3. elaborar estratégia de migração
4. priorizar refatorações

**Output**: `decisions/refactor-plan.json`

```json
{
  "modules": [
    {
      "name": "GuestManagement",
      "entities": ["Guest", "GuestStatus"],
      "priority": 1,
      "dependencies": []
    },
    {
      "name": "EventManagement",
      "entities": ["Event", "TicketType"],
      "priority": 2,
      "dependencies": ["GuestManagement"]
    }
  ],
  "migration_strategy": "strangler-fig-pattern",
  "phases": [
    {
      "phase": 1,
      "description": "Isolar GuestManagement",
      "duration": "2 semanas"
    },
    {
      "phase": 2,
      "description": "Migrar EventManagement",
      "duration": "3 semanas"
    }
  ],
  "priorities": [
    {
      "priority": 1,
      "item": "Centralizar validações",
      "reason": "Alto risco de inconsistency"
    },
    {
      "priority": 2,
      "item": "Adicionar transactions",
      "reason": "Integridade de dados"
    }
  ]
}
```

**Dependências**: Domain Extractor + Risk Detector (etapas 4 + 5)

---

## Fluxo de Dados

```
Project Scanner
      │
      ▼
[ analysis/project-structure.json ]
      │
      ▼
Runtime Behavior Mapper ◄────────────┐
      │                               │
      ▼                               │
[ flows/*.json ]                    │
      │                               │
      ▼                               │
Laravel Analyzer ◄───────────────────┘
      │
      ▼
[ analysis/*.json ]
      │
      ▼
Domain Extractor ◄───────────────────┘
      │
      ▼
[ domains/*.json ]
      │
      ▼
Risk Detector ◄──────────────────────┘
      │
      ▼
[ risks/*.json ]
      │
      ▼
Refactor Planner ◄───────────────────┘
      │
      ▼
[ decisions/refactor-plan.json ]
      │
      ▼
[ index.json atualizado ]
```

## Versionamento

Cada skill deve:
1. Verificar se já existe output anterior
2. Criar novo arquivo com sufixo de timestamp ou ID único
3. Atualizar índice (index.json) com referências

Formato de versionamento:
- `analysis/project-structure.json` (v1)
- `analysis/project-structure-2026-04-08-1430.json` (versão específica)

## Rastreabilidade

Cada output deve conter:
- `source.files`: Arquivos de origem
- `source.routes`: Rotas envolvidas  
- `source.components`: Componentes afetados
- `relationships`: Referências a outros documentos

---

*Última atualização: 2026-04-08*
