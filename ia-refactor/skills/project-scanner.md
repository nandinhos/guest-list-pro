# Skill: Project Scanner

## Objetivo
Mapear toda a estrutura do projeto Laravel

## Input
- Root do projeto

## Ações

1. Identificar:
   - Models
   - Controllers
   - Filament Resources
   - Jobs
   - Events
   - Services
   - Providers

2. Classificar arquivos por tipo

## Output

Salvar em:
.refactor/analysis/project-structure.json

Formato:

{
  "models": [],
  "controllers": [],
  "resources": [],
  "jobs": [],
  "events": [],
  "services": [],
  "unknown": []
}