#!/usr/bin/env bash
# First-start setup of the demo, run inside the DDEV web container by the
# post-start hook and inside the demo container (Build/container/entrypoint.sh).
# Every step is skipped when its result exists already, so the script is safe
# to run on each start. The DEMO_* variables default to the DDEV values.
set -euo pipefail

cd /var/www/html
typo3=vendor/bin/typo3
credentials="${DEMO_ADMIN_LOGIN_FILE:-.ddev/admin-credentials.txt}"

if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer packages"
    composer install --no-interaction --no-progress
fi

if [ ! -f config/system/settings.php ]; then
    echo "Installing TYPO3"
    # Letters, digits and a dot only: safe in shells and TYPO3's password policy.
    password="Demo.$(openssl rand -hex 8)"
    $typo3 setup --no-interaction --force \
        --driver=mysqli --host="${DEMO_DB_HOST:-db}" --port="${DEMO_DB_PORT:-3306}" \
        --dbname="${DEMO_DB_NAME:-db}" --username="${DEMO_DB_USER:-db}" --password="${DEMO_DB_PASSWORD:-db}" \
        --admin-username=admin --admin-user-password="$password" --admin-email=admin@example.org \
        --project-name="Content Provenance Demo" --server-type=other
    printf 'User: admin\nPassword: %s\n' "$password" > "$credentials"
    chmod 600 "$credentials"
fi

# Commands of packages added since the last start need a fresh container.
$typo3 cache:flush
$typo3 extension:setup
$typo3 provenance-demo:import
$typo3 provenance-demo:editor

if ! $typo3 provenance:keys:list | grep -q active; then
    $typo3 provenance:keys:generate
fi

$typo3 cache:flush

# Pages are signed when TYPO3 renders them. Render each page of the audit
# listing once, so a verifier finds every page signed right after the start.
site="${DEMO_SITE_URL:-${DDEV_PRIMARY_URL:?Neither DEMO_SITE_URL nor DDEV_PRIMARY_URL is set}}"
site="${site%/}"
curl --fail --silent --insecure "$site/.well-known/content-provenance" \
    | php -r 'foreach (json_decode(stream_get_contents(STDIN))->documents as $document) { echo $document->url, PHP_EOL; }' \
    | while read -r url; do
        curl --fail --silent --insecure --output /dev/null "$url"
    done

# The backend login, so nobody has to look for it after the first start.
password=$(sed -n 's/^Password: //p' "$credentials")
echo "Demo ready: ${site}"
echo "Backend:    ${site}/typo3"
echo "  Editor:   user editor, password Demo-Editor-1"
echo "  Admin:    user admin, password ${password} (also in $credentials)"
