#!/bin/bash

# Gerar a chave da aplicação se não existir
if [ ! -f .env ]; then
    cp .env.example .env
fi
php artisan key:generate --ansi

# Executar migrações
php artisan migrate --force

# Ajustar permissões para os diretórios storage e bootstrap/cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Iniciar PHP-FPM
exec php-fpm