# Skills — Índice

## Visão Geral

Esta seção documenta cada skill da pipeline ia-refactor. As skills são executadas em sequência pelo orquestrador, cada uma com responsabilidade específica.

## Ordem de Execução

| # | Skill | Arquivo | Descrição |
|---|-------|---------|-----------|
| 1 | Project Scanner | PROJECT-SCANNER.md | Mapeia estrutura do projeto |
| 2 | Runtime Behavior Mapper | RUNTIME-BEHAVIOR-MAPPER.md | Mapeia comportamento runtime |
| 3 | Laravel Analyzer | LARAVEL-ANALYZER.md | Analisa código Laravel |
| 4 | Domain Extractor | DOMAIN-EXTRACTOR.md | Extrai domínio de negócio |
| 5 | Risk Detector | RISK-DETECTOR.md | Detecta riscos arquiteturais |
| 6 | Refactor Planner | REFACTOR-PLANNER.md | Planeja reconstrução |

## Visão Geral das Skills

### Skills de Mapeamento (Etapas 1-2)
Responsáveis por mapear a estrutura estática e o comportamento dinâmico do sistema.

- **Project Scanner**: Identifica todos os arquivos relevantes
- **Runtime Behavior Mapper**: Rastreia a execução em runtime

### Skills de Análise (Etapas 3-4)
Responsáveis por analisar o código e extrair o domínio.

- **Laravel Analyzer**: Analisa código com contexto
- **Domain Extractor**: Transforma código em negócio

### Skills de Detecção (Etapas 5-6)
Responsáveis por detectar problemas e planejar a solução.

- **Risk Detector**: Identifica riscos arquiteturais
- **Refactor Planner**: Cria plano de refatoração

## Dependências entre Skills

```
Project Scanner (1)
       │
       ▼
Runtime Behavior Mapper (2) ◄────────────┐
       │                               │
       ▼                               │
Laravel Analyzer (3) ◄─────────────────┤
       │                               │
       ▼                               │
Domain Extractor (4) ◄─────────────────┤
       │                               │
       ▼                               │
Risk Detector (5) ◄────────────────────┤
       │                               │
       ▼                               │
Refactor Planner (6) ◄──────────────────┘
```

## Input/Output por Skill

| Skill | Input | Output Directory |
|-------|-------|-------------------|
| project-scanner | Root do projeto | analysis/ |
| runtime-behavior-mapper | Ação do usuário | flows/ |
| laravel-analyzer | Lista de arquivos | analysis/ |
| domain-extractor | flows + analysis | domains/ |
| risk-detector | análise completa | risks/ |
| refactor-planner | domains + risks | decisions/ |

## Documentação Específica

Cada skill tem sua própria página com:
- Objetivo claro
- Input esperado
- Ações executadas
- Output gerado
- Exemplos

Clique no nome de cada skill para ver a documentação completa.

---

*Última atualização: 2026-04-08*
