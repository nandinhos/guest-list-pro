# JSON Schemas — Referência

## Visão Geral

Esta seção contém os JSON Schemas para validação dos outputs da pipeline.

## Base Document Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["id", "type", "name", "source", "summary", "details", "relationships", "created_at", "updated_at"],
  "properties": {
    "id": {
      "type": "string",
      "pattern": "^[a-z0-9-]+$"
    },
    "type": {
      "type": "string",
      "enum": ["domain", "flow", "analysis", "risk", "decision"]
    },
    "name": {
      "type": "string",
      "minLength": 1
    },
    "source": {
      "$ref": "#/definitions/source"
    },
    "summary": {
      "type": "string",
      "minLength": 1
    },
    "details": {
      "type": "object"
    },
    "relationships": {
      "type": "array",
      "items": {
        "type": "string"
      }
    },
    "risks": {
      "type": "array",
      "items": {
        "type": "string"
      }
    },
    "created_at": {
      "type": "string",
      "format": "date-time"
    },
    "updated_at": {
      "type": "string",
      "format": "date-time"
    }
  },
  "definitions": {
    "source": {
      "type": "object",
      "required": ["files"],
      "properties": {
        "files": {
          "type": "array",
          "items": {
            "type": "string"
          }
        },
        "routes": {
          "type": "array",
          "items": {
            "type": "string"
          }
        },
        "components": {
          "type": "array",
          "items": {
            "type": "string"
          }
        }
      }
    }
  }
}
```

## Project Structure Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["details"],
  "properties": {
    "details": {
      "type": "object",
      "properties": {
        "models": {
          "type": "array",
          "items": { "type": "string" }
        },
        "controllers": {
          "type": "array",
          "items": { "type": "string" }
        },
        "resources": {
          "type": "array",
          "items": { "type": "string" }
        },
        "jobs": {
          "type": "array",
          "items": { "type": "string" }
        },
        "events": {
          "type": "array",
          "items": { "type": "string" }
        },
        "services": {
          "type": "array",
          "items": { "type": "string" }
        },
        "livewire": {
          "type": "array",
          "items": { "type": "string" }
        }
      }
    },
    "statistics": {
      "type": "object",
      "properties": {
        "total_files": { "type": "integer" },
        "total_models": { "type": "integer" },
        "total_resources": { "type": "integer" }
      }
    }
  }
}
```

## Flow Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["details"],
  "properties": {
    "details": {
      "type": "object",
      "properties": {
        "trigger": {
          "type": "object",
          "properties": {
            "type": { "type": "string" },
            "method": { "type": "string" },
            "url": { "type": "string" }
          }
        },
        "steps": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "order": { "type": "integer" },
              "layer": { "type": "string" },
              "action": { "type": "string" },
              "file": { "type": "string" },
              "line": { "type": "integer" },
              "description": { "type": "string" }
            }
          }
        },
        "side_effects": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "type": { "type": "string" },
              "description": { "type": "string" },
              "file": { "type": "string" }
            }
          }
        },
        "dependencies": {
          "type": "array",
          "items": { "type": "string" }
        }
      }
    }
  }
}
```

## Domain Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["details"],
  "properties": {
    "details": {
      "type": "object",
      "properties": {
        "domain": { "type": "string" },
        "entities": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "id": { "type": "string" },
              "name": { "type": "string" },
              "description": { "type": "string" },
              "attributes": { "type": "array" },
              "repository": { "type": "string" },
              "model": { "type": "string" }
            }
          }
        },
        "rules": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "id": { "type": "string" },
              "name": { "type": "string" },
              "description": { "type": "string" },
              "type": { "type": "string" },
              "enforcement": { "type": "string" },
              "locations": { "type": "array" }
            }
          }
        },
        "invariants": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "id": { "type": "string" },
              "description": { "type": "string" },
              "type": { "type": "string" },
              "enforcement": { "type": "string" }
            }
          }
        },
        "relationships": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "from": { "type": "string" },
              "to": { "type": "string" },
              "type": { "type": "string" },
              "pivot": { "type": "string" },
              "description": { "type": "string" }
            }
          }
        }
      }
    }
  }
}
```

## Risk Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["details"],
  "properties": {
    "details": {
      "type": "object",
      "properties": {
        "risk_level": {
          "type": "string",
          "enum": ["low", "medium", "high", "critical"]
        },
        "issues": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "id": { "type": "string" },
              "type": { "type": "string" },
              "severity": {
                "type": "string",
                "enum": ["low", "medium", "high", "critical"]
              },
              "description": { "type": "string" },
              "impact": { "type": "string" },
              "locations": {
                "type": "array",
                "items": {
                  "type": "object",
                  "properties": {
                    "file": { "type": "string" },
                    "line": { "type": "integer" },
                    "description": { "type": "string" }
                  }
                }
              },
              "suggestion": { "type": "string" },
              "effort": {
                "type": "string",
                "enum": ["low", "medium", "high"]
              }
            }
          }
        },
        "statistics": {
          "type": "object",
          "properties": {
            "total_issues": { "type": "integer" },
            "critical": { "type": "integer" },
            "high": { "type": "integer" },
            "medium": { "type": "integer" },
            "low": { "type": "integer" }
          }
        }
      }
    }
  }
}
```

## Decision Schema

```json
{
  "$schema": "http://json-schema.org/draft-07/schema#",
  "type": "object",
  "required": ["details"],
  "properties": {
    "details": {
      "type": "object",
      "properties": {
        "modules": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "id": { "type": "string" },
              "name": { "type": "string" },
              "description": { "type": "string" },
              "entities": { "type": "array" },
              "priority": { "type": "integer" },
              "dependencies": { "type": "array" },
              "status": { "type": "string" }
            }
          }
        },
        "migration_strategy": { "type": "string" },
        "phases": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "phase": { "type": "integer" },
              "name": { "type": "string" },
              "description": { "type": "string" },
              "duration": { "type": "string" },
              "tasks": { "type": "array" }
            }
          }
        },
        "priorities": {
          "type": "array",
          "items": {
            "type": "object",
            "properties": {
              "priority": { "type": "integer" },
              "item": { "type": "string" },
              "reason": { "type": "string" },
              "effort": { "type": "string" },
              "impact": { "type": "string" }
            }
          }
        },
        "estimated_duration": { "type": "string" }
      }
    }
  }
}
```

## Validação de Schema

### PHP (Laravel)

```php
use JsonSchema\Validator;

$validator = new Validator();
$validator->validate($data, $schema);

if (!$validator->isValid()) {
    foreach ($validator->getErrors() as $error) {
        echo "{$error['property']}: {$error['message']}\n";
    }
}
```

### JavaScript

```javascript
import Ajv from 'ajv';

const ajv = new Ajv();
const validate = ajv.compile(schema);

if (!validate(data)) {
  console.error(validate.errors);
}
```

---

*Última atualização: 2026-04-08*
