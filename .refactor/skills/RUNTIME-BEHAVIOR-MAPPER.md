# Skill: Runtime Behavior Mapper

## Objetivo

Mapear o comportamento real da aplicação baseado em execução runtime. Rastreia a cadeia de chamadas desde a ação do usuário até os efeitos colaterais.

## Input

- **Ação do usuário**: Ex: "create guest", "approve guest", "list events"

## Pré-requisitos

- [Project Scanner](PROJECT-SCANNER.md) ter sido executado
- Arquivo `analysis/project-structure.json` existir

## Ações Executadas

### 1. Identificação de Rota

A partir da ação do usuário, identifica a rota correspondente:
- Analisa arquivos em `routes/`
- Mapeia URL para método HTTP + controller
- Identifica middleware aplicado

### 2. Identificação de Controller/Resource

Localiza o controller ou Filament Resource responsável:
- Para web: `app/Http/Controllers/`
- Para Filament: `app/Filament/Resources/`
- Identifica método handler (create, edit, update, delete)

### 3. Mapeamento da Cadeia de Execução

Para cada passo da cadeia, identifica:

| Layer | O que procurar |
|-------|----------------|
| controller | Método do controller/resource |
| service | Services injetados |
| model | Models utilizados |
| repository | Repositories chamados |
| job | Jobs disparados |
| event | Events disparados |

### 4. Identificação de Side-Effects

Documenta efeitos colaterais:
- **Emails**: Notificações enviadas
- **Logs**: Entradas de log geradas
- **Integrações**: APIs externas chamadas
- **Redis/Cache**: Operações de cache

## Output

**Arquivo**: `flows/{feature}.json`

**Formato**:

```json
{
  "id": "flow-create-guest",
  "type": "flow",
  "name": "Create Guest",
  "created_at": "2026-04-08T14:31:00Z",
  "updated_at": "2026-04-08T14:31:00Z",
  "source": {
    "files": [
      "app/Filament/Resources/GuestResource/Pages/CreateGuest.php"
    ],
    "routes": ["POST /admin/guests"],
    "components": ["GuestResource"]
  },
  "summary": "Fluxo de criação de convidado",
  "details": {
    "trigger": {
      "type": "http_request",
      "method": "POST",
      "url": "/admin/guests"
    },
    "steps": [
      {
        "order": 1,
        "layer": "controller",
        "action": "GuestResource\\Pages\\CreateGuest::create",
        "file": "app/Filament/Resources/GuestResource/Pages/CreateGuest.php",
        "line": 45,
        "description": "Valida dados do formulário"
      },
      {
        "order": 2,
        "layer": "service",
        "action": "GuestService::create",
        "file": "app/Services/GuestService.php",
        "line": 23,
        "description": "Cria registro no banco"
      },
      {
        "order": 3,
        "layer": "model",
        "action": "Guest::created",
        "file": "app/Models/Guest.php",
        "line": 78,
        "description": "Dispara evento de criação"
      },
      {
        "order": 4,
        "layer": "event",
        "action": "GuestCreated::dispatch",
        "file": "app/Events/GuestCreated.php",
        "description": "Dispara job de notificação"
      },
      {
        "order": 5,
        "layer": "job",
        "action": "SendGuestNotification::dispatch",
        "file": "app/Jobs/SendGuestNotification.php",
        "description": "Envia email de confirmação"
      }
    ],
    "side_effects": [
      {
        "type": "email",
        "description": "Email de confirmação enviado",
        "file": "app/Notifications/GuestCreatedNotification.php"
      }
    ],
    "dependencies": [
      "app/Models/Guest.php",
      "app/Services/GuestService.php",
      "app/Events/GuestCreated.php",
      "app/Jobs/SendGuestNotification.php"
    ]
  },
  "relationships": [
    "analysis/project-structure.json"
  ]
}
```

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único no formato "flow-{feature}" |
| type | string | Sempre "flow" |
| name | string | Nome da feature |
| trigger | object | Informações da ação que iniciou o fluxo |
| steps | array | Cadeia de execução |
| side_effects | array | Efeitos colaterais |
| dependencies | array | Arquivos dependentes |

## Dependências

- **Input**: `analysis/project-structure.json` (do Project Scanner)
- **Próxima skill**: [Laravel Analyzer](LARAVEL-ANALYZER.md)

## Exemplo de Uso

```bash
# Mapear comportamento de criação de guest
php artisan refactor:map --action "create guest"

# Mapear comportamento de aprovação
php artisan refactor:map --action "approve guest"
```

---

*Última atualização: 2026-04-08*
