# Deploiement Render du frontend (pages).
# Un conteneur PHP 8.3 qui sert les pages. Il consomme l'API du backend via
# API_URL. Sessions, cache et file d'attente sont sur fichiers : aucune base
# de donnees cote frontend. Les assets compiles sont dans public/build.

FROM php:8.3-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache git unzip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Contexte de build = dossier frontend/ (Root Directory sur Render).
COPY . .

RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader \
    && composer clear-cache

RUN chmod -R 777 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint"]
