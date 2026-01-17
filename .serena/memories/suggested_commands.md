# Comandos Sugeridos

## Ambiente Docker (Sail)
- Iniciar containers: `./vendor/bin/sail up -d`
- Parar containers: `./vendor/bin/sail stop`
- Executar comandos artisan: `./vendor/bin/sail artisan [comando]`
- Executar comandos composer: `./vendor/bin/sail composer [comando]`
- Executar comandos npm: `./vendor/bin/sail npm [comando]`

## Testes e Qualidade
- Executar testes: `./vendor/bin/sail artisan test`
- Limpar cache: `./vendor/bin/sail artisan optimize:clear`
- Pint (Linting): `./vendor/bin/sail ./vendor/bin/pint`

## Controle de Versão (Git)
- Status: `git status`
- Commit: `git commit -m "tipo: mensagem em português"`
