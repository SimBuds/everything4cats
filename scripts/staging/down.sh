#!/usr/bin/env bash
#
# Stop and discard the staging container.
#
#   # ON HOST
#   bash scripts/staging/down.sh
#
# Discards, not stops. The staging site is ephemeral by design, so there is no
# state here worth preserving: the database came from the image, the theme and
# plugins are bind mounts that live in the working tree, and any content came
# from a backup that restore.sh can replay. Bringing it back is up.sh.
#
# The image is left alone. Removing it would mean a multi-minute rebuild for no
# reason, and it is the harness's artefact rather than this script's.

set -euo pipefail

NAME="e4c-staging"

command -v docker >/dev/null 2>&1 || { echo "docker is not on PATH." >&2; exit 1; }

if docker container inspect "$NAME" >/dev/null 2>&1; then
	docker rm -f "$NAME" >/dev/null
	echo "==> Removed $NAME."
else
	echo "==> $NAME is not running, nothing to do."
fi
