---
id: SPEC-0005-19-04-2026
title: Ticket Pricing por Setor — Preços Variados por Setor
domain: feature
status: draft
priority: high
author: Nando Dev
owner: team-core
source: SPEC-PERM-P3.1
created_at: 2026-04-19
updated_at: 2026-04-19
related_files:
  - app/Models/TicketType.php
  - app/Models/Sector.php
  - app/Models/TicketSale.php
  - app/Services/TicketSaleService.php
  - database/migrations/*_create_ticket_types_table.php
related_tasks:
  - P3.1
---

# SPEC-0005: Ticket Pricing por Setor

**Versão:** 1.0
**Data:** 2026-04-19
**Status:** Draft
**Prioridade:** Alta
**Estimativa:** 16h (complexidade alta)

---

## 1. Objetivo

Permitir que o administrador defina **preços variados por setor** para cada tipo de ingresso em um evento. Atualmente, `TicketType.price` é um valor global que não varia por setor.

**Problema de Negócio:**
- Evento com setores Pista, VIP, Camarote, Backstage
- Cada setor deve ter preços diferentes para o mesmo tipo de ingresso
- Example: Ingresso "Show Completo" - Pista R$50, VIP R$150, Camarote R$300

---

## 2. Arquitetura Proposta

### 2.1 Modelos Envolvidos

```
TicketType (existente)
├── id
├── name
├── description
├── price (fallback/default)
└── event_id

Sector (existente)
├── id
├── event_id
├── name
└── capacity

TicketTypeSector (NOVO - tabela pivot)
├── id
├── ticket_type_id (FK)
├── sector_id (FK)
├── price (override para este setor)
└── timestamps

TicketSale (existente)
├── id
├── ticket_type_id (FK)
├── sector_id (FK)
├── price_sold (preço efetiva)
├── payment_method
└── buyer_name
```

### 2.2 Lógica de Preço

```php
// Em TicketSaleService
public function getPriceForSector(TicketType $ticketType, int $sectorId): float
{
    $override = TicketTypeSector::where('ticket_type_id', $ticketType->id)
        ->where('sector_id', $sectorId)
        ->first();

    return $override?->price ?? $ticketType->price;
}
```

---

## 3. Plano de Implementação

### Fase 1: Migration (1h)

Criar migration para `ticket_type_sector`:

```php
Schema::create('ticket_type_sector', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ticket_type_id')->constrained()->cascadeOnDelete();
    $table->foreignId('sector_id')->constrained()->cascadeOnDelete();
    $table->decimal('price', 10, 2);
    $table->timestamps();

    $table->unique(['ticket_type_id', 'sector_id']);
});
```

### Fase 2: Model TicketTypeSector (1h)

Criar `app/Models/TicketTypeSector.php`:

```php
class TicketTypeSector extends Model
{
    protected $fillable = ['ticket_type_id', 'sector_id', 'price'];

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

### Fase 3: Relationships nos Models (1h)

**TicketType.php** - adicionar:
```php
public function sectorPrices(): HasMany
{
    return $this->hasMany(TicketTypeSector::class);
}
```

**Sector.php** - adicionar:
```php
public function ticketTypePrices(): BelongsToMany
{
    return $this->belongsToMany(TicketType::class, 'ticket_type_sector')
        ->withPivot('price');
}
```

### Fase 4: Admin UI - Formulário de Preços (4h)

No `TicketTypeResource`:
1. Ao editar/criar TicketType, mostrar card com setores do evento
2. Para cada setor, permitir definir preço override
3. Se não definir, usa `price` default do TicketType

**UI Proposal:**
```
┌─────────────────────────────────────────────────────────────┐
│  Preços por Setor                                             │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  [x] Usar preço custom por setor                             │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ Setor          │ Preço Padrão │ Preço custom         │   │
│  ├─────────────────────────────────────────────────────┤   │
│  │ Pista          │ R$ 50,00     │ [R$ 45,00        ]   │   │
│  │ VIP            │ R$ 150,00    │ [R$ 130,00       ]   │   │
│  │ Camarote       │ R$ 300,00    │ [R$ 280,00       ]   │   │
│  │ Backstage      │ R$ 80,00     │ [_______________]   │   │
│  └─────────────────────────────────────────────────────┘   │
│                                                             │
│  Preço padrão: usa o valor default do TicketType           │
└─────────────────────────────────────────────────────────────┘
```

### Fase 5: TicketSaleService - Aplicar Preço Correto (3h)

Modificar `TicketSaleService` para usar preço do setor:

```php
public function createSale(array $data): TicketSale
{
    $ticketType = TicketType::findOrFail($data['ticket_type_id']);
    $sectorId = $data['sector_id'];

    // Busca preço customizado ou usa fallback
    $price = $this->getPriceForSector($ticketType, $sectorId);

    return TicketSale::create([
        ...$data,
        'price_sold' => $price,
    ]);
}
```

### Fase 6: Bilheteria - Selector de Setor (3h)

Na venda de ingresso em `/bilheteria`:
1. Primeiro selecionar Setor (obrigatório)
2. Depois selecionar TicketType
3. Mostrar preço calculado em tempo real

### Fase 7: Validação E2E (3h)

Criar testes E2E para:
- Admin configura preços por setor
- Bilheteria vende ingresso com preço correto
- Fallback para price default quando não há override

---

## 4. Requisitos Funcionais

| Código | Descrição | Prioridade |
|--------|-----------|------------|
| RF01 | Admin pode definir preço customizado por setor | Alta |
| RF02 | Sistema usa preço customizado na venda | Alta |
| RF03 | Sistema faz fallback para price default | Alta |
| RF04 | Bilheteria vê preço correto antes de confirmar venda | Alta |
| RF05 | Relatórios mostram receita por setor (usando price_sold) | Média |

---

## 5. Casos de Borda

| Caso | Tratamento |
|------|-----------|
| Nenhum sector_price definido | Usa TicketType.price como fallback |
| sector_price = 0 | Permite R$ 0,00 (ingresso cortesia) |
| TicketType.price = null | Erro de validação ( preço obrigatório ) |
| Setor removido do evento | Mantém histórico (soft delete ou nullify sector_id) |

---

## 6. Riscos e Mitigações

| Risco | Probabilidade | Impacto | Mitigação |
|-------|---------------|---------|-----------|
| Breaking change em TicketSale | Média | Alto | Manter price_sold como campo existente |
| Complexidade UI alta | Alta | Médio | Card simples com lista de setores |
| Performance com muitos setores | Baixa | Baixo | Cache de preços por evento |

---

## 7. Estimativas

| Fase | Descrição | Tempo |
|------|-----------|-------|
| 1 | Migration | 1h |
| 2 | Model TicketTypeSector | 1h |
| 3 | Relationships | 1h |
| 4 | Admin UI | 4h |
| 5 | TicketSaleService | 3h |
| 6 | Bilheteria selector | 3h |
| 7 | E2E tests | 3h |
| **TOTAL** | | **16h** |

---

## 8. Critérios de Aceitação

- [ ] Migration cria tabela `ticket_type_sector`
- [ ] Model TicketTypeSector com relationships corretas
- [ ] Admin consegue definir preço por setor no formulário
- [ ] Venda de ingresso usa preço correto do setor
- [ ] Fallback funciona quando não há override
- [ ] E2E tests passam

---

## 9. Pré-requisitos

- [ ] SPEC-PERM implementada (já ✅)
- [ ] TicketType existente (sim)
- [ ] Sector existente (sim)
- [ ] TicketSale existente (sim)
- [ ] Database seeding com dados de teste

---

## 10. Dependências

- `app/Models/TicketType.php`
- `app/Models/Sector.php`
- `app/Services/TicketSaleService.php`
- `app/Filament/Admin/Resources/TicketTypeResource.php` (para UI)

---

## 11. Status de Implementação

| Fase | Status | Observação |
|------|--------|-------------|
| 1. Migration | ⬜ | - |
| 2. Model | ⬜ | - |
| 3. Relationships | ⬜ | - |
| 4. Admin UI | ⬜ | - |
| 5. TicketSaleService | ⬜ | - |
| 6. Bilheteria selector | ⬜ | - |
| 7. E2E tests | ⬜ | - |

---

## 12. Referências

- [SPEC-PERM - P3.1](./SPEC-PERM-17-04-2026-permissions-design.md) (seção 14)
- [SPEC-0004 - E2E Infrastructure](./SPEC-0004-17-04-2026-e2e-infraestrutura.md)
- [TicketType model](./app/Models/TicketType.php)
- [TicketSale model](./app/Models/TicketSale.php)