# shellcheck shell=bash disable=SC2034
# Connection options for the mariadb tools, sourced by the container scripts.
# Same variables as TYPO3 reads in config/system/additional.php. The database
# runs beside the demo without TLS; the client would insist on it otherwise.
export MYSQL_PWD="${DEMO_DB_PASSWORD:?DEMO_DB_PASSWORD is not set}"
database="${DEMO_DB_NAME:?DEMO_DB_NAME is not set}"
database_options=(
    --host="${DEMO_DB_HOST:?DEMO_DB_HOST is not set}"
    --port="${DEMO_DB_PORT:-3306}"
    --user="${DEMO_DB_USER:?DEMO_DB_USER is not set}"
    --skip-ssl
)
