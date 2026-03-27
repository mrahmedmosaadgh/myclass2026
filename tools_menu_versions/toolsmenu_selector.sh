#!/bin/bash

# ============================================================
# MyClass2026 — Tools Menu Version Selector
# ============================================================
# This launcher lets you choose between:
#   1) Version 1  (original archived menu)
#   2) Version 2  (recommended improved menu)
#   3) Final      (defaults to Version 2)
# ============================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/.." && pwd)"
V1_MENU="$REPO_ROOT/tools_menu_versions/toolsmenuv1/tools_menu.sh"
V2_MENU="$REPO_ROOT/tools_menu_versions/toolsmenuv2/tools_menu.sh"
DEFAULT_CHOICE="3"

# ── Color Constants ──
RED='\033[0;31m'
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
NC='\033[0m' # No Color

cd "$REPO_ROOT" || {
  echo -e "${RED}❌ Unable to enter repo root: $REPO_ROOT${NC}"
  exit 1
}

run_menu() {
  local MENU_PATH="$1"
  if [ ! -f "$MENU_PATH" ]; then
    echo -e "${RED}❌ Menu not found: $MENU_PATH${NC}"
    return 1
  fi

  bash "$MENU_PATH"
}

while true; do
  echo ""
  echo "╔══════════════════════════════════════════════════╗"
  echo "║     MyClass2026 — Tools Menu Version Selector   ║"
  echo "╠══════════════════════════════════════════════════╣"
  echo "║  1) Version 1 — Original archived menu          ║"
  echo "║  2) Version 2 — Recommended improved menu       ║"
  echo -e "║  ${GREEN}3) Final — Recommended choice (uses Version 2)${NC} ║"
  echo "║  ──────────────────────────────────────────────  ║"
  echo "║  4) Exit                                         ║"
  echo "╚══════════════════════════════════════════════════╝"
  echo ""
  echo -e "${GREEN}Default selection: 3${NC}"
  echo ""

  read -r -p "Choose a version (1-4) [3]: " CHOICE
  CHOICE="${CHOICE:-$DEFAULT_CHOICE}"
  echo ""

  case "$CHOICE" in
    1)
      echo "▶ Running Version 1"
      run_menu "$V1_MENU"
      ;;
    2)
      echo "▶ Running Version 2"
      run_menu "$V2_MENU"
      ;;
    3)
      echo "▶ Running Final Recommended Choice (Version 2)"
      run_menu "$V2_MENU"
      ;;
    4)
      echo "Bye 👋"
      exit 0
      ;;
    *)
      echo -e "${RED}❌ Invalid choice. Please enter 1-4.${NC}"
      ;;
  esac

  echo ""
  read -r -p "Press [Enter] to return to the selector..."
done
