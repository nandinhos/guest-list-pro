# Guia de Testes

> ⚠️ **IMPORTANTE**: Use SEMPRE `vendor/bin/sail artisan` ou `sail artisan` neste projeto!

---

## PHPUnit/Pest

### Executar Testes
```bash
# Todos os testes
vendor/bin/sail artisan test

# Teste específico
vendor/bin/sail artisan test --filter=TestName

# Com coverage
vendor/bin/sail artisan test --coverage

# Paralelo
vendor/bin/sail artisan test --parallel
```

---

## Factories

### Criar Factory
```bash
vendor/bin/sail artisan make:factory GuestFactory
vendor/bin/sail artisan make:factory -m Guest GuestFactory
```

### Usar Factory
```php
// Criar 1 registro
$guest = Guest::factory()->create();

// Criar 3 registros
$guests = Guest::factory()->count(3)->create();

// Com estado
$guest = Guest::factory()->checkedIn()->create();

// Com relações
$guest = Guest::factory()->for($event, 'event')->create();
```

### Estados
```php
// Em GuestFactory.php
public function checkedIn(): static {
    return $this->state(fn() => ['checked_in_at' => now()]);
}

public function pending(): static {
    return $this->state(fn() => ['status' => 'pending']);
}
```

---

## Testes de Feature

### Exemplo
```php
use function Pest\Laravel\postJson;

it('creates guest successfully', function () {
    $event = Event::factory()->create();
    
    postJson('/api/guests', [
        'event_id' => $event->id,
        'name' => 'John Doe',
        'document' => '12345678900',
    ])
    ->assertCreated()
    ->assertJsonStructure(['data' => ['id', 'name', 'document']]);
    
    expect(Guest::count())->toBe(1);
});
```

---

## Testes de Service

### Exemplo
```php
it('throws duplicate exception when document exists', function () {
    $event = Event::factory()->create();
    $existingGuest = Guest::factory()->for($event)->create([
        'document' => '12345678900',
        'document_normalized' => '12345678900',
    ]);
    
    $service = app(GuestService::class);
    
    expect(fn() => $service->create([
        'event_id' => $event->id,
        'name' => 'John Doe',
        'document' => '123.456.789-00',
    ]))->toThrow(DuplicateGuestException::class);
});
```

---

## Mocks

### Mockery
```php
use Mockery;

$mock = Mockery::mock(GuestRepository::class);
$mock->shouldReceive('find')
    ->with(1)
    ->once()
    ->andReturn($guest);
```

---

## Gaps de Testes (Cobertura Atual: 32%)

### Priority 1 - CRÍTICO
- GuestService (0 testes)
- GuestsImport (0 testes)
- CheckinAttempt (0 testes)

### Priority 2 - IMPORTANTE
- DocumentValidationService (0 testes)
- GuestSearchService (poucos testes)

### Priority 3 - BOM TER
- ApprovalRequestService (poucos testes)
- Controllers (poucos testes)

---

## Boas Práticas

### Arrange-Act-Assert
```php
it('does something', function () {
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $result = $this->service->doSomething($user);
    
    // Assert
    expect($result)->toBe(true);
});
```

### Nomeclatura
```php
// Bom
it('returns guest when document exists')
it('throws exception when document is invalid')
it('creates guest with normalized document')

// Ruim
it('test1')
it('guest')
```

### Isolamento
```php
// Usar DatabaseTransactions
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MyTest extends TestCase {
    use DatabaseTransactions;
    // ...
}
```

---

**Última atualização:** 2026-02-18
