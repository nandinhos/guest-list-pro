# Lição: Overhead do vendor/bin/sail

**Data**: 2026-02-17
**Stack**: Laravel + Sail + Docker
**Tags**: performance|config

## Contexto

O wrapper Sail (`vendor/bin/sail`) adiciona overhead de inicialização de aproximadamente 30 segundos ou mais para cada comando executado.

**Ambiente**: Docker container Laravel
**Frequência**: Alta (every command)
**Impacto**: Alto — comandos rápidos demoram indevidamente

## Problema

Comandos simples como `./vendor/bin/sail bin pint` ou `./vendor/bin/sail artisan test --compact` executam lentamente por causa do tempo de inicialização do wrapper Sail.

## Causa Raiz

O Sail wrapper precisa:
1. Carregar o script PHP do wrapper
2. Interpretar argumentos
3. Iniciar um novo processo Docker
4. Aguardar container iniciar

Esse overhead existe para cada invocação.

## Solução

Usar `docker compose exec -T` para execução direta no container:

```bash
# ANTES (lento ~30s)
./vendor/bin/sail bin pint --dirty

# DEPOIS (rápido ~1s)
docker compose exec -T laravel.test ./vendor/bin/pint --dirty
```

**Alternativa para sailors:**
```bash
alias sail='vendor/bin/sail'
# Manter compatibilidade, usar docker compose exec -T quando urgente
```

## Prevenção

- [ ] Criar alias no shell: `alias sail='vendor/bin/sail'` (mantém compatibilidade)
- [ ] Para comandos críticos/urgentes, usar `docker compose exec -T`
- [ ] Documentar no README do projeto

## Referências

- [Sail Docs](https://laravelsail.com/)
- Comando original: `docker compose exec -T laravel.test <comando>`