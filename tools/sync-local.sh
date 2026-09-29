#!/bin/bash
# Copy the plugin into the Local site (discovered-menu.local).
set -e
SRC="$(cd "$(dirname "$0")/.." && pwd)"
DEST="/Users/stevenjerez/Local Sites/discovered-menu/app/public/wp-content/plugins/thehiretalent-shortcode-pack"
rsync -a --delete --exclude .git --exclude tools "$SRC/" "$DEST/"
echo "synced -> $DEST"
