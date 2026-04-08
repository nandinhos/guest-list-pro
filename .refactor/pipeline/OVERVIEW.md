# Pipeline ia-Refactor — Visão Geral

## Objetivo

A pipeline ia-refactor é um sistema de engenharia reversa diseñado para analisar, documentar e planejar a reconstrução de sistemas legados Laravel + Filament. Seu propósito principal é transformar código existente em documentação estruturada queserve como base para estudos de migração.

## Escopo

A pipeline trabalha com as seguintes tecnologias:

- **Laravel** (PHP 8.x) — Framework backend
- **Filament** (v4) — Admin panel
- **Livewire** (v3) — Componentes reativos
- **Tailwind CSS** — Estilização

## Componentes Principais

### Orquestrador
O `refactor-orchestrator` é o agente central que coordena a execução das skills, garantindo consistência dos outputs e rastreabilidade entre análises.

### Skills
A pipeline é composta por 6 skills especializadas que executam em sequência:

| # | Skill | Função |
|---|-------|--------|
| 1 | project-scanner | Mapeia estrutura do projeto |
| 2 | runtime-behavior-mapper | Mapeia comportamento runtime |
| 3 | laravel-analyzer | Analisa código Laravel |
| 4 | domain-extractor | Extrai domínio de negócio |
| 5 | risk-detector | Detecta riscos arquiteturais |
| 6 | refactor-planner | Planeja reconstrução |

### Templates
Templates padronizados para geração de documentos estruturados.

### Output
Saídas JSON organizadas por categoria (domains, flows, analysis, risks, decisions).

## Arquitetura de Dados

```
.refactor/
├── analysis/     # Análises de código e estrutura
├── domains/      # Entidades e regras de negócio
├── flows/        # Fluxos de execução mapeados
├── risks/        # Riscos arquiteturais detectados
├── decisions/    # Decisões de refatoração
└── index.json    # Índice global
```

## Princípios Fundamentais

### 1. Rastreabilidade
Cada documento gerado deve referenciar suas fontes (arquivos, rotas, componentes).

### 2. Versionamento
Nunca sobrescrever arquivos sem versionamento. Criar novos arquivos com timestamp ou ID único.

### 3. Estrutura Padronizada
Todos os outputs devem seguir o template base-document.json.

### 4. ID Único
Entidades analisadas devem receber IDs únicos para facilitar referências cruzadas.

## Fluxo de Execução

```
[Projeto Legado]
       │
       ▼
[Project Scanner] ──► project-structure.json
       │
       ▼
[Runtime Behavior Mapper] ──► flows/{feature}.json
       │
       ▼
[Laravel Analyzer] ──► analysis/{file}.json
       │
       ▼
[Domain Extractor] ──► domains/{domain}.json
       │
       ▼
[Risk Detector] ──► risks/{context}.json
       │
       ▼
[Refactor Planner] ──► decisions/refactor-plan.json
       │
       ▼
[Índice Atualizado]
```

## Casos de Uso

### Migração de Versão Laravel
Analisar código legado para identificar:
- Funcionalidades que precisam de update
- Dependências incompatíveis
- Padrões de código a refatorar

### Modernização de Admin Panel
Mapear recursos Filament existentes para:
- Identificar customizações
- Planejar reimplementação
- Documentar lógicas de negócio

### Extração de Domínio
Transformar código em:
- Entidades de negócio
- Regras de validação
- Relacionamentos entre objetos

### Análise de Riscos
Detectar problemas como:
- Regras duplicadas
- Transações ausentes
- Jobs sem controle
- Dependências circulares

## Próximos Passos

- [Fluxo de Execução](FLOW.md) — Detalhamento do pipeline
- [Orquestrador](ORCHESTRATOR.md) — Documentação do agente principal
- [Skills](skills/INDEX.md) — Documentação de cada skill
- [Guia de Migração](guides/MIGRATION-STUDY.md) — Aplicação prática

---

*Última atualização: 2026-04-08*
