#!/bin/bash
# First-time setup of this Laravel shop on Hostinger shared hosting, run from a cron job
# (the deploy tool there cannot run composer). Safe to run again: it stops once setup.done exists.
#
#   SETUP_DB_NAME=... SETUP_DB_USER=... SETUP_DB_PASSWORD=... SETUP_APP_URL=https://... \
#   SETUP_ADMIN_EMAIL=... /bin/bash deploy/hostinger-first-run.sh
#
# It writes .env (when missing) with a random APP_KEY and a random admin password (ADMIN_PASSWORD in .env),
# installs PHP packages, migrates, seeds the Ghorer Bazar catalogue and builds the caches.
# Progress goes to storage/logs/setup.log.
set -u
APP="$(cd "$(dirname "$0")/.." && pwd)"
cd "$APP" || exit 1
LOG="$APP/storage/logs/setup.log"
[ -f "$APP/storage/logs/setup.done" ] && exit 0
mkdir "$APP/storage/logs/setup.lock" 2>/dev/null || exit 0   # another run is still busy
trap 'rmdir "$APP/storage/logs/setup.lock"' EXIT
exec >>"$LOG" 2>&1
echo "== setup started $(date)"

PHP="${PHP:-$(command -v php)}"
echo "PHP: $PHP ($("$PHP" -r 'echo PHP_VERSION;'))"

if [ ! -f .env ]; then
    : "${SETUP_DB_NAME:?}" "${SETUP_DB_USER:?}" "${SETUP_DB_PASSWORD:?}" "${SETUP_APP_URL:?}" "${SETUP_ADMIN_EMAIL:?}"
    ADMIN_PW="$("$PHP" -r 'echo substr(str_replace(["+","/","="],"",base64_encode(random_bytes(24))),0,16);')"
    APP_KEY="base64:$("$PHP" -r 'echo base64_encode(random_bytes(32));')"
    HOST="$(echo "$SETUP_APP_URL" | sed -E 's#^https?://##; s#/.*$##')"
    umask 077
    cat > .env <<ENV
APP_NAME="Ghorer Bazar"
APP_ENV=production
APP_KEY=$APP_KEY
APP_DEBUG=false
APP_URL=$SETUP_APP_URL
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$SETUP_DB_NAME
DB_USERNAME=$SETUP_DB_USER
DB_PASSWORD=$SETUP_DB_PASSWORD

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
CACHE_STORE=database

MAIL_MAILER=log
MAIL_FROM_ADDRESS="no-reply@$HOST"
MAIL_FROM_NAME="\${APP_NAME}"

TESTING_MODE=false

# First admin login (Admin panel: $SETUP_APP_URL/admin). Change the password after you sign in.
ADMIN_EMAIL=$SETUP_ADMIN_EMAIL
ADMIN_PASSWORD=$ADMIN_PW
SEED_DEMO=false
ENV
    umask 022
    echo ".env written"
fi

# Root .htaccess of the subdomain: send every request to this app's public folder
ROOT="$(dirname "$APP")"
APPDIR="$(basename "$APP")"
if [ ! -f "$ROOT/.htaccess" ]; then
    cat > "$ROOT/.htaccess" <<HT
DirectoryIndex index.php
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/$APPDIR/public/
    RewriteRule ^(.*)\$ $APPDIR/public/\$1 [L]
</IfModule>
HT
    echo "root .htaccess written"
fi

if command -v composer >/dev/null 2>&1; then
    COMPOSER=("$PHP" "$(command -v composer)")
else
    [ -f composer.phar ] || "$PHP" -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"
    COMPOSER=("$PHP" composer.phar)
fi
export COMPOSER_HOME="$APP/storage/composer-home"

step() { echo "-> $*"; "$@" || { echo "FAILED: $*"; exit 1; }; }
step "${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction
step "$PHP" artisan migrate --force --seed
step "$PHP" artisan db:seed --class=GhorerbazarSeeder --force
"$PHP" artisan storage:link || echo "storage:link failed (images in storage/app/public will not show)"
step "$PHP" artisan optimize:clear
step "$PHP" artisan config:cache
step "$PHP" artisan route:cache
step "$PHP" artisan view:cache

touch "$APP/storage/logs/setup.done"
echo "== setup finished $(date)"
