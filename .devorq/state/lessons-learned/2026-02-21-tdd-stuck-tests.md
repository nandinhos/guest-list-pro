# Lição: TDD e Testes Travados no Docker

**Data**: 2026-02-21
**Stack**: Laravel + PHPUnit + Docker
**Tags**: testing|tdd|docker

## Contexto

Testes com `RefreshDatabase` trait travam no container Docker sem output, dificultando debug e causando perda de tempo.

**Ambiente**: Docker container Laravel
**Frequência**: Média (testes com banco)
**Impacto**: Alto — tempo desperdiçado em testes travados

## Problema

Ao rodar `php artisan test` no container:
- Teste com `RefreshDatabase` trava silenciosamente
- Sem output por >30 segundos
- Parece que está rodando mas não termina

## Causa Raiz

`RefreshDatabase` recria banco em cada test. No Docker:
- Volume lento para migrations
- Memória insuficiente
- Conflito de conexão

## Mitigação

**1. Rodar testes focados com `--filter`:**
```bash
# ANTES (todos os testes)
docker compose exec -T laravel.test php artisan test

# DEPOIS (teste específico)
docker compose exec -T laravel.test php artisan test --filter=MyTestClass
```

**2. Usar verificação manual com UserSeeder:**
```php
// Para validações rápidas de fluxo de tela,
// usar dados existentes do UserSeeder
$admin = User::where('email', 'admin@guestlist.pro')->first();
```

**3. Monitorar processos demorados:**
```bash
# Ver processos ativos
docker compose exec -T laravel.test ps aux | grep php

# Ver logs em tempo real
docker compose logs -f laravel.test
```

## Prevenção

- [ ] Sempre usar `--filter` para testar classe específica
- [ ] Usar `RefreshDatabase` apenas quando necessário
- [ ] Manter factories leves
- [ ] Verificar memória disponível no container

## Referências

- [Laravel Testing](https://laravel.com/docs/11.x/testing)
- [RefreshDatabase Trait](https://laravel.com/docs/11.x/testing#migrations)