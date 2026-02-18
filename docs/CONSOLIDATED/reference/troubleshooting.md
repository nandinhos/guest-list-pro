# Troubleshooting - Problemas Comuns

> ⚠️ **IMPORTANTE**: Use SEMPRE `vendor/bin/sail artisan` ou `sail artisan` neste projeto!

---

## Problemas de Build/Asset

### Assets não compilam
```bash
# Limpar cache
vendor/bin/sail artisan config:clear
vendor/bin/sail artisan cache:clear

# Rebuild assets
vendor/bin/sail npm run build
```

### Tema Filament não carrega
```bash
vendor/bin/sail artisan filament:clear-cached-components
vendor/bin/sail artisan view:clear
```

---

## Problemas de Banco

### Migration falha
```bash
# Ver status
vendor/bin/sail artisan migrate:status

# Rollback e remigrate
vendor/bin/sail artisan migrate:fresh
vendor/bin/sail artisan migrate:fresh --seed
```

### Dados de teste Needed
```bash
# Seed
vendor/bin/sail artisan db:seed
vendor/bin/sail artisan migrate:fresh --seed
```

---

## Problemas de Autenticação

### Login não funciona
- Verificar .env: `APP_URL` está correto
- Limpar cache de sessão: `vendor/bin/sail artisan session:table`

### Redirect loop
- Verificar middleware `EnsureEventSelected`
- Verificar rotas em `routes/web.php`

---

## Problemas de Performance

### Queries lentas
- Verificar N+1 com debugbar
- Adicionar índices em migrations
- Usar eager loading: `$query->with(['relation'])`

### Widgets lentos
- Adicionar cache: `Cache::remember()`
- Adicionar polling interval: `protected static ?string $pollingInterval = '30s';`

---

## Problemas de Filament

### Componentes não aparecem
```bash
vendor/bin/sail artisan filament:clear-cached-components
vendor/bin/sail artisan cache:clear
```

### Forms não salvam
- Verificar `form()` method no Resource
- Verificar validation rules

### Tables não carregam
- Verificar `table()` method
- Verificar relations estão definidas

---

## Problemas de Testes

### Testes não rodam
```bash
# Verificar phpunit.xml
vendor/bin/sail artisan test --filter=TestName

# Com coverage
vendor/bin/sail artisan test --coverage
```

### Factory não funciona
- Verificar `DatabaseMigrations` ou `DatabaseTransactions` trait
- Verificar se seeders existem

---

## Problemas de Git

### Conflitos em merge
- Usar `git pull --rebase`
- Resolver conflitos manualmente
- Testar antes de commit

### Commits perdidos
```bash
# Ver reflog
git reflog
# Restaurar
git checkout HEAD@{n}
```

---

## Problemas de Docker/Sail

### Container não inicia
```bash
# Rebuild
vendor/bin/sail down -v
vendor/bin/sail up -d

# Ver logs
vendor/bin/sail logs -f
```

### Banco não conecta
- Verificar .env
- Verificar porta do MySQL
- Resetar banco: `sail mysql -u root -e "DROP DATABASE IF EXISTS homestead;"`

---

**Última atualização:** 2026-02-18
