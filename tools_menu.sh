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

# ── Menu ──
echo ""
echo "╔══════════════════════════════════════════╗"
echo "║     MyClass2026 — Deployment Tools       ║"
echo "╠══════════════════════════════════════════╣"
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

read -p "Choose an option (1-9): " CHOICE

case "$CHOICE" in

  1)
    run_script ./update.sh
    ;;

  2)
    run_script ./update_backend.sh
    ;;

  3)
    # Pre-flight: verify build output exists locally
    if [ ! -f "public/build/manifest.json" ]; then
      echo "⚠️  No manifest.json found in public/build."
      read -p "Run 'npm run build' first? (y/N): " DO_BUILD
      if [ "$DO_BUILD" = "y" ] || [ "$DO_BUILD" = "Y" ]; then
        npm run build || { echo "❌ Build failed. Aborting."; exit 1; }
      else
        echo "Cancelled."
        exit 0
      fi
    fi
    run_script ./update_frontend.sh
    ;;

  4)
    run_script ./update_production_hostinger_with_cache_clear.sh
    ;;

  5)
    read -p "How many lines? (default 120): " LINES
    LINES=${LINES:-120}
    test_ssh || exit 1
    echo "📜 Fetching last $LINES lines of laravel.log..."
    ssh -p "$SSH_PORT" "$SSH_CONN" "tail -n $LINES $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null || \
      echo "⚠️  Could not read log file. It may not exist yet."
    ;;

  6)
    read -p "⚠️  This will EMPTY laravel.log on production. Continue? (y/N): " CONFIRM
    if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
      test_ssh || exit 1
      ssh -p "$SSH_PORT" "$SSH_CONN" "truncate -s 0 $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null
      echo "✅ Production laravel.log cleared."
    else
      echo "Cancelled."
    fi
    ;;

  7)
    test_ssh || exit 1
    echo "🌐 Running remote cache clear + route verify on Hostinger..."
    ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
&& php artisan optimize:clear \
&& php artisan optimize \
&& echo '--- Route check (submit-answer) ---' \
&& php artisan route:list | grep submit-answer || true"
    echo "✅ Remote cache cleared and routes verified."
    ;;

  8)
    read -p "⚠️  This will DELETE all build assets on Hostinger. Continue? (y/N): " CONFIRM
    if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
      test_ssh || exit 1
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
&& rm -rf public/build/assets \
&& rm -f public/build/manifest.json"
      echo "✅ Remote build files deleted."
    else
      echo "Cancelled."
    fi
    ;;

  9)
    echo "Bye 👋"
    exit 0
    ;;

  *)
    echo "❌ Invalid choice. Please enter 1-9."
    exit 1
    ;;
esac
