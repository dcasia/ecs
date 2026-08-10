#!/usr/bin/env sh

set -eu

exec docker compose run --rm --user "$(id -u):$(id -g)" php php "$@"
