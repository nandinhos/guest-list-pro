# Guia de Deploy - Guest List Pro (VPS)

Este guia descreve como configurar seu servidor VPS (Ubuntu/Debian recomendado) e utilizar o script de deploy automatizado.

## Pré-requisitos do Servidor

Certifique-se de que seu VPS possui os seguintes softwares instalados (LEMP Stack):

1.  **PHP 8.2+** (com extensões: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml)
2.  **Composer** (Gerenciador de dependências PHP)
3.  **Node.js & NPM** (Para build do frontend - Recomendado Node 20+)
4.  **MySQL 8.0+** (Banco de dados)
5.  **Nginx** (Servidor Web)
6.  **Git**

## Configuração Inicial

1.  **Clone o Repositório**:
    ```bash
    cd /var/www
    git clone https://github.com/nandinhos/guest-list-pro.git guest-list-pro
    cd guest-list-pro
    ```

2.  **Configurar Arquivo .env**:
    Copie o exemplo e edite com suas credenciais de produção:
    ```bash
    cp .env.example .env
    nano .env
    ```
    *Ajuste:* `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://seudominio.com`, e credenciais de Banco de Dados.

3.  **Permissões Iniciais**:
    Dê permissão de execução ao script de deploy:
    ```bash
    chmod +x deploy.sh
    ```

## Executando o Deploy

Para realizar o deploy (inicial ou atualizações futuras), basta rodar:

```bash
./deploy.sh
```

O script irá automaticamente:
- Baixar o código mais recente (`git pull`)
- Instalar dependências PHP otimizadas (`composer install --no-dev`)
- Compilar o frontend (`npm run build`)
- Atualizar o banco de dados (`migrate`)
- Otimizar caches (`config`, `route`, `view`)
- Ajustar permissões

## Configuração do Nginx (Exemplo)

Crie um bloco de servidor em `/etc/nginx/sites-available/guest-list-pro`:

```nginx
server {
    listen 80;
    server_name seudominio.com;
    root /var/www/guest-list-pro/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock; # Verifique sua versão do PHP
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Filas (Supervisor)

Para processar tarefas em segundo plano, instale o Supervisor e crie uma configuração em `/etc/supervisor/conf.d/guest-list-pro-worker.conf`:

```ini
[program:guest-list-pro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/guest-list-pro/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=seusuario
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/guest-list-pro/storage/logs/worker.log
```
