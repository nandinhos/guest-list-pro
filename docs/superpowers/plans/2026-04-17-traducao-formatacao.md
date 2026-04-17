# Tradução e Formatação Regional pt-BR - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implementar serviço centralizado de formatação pt-BR e aplicar em toda a aplicação.

**Architecture:** Criar FormatService com métodos estáticos para money(), date(), datetime(), number(), percent(). Aplicar via helper global e nos widgets/tables existentes.

**Tech Stack:** Laravel 12, Carbon, PHP NumberFormatter

---

## Task 1: Criar FormatService

**Files:**
- Create: `app/Services/FormatService.php`

- [ ] **Step 1: Criar FormatService com métodos de formatação**

```php
<?php

namespace App\Services;

use Carbon\Carbon;
use NumberFormatter;

class FormatService
{
    protected static ?NumberFormatter $numberFormatter = null;
    protected static ?NumberFormatter $currencyFormatter = null;

    public static function money(float $value): string
    {
        if (! self::$currencyFormatter) {
            self::$currencyFormatter = new NumberFormatter('pt_BR', NumberFormatter::CURRENCY);
        }

        return self::$currencyFormatter->formatCurrency($value, 'BRL');
    }

    public static function date(Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->format('d/m/Y');
    }

    public static function datetime(Carbon|string|null $date): string
    {
        if (! $date) {
            return '-';
        }

        $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $carbon->format('d/m/Y H:i');
    }

    public static function number(float $value, int $decimals = 2): string
    {
        if (! self::$numberFormatter) {
            self::$numberFormatter = new NumberFormatter('pt_BR', NumberFormatter::DECIMAL);
            self::$numberFormatter->setAttribute(NumberFormatter::DECIMAL_SEPARATOR, ',');
            self::$numberFormatter->setAttribute(NumberFormatter::GROUPING_SEPARATOR, '.');
        }

        return number_format($value, $decimals, ',', '.');
    }

    public static function percent(float $value): string
    {
        return self::number($value, 1) . '%';
    }
}
```

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Services/FormatService.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Services/FormatService.php
git commit -m "feat: add FormatService for pt-BR formatting"
```

---

## Task 2: Criar helper global

**Files:**
- Create: `app/helpers.php`
- Modify: `bootstrap/app.php` (registrar helper)

- [ ] **Step 1: Criar arquivo helpers.php**

```php
<?php

use App\Services\FormatService;

if (! function_exists('format_money')) {
    function format_money(float $value): string
    {
        return FormatService::money($value);
    }
}

if (! function_exists('format_date')) {
    function format_date(Carbon|string|null $date): string
    {
        return FormatService::date($date);
    }
}

if (! function_exists('format_datetime')) {
    function format_datetime(Carbon|string|null $date): string
    {
        return FormatService::datetime($date);
    }
}

if (! function_exists('format_number')) {
    function format_number(float $value, int $decimals = 2): string
    {
        return FormatService::number($value, $decimals);
    }
}

if (! function_exists('format_percent')) {
    function format_percent(float $value): string
    {
        return FormatService::percent($value);
    }
}
```

- [ ] **Step 2: Registrar helper em bootstrap/app.php**

Adicionar após os imports:
```php
require_once __DIR__.'/../app/helpers.php';
```

- [ ] **Step 3: Verificar sintaxe**

Run: `php -l app/helpers.php && php -l bootstrap/app.php`
Expected: No syntax errors

- [ ] **Step 4: Testar no tinker**

Run: `vendor/bin/sail artisan tinker --execute="echo format_money(1234.56);"`
Expected: "R$ 1.234,56"

Run: `vendor/bin/sail artisan tinker --execute="echo format_datetime(now());"`
Expected: "17/04/2026 14:30"

- [ ] **Step 5: Commit**

```bash
git add app/helpers.php bootstrap/app.php
git commit -m "feat: add global formatting helpers for pt-BR"
```

---

## Task 3: Atualizar SectorMetricsTable

**Files:**
- Modify: `app/Filament/Widgets/SectorMetricsTable.php`

- [ ] **Step 1: Modificar coluna revenue para usar format_money()**

Modificar a coluna revenue de:
```php
TextColumn::make('revenue')
    ->label('Receita')
    ->money('BRL')
    ->sortable(),
```

Para:
```php
TextColumn::make('revenue')
    ->label('Receita')
    ->formatStateUsing(fn ($record) => $record->revenue ? format_money($record->revenue) : '-')
    ->sortable(),
```

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Filament/Widgets/SectorMetricsTable.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Widgets/SectorMetricsTable.php
git commit -m "refactor: apply pt-BR money formatting to SectorMetricsTable"
```

---

## Task 4: Atualizar TicketTypeReportTable

**Files:**
- Modify: `app/Filament/Widgets/TicketTypeReportTable.php`

- [ ] **Step 1: Modificar colunas de preço e valores**

Modificar:
```php
TextColumn::make('price')
    ->label('Preço Unitário')
    ->money('BRL')
    ->sortable(),

TextColumn::make('revenue')
    ->label('Receita Total')
    ->money('BRL')
    ->sortable(),

TextColumn::make('today_revenue')
    ->label('Receita Hoje')
    ->money('BRL')
    ->color('info')
    ->sortable(),
```

Para:
```php
TextColumn::make('price')
    ->label('Preço Unitário')
    ->formatStateUsing(fn ($record) => format_money($record->price))
    ->sortable(),

TextColumn::make('revenue')
    ->label('Receita Total')
    ->formatStateUsing(fn ($record) => $record->revenue ? format_money($record->revenue) : '-')
    ->sortable(),

TextColumn::make('today_revenue')
    ->label('Receita Hoje')
    ->formatStateUsing(fn ($record) => $record->today_revenue ? format_money($record->today_revenue) : '-')
    ->color('info')
    ->sortable(),
```

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Filament/Widgets/TicketTypeReportTable.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Widgets/TicketTypeReportTable.php
git commit -m "refactor: apply pt-BR money formatting to TicketTypeReportTable"
```

---

## Task 5: Atualizar BilheteriaOverview

**Files:**
- Modify: `app/Filament/Bilheteria/Widgets/BilheteriaOverview.php`

- [ ] **Step 1: Modificar stats para usar formatação brasileira**

Modificar:
```php
Stat::make('Receita Total', 'R$ '.number_format($totalRevenue, 2, ',', '.'))
```

Manter como está (já usa number_format com vírgula), mas garantir que usa helper:
```php
Stat::make('Receita Total', format_money($totalRevenue))
```

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Filament/Bilheteria/Widgets/BilheteriaOverview.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Bilheteria/Widgets/BilheteriaOverview.php
git commit -m "refactor: apply pt-BR money formatting to BilheteriaOverview"
```

---

## Task 6: Atualizar TicketSalesTable

**Files:**
- Modify: `app/Filament/Bilheteria/Resources/TicketSales/Tables/TicketSalesTable.php`

- [ ] **Step 1: Modificar coluna de valor**

Modificar:
```php
TextColumn::make('value')
    ->label('Valor')
    ->money('BRL')
    ->sortable()
    ->visibleFrom('md'),
```

Para:
```php
TextColumn::make('value')
    ->label('Valor')
    ->formatStateUsing(fn ($record) => format_money($record->value))
    ->sortable()
    ->visibleFrom('md'),
```

- [ ] **Step 2: Modificar coluna de data**

Modificar:
```php
TextColumn::make('created_at')
    ->label('Data/Hora')
    ->dateTime('d/m/Y H:i')
    ->sortable()
    ->visibleFrom('md'),
```

Para:
```php
TextColumn::make('created_at')
    ->label('Data/Hora')
    ->formatStateUsing(fn ($record) => format_datetime($record->created_at))
    ->sortable()
    ->visibleFrom('md'),
```

- [ ] **Step 3: Verificar sintaxe**

Run: `php -l app/Filament/Bilheteria/Resources/TicketSales/Tables/TicketSalesTable.php`
Expected: No syntax errors

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Bilheteria/Resources/TicketSales/Tables/TicketSalesTable.php
git commit -m "refactor: apply pt-BR formatting to TicketSalesTable"
```

---

## Task 7: Atualizar GuestsTable (Admin)

**Files:**
- Modify: `app/Filament/Resources/Guests/Tables/GuestsTable.php`

- [ ] **Step 1: Modificar colunas de data**

Modificar colunas `checked_in_at` e `created_at` para usar `format_datetime()`.

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Filament/Resources/Guests/Tables/GuestsTable.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Resources/Guests/Tables/GuestsTable.php
git commit -m "refactor: apply pt-BR datetime formatting to Admin GuestsTable"
```

---

## Task 8: Atualizar Validator GuestsTable

**Files:**
- Modify: `app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`

- [ ] **Step 1: Modificar colunas de data**

Modificar colunas de data para usar `format_datetime()`.

- [ ] **Step 2: Verificar sintaxe**

Run: `php -l app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php`
Expected: No syntax errors

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php
git commit -m "refactor: apply pt-BR datetime formatting to Validator GuestsTable"
```

---

## Task 9: Restart e Verificação Final

- [ ] **Step 1: Restart container**

```bash
docker restart guest-list-pro-laravel.test-1
sleep 3
```

- [ ] **Step 2: Verificar aplicação**

Acessar http://localhost:8888/admin e verificar:
- Valores monetários: R$ 1.234,56
- Datas: 17/04/2026 14:30
- Números: 1.234,56

---

## Resumo dos arquivos

| Task | Arquivo | Ação |
|------|---------|------|
| 1 | app/Services/FormatService.php | Criar |
| 2 | app/helpers.php | Criar |
| 2 | bootstrap/app.php | Modificar |
| 3 | app/Filament/Widgets/SectorMetricsTable.php | Modificar |
| 4 | app/Filament/Widgets/TicketTypeReportTable.php | Modificar |
| 5 | app/Filament/Bilheteria/Widgets/BilheteriaOverview.php | Modificar |
| 6 | app/Filament/Bilheteria/Resources/TicketSales/Tables/TicketSalesTable.php | Modificar |
| 7 | app/Filament/Resources/Guests/Tables/GuestsTable.php | Modificar |
| 8 | app/Filament/Validator/Resources/Guests/Tables/GuestsTable.php | Modificar |
