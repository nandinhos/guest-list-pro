# Lição: Pint — Formatação Automática de Código

**Data**: 2026-02-17
**Stack**: Laravel + Pint (PHP Code Style Fixer)
**Tags**: code-quality|tooling|laravel

## Contexto

Código inconsistente ( indentation, line endings, imports) causa ruído em PRs e dificulta code review.

**Ambiente**: Projeto Laravel
**Frequência**: Contínua (a cada commit)
**Impacto**: Médio — PRs com changes desnecessárias

## Problema

Cada desenvolvedor formata código de forma diferente:
- Tabs vs Spaces
- Imports em ordem incorreta
- Linhas em branco extras
- Colchetes mal alinhados

## Solução

Usar **Pint** (PHP Code Style Fixer baseado em PSR-12):

```bash
# Formatar todo projeto
./vendor/bin/pint

# Formatar apenas arquivos modificados (dirty)
./vendor/bin/pint --dirty

# Preview sem aplicar
./vendor/bin/pint --test
```

**Alias útil no shell:**
```bash
alias pint='./vendor/bin/pint'
pint --dirty  # rápido
```

**Configuração (pint.json):**
```json
{
    "preset": "laravel",
    "rules": {
        "not_operator_with_space": true
    }
}
```

## Prevenção

- [ ] Rodar `pint --dirty` antes de cada commit
- [ ] Configurar pre-commit hook para validar
- [ ] Não commitar arquivos com formatação diferente

## Referências

- [Laravel Pint](https://laravel.com/docs/11.x/pint)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)