# Skill: Risk Detector

## Objetivo
Detectar riscos arquiteturais

## Detectar:

- Regras duplicadas
- Escrita concorrente
- Falta de transaction
- Jobs sem controle
- Dependência circular

## Output

.refactor/risks/{context}.json

Formato:

{
  "risk_level": "low|medium|high|critical",
  "issues": [
    {
      "type": "",
      "description": "",
      "impact": "",
      "suggestion": ""
    }
  ]
}