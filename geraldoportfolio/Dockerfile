# Deploiement de l'application (site public, administration et API) en conteneur.
# Utilisable chez n'importe quel hebergeur compatible Docker.
# Un seul conteneur PHP 8.3 (Laravel 12 exige PHP >= 8.2) qui sert toute
# l'application via le serveur integre, apres migration et seed de la base.

# ---------------------------------------------------------------------------
# Etape 1 : compilation des assets (Vite + Tailwind).
# Les fichiers compiles (public/build) ne sont pas versionnes : ils doivent
# etre produits ici, sinon les vues @vite echouent en production.
# ---------------------------------------------------------------------------
FROM node:20-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

# ---------------------------------------------------------------------------
# Etape 2 : application PHP.
# ---------------------------------------------------------------------------
FROM php:8.3-cli-alpine

WORKDIR /var/www/html

# Extensions PDO (MySQL, PostgreSQL, SQLite) + outils Composer.
# postgresql-libs reste installe apres le nettoyage : pdo_pgsql en depend au
# chargement (sans elle, l'extension ne se charge plus et le service plante).
# pdo_mysql et pdo_sqlite couvrent les autres bases sans dependance externe.
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS postgresql-dev \
    && docker-php-ext-install pdo_pgsql pdo_mysql pdo_sqlite \
    && apk del .build-deps \
    && apk add --no-cache postgresql-libs git unzip

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Contexte de build = dossier geraldoportfolio/ (a indiquer comme dossier racine
# du service chez l'hebergeur).
COPY . .

# Assets compiles a l'etape 1.
COPY --from=assets /app/public/build ./public/build

RUN composer install --no-interaction --no-dev --prefer-dist --optimize-autoloader \
    && composer clear-cache

RUN chmod -R 777 storage bootstrap/cache

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

EXPOSE 8080

ENTRYPOINT ["docker-entrypoint"]
