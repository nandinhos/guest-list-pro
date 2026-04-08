# Template Base — Documentação

## Visão Geral

O template base é o padrão utilizado para todos os documentos gerados pela pipeline. Garante consistência, rastreabilidade e interoperability entre os outputs.

## Estrutura Base

```json
{
  "id": "uuid",
  "type": "domain|flow|analysis|risk|decision",
  "name": "",
  "source": {
    "files": [],
    "routes": [],
    "components": []
  },
  "summary": "",
  "details": {},
  "relationships": [],
  "risks": [],
  "created_at": "",
  "updated_at": ""
}
```

## Campos Obrigatórios

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| id | string | ✅ | UUID único |
| type | string | ✅ | Tipo do documento |
| name | string | ✅ | Nome descritivo |
| source | object | ✅ | Fontes da análise |
| summary | string | ✅ | Resumo curto |
| details | object | ✅ | Detalhes específicos |
| relationships | array | ✅ | Referências a outros documentos |
| created_at | string | ✅ | Timestamp ISO 8601 |
| updated_at | string | ✅ | Timestamp ISO 8601 |

## Campos Opcionais

| Campo | Tipo | Descrição |
|-------|------|-----------|
| risks | array | Riscos associados |
| metadata | object | Metadados adicionais |
| tags | array | Tags para categorização |

## Tipos de Documento

### domain
Domínio de negócio extraído.

```json
{
  "type": "domain",
  "details": {
    "domain": "guest-management",
    "entities": [],
    "rules": [],
    "relationships": []
  }
}
```

### flow
Fluxo de execução mapeado.

```json
{
  "type": "flow",
  "details": {
    "trigger": {},
    "steps": [],
    "side_effects": []
  }
}
```

### analysis
Análise de código.

```json
{
  "type": "analysis",
  "details": {
    "file": "",
    "responsibilities": [],
    "violations": []
  }
}
```

### risk
Risco detectado.

```json
{
  "type": "risk",
  "details": {
    "risk_level": "high",
    "issues": []
  }
}
```

### decision
Decisão de refatoração.

```json
{
  "type": "decision",
  "details": {
    "modules": [],
    "phases": [],
    "priorities": []
  }
}
```

## Fonte (source)

O objeto `source` é obrigatório e rastreia as origens do documento:

```json
"source": {
  "files": [
    "app/Models/Guest.php",
    "app/Services/GuestService.php"
  ],
  "routes": [
    "POST /admin/guests"
  ],
  "components": [
    "GuestResource"
  ]
}
```

### Campos de Source

| Campo | Tipo | Descrição |
|-------|------|-----------|
| files | array | Arquivos de código fonte |
| routes | array | Rotas envolvidas |
| components | array | Componentes (Livewire, Filament) |

## Relacionamentos (relationships)

Lista de IDs de outros documentos relacionados:

```json
"relationships": [
  "analysis/project-structure.json",
  "flows/create-guest.json",
  "domains/guest-management.json"
]
```

## Timestamps

Formato ISO 8601:

```json
{
  "created_at": "2026-04-08T14:30:00Z",
  "updated_at": "2026-04-08T14:30:00Z"
}
```

## Geração de UUID

```php
// PHP
$uuid = uuid_create(UUID_TYPE_RANDOM);

// Ou usando Laravel
use Illuminate\Support\Str;
$uuid = Str::uuid()->toString();
```

```javascript
// JavaScript
const uuid = crypto.randomUUID();
```

## Exemplo Completo

```json
{
  "id": "domain-guest-management",
  "type": "domain",
  "name": "Guest Management",
  "created_at": "2026-04-08T14:30:00Z",
  "updated_at": "2026-04-08T14:30:00Z",
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
    "entities": [],
    "rules": [],
    "relationships": []
  },
  "relationships": [
    "flows/create-guest.json"
  ],
  "risks": [
    "issue-duplicate-validation"
  ],
  "metadata": {
    "version": "1.0",
    "author": "refactor-orchestrator"
  },
  "tags": [
    "guest",
    "management",
    "core"
  ]
}
```

## Validação

Todos os documentos devem validar contra este schema. Use JSON Schema para validação automática.

---

*Última atualização: 2026-04-08*
