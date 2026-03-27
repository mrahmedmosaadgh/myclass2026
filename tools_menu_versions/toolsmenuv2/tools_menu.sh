#!/bin/bash

# ============================================================
# MyClass2026 — Deployment & Server Tools Menu (Version 2)
# ============================================================
# Recommended version with:
# - self-locating repo root
# - stale lock recovery
# - clearer confirmations
# - better route verification
# - less hidden SSH errors
# ============================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
cd "$REPO_ROOT" || {
  echo "❌ Unable to enter repo root: $REPO_ROOT"
  exit 1
}

# ── Shared Constants ──
# Defaults can still be overridden by environment variables.
export SSH_USER="${SSH_USER:-u474447882}"
export SSH_HOST="${SSH_HOST:-62.72.37.122}"
export SSH_PORT="${SSH_PORT:-65002}"
export REMOTE_DIR="${REMOTE_DIR:-~/domains/qudratpro.com/public_html}"
export SSH_CONN="${SSH_CONN:-${SSH_USER}@${SSH_HOST}}"

# ── Deployment Lock Mechanism ──
DEPLOY_LOCK="${TMPDIR:-/tmp}/myclass2026_deploy.lock"

print_header() {
  echo ""
  echo "╔════════════════════════════════════════════════════╗"
  echo "║     MyClass2026 — Deployment Tools (Version 2)     ║"
  echo "║     Recommended: safer lock + route verification   ║"
  echo "╚════════════════════════════════════════════════════╝"
  echo ""
  echo "Repo: $REPO_ROOT"
  echo "Lock: $DEPLOY_LOCK"
  echo ""
}

cleanup_stale_lock() {
  if [ ! -f "$DEPLOY_LOCK" ]; then
    return 0
  fi

  OTHER_PID="$(cat "$DEPLOY_LOCK" 2>/dev/null || true)"
  if [ -n "$OTHER_PID" ] && ps -p "$OTHER_PID" >/dev/null 2>&1; then
    echo "🛑  ERROR: Another deployment/write task is already running (PID: $OTHER_PID)."
    echo "   Please wait for it to finish or close the other terminal."
    return 1
  fi

  echo "🧹 Stale lock found. Removing: $DEPLOY_LOCK"
  rm -f "$DEPLOY_LOCK"
  return 0
}

acquire_lock() {
  cleanup_stale_lock || return 1
  echo $$ > "$DEPLOY_LOCK"
  trap 'rm -f "$DEPLOY_LOCK"' EXIT INT TERM
  return 0
}

release_lock() {
  rm -f "$DEPLOY_LOCK"
  trap - EXIT INT TERM
}

confirm_action() {
  local PROMPT="$1"
  local ANSWER=""
  read -r -p "$PROMPT (type YES to continue): " ANSWER
  [ "$ANSWER" = "YES" ]
}

# ── Helper: Test SSH connectivity before any remote operation ──
test_ssh() {
  echo "🔌 Testing SSH connection..."
  if ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_CONN" "echo ok" >/dev/null 2>&1; then
    echo "✅ SSH connection verified."
    return 0
  fi

  echo "❌ Cannot reach Hostinger via SSH. Check your network, VPN, or credentials."
  return 1
}

# ── Helper: Check if a script file exists before running it ──
run_script() {
  local SCRIPT="$1"
  if [ ! -f "$SCRIPT" ]; then
    echo "❌ Script not found: $SCRIPT"
    echo "   Make sure the file exists in the project root."
    return 1
  fi

  bash "$SCRIPT"
}

view_production_log() {
  local LINES="$1"
  test_ssh || return 1
  echo "📜 Fetching last $LINES lines of laravel.log..."
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && if [ -f storage/logs/laravel.log ]; then tail -n $LINES storage/logs/laravel.log; else echo '⚠️  laravel.log not found at storage/logs/laravel.log'; fi"
}

clear_production_log() {
  test_ssh || return 1
  if ! confirm_action "⚠️  This will EMPTY laravel.log on production. Continue?"; then
    echo "Cancelled."
    return 0
  fi

  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && if [ -f storage/logs/laravel.log ]; then : > storage/logs/laravel.log && echo '✅ Production laravel.log cleared.'; else echo '⚠️  laravel.log not found at storage/logs/laravel.log'; fi"
}

verify_remote_routes() {
  local ROUTE_OUTPUT
  ROUTE_OUTPUT="$(ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && php artisan route:list" 2>&1)"

  echo "--- Route verification ---"
  echo "$ROUTE_OUTPUT" | grep -E "classroom-records/presentation/remote/(teacher|student|test)|submit-answer" || true
  echo ""

  local EXPECTED_ROUTES=(
    "classroom-records/presentation/remote/teacher"
    "classroom-records/presentation/remote/student"
    "classroom-records/presentation/remote/test"
    "submit-answer"
  )

  local MISSING=0
  for ROUTE in "${EXPECTED_ROUTES[@]}"; do
    if echo "$ROUTE_OUTPUT" | grep -Fq "$ROUTE"; then
      echo "✅ Found route: $ROUTE"
    else
      echo "⚠️  Missing route: $ROUTE"
      MISSING=1
    fi
  done

  return $MISSING
}

clear_cache_and_verify() {
  test_ssh || return 1
  if ! confirm_action "🌐 This will clear production caches and verify remote routes. Continue?"; then
    echo "Cancelled."
    return 0
  fi

  acquire_lock || return 1
  echo "🌐 Running remote cache clear + route verify on Hostinger..."
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && php artisan optimize:clear && php artisan optimize"
  verify_remote_routes
  local VERIFY_STATUS=$?
  release_lock

  if [ $VERIFY_STATUS -eq 0 ]; then
    echo "✅ Remote cache cleared and expected routes verified."
  else
    echo "⚠️  Remote cache cleared, but some expected routes were not found."
  fi
}

remove_build_files() {
  test_ssh || return 1
  if ! confirm_action "⚠️  This will DELETE all build assets on Hostinger. Continue?"; then
    echo "Cancelled."
    return 0
  fi

  acquire_lock || return 1
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && rm -rf public/build/assets && rm -f public/build/manifest.json && echo '✅ Remote build files deleted.'"
  release_lock
}

# ── Menu Loop ──
while true; do
  print_header

  echo "╔══════════════════════════════════════════╗"
  echo "║  1) Deploy: Full (build + push + sync)   ║"
  echo "║  2) Deploy: Backend Only                 ║"
  echo "║  3) Deploy: Frontend Only (after build)  ║"
  echo "║  4) Deploy: Full + Cache Clear           ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  5) Logs: Show production laravel.log    ║"
  echo "║  6) Logs: Clear production laravel.log   ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  7) Server: Cache clear + route verify   ║"
  echo "║  8) Server: Delete remote build files    ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  9) Exit                                 ║"
  echo "╚══════════════════════════════════════════╝"
  echo ""

  read -r -p "Choose an option (1-9): " CHOICE
  echo ""

  case "$CHOICE" in
    1)
      acquire_lock || continue
      run_script ./update.sh
      release_lock
      ;;

    2)
      acquire_lock || continue
      run_script ./update_backend.sh
      release_lock
      ;;

    3)
      if [ ! -f "public/build/manifest.json" ]; then
        echo "⚠️  No manifest.json found in public/build."
        read -r -p "Run 'npm run build' first? (y/N): " DO_BUILD
        if [ "$DO_BUILD" = "y" ] || [ "$DO_BUILD" = "Y" ]; then
          npm run build || {
            echo "❌ Build failed. Aborting."
            continue
          }
        else
          echo "Cancelled."
          continue
        fi
      fi

      acquire_lock || continue
      run_script ./update_frontend.sh
      release_lock
      ;;

    4)
      acquire_lock || continue
      run_script ./update_production_hostinger_with_cache_clear.sh
      release_lock
      ;;

    5)
      read -r -p "How many lines? (default 120): " LINES
      LINES=${LINES:-120}
      view_production_log "$LINES"
      ;;

    6)
      clear_production_log
      ;;

    7)
      clear_cache_and_verify
      ;;

    8)
      remove_build_files
      ;;

    9)
      echo "Bye 👋"
      exit 0
      ;;

    *)
      echo "❌ Invalid choice. Please enter 1-9."
      ;;
  esac

  echo ""
  read -r -p "Press [Enter] to return to the menu..."
done
