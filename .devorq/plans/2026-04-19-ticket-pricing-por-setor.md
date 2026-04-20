# Plano de Implementação: Ticket Pricing por Setor

> **Para agentes:** SKILL OBRIGATÓRIA: Use `subagent-driven-development` (recomendado) ou `executing-plans` para implementar task-by-task. Passos usam sintaxe checkbox (`- [ ]`).

**Meta:** Permitir que o admin defina preços variados por setor para cada tipo de ingresso, com fallback para preço padrão.

**Arquitetura:** Criar tabela pivot `ticket_type_sector` para armazenar preços customizados. O `TicketSaleService` aplicará o preço correto, buscando primeiro na pivot e fazendo fallback para `TicketType.price`.

**Stack:** Laravel 12, Filament 4, MySQL, TDD

---

## Estrutura de Arquivos

```
app/
├── Models/
│   └── TicketTypeSector.php          # CRIAR
├── Services/
│   └── TicketSaleService.php         # MODIFICAR
database/migrations/
└── YYYY_MM_DD_create_ticket_type_sector_table.php  # CRIAR
```

---

## Task 1: Migration — Criar Tabela ticket_type_sector

**Arquivos:**
- Criar: `database/migrations/YYYY_MM_DD_HHMMSS_create_ticket_type_sector_table.php`

- [ ] **Passo 1: Gerar migration**

```bash
vendor/bin/sail artisan make:migration create_ticket_type_sector_table
```

- [ ] **Passo 2: Editar migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_type_sector', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->timestamps();

            $table->unique(['ticket_type_id', 'sector_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_type_sector');
    }
};
```

- [ ] **Passo 3: Executar migration**

```bash
vendor/bin/sail artisan migrate
```

- [ ] **Passo 4: Verificar tabela criada**

```bash
vendor/bin/sail artisan schema:show ticket_type_sector
```

---

## Task 2: Model TicketTypeSector

**Arquivos:**
- Criar: `app/Models/TicketTypeSector.php`

- [ ] **Passo 1: Criar o model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketTypeSector extends Model
{
    protected $table = 'ticket_type_sector';

    protected $fillable = [
        'ticket_type_id',
        'sector_id',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
        ];
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }
}
```

- [ ] **Passo 2: Verificar sintaxe**

```bash
vendor/bin/sail php -l app/Models/TicketTypeSector.php
```

---

## Task 3: Relationships nos Models Existentes

**Arquivos:**
- Modificar: `app/Models/TicketType.php`
- Modificar: `app/Models/Sector.php`

- [ ] **Passo 1: Adicionar sectorPrices ao TicketType**

```php
public function sectorPrices(): HasMany
{
    return $this->hasMany(TicketTypeSector::class);
}
```

Adicionar após método `ticketSales()`.

- [ ] **Passo 2: Adicionar ticketTypePrices ao Sector**

```php
public function ticketTypePrices(): BelongsToMany
{
    return $this->belongsToMany(TicketType::class, 'ticket_type_sector')
        ->withPivot('price');
}
```

Adicionar após método `ticketSales()`.

- [ ] **Passo 3: Verificar sintaxe**

```bash
vendor/bin/sail php -l app/Models/TicketType.php && vendor/bin/sail php -l app/Models/Sector.php
```

---

## Task 4: TicketSaleService — getPriceForSector

**Arquivos:**
- Modificar: `app/Services/TicketSaleService.php`

- [ ] **Passo 1: Ler TicketSaleService atual**

```bash
head -60 app/Services/TicketSaleService.php
```

- [ ] **Passo 2: Adicionar método getPriceForSector**

```php
/**
 * Retorna o preço para um tipo de ingresso em um setor específico.
 * Se houver preço customizado na pivot, usa ele. Caso contrário, usa o price default.
 */
public static function getPriceForSector(TicketType $ticketType, int $sectorId): float
{
    $override = TicketTypeSector::where('ticket_type_id', $ticketType->id)
        ->where('sector_id', $sectorId)
        ->first();

    return (float) ($override?->price ?? $ticketType->price);
}
```

- [ ] **Passo 3: Verificar sintaxe**

```bash
vendor/bin/sail php -l app/Services/TicketSaleService.php
```

---

## Task 5: E2E Test — Admin Configura Preços por Setor

**Arquivos:**
- Modificar: `e2e/smoke-tests.spec.ts`

- [ ] **Passo 1: Ler smoke-tests para entender padrões**

```bash
tail -100 e2e/smoke-tests.spec.ts
```

- [ ] **Passo 2: Adicionar teste de ticket pricing**

```typescript
test.describe('🎫 Ticket Pricing Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.fill('input[name="email"]', 'admin@guestlistpro.com');
    await page.fill('input[name="password"]', 'password');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/admin**');
  });

  test('TC-TICKETPRICING-001: Admin can set custom price per sector', async ({ page }) => {
    await page.click('text=Tipo de Ingresso');
    const editButton = page.locator('button[title="Editar"]').first();
    await editButton.click();

    const sectorPricingSection = page.locator('text=Preços por Setor');
    await expect(sectorPricingSection).toBeVisible();
  });
});
```

- [ ] **Passo 3: Rodar testes para garantir não quebrou**

```bash
node node_modules/.bin/playwright test e2e/smoke-tests.spec.ts --reporter=list 2>&1 | tail -30
```

---

## Task 6: Validação

**Arquivos:**
- Verificar: `app/Models/TicketTypeSector.php`
- Verificar: `app/Models/TicketType.php`
- Verificar: `app/Models/Sector.php`
- Verificar: `app/Services/TicketSaleService.php`

- [ ] **Passo 1: Rodar todos os E2E**

```bash
node node_modules/.bin/playwright test e2e/smoke-tests.spec.ts --reporter=list 2>&1 | tail -35
```

- [ ] **Passo 2: Verificar migration aplicada**

```bash
vendor/bin/sail artisan migrate:status
```

- [ ] **Passo 3: Verificar sintaxe PHP**

```bash
vendor/bin/sail php -l app/Models/TicketTypeSector.php
vendor/bin/sail php -l app/Services/TicketSaleService.php
```

---

## Checklist de Verificação

- [ ] Migration `create_ticket_type_sector_table` executada
- [ ] Model `TicketTypeSector` criado com relationships
- [ ] `TicketType.sectorPrices()` retorna HasMany
- [ ] `Sector.ticketTypePrices()` retorna BelongsToMany
- [ ] `TicketSaleService.getPriceForSector()` implementado
- [ ] E2E tests passam (26+)

---

## Opções de Execução

**1. Subagent-Driven (recomendado)** — Dispatch fresh subagent per task, review between tasks

**2. Inline Execution** — Execute tasks in this session using `executing-plans`

Qual abordagem prefere?