#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/html/asset_tracker"
VIEW="$APP_DIR/resources/views/admin/assets/edit.blade.php"
TS="$(date +%Y%m%d_%H%M%S)"

echo "[*] Ensuring Add/Remove PM assignment JavaScript exists…"

if [[ ! -f "$VIEW" ]]; then
  echo "[ERROR] $VIEW not found"; exit 1
fi

# append only if markers not present
if grep -q "BEGIN PM ASSIGNMENTS JS" "$VIEW"; then
  echo "  - JS already present. Skipping."
else
  cp -a "$VIEW" "$VIEW.$TS.pmjs.bak"
  echo "  - Backup: $VIEW.$TS.pmjs.bak"

  cat >> "$VIEW" <<'BLADE'
{{-- BEGIN PM ASSIGNMENTS JS (safe append) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  var addBtn    = document.getElementById('add-assignment');
  var container = document.getElementById('assignment-body');

  function updateNames(row, idx) {
    var sel = row.querySelector('select');
    var inp = row.querySelector('input[type="text"]');
    if (sel) sel.name = 'checklist_assignments['+idx+'][template_id]';
    if (inp) inp.name = 'checklist_assignments['+idx+'][component_name]';
  }

  function addRow() {
    if (!container) return;
    var rows = container.getElementsByClassName('assignment-row');
    var idx  = rows.length;
    var tmpl = rows[0];
    if (!tmpl) return;
    var clone = tmpl.cloneNode(true);
    var sel = clone.querySelector('select');
    var inp = clone.querySelector('input[type="text"]');
    if (sel) sel.value = '';
    if (inp) inp.value = '';
    updateNames(clone, idx);
    container.appendChild(clone);
  }

  function removeRow(btn) {
    if (!container) return;
    var row = btn.closest ? btn.closest('.assignment-row') : null;
    if (!row) return;
    var rows = container.getElementsByClassName('assignment-row');
    if (rows.length > 1) {
      row.remove();
      rows = container.getElementsByClassName('assignment-row');
      Array.prototype.forEach.call(rows, function(r, i){ updateNames(r, i); });
    } else {
      var sel = row.querySelector('select'); if (sel) sel.value = '';
      var inp = row.querySelector('input[type="text"]'); if (inp) inp.value = '';
    }
  }

  if (addBtn) addBtn.addEventListener('click', function (e) { e.preventDefault(); addRow(); });

  if (container) {
    container.addEventListener('click', function (e) {
      var btn = e.target && (e.target.closest ? e.target.closest('.remove-row') : null);
      if (btn) { e.preventDefault(); removeRow(btn); }
    });
  }
});
</script>
{{-- END PM ASSIGNMENTS JS --}}
BLADE

  echo "  - JS appended."
fi

echo "  - Clearing compiled views…"
cd "$APP_DIR"
php artisan view:clear || true

echo "[SUCCESS] JS ensured. Reload /admin/assets/{id}/edit and use the PM section with the Add button."

