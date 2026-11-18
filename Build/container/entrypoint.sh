#!/usr/bin/env bash
# Start of the demo container: Apache serves in the foreground, beside it the
# setup runs and then the hourly reset (Build/demo-reset.sh).
set -euo pipefail

cd /var/www/html
: "${DEMO_SITE_URL:?DEMO_SITE_URL is not set}"
# shellcheck source=Build/container/database.sh
. Build/container/database.sh
mkdir -p var/demo

prepare() {
    until mariadb-admin "${database_options[@]}" --silent ping > /dev/null 2>&1; do
        echo "Waiting for the database at ${DEMO_DB_HOST}"
        sleep 2
    done
    # Each container starts from the state the last one saved; the setup then
    # adds what a new image brings, and the result is the new saved state.
    # It runs inside "if", where bash ignores set -e: pass errors on by hand.
    if [ -f var/demo/database.sql ]; then
        Build/demo-reset.sh || return 1
    fi
    Build/demo-setup.sh || return 1
    Build/demo-reset.sh --save
}

reset_every_hour() {
    while true; do
        sleep $((3600 - $(date +%s) % 3600))
        Build/demo-reset.sh || echo "The hourly reset failed" >&2
    done
}

# A failed setup stops the container, so it shows up instead of serving a
# half-installed site.
if prepare; then reset_every_hour; else kill -TERM 1; fi &

exec apache2-foreground
