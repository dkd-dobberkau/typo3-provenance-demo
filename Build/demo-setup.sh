#!/usr/bin/env bash
# First-start setup of the demo, run inside the DDEV web container by the
# post-start hook. Every step is skipped when its result exists already, so
# the script is safe to run on each "ddev start".
set -euo pipefail

cd /var/www/html
typo3=vendor/bin/typo3
credentials=.ddev/admin-credentials.txt

if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer packages"
    composer install --no-interaction --no-progress
fi

if [ ! -f config/system/settings.php ]; then
    echo "Installing TYPO3"
    # Letters, digits and a dot only: safe in shells and TYPO3's password policy.
    password="Demo.$(openssl rand -hex 8)"
    $typo3 setup --no-interaction --force \
        --driver=mysqli --host=db --port=3306 --dbname=db --username=db --password=db \
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
site="${DDEV_PRIMARY_URL:?DDEV_PRIMARY_URL is not set}"
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
