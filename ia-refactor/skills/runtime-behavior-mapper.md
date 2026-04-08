# Skill: Runtime Behavior Mapper

## Objetivo
Mapear comportamento real da aplicação baseado em execução

## Input
- Ação do usuário (ex: "create contract")

## Ações

1. Identificar rota
2. Identificar controller/resource
3. Mapear cadeia de execução:
   - Methods chamados
   - Services utilizados
   - Models afetados
   - Jobs disparados
   - Events disparados

4. Identificar side-effects:
   - Emails
   - Logs
   - Integrações externas

## Output

Salvar em:
.refactor/flows/{feature}.json

Formato:

{
  "id": "flow-create-contract",
  "steps": [
    {
      "layer": "controller",
      "action": ""
    },
    {
      "layer": "service",
      "action": ""
    }
  ],
  "side_effects": [],
  "dependencies": []
}