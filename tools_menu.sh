#!/bin/bash

# ============================================================
# MyClass2026 — Deployment & Server Tools Menu
# ============================================================
# This is the main entry point for all deployment operations.
# Each option delegates to a standalone script for modularity.
# ============================================================

# ── Shared Constants ──
# All scripts source these so SSH details live in ONE place.
export SSH_USER="u474447882"
export SSH_HOST="62.72.37.122"
export SSH_PORT="65002"
export REMOTE_DIR="~/domains/qudratpro.com/public_html"
export SSH_CONN="${SSH_USER}@${SSH_HOST}"

# ── Deployment Lock Mechanism ──
# Prevents two scripts from running git/rsync ops at the exact same time.
DEPLOY_LOCK="/tmp/myclass2026_deploy.lock"

acquire_lock() {
  if [ -f "$DEPLOY_LOCK" ]; then
    OTHER_PID=$(cat "$DEPLOY_LOCK")
    if ps -p "$OTHER_PID" > /dev/null 2>&1; then
      echo "🛑  ERROR: Another deployment/write task is already running (PID: $OTHER_PID)."
      echo "   Please wait for it to finish or close the other terminal."
      return 1
    fi
  fi
  echo $$ > "$DEPLOY_LOCK"
  # Clean up lock when this shell (or its current process) exits
  trap 'rm -f "$DEPLOY_LOCK"' EXIT
  return 0
}

release_lock() {
  rm -f "$DEPLOY_LOCK"
}

# ── Helper: Test SSH connectivity before any remote operation ──
test_ssh() {
  echo "🔌 Testing SSH connection..."
  if ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_CONN" "echo ok" > /dev/null 2>&1; then
    echo "✅ SSH connection verified."
    return 0
  else
    echo "❌ Cannot reach Hostinger via SSH. Check your network or VPN."
    return 1
  fi
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

# ── Menu Loop ──
while true; do
  echo ""
  echo "╔══════════════════════════════════════════╗"
  echo "║     MyClass2026 — Deployment Tools       ║"
  echo "╠══════════════════════════════════════════╣"
  echo "║  1) Deploy: Full (build + push + sync)   ║"
  echo "║  2) Deploy: Backend Only                 ║"
  echo "║  3) Deploy: Frontend Only (after build)  ║"
  echo "║  4) Build + Deploy Frontend (build + sync)║"
  echo "║  5) Deploy: Full + Cache Clear           ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  6) Logs: Show production laravel.log    ║"
  echo "║  7) Logs: Clear production laravel.log   ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  8) Server: Cache clear + route verify   ║"
  echo "║  9) Build + Deploy (build + sync)        ║"
  echo "║  10) Server: Delete remote build files    ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  11) Database: Run migrations on Hostinger ║"
  echo "║  ──────────────────────────────────────  ║"
  echo "║  12) Route Check: Local + Hostinger      ║"
  echo "║  13) Routes: Sync to Hostinger          ║"
  echo "║  14) Exit                               ║"
  echo "╚══════════════════════════════════════════╝"
  echo ""

  read -p "Choose an option (1-14): " CHOICE
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
      # Pre-flight: verify build output exists locally
      if [ ! -f "public/build/manifest.json" ]; then
        echo "⚠️  No manifest.json found in public/build."
        read -p "Run 'npm run build' first? (y/N): " DO_BUILD
        if [ "$DO_BUILD" = "y" ] || [ "$DO_BUILD" = "Y" ]; then
          npm run build || { echo "❌ Build failed. Aborting."; continue; }
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
      run_script ./update_build_and_deploy.sh
      release_lock
      ;;

    5)
      acquire_lock || continue
      run_script ./update_production_hostinger_with_cache_clear.sh
      release_lock
      ;;

    6)
      read -p "How many lines? (default 120): " LINES
      LINES=${LINES:-120}
      test_ssh || continue
      echo "📜 Fetching last $LINES lines of laravel.log..."
      ssh -p "$SSH_PORT" "$SSH_CONN" "tail -n $LINES $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null || \
        echo "⚠️  Could not read log file. It may not exist yet."
      ;;

    7)
      read -p "⚠️  This will EMPTY laravel.log on production. Continue? (y/N): " CONFIRM
      if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
        test_ssh || continue
        ssh -p "$SSH_PORT" "$SSH_CONN" "truncate -s 0 $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null
        echo "✅ Production laravel.log cleared."
      else
        echo "Cancelled."
      fi
      ;;

    8)
      test_ssh || continue
      acquire_lock || continue
      echo "🌐 Running remote cache clear + route verify on Hostinger..."
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
  && php artisan optimize:clear \
  && php artisan optimize \
  && echo '--- Route check (submit-answer) ---' \
  && php artisan route:list | grep submit-answer || true"
      echo "✅ Remote cache cleared and routes verified."
      release_lock
      ;;

    9)
      acquire_lock || continue
      run_script ./update_build_and_deploy.sh
      release_lock
      ;;

    10)
      test_ssh || continue
      echo "🗑️  Deleting remote build files..."
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR/public/build && rm -rf assets/* manifest.json" || echo "⚠️  Delete failed or no files found."
      echo "✅ Remote build files deleted."
      ;;

    11)
      echo "🗄️  Running Laravel migrations on Hostinger..."
      test_ssh || continue
      echo "⚠️  This will run migrations on production database."
      read -p "Continue? (y/N): " CONFIRM
      if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
        echo "🔄 Running migrations..."
        ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
          && php artisan migrate --force \
          && echo '✅ Migrations completed successfully!' \
          || echo '❌ Migration failed. Check logs for details.'"
      else
        echo "Cancelled."
      fi
      ;;

    12)
      echo "🔍 Route Check: Local + Hostinger"
      echo ""
      read -p "Enter route to check (e.g., '/api/test' or 'submit-answer'): " ROUTE_TO_CHECK
      
      if [ -z "$ROUTE_TO_CHECK" ]; then
        echo "❌ No route specified. Please provide a route path."
        continue
      fi
      
      echo ""
      echo "📍 Checking route: $ROUTE_TO_CHECK"
      echo "─────────────────────────────────────────"
      
      # Local route check
      echo "🏠 Local Route Check:"
      if command -v php >/dev/null 2>&1; then
        php artisan route:list | grep -i "$ROUTE_TO_CHECK" || echo "⚠️  Route '$ROUTE_TO_CHECK' not found locally"
      else
        echo "⚠️  PHP not available for local route check"
      fi
      
      echo ""
      
      # Hostinger route check
      echo "🌐 Hostinger Route Check:"
      test_ssh || continue
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
        && echo \"--- Checking for route: $ROUTE_TO_CHECK ---\" \
        && php artisan route:list | grep -i \"$ROUTE_TO_CHECK\" || echo \"⚠️  Route \\\"$ROUTE_TO_CHECK\\\" not found on Hostinger\""
      
      echo ""
      echo "✅ Route check completed."
      ;;
      
    13)
      echo "Routes: Sync to Hostinger"
      ;;
      
    14)
      echo "Bye 👋"
      exit 0
      ;;

    
    *)
      echo "❌ Invalid choice. Please enter 1-14."
      ;;
  esac

  echo ""
  read -p "Press [Enter] to return to the menu..."
done
