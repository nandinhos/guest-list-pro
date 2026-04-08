# Refactor Orchestrator — Documentação

## Visão Geral

O **Refactor Orchestrator** é o agente central da pipeline ia-refactor. Ele é responsável por coordenar a execução das skills, garantir consistência dos outputs, versionar descobertas e manter o índice global atualizado.

## Responsabilidades

### 1. Coordenação de Execução das Skills

O orquestrador determina a ordem de execução e gerencia as dependências entre skills:

```
Project Scanner → Runtime Behavior Mapper → Laravel Analyzer 
    → Domain Extractor → Risk Detector → Refactor Planner
```

Cada skill só pode executar após suas dependências terem sido completadas.

### 2. Consistência dos Outputs

O orquestrador garante que todos os outputs sigam:
- Template base documentado
- Estrutura JSON padronizada
- Convenção de nomenclatura
- Campos obrigatórios

### 3. Versionamento

Regras de versionamento:
- **Nunca sobrescrever** arquivos sem versionamento
- Criar novos arquivos com sufixo de timestamp ou ID único
- Manter histórico de versões
- Atualizar referências

### 4. Índice Global

O orquestrador atualiza `.refactor/index.json` com:
- Novas descobertas em domains
- Fluxos mapeados em flows
- Análises em analysis
- Riscos em risks
- Decisões em decisions

### 5. Prevenção de Duplicidade

Antes de executar uma análise:
1. Verificar se já existe análise similar
2. Comparar timestamps
3. Decidir se reuse ou create new

## Estrutura do Orquestrador

```
ia-refactor/
├── agents/
│   └── refactor-orchestrator.md   # Este arquivo
├── skills/
│   ├── project-scanner.md
│   ├── runtime-behavior-mapper.md
│   ├── laravel-analyzer.md
│   ├── domain-extractor.md
│   ├── risk-detector.md
│   └── refactor-planner.md
└── templates/
    └── base-document.json
```

## Regras de Operação

### Regras Gerais

| Regra | Descrição |
|-------|-----------|
| Nunca sobrescrever | Sempre criar com versionamento |
| Output estruturado | Seguir template base |
| Rastreabilidade | Referenciar fontes |
| IDs únicos | Identificar entidades |

### Regras de Output

Todos os outputs devem:
1. Ser salvos em `.refactor/`
2. Seguir o template base-document.json
3. Conter campos obrigatórios:
   - `id`: UUID único
   - `type`: domain|flow|analysis|risk|decision
   - `name`: Nome descritivo
   - `source`: Fontes (files, routes, components)
   - `summary`: Resumo
   - `created_at`: Timestamp
   - `updated_at`: Timestamp

### Regras de Índice

O índice global deve ser atualizado após cada skill:
- Adicionar novas descobertas
- Atualizar timestamps
- Manter referências cruzadas

## Fluxo de Trabalho

### Inicialização

1. Verificar se `.refactor/` existe
2. Se não existir, criar estrutura
3. Carregar índice atual (index.json)
4. Identificar última análise

### Execução de Skill

1. Identificar skill a executar
2. Verificar dependências (prerequisites)
3. Executar skill
4. Validar output
5. Salvar output com versionamento
6. Atualizar índice

### Finalização

1. Consolidar todos os outputs
2. Gerar relatório de descobertas
3. Atualizar índice final
4. Notificar conclusão

## Integração com Skills

### Project Scanner
- **Entrada**: Root do projeto
- **Saída**: analysis/project-structure.json
- **Dependências**: Nenhuma

### Runtime Behavior Mapper
- **Entrada**: Ação do usuário
- **Saída**: flows/{feature}.json
- **Dependências**: Project Scanner

### Laravel Analyzer
- **Entrada**: Lista de arquivos
- **Saída**: analysis/{file}.json
- **Dependências**: Project Scanner, Runtime Behavior Mapper

### Domain Extractor
- **Entrada**: flows + analysis
- **Saída**: domains/{domain}.json
- **Dependências**: Todas anteriores

### Risk Detector
- **Entrada**: análise completa
- **Saída**: risks/{context}.json
- **Dependências**: Domain Extractor

### Refactor Planner
- **Entrada**: domains + risks
- **Saída**: decisions/refactor-plan.json
- **Dependências**: Risk Detector

## Exemplos de Comandos

### Iniciar Análise Completa
```bash
# Executar todas as skills em sequência
refactor-orchestrator run --full
```

### Executar Skill Específica
```bash
# Executar apenas Project Scanner
refactor-orchestrator run --skill project-scanner
```

### Verificar Status
```bash
# Verificar índice atual
refactor-orchestrator status
```

### Listar Descobertas
```bash
# Listar todas as análises
refactor-orchestrator list --type analysis
```

## Estrutura do Índice (index.json)

```json
{
  "domains": [
    {
      "id": "domain-guest-management",
      "name": "Guest Management",
      "file": "domains/guest-management.json",
      "created_at": "2026-04-08T14:30:00Z"
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
    }
  ],
  "risks": [
    {
      "id": "risk-validation",
      "name": "Validation Risks",
      "file": "risks/validation.json",
      "created_at": "2026-04-08T14:35:00Z"
    }
  ],
  "decisions": [
    {
      "id": "decision-refactor-plan",
      "name": "Refactor Plan",
      "file": "decisions/refactor-plan.json",
      "created_at": "2026-04-08T14:36:00Z"
    }
  ]
}
```

## Erros Comuns e Tratamento

| Erro | Causa | Solução |
|------|-------|---------|
| Output já existe | Análise duplicada | Verificar timestamp, criar nova versão |
| Dependência faltando | Skill executada antes do tempo | Executar dependências primeiro |
| Output inválido | JSON mal formatado | Validar contra schema |
| Índice corrompido | Atualização parcial | Restaurar de backup |

## Melhores Práticas

1. **Sempre versionar**: Nunca sobrescrever sem criar nova versão
2. **Manter rastreabilidade**: Cada documento deve referenciar suas fontes
3. **Atualizar índice**: Sempre manter índice sincronizado
4. **Validar outputs**: Verificar estrutura antes de salvar
5. **Documentar decisões**: Registrar rationale de cada escolha

---

*Última atualização: 2026-04-08*
