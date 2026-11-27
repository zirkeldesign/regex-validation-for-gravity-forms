#!/usr/bin/env bash
# Fails when a script the plugin enqueues from dist/js was not built. dist/ is
# gitignored, so a fresh checkout ships without it unless the build ran; that is
# how 1.1.0 to 1.1.4 reached wordpress.org without the form editor settings.
set -euo pipefail
cd "$(dirname "$0")/.."

missing=0
for name in $(grep -ohE 'dist/js/[a-z0-9-]+' src/*.php | sort -u); do
    for file in "$name.js" "$name.min.js"; do
        if [ ! -s "$file" ]; then
            echo "Missing built asset: $file" >&2
            missing=1
        fi
    done
done

exit "$missing"
