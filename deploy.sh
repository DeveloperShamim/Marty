#!/usr/bin/env bash
# Update the live site with the latest code from GitHub.
# Run on the server from the project folder:   bash deploy.sh
# Uses PHP from $PHP if set (e.g. PHP=/opt/cpanel/ea-php83/root/usr/bin/php bash deploy.sh).
set -euo pipefail

cd "$(dirname "$0")"
PHP="${PHP:-php}"
BRANCH="${BRANCH:-VantBangladesh}"

if command -v composer >/dev/null 2>&1; then
    COMPOSER=(composer)
else
    [ -f composer.phar ] || curl -sS https://getcomposer.org/installer | "$PHP"
    COMPOSER=("$PHP" composer.phar)
fi

echo "→ Maintenance mode on"
"$PHP" artisan down --retry=15 || true

echo "→ Pulling $BRANCH"
git pull --ff-only origin "$BRANCH"

echo "→ Installing PHP packages"
"${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction

echo "→ Database changes"
"$PHP" artisan migrate --force

echo "→ Caches"
"$PHP" artisan optimize:clear
"$PHP" artisan config:cache
"$PHP" artisan route:cache
"$PHP" artisan view:cache

echo "→ Maintenance mode off"
"$PHP" artisan up

echo "✓ Deployed $(git log -1 --format='%h %s')"
