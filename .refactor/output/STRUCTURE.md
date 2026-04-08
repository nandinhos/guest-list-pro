# Estrutura de Saída — Referência

## Visão Geral

Este documento descreve a estrutura de saída da pipeline, detalhando onde cada tipo de output é salvo e como os arquivos são organizados.

## Diretório Base

Todos os outputs são salvos em `.refactor/`:

```
.refactor/
├── analysis/     # Análises de código e estrutura
├── domains/     # Domínios de negócio
├── flows/       # Fluxos de execução
├── risks/       # Riscos detectados
├── decisions/   # Decisões de refatoração
└── index.json   # Índice global
```

## analysis/

### project-structure.json

**Descrição**: Mapeamento completo da estrutura do projeto

**Localização**: `analysis/project-structure.json`

**Conteúdo**:
```json
{
  "details": {
    "models": [],
    "controllers": [],
    "resources": [],
    "jobs": [],
    "events": [],
    "services": []
  }
}
```

### {file}.json

**Descrição**: Análise de arquivo específico

**Localização**: `analysis/{filename}.json`

**Exemplo**: `analysis/guest-service.json`

---

## flows/

### {feature}.json

**Descrição**: Fluxo de execução mapeado

**Localização**: `flows/{feature-name}.json`

**Exemplo**: `flows/create-guest.json`

**Estrutura**:
```json
{
  "details": {
    "trigger": {},
    "steps": [],
    "side_effects": [],
    "dependencies": []
  }
}
```

---

## domains/

### {domain-name}.json

**Descrição**: Domínio de negócio extraído

**Localização**: `domains/{domain-name}.json`

**Exemplo**: `domains/guest-management.json`

**Estrutura**:
```json
{
  "details": {
    "domain": "",
    "entities": [],
    "rules": [],
    "invariants": [],
    "relationships": []
  }
}
```

---

## risks/

### {context}.json

**Descrição**: Riscos detectados em um contexto

**Localização**: `risks/{context}.json`

**Exemplo**: `risks/validation.json`

**Estrutura**:
```json
{
  "details": {
    "risk_level": "",
    "issues": [],
    "statistics": {}
  }
}
```

---

## decisions/

### refactor-plan.json

**Descrição**: Plano de refatoração

**Localização**: `decisions/refactor-plan.json`

**Estrutura**:
```json
{
  "details": {
    "modules": [],
    "migration_strategy": "",
    "phases": [],
    "priorities": [],
    "estimated_duration": ""
  }
}
```

---

## index.json

**Descrição**: Índice global de todas as descobertas

**Localização**: `.refactor/index.json`

**Estrutura**:
```json
{
  "domains": [
    {
      "id": "",
      "name": "",
      "file": "",
      "created_at": ""
    }
  ],
  "flows": [],
  "analysis": [],
  "risks": [],
  "decisions": []
}
```

## Convenções de Nomenclatura

### Arquivos

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| analysis | `{filename}.json` | `guest-service.json` |
| flow | `{feature}.json` | `create-guest.json` |
| domain | `{domain-name}.json` | `guest-management.json` |
| risk | `{context}.json` | `validation.json` |

### IDs

| Tipo | Padrão | Exemplo |
|------|--------|---------|
| analysis | `analysis-{name}` | `analysis-guest-service` |
| flow | `flow-{feature}` | `flow-create-guest` |
| domain | `domain-{name}` | `domain-guest-management` |
| risk | `risk-{context}` | `risk-validation` |
| decision | `decision-refactor-plan` | — |

## Versionamento

Quando um output precisa ser versionado:

```
# Padrão com timestamp
analysis/project-structure-2026-04-08-1430.json

# Padrão com versão
analysis/project-structure-v2.json
```

---

*Última atualização: 2026-04-08*
