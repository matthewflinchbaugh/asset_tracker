#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/html/asset_tracker"
VIEW="$APP_DIR/resources/views/admin/assets/edit.blade.php"

echo "[*] Rolling back to last known-good backup (.fixbak)…"

if [[ ! -f "$VIEW" ]]; then
  echo "[ERROR] $VIEW not found"; exit 1
fi

LATEST_FIXBAK="$(ls -1t "$VIEW".*.fixbak 2>/dev/null | head -n1 || true)"
if [[ -z "${LATEST_FIXBAK}" ]]; then
  echo "[ERROR] No .fixbak backups found next to $VIEW"; exit 1
fi

echo "  - Restoring: ${LATEST_FIXBAK}"
cp -a "${LATEST_FIXBAK}" "$VIEW"

echo "  - Clearing compiled views…"
cd "$APP_DIR"
php artisan view:clear || true

echo "[SUCCESS] Restored $VIEW from ${LATEST_FIXBAK}. Reload /admin/assets/{id}/edit."

