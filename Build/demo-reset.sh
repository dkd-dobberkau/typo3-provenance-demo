#!/usr/bin/env bash
# Puts the demo container back to its saved state: database and fileadmin.
# The entrypoint saves the state after each setup (--save) and restores it at
# every full hour, so whatever visitors change in the backend is gone then.
# The site key in var/provenance-keys stays, so signatures keep verifying.
set -euo pipefail

cd /var/www/html
# shellcheck source=Build/container/database.sh
. Build/container/database.sh
state=var/demo

if [ "${1:-}" = --save ]; then
    mariadb-dump "${database_options[@]}" --single-transaction --add-drop-table "$database" > "$state/database.sql.new"
    tar -C public -czf "$state/fileadmin.tar.gz.new" fileadmin
    mv "$state/database.sql.new" "$state/database.sql"
    mv "$state/fileadmin.tar.gz.new" "$state/fileadmin.tar.gz"
    echo "Saved the demo state"
    exit 0
fi

mariadb "${database_options[@]}" "$database" < "$state/database.sql"
find public/fileadmin -mindepth 1 -delete
tar -C public -xzf "$state/fileadmin.tar.gz"
vendor/bin/typo3 cache:flush
echo "Reset the demo to its saved state"
