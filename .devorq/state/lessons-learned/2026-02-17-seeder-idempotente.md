# Lição: Seeders Idempotentes

**Data**: 2026-02-17
**Stack**: Laravel + Database Seeding
**Tags**: database|best-practice

## Contexto

Seeders que falham ao rodar mais de uma vez limpam ou duplicam dados, causando inconsistência no banco de desenvolvimento.

**Ambiente**: Docker + Laravel + MySQL
**Frequência**: Alta (a cada `db:seed` ou `migrate:fresh --seed`)
**Impacto**: Alto — dados inconsistentes invalidam testes

## Problema

Seeder tradicional com `Model::create()` em rodadas subsequentes:
- Falha por dados duplicados (unique constraints)
- Ou causa duplicação de registros
- Ou limpa dados de outras tabelas dependentes

## Causa Raiz

`Model::create()` sempre tenta criar, mesmo se registro já existir. Não há verificação de existência prévia.

## Solução

Usar `Model::firstOrCreate()` para operações idempotentes:

```php
// ANTES (não idempotente)
User::create([
    'name' => 'Admin',
    'email' => 'admin@example.com',
]);

// DEPOIS (idempotente)
User::firstOrCreate(
    ['email' => 'admin@example.com'],
    ['name' => 'Admin']
);
```

**Variação para_UPDATE_OR_CREATE:**
```php
// Para dados que podem mudar entre seeders
User::updateOrCreate(
    ['email' => 'admin@example.com'],
    ['name' => 'Admin', 'role' => 'admin']
);
```

## Prevenção

- [ ] Sempre usar `firstOrCreate` ou `updateOrCreate` em seeders
- [ ] Seeders devem poder rodar N vezes sem efeito colateral
- [ ] Testar com `migrate:fresh --seed` antes de considerar pronto

## Referências

- [Laravel Database Seeding](https://laravel.com/docs/11.x/seeding#writing-seeders)
- [Eloquent with firstOrCreate](https://laravel.com/docs/11.x/eloquent#first-or-create)