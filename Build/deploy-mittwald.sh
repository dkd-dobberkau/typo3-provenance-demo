#!/usr/bin/env bash
# Deploys an image of the demo to the mittwald container stack behind
# demo.cms-provenance.org. CI builds and pushes the image for every commit on
# main that passes; this script only points the stack at it.
#
# Usage:
#   Build/deploy-mittwald.sh          # the image of the current commit
#   Build/deploy-mittwald.sh <tag>    # any image tag on ghcr.io, e.g. latest
#
# Requires: mw (logged in), curl, python3 and .deploy.env (see .deploy.env.example).
set -euo pipefail

cd "$(dirname "$0")/.."

# The stack ID and the database password stay out of the public repository.
if [ ! -f .deploy.env ]; then
    echo "ERROR: .deploy.env not found. Copy .deploy.env.example and fill it in." >&2
    exit 1
fi
set -a
# shellcheck source=/dev/null
. .deploy.env
set +a
: "${PROJECT_ID:?PROJECT_ID missing from .deploy.env}"
: "${STACK_ID:?STACK_ID missing from .deploy.env}"
: "${DEMO_SITE_URL:?DEMO_SITE_URL missing from .deploy.env}"
: "${DEMO_DB_PASSWORD:?DEMO_DB_PASSWORD missing from .deploy.env}"

tag="${1:-$(git rev-parse HEAD)}"
site="${DEMO_SITE_URL%/}"

echo "Deploying ghcr.io/dkd-dobberkau/typo3-provenance-demo:${tag}"
env_file="$(mktemp -t deploy.env.XXXX)"
trap 'rm -f "$env_file"' EXIT
{
    echo "VERSION=${tag}"
    cat .deploy.env
} > "$env_file"
mw stack deploy --stack-id "$STACK_ID" -c docker-compose.mittwald.yml --env-file "$env_file" --force

live_version() {
    curl --fail --silent --max-time 5 "${site}/demo-version.txt" 2>/dev/null || true
}

wait_for_version() {
    for _ in $(seq 1 60); do
        [ "$(live_version)" = "$1" ] && return 0
        sleep 5
    done
    return 1
}

web_container() {
    mw container list --project-id "$PROJECT_ID" --output json \
        | python3 -c 'import json, sys; print(next(c["shortId"] for c in json.load(sys.stdin) if c["serviceName"] == "web"))'
}

# A mutable tag such as latest does not always make mittwald pull the new
# image. What the site serves is the truth, so recreate if it lags behind.
if [ "$tag" != latest ]; then
    echo "Waiting for ${site}/demo-version.txt to report ${tag}"
    if ! wait_for_version "$tag"; then
        echo "Still serving '$(live_version)'; recreating the web container"
        mw container recreate "$(web_container)" --project-id "$PROJECT_ID" --force
        wait_for_version "$tag" || {
            echo "ERROR: ${site} still serves '$(live_version)' instead of ${tag}" >&2
            mw container logs "$(web_container)" --project-id "$PROJECT_ID" | tail -30 || true
            exit 1
        }
    fi
fi

# The setup runs after Apache starts; the audit endpoint answers once it is done.
echo "Waiting for the audit endpoint"
for _ in $(seq 1 60); do
    if curl --fail --silent --output /dev/null "${site}/.well-known/content-provenance"; then
        echo "Live: ${site} (image ${tag})"
        echo "Check it: verify_site.py and check_site.py from content-provenance-conformance"
        exit 0
    fi
    sleep 5
done
echo "ERROR: ${site}/.well-known/content-provenance does not answer" >&2
exit 1
