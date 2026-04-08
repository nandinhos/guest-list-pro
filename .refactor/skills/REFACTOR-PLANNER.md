# Skill: Refactor Planner

## Objetivo

Planejar a reconstrução do sistema, definindo estrutura de módulos, estratégia de migração e prioridades.

## Input

- **domains**: Domínios extraídos
- **risks**: Riscos detectados

## Pré-requisitos

- [Project Scanner](PROJECT-SCANNER.md) ter sido executado
- [Runtime Behavior Mapper](RUNTIME-BEHAVIOR-MAPPER.md) ter sido executado
- [Laravel Analyzer](LARAVEL-ANALYZER.md) ter sido executado
- [Domain Extractor](DOMAIN-EXTRACTOR.md) ter sido executado
- [Risk Detector](RISK-DETECTOR.md) ter sido executado

## Ações Executadas

### 1. Definição de Estrutura de Módulos

Organiza o código em módulos baseado nos domínios:
- Separação por contexto delimitado
- Identificação de módulos existentes
- Proposta de novos módulos

### 2. Separação por Domínio

Aplica DDD para separar responsabilidades:
- Identifica bounded contexts
- Define agregados
- Separa entidades de value objects

### 3. Elaboração de Estratégia de Migração

Define como será a migração do sistema legado:
- Strangler Fig Pattern
- Big Bang (se pequeno)
- Feature flags
- Blue/Green

### 4. Priorização de Refatorações

Ordena as refatorações por:
- Risco (criticos primeiro)
- Dependências (base primeiro)
- Esforço (quick wins)
- Impacto (maior valor)

## Output

**Arquivo**: `decisions/refactor-plan.json`

**Formato**:

```json
{
  "id": "decision-refactor-plan",
  "type": "decision",
  "name": "Refactor Plan",
  "created_at": "2026-04-08T14:35:00Z",
  "updated_at": "2026-04-08T14:35:00Z",
  "source": {
    "files": [],
    "routes": [],
    "components": []
  },
  "summary": "Plano de refatoração para o sistema",
  "details": {
    "modules": [
      {
        "id": "module-guest-management",
        "name": "GuestManagement",
        "description": "Gestão de convidados",
        "entities": ["Guest", "GuestStatus"],
        "priority": 1,
        "dependencies": [],
        "risks_addressed": ["duplicate-validation"],
        "status": "pending"
      },
      {
        "id": "module-event-management",
        "name": "EventManagement",
        "description": "Gestão de eventos",
        "entities": ["Event", "TicketType"],
        "priority": 2,
        "dependencies": ["GuestManagement"],
        "risks_addressed": [],
        "status": "pending"
      }
    ],
    "migration_strategy": "strangler-fig-pattern",
    "strategy_description": "Gradualmente substituir funcionalidades legadas por novas implementações, mantendo ambas em execução até completa transição",
    "phases": [
      {
        "phase": 1,
        "name": "Foundation",
        "description": "Setup de infraestrutura e correções críticas",
        "duration": "1 semana",
        "tasks": [
          {
            "id": "task-setup-validation",
            "description": "Centralizar validações em FormRequests",
            "effort": "medium",
            "risks_addressed": ["duplicate-validation"]
          },
          {
            "id": "task-add-transactions",
            "description": "Adicionar transactions em operações críticas",
            "effort": "low",
            "risks_addressed": ["missing-transaction"]
          }
        ]
      },
      {
        "phase": 2,
        "name": "Guest Management",
        "description": "Implementar novo módulo de gestão de convidados",
        "duration": "3 semanas",
        "tasks": [
          {
            "id": "task-create-guest-module",
            "description": "Criar GuestManagement module",
            "effort": "high"
          },
          {
            "id": "task-migrate-guests",
            "description": "Migrar dados de guests",
            "effort": "medium"
          }
        ]
      },
      {
        "phase": 3,
        "name": "Event Management",
        "description": "Implementar módulo de gestão de eventos",
        "duration": "3 semanas",
        "tasks": [
          {
            "id": "task-create-event-module",
            "description": "Criar EventManagement module",
            "effort": "high"
          }
        ]
      },
      {
        "phase": 4,
        "name": "Decommission",
        "description": "Remover código legado",
        "duration": "1 semana",
        "tasks": [
          {
            "id": "task-remove-legacy",
            "description": "Remover código não mais utilizado",
            "effort": "medium"
          }
        ]
      }
    ],
    "priorities": [
      {
        "priority": 1,
        "item": "Centralizar validações",
        "reason": "Alto risco de inconsistência",
        "effort": "medium",
        "impact": "high"
      },
      {
        "priority": 2,
        "item": "Adicionar transactions",
        "reason": "Integridade de dados",
        "effort": "low",
        "impact": "critical"
      },
      {
        "priority": 3,
        "item": "Configurar retry em jobs",
        "reason": "Confiabilidade",
        "effort": "low",
        "impact": "medium"
      }
    ],
    "estimated_duration": "8 semanas",
    "risks_summary": {
      "critical": 1,
      "high": 1,
      "medium": 1,
      "addressed_in_phase": {
        "1": ["duplicate-validation", "missing-transaction"],
        "2": [],
        "3": [],
        "4": ["job-no-retry"]
      }
    }
  },
  "relationships": [
    "domains/guest-management.json",
    "risks/validation.json"
  ]
}
```

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único |
| type | string | Sempre "decision" |
| modules | array | Módulos propostos |
| migration_strategy | string | Estratégia de migração |
| phases | array | Fases de implementação |
| priorities | array | Prioridades de refatoração |
| estimated_duration | string | Estimativa de tempo |

## Estratégias de Migração

| Estratégia | Quando Usar | Vantagens | Desvantagens |
|------------|-------------|-----------|--------------|
| Strangler Fig | Sistemas médios/grandes | Migração gradual, menos risco | Mais complexo |
| Big Bang | Sistemas pequenos | Simples, rápido | Alto risco |
| Feature Flags | Qualquer | Controle fino |代码复杂 |
| Blue/Green | Qualquer | Zero downtime | Recursos extras |

## Dependências

- **Inputs**: 
  - `domains/*.json`
  - `risks/*.json`
- **Fim da pipeline**: Este é o último passo

## Exemplo de Uso

```bash
# Gerar plano de refatoração
php artisan refactor:plan

# Gerar com estratégia específica
php artisan refactor:plan --strategy blue-green
```

---

*Última atualização: 2026-04-08*
