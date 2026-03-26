# 🛠️ Tools Menu Guide — `tools_menu.sh`

> **Location:** `/myclass2026-main/tools_menu.sh`  
> **Purpose:** Interactive CLI menu for common developer operations (deploy, logs, maintenance).

---

## 🚀 How to Run

```bash
# From the project root
bash tools_menu.sh
```

You'll see an interactive menu:

```
==============================
 MyClass2026 Tools Menu
==============================
1) Deploy: Full update + sync (update.sh)
2) Deploy: Full update + sync (cache clear + route verify)
3) Logs: Show last N lines of production laravel.log
4) Logs: Clear (truncate) production laravel.log
5) Exit
```

Type a number (1–5) and press **Enter**.

---

## 📋 Menu Options Explained

| # | Label | Script Called | Description |
|---|-------|---------------|-------------|
| 1 | Deploy: Full update + sync | `update.sh` | Standard deploy: git pull, composer, npm build, artisan |
| 2 | Deploy: Full update + sync (cache clear + route verify) | `update_production_hostinger_with_cache_clear.sh` | Same as #1 + clears caches, verifies routes — safer for breaking changes |
| 3 | Logs: Show last N lines | `get_production_laravel_errors.sh <N>` | Tails the production `laravel.log`; prompts how many lines (default: 120) |
| 4 | Logs: Clear | `clear_production_laravel_log.sh` | Truncates `laravel.log` on production. **Asks for confirmation** (y/N) |
| 5 | Exit | — | Exits the menu |

---

## 📁 Related Scripts (Same Directory)

All scripts called by `tools_menu.sh` live in the project root:

```
myclass2026-main/
├── tools_menu.sh                               ← This menu (entry point)
├── update.sh                                   ← Option 1
├── update_production_hostinger_with_cache_clear.sh  ← Option 2
├── get_production_laravel_errors.sh            ← Option 3
└── clear_production_laravel_log.sh             ← Option 4
```

---

## 💡 How to Improve It

### 1. Move All Scripts to a Dedicated Folder

**Recommended location:** `scripts/deploy/`

Benefits: root directory is cleaner, scripts are grouped logically.

```bash
# Suggested structure
scripts/
└── deploy/
    ├── tools_menu.sh          ← move here (rename optional)
    ├── update.sh
    ├── update_production_hostinger_with_cache_clear.sh
    ├── get_production_laravel_errors.sh
    └── clear_production_laravel_log.sh
```

After moving, update the paths inside `tools_menu.sh`:

```bash
# Before
bash ./update.sh

# After (if menu is run from project root)
bash ./scripts/deploy/update.sh
```

Or add a `cd` guard at the top to always run relative to script location:

```bash
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
bash "$SCRIPT_DIR/update.sh"
```

### 2. Add a Confirmation Step Before Destructive Actions

Option 4 already has a confirmation. Option 2 (cache clear) should too:

```bash
read -p "This will clear all caches on production. Continue? (y/N): " CONFIRM
[ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ] || { echo "Cancelled."; exit 0; }
```

### 3. Add Color Output

Makes it much easier to read in the terminal:

```bash
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'
echo -e "${GREEN}1)${NC} Deploy: Full update + sync"
echo -e "${RED}4)${NC} Logs: Clear (DESTRUCTIVE)"
```

### 4. Add a "Status Check" Option

Useful before deploying:

```bash
6) Status: Check git status + last commit on production
```

```bash
6)
  ssh user@host "cd /path && git log -1 --oneline && git status"
  ;;
```

### 5. Log Each Action with a Timestamp

```bash
LOG_FILE="./logs/tools_menu.log"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Ran option $CHOICE" >> "$LOG_FILE"
```

### 6. Remove `set -e` or Handle It Carefully

`set -e` exits on any error. If a subshell returns non-zero (e.g., SSH warning), the whole menu quits unexpectedly. Consider wrapping calls:

```bash
bash ./update.sh || echo "⚠️  Script exited with errors. Check output above."
```

---

## 📌 Recommended Migration Plan

If you want to keep everything tidy **without breaking anything**:

1. Create `scripts/deploy/` folder
2. Move all 5 scripts there
3. Update path references inside `tools_menu.sh`
4. Add a symlink at root for convenience:
   ```bash
   ln -s scripts/deploy/tools_menu.sh tools_menu.sh
   ```
5. Update any CI/CD or deployment docs that reference these scripts

---

## 🗓️ History

| Date | Change |
|------|--------|
| 2026-03-26 | Initial guide written |
