# Regras Específicas - Guest List Pro

> Regras de negócio e padrões específicos do projeto que devem ser seguidos por todos os agentes.

---

## 1. Regras de Negócio Críticas

### 1.1 Unicidade de Convidados

- **Documento deve ser único por evento**
- Usar `document_normalized` para comparações (sem pontuação)
- Validar ANTES do commit (preview em imports)
- Nunca permitir duplicatas silenciosamente

```php
// Correto: usar campo normalizado
Guest::where('event_id', $eventId)
    ->where('document_normalized', $normalizedDoc)
    ->exists();

// Errado: comparar documento bruto
Guest::where('document', $document)->exists();
```

### 1.2 Sistema de Aprovações

- **Usar `ApprovalRequestService` para toda lógica de aprovações**
- Nunca aprovar/rejeitar diretamente no Model
- Sempre notificar requester após mudança de status
- Respeitar expiração (`expires_at`) das solicitações

```php
// Correto
$this->approvalRequestService->approve($request, $approver);

// Errado
$request->update(['status' => 'approved']);
```

### 1.3 Check-in

- **Registrar toda tentativa em `CheckinAttempt`**
- Um único check-in por guest (não permitir duplicado)
- Busca por similaridade deve usar `GuestSearchService`
- Logar resultado: `success`, `duplicate`, `not_found`, `suspicious`

### 1.4 Bilheteria

- Vendas vinculadas ao operador (`sold_by`)
- Fechamento de caixa por período e operador
- Método de pagamento obrigatório

---

## 2. Padrões Obrigatórios Filament

### 2.1 Mobile-First em Tabelas

```php
// Correto: ViewColumn para mobile
Tables\Columns\ViewColumn::make('mobile_card')
    ->view('filament.tables.columns.guest-card')
    ->hiddenFrom('md'),

Tables\Columns\TextColumn::make('name')
    ->visibleFrom('md'),
```

```php
// Errado: Layout\View em tabelas (causa bugs)
Tables\Columns\Layout\View::make('...');
```

### 2.2 Validação de Enums

Sempre validar tipo antes de usar enum em formulários:

```php
// Correto
$type = $get('type');
$enum = $type instanceof MyEnum ? $type : MyEnum::tryFrom($type ?? '');
if ($enum === MyEnum::SomeValue) { ... }

// Errado (causa TypeError)
if ($get('type') === MyEnum::SomeValue) { ... }
```

### 2.3 SPA Desabilitado

- Manter `->spa()` desabilitado em todos os painéis
- Evita erros de JS com Chart.js e Alpine.js
- Configurado em cada `PanelProvider`

### 2.4 Notificações Database

```php
// Correto: getDatabaseMessage sem Actions
public function getDatabaseMessage(): string
{
    return 'Sua solicitação foi aprovada.';
}

// Errado: toArray com Actions (causa erro de serialização)
public function toArray(object $notifiable): array
{
    return [
        'actions' => [Action::make('view')...], // ERRO!
    ];
}
```

---

## 3. Padrões de Services

### 3.1 ApprovalRequestService

Métodos principais:
- `create()` - Criar nova solicitação
- `approve()` - Aprovar com notificação
- `reject()` - Rejeitar com motivo
- `checkForDuplicates()` - Verificar duplicidade
- `isExpired()` - Verificar expiração

### 3.2 GuestSearchService

Métodos principais:
- `search()` - Busca por similaridade
- `findByDocument()` - Busca exata por documento
- `normalize()` - Normalizar texto para busca

### 3.3 DocumentValidationService

Métodos principais:
- `validateCPF()` - Validar CPF
- `validateRG()` - Validar RG
- `normalize()` - Remover pontuação

---

## 4. Padrões de Performance

### 4.1 Eager Loading Obrigatório

```php
// Em widgets e listagens
$query->with(['event', 'requester', 'approver']);

// Em tables Filament
public static function table(Table $table): Table
{
    return $table
        ->modifyQueryUsing(fn ($query) => $query->with(['relation']));
}
```

### 4.2 Índices Requeridos

```sql
-- guests
INDEX (event_id, name_normalized)
INDEX (event_id, document_normalized)

-- checkin_attempts
INDEX (event_id, result)
INDEX (event_id, created_at)

-- approval_requests
INDEX (expires_at)

-- ticket_sales
INDEX (sold_by)
INDEX (event_id, created_at)
```

### 4.3 Cache em Widgets

```php
// Para widgets com dados que mudam pouco
protected static ?string $pollingInterval = '30s';

// Ou cache manual
Cache::remember('widget_key', 300, fn () => $this->getData());
```

---

## 5. Padrões de Testes

### 5.1 Usar Factories

```php
// Correto
$guest = Guest::factory()
    ->for($event)
    ->checkedIn()
    ->create();

// Errado
$guest = Guest::create([...dados manuais...]);
```

### 5.2 Estados de Factory Obrigatórios

- `Guest`: `checkedIn()`, `pending()`, `withCompanions()`
- `ApprovalRequest`: `pending()`, `approved()`, `rejected()`, `expired()`
- `Event`: `active()`, `past()`, `future()`

### 5.3 Testes de Duplicidade

Sempre testar:
- Documento idêntico
- Documento com formatação diferente
- Nome similar (fuzzy match)

---

## 6. Convenções de Código

### 6.1 Nomenclatura

| Tipo | Convenção | Exemplo |
|------|-----------|---------|
| Model | Singular PascalCase | `Guest`, `ApprovalRequest` |
| Table | Plural snake_case | `guests`, `approval_requests` |
| Service | PascalCase + Service | `GuestService` |
| Enum | PascalCase | `ApprovalStatus` |
| Enum Values | PascalCase | `Pending`, `Approved` |

### 6.2 Imports Filament v4

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
```

---

## 7. Checklist de Code Review

### Antes de Commit

- [ ] PSR-12 compliance (`vendor/bin/sail bin pint`)
- [ ] Type hints em parâmetros e retornos
- [ ] Eager loading onde necessário
- [ ] Sem N+1 queries
- [ ] Enums validados em forms
- [ ] Mobile-first em tabelas
- [ ] Testes cobrindo happy path e edge cases

### Antes de PR

- [ ] Todos os testes passando
- [ ] Migrations testadas (up e down)
- [ ] Sem código duplicado
- [ ] Documentação atualizada se necessário

---

**Este arquivo deve ser consultado antes de qualquer implementação no projeto.**
