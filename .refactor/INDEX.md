# .refactor — Documentação da Pipeline de Refatoração

## Visão Geral

Este diretório contém a documentação completa da pipeline ia-refactor, um sistema de engenharia reversa, análise e reconstrução de sistemas legados Laravel + Filament.

## Objetivo

Fornecer embasamento técnico para estudo de migração de sistemas legados, através de análise estruturada e documentação de discoveries.

## Índice

### pipeline/
Documentação da arquitetura e fluxo da pipeline.

| Arquivo | Descrição |
|---------|-----------|
| [OVERVIEW.md](pipeline/OVERVIEW.md) | Visão geral da pipeline |
| [FLOW.md](pipeline/FLOW.md) | Fluxo de execução detalhado |
| [ORCHESTRATOR.md](pipeline/ORCHESTRATOR.md) | Documentação do orquestrador |

### skills/
Documentação de cada skill individual.

| Arquivo | Descrição | Etapa |
|---------|-----------|-------|
| [INDEX.md](skills/INDEX.md) | Índice das skills | — |
| [PROJECT-SCANNER.md](skills/PROJECT-SCANNER.md) | Mapeamento de estrutura | 1 |
| [RUNTIME-BEHAVIOR-MAPPER.md](skills/RUNTIME-BEHAVIOR-MAPPER.md) | Comportamento runtime | 2 |
| [LARAVEL-ANALYZER.md](skills/LARAVEL-ANALYZER.md) | Análise de código | 3 |
| [DOMAIN-EXTRACTOR.md](skills/DOMAIN-EXTRACTOR.md) | Extração de domínio | 4 |
| [RISK-DETECTOR.md](skills/RISK-DETECTOR.md) | Detecção de riscos | 5 |
| [REFACTOR-PLANNER.md](skills/REFACTOR-PLANNER.md) | Planejamento de refatoração | 6 |

### templates/
Templates e esquemas para geração de documentos.

| Arquivo | Descrição |
|---------|-----------|
| [BASE-DOCUMENT.md](templates/BASE-DOCUMENT.md) | Template base documentado |
| [JSON-SCHEMAS.md](templates/JSON-SCHEMAS.md) | Esquemas JSON |

### guides/
Guias práticos de uso.

| Arquivo | Descrição |
|---------|-----------|
| [MIGRATION-STUDY.md](guides/MIGRATION-STUDY.md) | Guia para estudo de migração |
| [USAGE.md](guides/USAGE.md) | Como usar a pipeline |

### output/
Estrutura e exemplos dos outputs gerados.

| Arquivo | Descrição |
|---------|-----------|
| [STRUCTURE.md](output/STRUCTURE.md) | Estrutura de saída esperada |
| [EXAMPLES.md](output/EXAMPLES.md) | Exemplos de output |

## Quick Links

- [Visão Geral da Pipeline](pipeline/OVERVIEW.md)
- [Guia de Migração](guides/MIGRATION-STUDY.md)
- [Uso da Pipeline](guides/USAGE.md)

---

*Última atualização: 2026-04-08*
