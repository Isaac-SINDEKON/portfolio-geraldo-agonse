#!/usr/bin/env sh
set -e

cd /var/www/html

# L'image ne contient aucun .env : les defauts ci-dessous doivent donc etre
# sûrs pour la production (sinon APP_DEBUG=true affiche le code en erreur).
export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
# Sans .env, config('app.name') vaudrait "Laravel" : en-tete, titres et mails
# porteraient donc le nom du framework.
export APP_NAME="${APP_NAME:-Portfolio Geraldo Perridys AGONSE}"

# Reconnait les conventions courantes des hebergeurs (aucune n'est imposee) :
#   - APP_URL      : adresse publique du site (liens, images, mails). A definir
#                    chez l'hebergeur, sinon Laravel utilise config('app.url').
#   - DATABASE_URL : chaine de connexion complete, fournie par beaucoup de
#                    plateformes ; on la traduit en DB_URL pour Laravel.
if [ -z "$DB_URL" ] && [ -n "$DATABASE_URL" ]; then
    DB_URL="$DATABASE_URL"
    export DB_URL
fi

# Type de base : DB_CONNECTION explicite d'abord, sinon le schema de DB_URL,
# sinon MySQL des qu'un hote est fourni, sinon SQLite (perdue au redemarrage).
if [ -z "$DB_CONNECTION" ]; then
    case "$DB_URL" in
        postgres://*|postgresql://*) DB_CONNECTION="pgsql" ;;
        mysql://*|mariadb://*)       DB_CONNECTION="mysql" ;;
        sqlite://*|sqlite:*)         DB_CONNECTION="sqlite" ;;
        "")
            if [ -n "$DB_HOST" ]; then
                DB_CONNECTION="mysql"
            else
                DB_CONNECTION="sqlite"
            fi
            ;;
        *) DB_CONNECTION="mysql" ;;
    esac
    export DB_CONNECTION
fi

if [ "$DB_CONNECTION" = "sqlite" ]; then
    mkdir -p database
    [ -f database/database.sqlite ] || touch database/database.sqlite
fi

# Sans .env, la cle d'encryption vient de l'environnement (APP_KEY, a definir
# chez l'hebergeur pour conserver les sessions) ou est generee a chaque
# demarrage.
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
    export APP_KEY
fi

# Repertoires que l'ignore du depot empeche de versionner mais que Laravel exige.
mkdir -p storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache

export PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-4}"

php artisan config:cache
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction
php artisan storage:link

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
