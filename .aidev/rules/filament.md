# Filament Stack Rules

## Project Structure
```
app/
├── Filament/
│   ├── Admin/              # Painel Admin
│   ├── Promoter/          # Painel Promoter
│   ├── Validator/         # Painel Validator
│   ├── Bilheteria/        # Painel Bilheteria
│   ├── Resources/
│   ├── Pages/
│   ├── Widgets/
│   └── Actions/
├── Enums/                  # UserRole, EventStatus, RequestStatus, etc.
├── Services/               # GuestService, ApprovalRequestService, etc.
├── Models/
├── Observers/
├── Livewire/
├── Imports/                # GuestsImport
├── Notifications/
├── Rules/                  # DocumentValidation
tests/Feature/
tests/Unit/
```

## guest-list-pro Specific Rules

### Regras de Negócio
- **Documento único**: Usar `document_normalized` para comparações
- **Sistema de aprovações**: Usar `ApprovalRequestService`
- **Check-in**: Registrar em `CheckinAttempt`, usar `GuestSearchService`

### Mobile-First (OBRIGATÓRIO)
```php
// Correto: ViewColumn para mobile
Tables\Columns\ViewColumn::make('mobile_card')
    ->view('filament.tables.columns.guest-card')
    ->hiddenFrom('md'),

Tables\Columns\TextColumn::make('name')
    ->visibleFrom('md'),

// Errado: Layout\View em tabelas
Tables\Columns\Layout\View::make('...');
```

### SPA Desabilitado
```php
// Em todos os PanelProviders
->spa(false)
```

### Enum Validation
```php
// Correto
$type = $get('type');
$enum = $type instanceof MyEnum ? $type : MyEnum::tryFrom($type ?? '');
if ($enum === MyEnum::SomeValue) { ... }

// Errado
if ($get('type') === MyEnum::SomeValue) { ... }
```

### Database Notifications
```php
// Correto: getDatabaseMessage sem Actions
public function getDatabaseMessage(): string {
    return 'Sua solicitação foi aprovada.';
}

// Errado: toArray com Actions (erro de serialização)
public function toArray(object $notifiable): array {
    return ['actions' => [Action::make('view')...]];
}
```

### Performance
- Eager loading: `$query->with(['event', 'requester'])`
- Cache em widgets: `protected static ?string $pollingInterval = '30s';`

## Naming Conventions
- **Resources**: `UserResource`, `PostResource`
- **Pages**: `Dashboard`, `Settings`
- **Widgets**: `StatsOverview`, `LatestOrders`
- **Actions**: `CreateUser`, `ApproveOrder`
- **Models**: `User`, `Post` (singular)
- **Services**: `UserService`, `PaymentService`
- **Requests**: `StoreUserRequest`, `UpdatePostRequest`

## Filament Patterns

### Resources (CRUD)
- Um Resource por Model
- Definir form() e table() no Resource
- Usar RelationManagers para relacionamentos
- Actions customizadas para operações especiais

```php
public static function form(Form $form): Form
{
    return $form->schema([
        TextInput::make('name')->required(),
        Select::make('status')->options([...]),
    ]);
}
```

### Pages
- Pages customizadas para views fora do CRUD
- Usar HasActions para operações
- Widgets para métricas e gráficos

### Widgets
- StatsOverviewWidget para KPIs
- ChartWidget para gráficos
- TableWidget para listagens rápidas

### Livewire Components
- Component per feature
- Wire:model para binding
- Events para comunicação entre componentes
- AlpineJS para interatividade client-side

## Laravel Base Patterns

### Controllers (API)
- Single responsibility
- Form Requests para validação
- Resources para respostas API
- Controllers finos, Services grossos

### Models
- Relationships bem definidos
- Accessors/Mutators
- Scopes para queries comuns
- Soft deletes quando apropriado

### Services
- Camada de lógica de negócio
- Single responsibility
- Dependency injection
- Retornar DTOs ou Models

### Testing
- Feature tests para endpoints HTTP
- Unit tests para services/actions
- Factories para dados de teste
- Database transactions para isolamento

```php
public function test_user_can_be_created(): void
{
    $response = $this->postJson('/api/users', [
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
}
```

## Artisan Commands
```bash
# Testing
php artisan test --filter=UserTest

# Filament
php artisan make:filament-resource User --generate
php artisan make:filament-page Settings
php artisan make:filament-widget StatsOverview

# Migrations
php artisan migrate
php artisan migrate:fresh --seed

# Cache
php artisan config:clear
php artisan cache:clear
php artisan filament:clear-cached-components
```