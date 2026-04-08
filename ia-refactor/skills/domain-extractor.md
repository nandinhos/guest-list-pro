# Skill: Domain Extractor

## Objetivo
Transformar código em domínio de negócio

## Input
- flows + analysis

## Ações

1. Identificar entidades
2. Identificar regras de negócio
3. Identificar invariantes
4. Mapear relacionamentos

## Output

Salvar em:
.refactor/domains/{domain}.json

Formato:

{
  "entities": [],
  "rules": [],
  "invariants": [],
  "relationships": []
}