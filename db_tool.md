# `db_tool.sh` – Database Backup & Restore Helper

This script provides an interactive way to **backup** and **restore** the database used by the Asset Tracker application.

It reads connection details from your project **`.env`** file and uses `mysqldump` / `mysql` under the hood.

---

## Features

- ✅ Backup the current database to a timestamped `.sql` file  
- ✅ Restore a backup **into the existing database** (overwrites data)  
- ✅ Restore a backup **into a new database** (for testing / cloning)  
- ✅ Show the current DB configuration (from `.env`)  
- 🧠 Uses `.env` values: `DB_DATABASE`, `DB_USERNAME`, `DB_HOST`, `DB_PORT`

Backups are stored in:

```text
db_backups/

