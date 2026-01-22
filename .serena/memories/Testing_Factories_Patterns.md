# Padrões de Testes & Factories - Guest List Pro

## Contexto
Durante a Sprint de Solicitações (S.6), descobrimos padrões importantes para testes no projeto.

## Lição 1: Factories São Obrigatórias
**Problema**: Models que usam `HasFactory` trait precisam ter factories criadas.
**Erro**: `Class "Database\Factories\XxxFactory" not found`

**Solução**: Criar factories para TODOS os models que serão usados em testes.

### Factories Disponíveis:
```
database/factories/
├── UserFactory.php          # States por role
├── EventFactory.php         # States: active, draft, finished, cancelled
├── SectorFactory.php        # States: vip, pista, withCapacity
├── GuestFactory.php         # States: checkedIn, notCheckedIn, withRg, withPassport
└── ApprovalRequestFactory.php # States: pending, approved, rejected, guestInclusion, emergencyCheckin
```

## Lição 2: Padrão de Factory States
**Protocolo**: Criar states descritivos para cenários comuns de teste.

```php
// Exemplo de factory com states
class ApprovalRequestFactory extends Factory
{
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RequestStatus::PENDING,
        ]);
    }

    public function guestInclusion(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => RequestType::GUEST_INCLUSION,
            'requester_id' => User::factory()->state(['role' => UserRole::PROMOTER]),
        ]);
    }
}
```

## Lição 3: Testes Filament com Livewire
**Protocolo**: Usar `Livewire::test()` para testar componentes Filament.

```php
use Livewire\Livewire;

// Testar table actions
Livewire::test(ListApprovalRequests::class)
    ->callTableAction('approve', $request)
    ->assertNotified('Solicitação Aprovada');

// Testar bulk actions
Livewire::test(ListApprovalRequests::class)
    ->callTableBulkAction('approveSelected', $requests)
    ->assertNotified();

// Testar filtros
Livewire::test(ListApprovalRequests::class)
    ->filterTable('status', 'pending')
    ->assertCanSeeTableRecords([$pending])
    ->assertCanNotSeeTableRecords([$approved]);

// Testar busca
Livewire::test(ListApprovalRequests::class)
    ->searchTable('João')
    ->assertCanSeeTableRecords([$matchingRequest]);
```

## Lição 4: Isolamento de Notificações
**Protocolo**: Sempre usar `Notification::fake()` no setUp() para evitar side effects.

```php
protected function setUp(): void
{
    parent::setUp();
    Notification::fake();
    // ... criar usuários e dados de teste
}
```

## Lição 5: PHPUnit vs Pest
**Protocolo**: Este projeto usa PHPUnit. Usar métodos corretos:
- ✅ `assertStringContainsString()`
- ❌ `assertStringContains()` (não existe)

## Lição 6: Estrutura de Testes
```
tests/
├── Unit/
│   └── ApprovalRequestServiceTest.php  # Testes do Service isolado
└── Feature/
    └── ApprovalRequestResourceTest.php # Testes E2E do Filament Resource
```

## Comandos Úteis
```bash
# Criar factory
vendor/bin/sail artisan make:factory EventFactory

# Criar teste unitário
vendor/bin/sail artisan make:test ApprovalRequestServiceTest --unit

# Criar teste feature
vendor/bin/sail artisan make:test ApprovalRequestResourceTest

# Rodar testes específicos
vendor/bin/sail artisan test --filter=ApprovalRequestServiceTest

# Rodar todos os testes
vendor/bin/sail artisan test --compact
```

## Checklist Pré-Teste
- [ ] Todas as factories dos models usados existem?
- [ ] Os states necessários estão criados nas factories?
- [ ] `Notification::fake()` está no setUp()?
- [ ] Usando métodos PHPUnit corretos?
- [ ] Testes unitários separados dos feature tests?

---
*Criado: Janeiro 2026*
*Sprint: S.6 - Testes e Validação*
