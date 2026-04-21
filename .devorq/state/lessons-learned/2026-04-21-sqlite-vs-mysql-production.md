# LL-025: SQLite vs MySQL em Produção — guest-list-pro

**Data:** 2026-04-21
**Projeto:** guest-list-pro
**Tags:** `production`, `database`, `sqlite`, `mysql`

---

## Problema

Em produção, os gráficos do dashboard não apareciam e o login não persistia (voltava para landing page após login).

**Erro no log:**
```
SQLSTATE[HY000]: General error: 1 no such function: HOUR
```

**Causa:** Produção usava **SQLite** como banco de dados, que não suporta a função `HOUR()` do MySQL.

---

## Análise Comparativa

### SQLite — Limitações

| Função | MySQL | SQLite |
|--------|-------|--------|
| `HOUR(datetime)` | ✅ | ❌ (usar `strftime('%H', datetime)`) |
| `DAY(datetime)` | ✅ | ❌ (usar `strftime('%d', datetime)`) |
| `MONTH(datetime)` | ✅ | ❌ (usar `strftime('%m', datetime)`) |
| `YEAR(datetime)` | ✅ | ❌ (usar `strftime('%Y', datetime)`) |
| `FULLTEXT indexes` | ✅ | ❌ |
| `JSON functions` | ✅ | Limited |
| `Window functions` | ✅ | ❌ (desde 3.25.0) |
| `CTEs (WITH)` | ✅ | ✅ (3.8.3+) |

### MySQL — Vantagens

1. **Funções de data/hora completas** — `HOUR()`, `DAY()`, `MONTH()`, `YEAR()`, `DATE_FORMAT()`, etc.
2. **Performance em queries complexas** — Melhor optimizer
3. **Concorrência** — Suporta múltiplas conexões simultâneas
4. **Replication** — Suporta master-slave
5. **Full-text search** — native support
6. **Stored procedures/triggers** — mais features

### Performance Comparativa (teórico)

| Operação | SQLite | MySQL |
|----------|--------|-------|
| Leituras simples | ⚡⚡⚡⚡⚡ | ⚡⚡⚡ |
| Escritas concorrentes | ⚡ | ⚡⚡⚡⚡⚡ |
| Queries agregadas | ⚡⚡ | ⚡⚡⚡⚡ |
| Queries com JOINs | ⚡⚡ | ⚡⚡⚡⚡ |
| Queries com functions SQL | ❌ | ⚡⚡⚡⚡ |

---

## Widgets Afetados

O projeto usa `HOUR()` em 3 widgets:

### 1. SalesTimelineChart
```php
// MySQL:
->selectRaw('HOUR(created_at) as hour, COUNT(*) as total')

// SQLite:
->selectRaw("strftime('%H', created_at) as hour, COUNT(*) as total")
```

### 2. CheckinFlowChart
```php
// MySQL:
->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')

// SQLite:
->selectRaw("strftime('%H', checked_in_at) as hour, count(*) as count")
```

### 3. AdminOverview (getCheckinTrend e getSalesTrend)
```php
// MySQL:
->selectRaw('HOUR(checked_in_at) as hour, count(*) as count')

// SQLite:
->selectRaw("strftime('%H', checked_in_at) as hour, count(*) as count")
```

---

## Solução Implementada (Workaround temporário)

Foi adicionada detecção automática do banco:

```php
$isSqlite = DB::connection()->getDriverName() === 'sqlite';
$hourExpr = $isSqlite ? "strftime('%H', created_at)" : 'HOUR(created_at)';

->selectRaw("{$hourExpr} as hour, COUNT(*) as total")
```

**Commit:** `e032c75`

---

## Recomendação Final

**Para produção: Usar MySQL** (ou PostgreSQL)

### Configuração Recomendada

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=guestlist_pro
DB_USERNAME=guestlist_user
DB_PASSWORD=(senha forte)

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

###顺德

Se usar Redis junto com MySQL:
- Session persistence funciona melhor
- Cache mais rápido
- Queue mais confiável

---

## Quando Usar SQLite

| Caso | SQLite OK? |
|------|-----------|
| Desenvolvimento local | ✅ |
| Aplicação simples (poucos users) | ✅ |
| Staging com apenas 1 dyno | ✅ |
| Microserviços com dados isolados | ✅ |
| **Produção multi-usuário** | ❌ Não recomendado |
| **Aplicação com gráficos/analytics** | ❌ Não recomendado |
| **Alta concorrência** | ❌ Não recomendado |

---

## Checklist para Deploy

- [ ] Verificar se `.env`指向 MySQL (não SQLite)
- [ ] Verificar se migrations foram executadas
- [ ] Verificar se Redis está acessível (se usado)
- [ ] Testar login após deploy
- [ ] Verificar gráficos do dashboard

---

## Tags

`production`, `database`, `sqlite`, `mysql`, `performance`, `laravel`

---

*Criado em: 2026-04-21*
*Commit do fix: e032c75*