<?php

# HANDOFF - Bug 404 em ExcursaoResource e MonitorResource

## Project: guest-list-pro
- Stack: Laravel 12 + Filament 4 + Livewire 3
- Docker: Sail (php artisan via vendor/bin/sail)
- Branch: main

## Status: BLOQUEADO

### Bug Principal
Rotas /admin/excursoes e /admin/monitores retornam HTTP 404 via browser mas funcionam via CLI.

## O que foi investigado (DESCARTADO)

| Hipotese | Teste | Resultado |
|----------|-------|-----------|
| RelationManager causa erro | Removido temporariamente | 404 persiste |
| getEloquentQuery customizado | Simplificado | 404 persiste |
| Middleware de autenticacao | Testado com cookies admin | 404 persiste |
| VeiculosRelationManager | Removido | 404 persiste |

## O que funciona

- admin/events -> 302 redirect para login (recurso em App Filament Resources Events)
- admin/excursionistas -> funciona (recurso em App Filament Resources Excursionistas)
- Rotas aparecem em php artisan route:list corretamente
- Classe PHP carrega sem erro via CLI
- Autoload funciona (composer dump-autoload executado)

## Estrutura dos resources

app/Filament/
  Admin/Resources/
    Excursao/
      ExcursaoResource.php
      Pages/
        ListExcursoes.php
        CreateExcursao.php
        EditExcursao.php
      RelationManagers/
        VeiculosRelationManager.php
    Monitor/
      MonitorResource.php
      Pages/
        ListMonitores.php
      RelationManagers/
        MonitoresVeiculosRelationManager.php
  Resources/
    Events/ (FUNCIONA)
    Excursionistas/ (FUNCIONA)

## Discovery no AdminPanelProvider

->discoverResources(in: app_path(Filament/Admin/Resources), for: App Filament Admin Resources)
->discoverResources(in: app_path(Filament/Resources), for: App Filament Resources)

Recursos em Admin/Resources nao funcionam via HTTP. Recursos em Resources/ funcionam.

## Possiveis causas raiz

1. PHP autoload cache: CLI e HTTP podem ter caches diferentes
2. Class loading order: discovery de Admin/Resources pode estar rodando antes de outras inicializacoes
3. Container de rotas: rotas de Admin/Resources podem estar sendo sobrescritas
4. Docker file watcher: pode nao estar recarregando classes PHP corretamente

## Proximos passos sugeridos

1. Reiniciar completamente o container Docker
2. Testar com resource minimal (só getPages() retornando array vazio)
3. Comparar headers HTTP entre admin/events e admin/excursoes
4. Verificar se há algo diferente no namespace App Filament Admin Resources vs App Filament Resources

## Comandos uteis

# Reiniciar container
docker-compose down && docker-compose up -d

# Testar rota
curl -s -o /dev/null -w "%{http_code}" http://localhost:8888/admin/excursoes

# Ver rotas
vendor/bin/sail artisan route:list --path=admin

# Limpar cache
vendor/bin/sail artisan route:clear
composer dump-autoload -o

## Git Log (ultimos commits)
51ea164 feat(reports): mobile-first responsive table with cards layout for promoters
1b0108b feat(reports): refactor GuestsReport to single-row-per-promoter format with check-in percentage by sector
d0ad9e1 fix: update document type enum, guest observer, import service and add docs
aa63129 fix (deploy): cria diretórios storage/app/private e backups no deploy e corrige mismatch de modalAction no blade de backups
95b8dad fix(resetDatabase): simplify modal UX with processing feedback
bc62547 feat(admin): SPEC-0016 reset database step-by-step modal
a5a95eb fix(resetDatabase): separate migrate:fresh and db:seed calls
1e70ecd fix(modal): wrap x-if icons in template tags for Alpine.js
50cbec1 feat(admin): SPEC-0015 reset database button in BackupManagement
9c1d92c feat(import): SPEC-0014 auto-import event from .md header


---
Handoff criado: 2026-04-25T11:22:51.221Z