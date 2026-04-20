# Lição: Database Performance — Índices e Integridade

**Data**: 2026-02-17
**Stack**: MySQL + Laravel + Eloquent
**Tags**: database|performance

## Contexto

Consultas lentas em tabelas de alto volume (Convidados, Vendas) podem causar timeouts e用户体验 degradado em produção.

**Ambiente**: MySQL em container Docker
**Frequência**: Contínua (tabelas grandes)
**Impacto**: Alto — queries lentas afetam toda a aplicação

## Problema

Sem índices apropriados, queries com `WHERE`, `ORDER BY` e `JOIN` em tabelas grandes (>10k registros) ficam extremamente lentas.

## Causa Raiz

Índices faltando em campos frequentemente usados em:
- Filtros (WHERE status = ?)
- Ordenação (ORDER BY created_at)
- Busca (LIKE %termo%)
- Relacionamentos (JOIN on user_id)

## Solução

**1. Identificar queries lentas:**
```sql
-- Ver queries sem índice
EXPLAIN SELECT * FROM guests WHERE sector_id = 5;

-- Index recommendations
SHOW INDEX FROM guests;
```

**2. Adicionar índices específicos:**
```php
// Migration
Schema::table('guests', function (Blueprint $table) {
    // Para buscas por documento
    $table->index(['document_normalized'], 'idx_guest_document');

    // Para filtros por setor e status
    $table->index(['sector_id', 'status'], 'idx_guest_sector_status');

    // Para ordenação por data
    $table->index(['created_at'], 'idx_guest_created');
});
```

**3. Usar `document_normalized` para chaves únicas:**

Evita duplicidade por formatação (pontos, traços):
```php
// Normalizar documento antes de salvar
$guest->document_normalized = preg_replace('/[^0-9]/', '', $document);
```

## Prevenção

- [ ] Adicionar índices para campos em WHERE/ORDER/JOIN
- [ ] Usar `EXPLAIN` para verificar queries
- [ ] Normalizar documentos para busca única
- [ ] Monitorar slow queries em produção

## Referências

- [Laravel Migrations - Indexes](https://laravel.com/docs/11.x/migrations#indexes)
- [MySQL EXPLAIN](https://dev.mysql.com/doc/refman/8.0/en/explain.html)