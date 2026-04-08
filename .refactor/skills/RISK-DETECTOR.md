# Skill: Risk Detector

## Objetivo

Detectar riscos arquiteturais no código, identificando problemas que podem causar inconsistências, falhas ou dificuldades de manutenção.

## Input

- **Análise completa**:Todas as análises das etapas anteriores

## Pré-requisitos

- [Project Scanner](PROJECT-SCANNER.md) ter sido executado
- [Runtime Behavior Mapper](RUNTIME-BEHAVIOR-MAPPER.md) ter sido executado
- [Laravel Analyzer](LARAVEL-ANALYZER.md) ter sido executado
- [Domain Extractor](DOMAIN-EXTRACTOR.md) ter sido executado

## Riscos Detectados

### 1. Regras Duplicadas

Validações ou regras de negócio em múltiplos lugares.

**Impacto**: Manutenção difícil, potencial inconsistência.

**Locações típicas**:
- Controller + Service + Model
- Form Request + Service
- Multiple Services

### 2. Escrita Concorrente

Operações de escrita sem controle de concorrência.

**Impacto**: Race conditions, dados corrompidos.

**Locações típicas**:
- Updates sem locking
- Counter sem atomic operations

### 3. Falta de Transação

Operações de banco sem transaction.

**Impacto**: Dados parciais em caso de falha.

**Locações típicas**:
- Create + Send Email
- Multiple Creates
- Create + Update

### 4. Jobs Sem Controle

Jobs disparados sem retries ou queue configured.

**Impacto**: Falha silenciosa, perda de ações.

**Locações típicas**:
- Email jobs sem retry
- Important jobs com queue padrão

### 5. Dependência Circular

Classes dependem uma da outra circularmente.

**Impacto**: Dificuldade de teste, memory leaks.

**Locações típicas**:
- Service A → Service B → Service A
- Model ↔ Service

## Output

**Arquivo**: `risks/{context}.json`

**Formato**:

```json
{
  "id": "risk-validation",
  "type": "risk",
  "name": "Validation Risks",
  "created_at": "2026-04-08T14:34:00Z",
  "updated_at": "2026-04-08T14:34:00Z",
  "source": {
    "files": [
      "app/Services/GuestService.php",
      "app/Models/Guest.php"
    ],
    "routes": [],
    "components": []
  },
  "summary": "Riscos relacionados a validação",
  "details": {
    "risk_level": "high",
    "issues": [
      {
        "id": "issue-duplicate-validation",
        "type": "duplicate-validation",
        "severity": "high",
        "description": "Validação de email duplicado em 3 lugares",
        "impact": "Manutenção difícil, potencial inconsistência",
        "locations": [
          {
            "file": "app/Http/Controllers/GuestController.php",
            "line": 45,
            "description": "Validação no controller"
          },
          {
            "file": "app/Services/GuestService.php",
            "line": 23,
            "description": "Validação no service"
          },
          {
            "file": "app/Models/Guest.php",
            "line": 78,
            "description": "Validação no model"
          }
        ],
        "suggestion": "Centralizar em FormRequest + GuestValidator",
        "effort": "medium"
      },
      {
        "id": "issue-missing-transaction",
        "type": "missing-transaction",
        "severity": "critical",
        "description": "Criação de guest + envio de email sem transaction",
        "impact": "Email enviado mas guest não foi criado (ou vice versa)",
        "locations": [
          {
            "file": "app/Services/GuestService.php",
            "line": 67,
            "description": "Create + Mail::send fora de transaction"
          }
        ],
        "suggestion": "Wrap em DB::transaction()",
        "effort": "low"
      },
      {
        "id": "issue-job-no-retry",
        "type": "job-no-retry",
        "severity": "medium",
        "description": "Job de notificação sem configuração de retry",
        "impact": "Se email falhar, não há retry automático",
        "locations": [
          {
            "file": "app/Jobs/SendGuestNotification.php",
            "line": 15,
            "description": "Job sem $tries configurado"
          }
        ],
        "suggestion": "Adicionar $tries = 3 e $backoff",
        "effort": "low"
      }
    ],
    "statistics": {
      "total_issues": 3,
      "critical": 1,
      "high": 1,
      "medium": 1,
      "low": 0
    }
  },
  "relationships": [
    "analysis/guest-service.json",
    "domains/guest-management.json"
  ]
}
```

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único no formato "risk-{context}" |
| type | string | Sempre "risk" |
| risk_level | string | low/medium/high/critical |
| issues | array | Lista de problemas |
| statistics | object | Contagem por severidade |

## Níveis de Severidade

| Level | Cor | Descrição |
|-------|-----|-----------|
| critical | 🔴 | Problema crítico, deve ser corrigido imediatamente |
| high | 🟠 | Problema grave, corrigir antes de migrar |
| medium | 🟡 | Problema moderado, corrigir em breve |
| low | 🔵 | Problema menor, corrigir quando possível |

## Dependências

- **Inputs**: 
  - `analysis/*.json`
  - `domains/*.json`
- **Próxima skill**: [Refactor Planner](REFACTOR-PLANNER.md)

## Exemplo de Uso

```bash
# Detectar todos os riscos
php artisan refactor:detect-risks

# Detectar riscos específicos
php artisan refactor:detect-risks --type transaction
```

---

*Última atualização: 2026-04-08*
