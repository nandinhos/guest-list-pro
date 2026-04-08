# Skill: Project Scanner

## Objetivo

Mapear toda a estrutura do projeto Laravel, identificando e classificando arquivos por tipo. Esta é a primeira skill da pipeline e fornece a base para todas as análises subsequentes.

## Input

- **Root do projeto**: `/home/nandodev/projects/guest-list-pro`

## Pré-requisitos

Nenhum. Esta é a primeira skill a ser executada.

## Ações Executadas

### 1. Varredura de Diretórios

O scanner varre recursivamente os diretórios principais do projeto:
- `app/` — Código da aplicação
- `routes/` — Definições de rotas
- `database/` — Migrations e seeders
- `config/` — Arquivos de configuração

### 2. Identificação de Arquivos

Para cada diretório, identifica arquivos por tipo:

| Categoria | Diretórios Procurados |
|-----------|----------------------|
| Models | `app/Models/` |
| Controllers | `app/Http/Controllers/` |
| Filament Resources | `app/Filament/Resources/` |
| Jobs | `app/Jobs/` |
| Events | `app/Events/` |
| Services | `app/Services/` |
| Providers | `app/Providers/` |
| Livewire Components | `app/Livewire/` |
| Notifications | `app/Notifications/` |
| Policies | `app/Policies/` |
| Migrations | `database/migrations/` |

### 3. Classificação

Cada arquivo é classificado em uma das categorias:
- `models`
- `controllers`
- `resources` (Filament)
- `jobs`
- `events`
- `services`
- `livewire`
- `notifications`
- `policies`
- `migrations`
- `unknown` (outros arquivos relevantes)

## Output

**Arquivo**: `analysis/project-structure.json`

**Formato**:

```json
{
  "id": "analysis-project-structure",
  "type": "analysis",
  "name": "Project Structure",
  "created_at": "2026-04-08T14:30:00Z",
  "updated_at": "2026-04-08T14:30:00Z",
  "source": {
    "files": ["/home/nandodev/projects/guest-list-pro"],
    "routes": [],
    "components": []
  },
  "summary": "Mapeamento completo da estrutura do projeto Laravel",
  "details": {
    "models": [
      "app/Models/Guest.php",
      "app/Models/Event.php"
    ],
    "controllers": [],
    "resources": [
      "app/Filament/Resources/GuestResource.php",
      "app/Filament/Resources/EventResource.php"
    ],
    "jobs": [],
    "events": [],
    "services": [],
    "livewire": [],
    "notifications": [],
    "policies": [],
    "migrations": [],
    "unknown": []
  },
  "statistics": {
    "total_files": 50,
    "total_models": 2,
    "total_resources": 2
  }
}
```

## Campos do Output

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | string | UUID único |
| type | string | Sempre "analysis" |
| name | string | Nome descritivo |
| created_at | string | Timestamp ISO 8601 |
| updated_at | string | Timestamp ISO 8601 |
| source | object | Fontes da análise |
| summary | string | Resumo |
| details | object | Detalhes por categoria |
| statistics | object | Estatísticas gerais |

## Dependências

Esta skill não depende de nenhuma outra. É a primeira a ser executada.

## Próxima Skill

[Runtime Behavior Mapper](RUNTIME-BEHAVIOR-MAPPER.md) — Usa a estrutura mapeada para identificar fluxos de execução.

## Exemplo de Uso

```bash
# Executar apenas o Project Scanner
php artisan refactor:scan --skill project-scanner

# Ver resultado
cat .refactor/analysis/project-structure.json
```

---

*Última atualização: 2026-04-08*
