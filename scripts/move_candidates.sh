#!/usr/bin/env bash
# move_candidates.sh — Move candidate files into ./delete/ preserving structure.
# Usage:
#   ./move_candidates.sh -f cleanup_candidates.csv
#   ./move_candidates.sh -f cleanup_candidates.csv --dry-run
# Notes:
# - Expects a CSV with a header and "file" as the first column.
# - Skips files already under ./delete or missing files.
# - Uses git mv when possible; otherwise mv.
# - Won't overwrite existing files in ./delete (it will skip with a warning).

set -euo pipefail

CSV_PATH=""
DRY_RUN="0"
PROJECT_ROOT="$(pwd)"
DELETE_DIR="${PROJECT_ROOT}/delete"

# --- args ---
while [[ $# -gt 0 ]]; do
  case "$1" in
    -f|--file)
      CSV_PATH="$2"
      shift 2
      ;;
    -n|--dry-run|--dryrun)
      DRY_RUN="1"
      shift 1
      ;;
    -h|--help)
      echo "Usage: $0 -f cleanup_candidates.csv [--dry-run]"
      exit 0
      ;;
    *)
      echo "Unknown option: $1" >&2
      exit 1
      ;;
  esac
done

if [[ -z "${CSV_PATH}" ]]; then
  echo "ERROR: please specify the candidates CSV with -f path/to/cleanup_candidates.csv" >&2
  exit 1
fi
if [[ ! -f "${CSV_PATH}" ]]; then
  echo "ERROR: CSV not found: ${CSV_PATH}" >&2
  exit 1
fi

echo "[INFO] Project root: ${PROJECT_ROOT}"
echo "[INFO] CSV: ${CSV_PATH}"
echo "[INFO] Delete dir: ${DELETE_DIR}"
[[ "${DRY_RUN}" == "1" ]] && echo "[INFO] DRY RUN (no changes will be made)"

mkdir -p "${DELETE_DIR}"

# Detect git repo
IN_GIT="0"
if command -v git >/dev/null 2>&1 && git rev-parse --is-inside-work-tree >/dev/null 2>&1; then
  IN_GIT="1"
  echo "[INFO] Git repo detected — will prefer 'git mv'."
fi

# Extract first column named "file" (skip header). Handles commas in other columns.
# Assumes the first column is the relative path.
# If your CSV differs, adjust the awk below.
awk -F',' 'NR>1 {print $1}' "${CSV_PATH}" | while IFS= read -r REL_PATH; do
  # Trim quotes/whitespace
  REL_PATH="${REL_PATH%\"}"; REL_PATH="${REL_PATH#\"}"
  REL_PATH="$(echo -n "${REL_PATH}" | sed 's/^[[:space:]]\+//; s/[[:space:]]\+$//')"

  # Skip empty
  [[ -z "${REL_PATH}" ]] && continue

  # Skip already under delete/
  if [[ "${REL_PATH}" == delete/* ]]; then
    echo "[SKIP] Already under delete/: ${REL_PATH}"
    continue
  fi

  SRC="${PROJECT_ROOT}/${REL_PATH}"
  if [[ ! -f "${SRC}" ]]; then
    echo "[MISS] Not found: ${REL_PATH}"
    continue
  fi

  DEST="${DELETE_DIR}/${REL_PATH}"
  DEST_DIR="$(dirname "${DEST}")"
  mkdir -p "${DEST_DIR}"

  if [[ -e "${DEST}" ]]; then
    echo "[WARN] Destination exists, skipping to avoid overwrite: ${REL_PATH}"
    continue
  fi

  echo "[MOVE] ${REL_PATH}  ->  delete/${REL_PATH}"

  if [[ "${DRY_RUN}" == "1" ]]; then
    continue
  fi

  if [[ "${IN_GIT}" == "1" ]]; then
    # Only use git mv if the file is tracked
    if git ls-files --error-unmatch "${REL_PATH}" >/dev/null 2>&1; then
      git mv -v "${REL_PATH}" "delete/${REL_PATH}" || {
        # If git mv to nested path fails, fallback to mkdir+mv then git add/rm
        mv -v "${SRC}" "${DEST}"
        git add -v "delete/${REL_PATH}" || true
        git rm -f --cached "${REL_PATH}" >/dev/null 2>&1 || true
      }
    else
      # Untracked file — plain mv
      mv -v "${SRC}" "${DEST}"
      # If you want it tracked under delete/, you can uncomment:
      # git add -v "delete/${REL_PATH}" || true
    fi
  else
    mv -v "${SRC}" "${DEST}"
  fi
done

echo "[DONE] Finished moving candidates to ./delete/"
echo "       Review and test. To restore a specific file, move it back:"
echo "         mv delete/path/to/file path/to/file"
echo "       (Or 'git mv' the file back if using git.)"

