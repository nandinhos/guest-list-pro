# Skill: Domain Extractor

## Objetivo

Transformar código em domínio de negócio, extraindo entidades, regras, invariantes e relacionamentos.

## Input

- **flows**: Arquivos de fluxo mapeados
- **analysis**: Análises de código

## Pré-requisitos

- [Project Scanner](PROJECT-SCANNER.md) ter sido executado
- [Runtime Behavior Mapper](RUNTIME-BEHAVIOR-MAPPER.md) ter sido executado
- [Laravel Analyzer](LARAVEL-ANALYZER.md) ter sido executado

## Ações Executadas

### 1. Identificação de Entidades

Extrai entidades de negócio do código:
- **Models** mapeados para entidades de domínio
- **Atributos** de cada entidade
- **Repositories** associados

### 2. Identificação de Regras de Negócio

Extrai regras de validação e negócio:
- **Validações**: Regras de campos (required, email, unique)
- **Autorizações**: Policies e gates
- **Workflows**: Estados e transições

### 3. Identificação de Invariantes

Identifica regras que devem ser sempre verdadeiras:
- Constraints de banco
- Regras de negócio críticas
- Assertions

### 4. Mapeamento de Relacionamentos

Identifica como entidades se relacionam:
- One-to-One
- One-to-Many
- Many-to-Many
- Agregações
- Composições

## Output

**Arquivo**: `domains/{domain}.json`

**Formato**:

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
      "app/Models/GuestStatus.php"
    ],
    "routes": [],
    "components": ["GuestResource"]
  },
  "summary": "Domínio de gestão de convidados",
  "details": {
    "domain": "guest-management",
    "entities": [
      {
        "id": "entity-guest",
        "name": "Guest",
        "description": "Convidado do evento",
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
            "validation": "nullable|string"
          },
          {
            "name": "status",
            "type": "enum",
            "validation": "required|in:pending,approved,rejected"
          }
        ],
        "repository": "app/Repositories/GuestRepository.php",
        "model": "app/Models/Guest.php"
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
          "app/Http/Requests/CreateGuestRequest.php"
        ]
      },
      {
        "id": "rule-pending-to-approved",
        "name": "Pending to Approved",
        "description": "Só pode aprovar convidados pendentes",
        "type": "workflow",
        "enforcement": "Policy + Service",
        "locations": [
          "app/Policies/GuestPolicy.php",
          "app/Services/GuestService.php"
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
        "description": "Guest aprovado não pode ser removido",
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
        "description": "Um convidado pode estar em múltiplos eventos"
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

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único no formato "domain-{name}" |
| type | string | Sempre "domain" |
| domain | string | Nome do domínio |
| entities | array | Entidades extraídas |
| rules | array | Regras de negócio |
| invariants | array | Invariantes |
| relationships | array | Relacionamentos |
| aggregates | array | Agregados (DDD) |

## Dependências

- **Inputs**: 
  - `flows/*.json`
  - `analysis/*.json`
- **Próxima skill**: [Risk Detector](RISK-DETECTOR.md)

## Conceitos DDD

### Entidade
Objeto com identidade única. Ex: Guest tem ID único.

### Value Object
Objeto sem identidade. Ex: Email, PhoneNumber.

### Aggregate
Grupo de objetos tratados como unidade. GuestRegistration é o aggregate root.

### Domain Event
Evento que acontece no domínio. GuestApproved.

## Exemplo de Uso

```bash
# Extrair domínio completo
php artisan refactor:extract-domain

# Extrair domínio específico
php artisan refactor:extract-domain --domain guest-management
```

---

*Última atualização: 2026-04-08*
