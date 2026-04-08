# Skill: Laravel Analyzer

## Objetivo

Analisar código Laravel com contexto pré-mapeado, identificando responsabilidades,violações de princípios e padrões problemáticos.

## Input

- **Lista de arquivos**: Arquivos identificados nas etapas anteriores
- **Contexto**: Estrutura do projeto + fluxos mapeados

## Pré-requisitos

- [Project Scanner](PROJECT-SCANNER.md) ter sido executado
- [Runtime Behavior Mapper](RUNTIME-BEHAVIOR-MAPPER.md) ter sido executado

## Ações Executadas

### 1. Análise de Responsabilidades

Para cada arquivo, identifica:
- Função principal (SRP)
- Métodos e suas responsabilidades
- Dependências explícitas e implícitas

### 2. Detecção de Violações de SRP

Identifica violações do Single Responsibility Principle:
- Controllers que fazem muito (HTTP + lógica de negócio)
- Models com regras de validação + business logic
- Services com múltiplas responsabilidades

### 3. Análise de Acoplamento

Mapeia dependências entre classes:
- Acoplamento direto (instância explícita)
- Acoplamento via injeção de dependência
- Acoplamento via Facades

### 4. Análise de Facades

Identifica uso de Facades e detecta:
- Uso indevido em Services (deveria injetar dependência)
- Facades em Models (violação de camada)
- Facades emJobs (aceitável para logging)

### 5. Localização de Validação

Identifica onde validações estão localizadas:
- **Ideal**: Form Requests ou Validator em Service
- **Problema**: Validação em Controller, Model, ou duplicada

### 6. Mapeamento de Transações

Verifica uso de transações:
- Operações DB sem transaction
- Transaction incompleta (sem try/catch)
- Transaction mal posicionada

## Output

**Arquivo**: `analysis/{file}.json`

**Formato**:

```json
{
  "id": "analysis-guest-service",
  "type": "analysis",
  "name": "GuestService Analysis",
  "created_at": "2026-04-08T14:32:00Z",
  "updated_at": "2026-04-08T14:32:00Z",
  "source": {
    "files": ["app/Services/GuestService.php"],
    "routes": [],
    "components": []
  },
  "summary": "Análise de código do GuestService",
  "details": {
    "file": "app/Services/GuestService.php",
    "responsibilities": [
      "Criar convidado",
      "Validar duplicata",
      "Atualizar status"
    ],
    "violations": [
      {
        "type": "SRP",
        "severity": "medium",
        "description": "Service também envia email",
        "line": 78,
        "suggestion": "Extrair para Notification class"
      }
    ],
    "facades_used": [
      {
        "facade": "Mail",
        "line": 45,
        "issue": "Deve injetar Mailer via construtor",
        "suggestion": "Inject MailInterface"
      },
      {
        "facade": "Log",
        "line": 23,
        "issue": null,
        "suggestion": null
      }
    ],
    "coupling": [
      {
        "class": "Guest",
        "type": "direct",
        "issue": null
      },
      {
        "class": "Event",
        "type": "indirect",
        "issue": null
      }
    ],
    "validation_location": "service",
    "validation_rules": [
      "name: required|string|max:255",
      "email: required|email"
    ],
    "transactions": [],
    "issues": [
      {
        "type": "coupling",
        "description": "Acoplamento com Event service",
        "severity": "low"
      }
    ]
  },
  "relationships": [
    "analysis/project-structure.json",
    "flows/create-guest.json"
  ]
}
```

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único |
| type | string | Sempre "analysis" |
| file | string | Caminho do arquivo analisado |
| responsibilities | array | Lista de responsabilidades |
| violations | array | Violações de princípios |
| facades_used | array | Facades utilizadas |
| coupling | array | Classes acopladas |
| validation_location | string | Onde validação está |
| issues | array | Problemas identificados |

## Dependências

- **Inputs**: 
  - `analysis/project-structure.json`
  - `flows/*.json`
- **Próxima skill**: [Domain Extractor](DOMAIN-EXTRACTOR.md)

## Regras de Análise

### Problemas Comuns Detectados

| Problema | O que procurar | Severidade |
|----------|---------------|------------|
| Controller God | Métodos demais no controller | high |
| Fat Model | Lógica de negócio no Model | medium |
| Duplicate Validation | Validação em múltiplos lugares | high |
| Missing Transaction | Operações DB sem transaction | critical |
| Facade Abuse | Facades em Services | medium |
| Circular Dependency | A → B → A | critical |

## Exemplo de Uso

```bash
# Analisar arquivo específico
php artisan refactor:analyze --file app/Services/GuestService.php

# Analisar todos os Services
php artisan refactor:analyze --type service
```

---

*Última atualização: 2026-04-08*
