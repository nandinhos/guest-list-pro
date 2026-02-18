# Comandos Úteis

> ⚠️ **IMPORTANTE**: Este projeto usa Docker/Sail. Use SEMPRE `vendor/bin/sail` ou `sail` para comandos!

---

## Artisan

### Servidor
```bash
# Com Sail (use sempre!)
vendor/bin/sail up -d
vendor/bin/sail down
sail up -d
sail down

# Logs
vendor/bin/sail logs -f
sail logs -f
```

### Cache
```bash
# Use vendor/bin/sail ou sail
vendor/bin/sail artisan config:clear
vendor/bin/sail artisan cache:clear
vendor/bin/sail artisan route:clear
vendor/bin/sail artisan view:clear

# Cachear
vendor/bin/sail artisan config:cache
vendor/bin/sail artisan route:cache
```

### Database
```bash
vendor/bin/sail artisan migrate
vendor/bin/sail artisan migrate:fresh
vendor/bin/sail artisan migrate:fresh --seed
vendor/bin/sail artisan migrate:rollback
vendor/bin/sail artisan migrate:status

# Seed
vendor/bin/sail artisan db:seed
vendor/bin/sail artisan db:wipe
```

### Filament
```bash
vendor/bin/sail artisan filament:clear-cached-components
vendor/bin/sail artisan make:filament-resource Guest --generate
vendor/bin/sail artisan make:filament-page Settings
vendor/bin/sail artisan make:filament-widget StatsOverview
```

### Testing
```bash
vendor/bin/sail artisan test
vendor/bin/sail artisan test --filter=TestName
vendor/bin/sail artisan test --coverage
vendor/bin/sail artisan test --parallel
```

### Queue
```bash
vendor/bin/sail artisan queue:work
vendor/bin/sail artisan queue:listen
vendor/bin/sail artisan queue:restart
vendor/bin/sail artisan queue:failed
```

---

## NPM

### Build
```bash
vendor/bin/sail npm run dev
vendor/bin/sail npm run build
vendor/bin/sail npm run lint
vendor/bin/sail npm run typecheck
```

---

## Git

### Branch
```bash
git checkout -b feature/nome
git checkout -b hotfix/nome

# Listar branches
git branch -a
git branch -vv
```

### Commit
```bash
git add .
git commit -m "tipo(escopo): descricao"

# Amend (apenas se não push)
git commit --amend
```

### Merge
```bash
git pull --rebase origin main
git merge feature/nome
git push origin main
```

### Log
```bash
git log --oneline -10
git log --graph --oneline
git diff HEAD~1
```

---

## PHP

### Lint
```bash
vendor/bin/sail bin pint
vendor/bin/sail bin phpstan analyse
vendor/bin/sail bin phpunit
```

### Code Style
```bash
vendor/bin/sail bin pint --fix
```

---

## Docker/Sail

### Containers
```bash
# Listar
docker ps
vendor/bin/sail ps
sail ps

# Parar/Iniciar
vendor/bin/sail stop
vendor/bin/sail start

# Rebuild
vendor/bin/sail down -v
vendor/bin/sail up -d --build
```

### MySQL
```bash
# Acessar
vendor/bin/sail mysql

# Importar
vendor/bin/sail mysql < backup.sql

# Exportar
vendor/bin/sail mysqldump database > backup.sql
```

---

## Dica: Alias

Adicione ao seu `~/.bashrc` ou `~/.zshrc`:
```bash
alias sail='vendor/bin/sail'
```

---

**Última atualização:** 2026-02-18
