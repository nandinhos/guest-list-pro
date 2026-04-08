# Exemplos de Output — Referência

## Visão Geral

Este documento contém exemplos completos de cada tipo de output gerado pela pipeline.

## 1. Project Structure

### Exemplo Completo

```json
{
  "id": "analysis-project-structure",
  "type": "analysis",
  "name": "Project Structure",
  "created_at": "2026-04-08T14:30:00Z",
  "updated_at": "2026-04-08T14:30:00Z",
  "source": {
    "files": ["/home/nandodev/projects/guest-list-pro"],
    "routes": [],
    "components": []
  },
  "summary": "Mapeamento completo da estrutura do projeto Laravel guest-list-pro",
  "details": {
    "models": [
      "app/Models/Guest.php",
      "app/Models/Event.php",
      "app/Models/GuestStatus.php"
    ],
    "controllers": [],
    "resources": [
      "app/Filament/Resources/GuestResource.php",
      "app/Filament/Resources/EventResource.php"
    ],
    "jobs": [
      "app/Jobs/SendGuestNotification.php"
    ],
    "events": [
      "app/Events/GuestCreated.php"
    ],
    "services": [
      "app/Services/GuestService.php"
    ],
    "livewire": [],
    "notifications": [
      "app/Notifications/GuestCreatedNotification.php"
    ],
    "policies": [
      "app/Policies/GuestPolicy.php"
    ],
    "migrations": [
      "database/migrations/2024_01_01_000001_create_guests_table.php",
      "database/migrations/2024_01_01_000002_create_events_table.php"
    ]
  },
  "statistics": {
    "total_files": 24,
    "total_models": 3,
    "total_resources": 2,
    "total_services": 1,
    "total_jobs": 1,
    "total_events": 1,
    "total_migrations": 2
  },
  "relationships": []
}
```

---

## 2. Flow

### Exemplo: Create Guest Flow

```json
{
  "id": "flow-create-guest",
  "type": "flow",
  "name": "Create Guest",
  "created_at": "2026-04-08T14:31:00Z",
  "updated_at": "2026-04-08T14:31:00Z",
  "source": {
    "files": [
      "app/Filament/Resources/GuestResource/Pages/CreateGuest.php"
    ],
    "routes": ["POST /admin/guests"],
    "components": ["GuestResource"]
  },
  "summary": "Fluxo de criação de convidado através do admin panel Filament",
  "details": {
    "trigger": {
      "type": "http_request",
      "method": "POST",
      "url": "/admin/guests",
      "description": "Formulário de criação de convidado"
    },
    "steps": [
      {
        "order": 1,
        "layer": "controller",
        "action": "GuestResource\\Pages\\CreateGuest::create",
        "file": "app/Filament/Resources/GuestResource/Pages/CreateGuest.php",
        "line": 45,
        "description": "Valida dados do formulário via form request"
      },
      {
        "order": 2,
        "layer": "service",
        "action": "GuestService::create",
        "file": "app/Services/GuestService.php",
        "line": 23,
        "description": "Cria registro no banco e valida duplicata"
      },
      {
        "order": 3,
        "layer": "model",
        "action": "Guest::created event",
        "file": "app/Models/Guest.php",
        "line": 78,
        "description": "Dispara evento de criação"
      },
      {
        "order": 4,
        "layer": "event",
        "action": "GuestCreated::dispatch",
        "file": "app/Events/GuestCreated.php",
        "line": 15,
        "description": "Dispara job de notificação"
      },
      {
        "order": 5,
        "layer": "job",
        "action": "SendGuestNotification::dispatch",
        "file": "app/Jobs/SendGuestNotification.php",
        "line": 12,
        "description": "Envia email de confirmação"
      }
    ],
    "side_effects": [
      {
        "type": "email",
        "description": "Email de confirmação enviado",
        "file": "app/Notifications/GuestCreatedNotification.php"
      },
      {
        "type": "log",
        "description": "Log de criação",
        "file": "app/Services/GuestService.php"
      }
    ],
    "dependencies": [
      "app/Models/Guest.php",
      "app/Services/GuestService.php",
      "app/Events/GuestCreated.php",
      "app/Jobs/SendGuestNotification.php",
      "app/Notifications/GuestCreatedNotification.php"
    ]
  },
  "relationships": [
    "analysis/project-structure.json"
  ]
}
```

---

## 3. Domain

### Exemplo: Guest Management Domain

```json
{
  "id": "domain-guest-management",
  "type": "domain",
  "name": "Guest Management",
  "created_at": "2026-04-08T14:33:00Z",
  "updated_at": "2026-04-08T14:33:00Z",
  "source": {
    "files": [
      "app/Models/Guest.php",
      "app/Models/GuestStatus.php",
      "app/Services/GuestService.php"
    ],
    "routes": ["POST /admin/guests", "GET /admin/guests"],
    "components": ["GuestResource"]
  },
  "summary": "Domínio de gestão de convidados para eventos",
  "details": {
    "domain": "guest-management",
    "entities": [
      {
        "id": "entity-guest",
        "name": "Guest",
        "description": "Convidado de um evento",
        "attributes": [
          {
            "name": "name",
            "type": "string",
            "validation": "required|string|max:255"
          },
          {
            "name": "email",
            "type": "string",
            "validation": "required|email|unique"
          },
          {
            "name": "phone",
            "type": "string",
            "validation": "nullable|string|max:20"
          },
          {
            "name": "status",
            "type": "enum",
            "values": ["pending", "approved", "rejected"],
            "validation": "required|in:pending,approved,rejected"
          }
        ],
        "repository": null,
        "model": "app/Models/Guest.php"
      },
      {
        "id": "entity-event",
        "name": "Event",
        "description": "Evento",
        "attributes": [
          {
            "name": "title",
            "type": "string",
            "validation": "required|string|max:255"
          },
          {
            "name": "date",
            "type": "datetime",
            "validation": "required|date"
          }
        ],
        "model": "app/Models/Event.php"
      },
      {
        "id": "entity-guest-status",
        "name": "GuestStatus",
        "description": "Status do convidado",
        "attributes": [
          {
            "name": "name",
            "type": "string"
          },
          {
            "name": "color",
            "type": "string"
          }
        ],
        "model": "app/Models/GuestStatus.php"
      }
    ],
    "rules": [
      {
        "id": "rule-no-duplicate-email",
        "name": "No Duplicate Email",
        "description": "Email deve ser único por evento",
        "type": "validation",
        "enforcement": "database unique constraint + application validation",
        "locations": [
          "app/Models/Guest.php",
          "database/migrations/..."
        ]
      },
      {
        "id": "rule-pending-to-approved",
        "name": "Pending to Approved",
        "description": "Só pode aprovar convidados com status pending",
        "type": "workflow",
        "enforcement": "Service + Policy",
        "locations": [
          "app/Services/GuestService.php",
          "app/Policies/GuestPolicy.php"
        ]
      }
    ],
    "invariants": [
      {
        "id": "invariant-guest-has-contact",
        "description": "Guest deve ter pelo menos um contato (email ou phone)",
        "type": "business",
        "enforcement": "validation"
      },
      {
        "id": "invariant-approved-not-deletable",
        "description": "Guest com status approved não pode ser removido",
        "type": "business",
        "enforcement": "policy"
      }
    ],
    "relationships": [
      {
        "from": "Guest",
        "to": "Event",
        "type": "many-to-many",
        "pivot": "guest_event",
        "description": "Um convidado pode participar de múltiplos eventos"
      },
      {
        "from": "Guest",
        "to": "GuestStatus",
        "type": "many-to-one",
        "description": "Um convidado tem um status"
      }
    ],
    "aggregates": [
      {
        "name": "GuestRegistration",
        "root": "Guest",
        "entities": ["Guest", "GuestStatus"],
        "description": "Agregado raiz para registro de convidado"
      }
    ]
  },
  "relationships": [
    "flows/create-guest.json",
    "analysis/guest-service.json"
  ]
}
```

---

## 4. Risk

### Exemplo: Validation Risks

```json
{
  "id": "risk-validation",
  "type": "risk",
  "name": "Validation Risks",
  "created_at": "2026-04-08T14:34:00Z",
  "updated_at": "2026-04-08T14:34:00Z",
  "source": {
    "files": [
      "app/Services/GuestService.php",
      "app/Models/Guest.php",
      "app/Filament/Resources/GuestResource.php"
    ],
    "routes": [],
    "components": []
  },
  "summary": "Riscos relacionados a validação duplicada",
  "details": {
    "risk_level": "high",
    "issues": [
      {
        "id": "issue-duplicate-validation-email",
        "type": "duplicate-validation",
        "severity": "high",
        "description": "Validação de email duplicado em 3 lugares",
        "impact": "Manutenção difícil, potencial inconsistência de dados",
        "locations": [
          {
            "file": "app/Services/GuestService.php",
            "line": 45,
            "description": "Validação no service"
          },
          {
            "file": "app/Models/Guest.php",
            "line": 23,
            "description": "Validação no model (boot method)"
          },
          {
            "file": "app/Filament/Resources/GuestResource.php",
            "line": 78,
            "description": "Validação no resource"
          }
        ],
        "suggestion": "Centralizar em FormRequest + GuestValidator",
        "effort": "medium"
      },
      {
        "id": "issue-missing-transaction",
        "type": "missing-transaction",
        "severity": "critical",
        "description": "Criação de guest + envio de email sem transaction",
        "impact": "Email enviado mas guest não foi criado, ou guest criado mas email não enviou",
        "locations": [
          {
            "file": "app/Services/GuestService.php",
            "line": 67,
            "description": "Create + Mail::send fora de transaction"
          }
        ],
        "suggestion": "Wrap em DB::transaction() com dispatch afterCommit",
        "effort": "low"
      }
    ],
    "statistics": {
      "total_issues": 2,
      "critical": 1,
      "high": 1,
      "medium": 0,
      "low": 0
    }
  },
  "relationships": [
    "analysis/guest-service.json",
    "domains/guest-management.json"
  ]
}
```

---

## 5. Decision (Refactor Plan)

### Exemplo Completo

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
  "summary": "Plano de refatoração para sistema guest-list-pro",
  "details": {
    "modules": [
      {
        "id": "module-guest-management",
        "name": "GuestManagement",
        "description": "Gestão completa de convidados",
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
    "strategy_description": "Gradualmente substituir funcionalidades legadas por novas implementações em módulos separados, mantendo ambas em execução até completa transição.",
    "phases": [
      {
        "phase": 1,
        "name": "Foundation",
        "description": "Correção de issues críticos",
        "duration": "1 semana",
        "tasks": [
          {
            "id": "task-add-transactions",
            "description": "Adicionar DB::transaction() em GuestService::create",
            "effort": "low",
            "risks_addressed": ["issue-missing-transaction"]
          },
          {
            "id": "task-centralize-validation",
            "description": "Centralizar validações em FormRequests",
            "effort": "medium",
            "risks_addressed": ["issue-duplicate-validation-email"]
          }
        ]
      },
      {
        "phase": 2,
        "name": "Guest Module",
        "description": "Implementar novo módulo GuestManagement",
        "duration": "3 semanas",
        "tasks": [
          {
            "id": "task-create-repository",
            "description": "Criar GuestRepository",
            "effort": "medium"
          },
          {
            "id": "task-create-validator",
            "description": "Criar GuestValidator service",
            "effort": "medium"
          },
          {
            "id": "task-migrate-guests",
            "description": "Migrar dados existentes",
            "effort": "medium"
          }
        ]
      },
      {
        "phase": 3,
        "name": "Event Module",
        "description": "Implementar novo módulo EventManagement",
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
        "item": "Adicionar transactions",
        "reason": "Integridade de dados crítica",
        "effort": "low",
        "impact": "critical"
      },
      {
        "priority": 2,
        "item": "Centralizar validações",
        "reason": "Manutenibilidade",
        "effort": "medium",
        "impact": "high"
      },
      {
        "priority": 3,
        "item": "Criar GuestRepository",
        "reason": "Separação de responsabilidades",
        "effort": "medium",
        "impact": "medium"
      }
    ],
    "estimated_duration": "8 semanas",
    "risks_summary": {
      "critical": 1,
      "high": 1,
      "medium": 0,
      "low": 0,
      "addressed_in_phase": {
        "1": ["issue-missing-transaction", "issue-duplicate-validation-email"],
        "2": [],
        "3": [],
        "4": []
      }
    }
  },
  "relationships": [
    "domains/guest-management.json",
    "risks/validation.json"
  ]
}
```

---

## 6. Index

### Exemplo: index.json

```json
{
  "domains": [
    {
      "id": "domain-guest-management",
      "name": "Guest Management",
      "file": "domains/guest-management.json",
      "created_at": "2026-04-08T14:33:00Z"
    }
  ],
  "flows": [
    {
      "id": "flow-create-guest",
      "name": "Create Guest",
      "file": "flows/create-guest.json",
      "created_at": "2026-04-08T14:31:00Z"
    }
  ],
  "analysis": [
    {
      "id": "analysis-project-structure",
      "name": "Project Structure",
      "file": "analysis/project-structure.json",
      "created_at": "2026-04-08T14:30:00Z"
    },
    {
      "id": "analysis-guest-service",
      "name": "GuestService Analysis",
      "file": "analysis/guest-service.json",
      "created_at": "2026-04-08T14:32:00Z"
    }
  ],
  "risks": [
    {
      "id": "risk-validation",
      "name": "Validation Risks",
      "file": "risks/validation.json",
      "created_at": "2026-04-08T14:34:00Z"
    }
  ],
  "decisions": [
    {
      "id": "decision-refactor-plan",
      "name": "Refactor Plan",
      "file": "decisions/refactor-plan.json",
      "created_at": "2026-04-08T14:35:00Z"
    }
  ]
}
```

---

*Última atualização: 2026-04-08*
