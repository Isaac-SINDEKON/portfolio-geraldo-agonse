# Deploiement Render du backend (API).
# Render ne propose plus de runtime PHP natif : le service est conteneurise.
# Un seul conteneur PHP 8.3 (Laravel 12 exige PHP >= 8.2) qui sert l'API
# via le serveur integre, apres migration et seed de la base.

FROM php:8.3-cli-alpine

WORKDIR /var/www/html

# Extension PostgreSQL (pdo_pgsql) + outils Composer.
# postgresql-libs reste installe apres le nettoyage : pdo_pgsql en depend au
# chargement (sans elle, l'extension ne se charge plus et le service plante).
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev \
    && docker-php-ext-install pdo_pgsql \
    && apk del .build-deps \
    && apk add --no-cache postgresql-libs git unzip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Contexte de build = dossier backend/ (Root Directory sur Render).
COPY . .

RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader \
    && composer clear-cache

RUN chmod -R 777 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint"]
