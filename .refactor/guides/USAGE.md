# Guia de Uso da Pipeline

## Visão Geral

Este guia descreve como usar a pipeline ia-refactor para analisar sistemas Laravel legados.

## Requisitos

- PHP 8.2+
- Laravel 12+
- Acesso ao diretório do projeto

## Comandos Disponíveis

### 1. Project Scanner

Mapeia a estrutura completa do projeto.

```bash
# Executar scan completo
php artisan refactor:scan

# Com verbose output
php artisan refactor:scan -v

# Scan apenas models
php artisan refactor:scan --type models
```

### 2. Runtime Behavior Mapper

Mapeia o comportamento de uma feature.

```bash
# Mapear ação específica
php artisan refactor:map --action "create guest"

# Mapear múltiplas ações
php artisan refactor:map --action "create guest,approve guest"

# Mapear por rota
php artisan refactor:map --route "POST /admin/guests"
```

### 3. Laravel Analyzer

Analisa código de arquivos específicos.

```bash
# Analisar arquivo específico
php artisan refactor:analyze --file app/Services/GuestService.php

# Analisar todos os services
php artisan refactor:analyze --type service

# Analisar com contexto (requer scan prévio)
php artisan refactor:analyze --file app/Services/GuestService.php --with-context
```

### 4. Domain Extractor

Extrai domínio de negócio.

```bash
# Extrair todos os domínios
php artisan refactor:extract-domain

# Extrair domínio específico
php artisan refactor:extract-domain --domain guest-management

# Extrair apenas entidades
php artisan refactor:extract-domain --only entities
```

### 5. Risk Detector

Detecta riscos arquiteturais.

```bash
# Detectar todos os riscos
php artisan refactor:detect-risks

# Detectar por tipo
php artisan refactor:detect-risks --type transaction

# Detectar apenas críticos
php artisan refactor:detect-risks --severity critical
```

### 6. Refactor Planner

Gera plano de refatoração.

```bash
# Gerar plano padrão
php artisan refactor:plan

# Com estratégia específica
php artisan refactor:plan --strategy strangler-fig

# Com timeline
php artisan refactor:plan --timeline
```

### Pipeline Completa

Executa todas as etapas em sequência.

```bash
# Executar pipeline completa
php artisan refactor:run

# Com geração de documentação
php artisan refactor:run --docs

# Com dry-run (não salva)
php artisan refactor:run --dry-run
```

## Estrutura de Diretórios

Após execução, os outputs são salvos em `.refactor/`:

```
.refactor/
├── analysis/
│   ├── project-structure.json
│   └── {file}.json
├── flows/
│   ├── create-guest.json
│   └── {feature}.json
├── domains/
│   ├── guest-management.json
│   └── {domain}.json
├── risks/
│   ├── validation.json
│   └── {context}.json
├── decisions/
│   └── refactor-plan.json
└── index.json
```

## Uso Programático

### Como Skill no Agente

A pipeline pode ser usada como skill dentro do orquestrador:

```yaml
# Em refactor-orchestrator.md
- skill: project-scanner
  command: refactor:scan
- skill: runtime-behavior-mapper
  command: refactor:map --action "{action}"
```

### Integração via API

```php
use App\Services\RefactorService;

$service = new RefactorService();

// Executar scan
$structure = $service->scan();

// Mapear fluxo
$flow = $service->map('create guest');

// Detectar riscos
$risks = $service->detectRisks();

// Gerar plano
$plan = $service->plan();
```

## Opções Comuns

| Opção | Descrição |
|-------|-----------|
| `--output` | Diretório de saída |
| `--format` | Formato (json, yaml) |
| `--verbose` | Output detalhado |
| `--dry-run` | Simulação sem salvar |
| `--force` | Sobrescrever existente |

## Troubleshooting

### "Output já existe"

```bash
# Verificar outputs existentes
ls -la .refactor/analysis/

# Forçar sobrescrita
php artisan refactor:scan --force
```

### "Dependência faltando"

```bash
# Executar em ordem
php artisan refactor:scan
php artisan refactor:map --action "create guest"
php artisan refactor:analyze --file app/Services/GuestService.php
```

### "JSON inválido"

```bash
# Validar JSON
cat .refactor/analysis/project-structure.json | jq .

# Verificar erros
php artisan refactor:scan --debug
```

## Melhores Práticas

1. **Sempre execute em ordem** — Respeite dependências
2. **Use --dry-run primeiro** — Teste antes de salvar
3. **Revise outputs** — Validar antes de prosseguir
4. **Mantenha backup** — Preserve análises anteriores
5. **Versione outputs** — Use timestamps quando necessário

## Exemplos de Workflow

### Análise Rápida

```bash
# 1. Scan rápido
php artisan refactor:scan

# 2. Ver resultado
cat .refactor/analysis/project-structure.json | jq
```

### Análise Completa

```bash
# 1. Executar pipeline completa
php artisan refactor:run --docs

# 2. Ver índice
cat .refactor/index.json | jq

# 3. Listar riscos
ls -la .refactor/risks/

# 4. Ver plano
cat .refactor/decisions/refactor-plan.json | jq '.details.phases'
```

### Análise de Feature Específica

```bash
# 1. Mapear feature
php artisan refactor:map --action "create guest"

# 2. Analisar código
php artisan refactor:analyze --file app/Services/GuestService.php

# 3. Verificar riscos
php artisan refactor:detect-risks --severity high
```

---

*Última atualização: 2026-04-08*
