#!/bin/bash

# ============================================================
# MyClass2026 — Deployment & Server Tools Menu v3
# ============================================================
# Enhanced version with arrow key navigation
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
    if [ "$OTHER_PID" = "$$" ]; then
      return 0
    fi

    if ps -p "$OTHER_PID" > /dev/null 2>&1; then
      OTHER_CMD=$(ps -p "$OTHER_PID" -o command= 2>/dev/null | head -n 1)
      if echo "$OTHER_CMD" | grep -Eq "tools_menu(_v3)?\\.sh|update(_.*)?\\.sh|sync_routes_to_hostinger\\.sh"; then
        echo "🛑  ERROR: Another deployment/write task is already running (PID: $OTHER_PID)."
        echo "   Command: $OTHER_CMD"
        read -p "Force stop PID $OTHER_PID and continue? (y/N): " KILL_CONFIRM
        if [ "$KILL_CONFIRM" = "y" ] || [ "$KILL_CONFIRM" = "Y" ]; then
          kill "$OTHER_PID" 2>/dev/null || true
          rm -f "$DEPLOY_LOCK" 2>/dev/null || true
        else
          echo "   Please wait for it to finish or close the other terminal."
          return 1
        fi
      fi
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

# ── Menu Options Array ──
declare -a MENU_OPTIONS=(
  "Deploy: Full (build + push + sync)"
  "Deploy: Backend Only"
  "Deploy: Frontend Only (after build)"
  "Build + Deploy Frontend (build + sync)"
  "Deploy: Full + Cache Clear"
  "Logs: Show production laravel.log"
  "Logs: Clear production laravel.log"
  "Server: Cache clear + route verify"
  "Build + Deploy (build + sync)"
  "Server: Delete remote build files"
  "Database: Run migrations on Hostinger"
  "Route Check: Local + Hostinger"
  "Routes: Sync to Hostinger"
  "Exit"
)

# ── Terminal Control for Arrow Navigation ──
# Enable keyboard input and hide cursor
setup_terminal() {
  # Save terminal settings
  stty -echo
  tput civis
}

# Restore terminal settings
restore_terminal() {
  # Restore terminal settings
  stty echo
  tput cnorm
  clear
}

# ── Display Menu with Selection ──
display_menu() {
  local selected=$1
  
  clear
  echo "╔══════════════════════════════════════════╗"
  echo "║     MyClass2026 — Deployment Tools v3    ║"
  echo "╠══════════════════════════════════════════╣"
  
  for i in "${!MENU_OPTIONS[@]}"; do
    if [ $i -eq $selected ]; then
      printf "║  ▶ %2d) %-35s ║\n" $((i+1)) "${MENU_OPTIONS[$i]}"
    else
      printf "║    %2d) %-35s ║\n" $((i+1)) "${MENU_OPTIONS[$i]}"
    fi
  done
  
  echo "╚══════════════════════════════════════════╝"
  echo ""
  echo "Use ↑↓ arrows to navigate, Enter to select, q to quit"
}

# ── Read Single Character Input ──
read_key() {
  local key
  read -s -n1 key 2>/dev/null >&2
  
  # Handle escape sequences for arrow keys
  if [[ $key == $'\x1b' ]]; then
    read -s -n2 key 2>/dev/null >&2
    case $key in
      '[A') echo "UP" ;;
      '[B') echo "DOWN" ;;
      *) echo "OTHER" ;;
    esac
  else
    echo "$key"
  fi
}

# ── Execute Selected Action ──
execute_action() {
  local choice=$1
  echo ""
  
  case $choice in
    1)
      acquire_lock || return
      run_script ./update.sh
      release_lock
      ;;
    2)
      acquire_lock || return
      run_script ./update_backend.sh
      release_lock
      ;;
    3)
      # Pre-flight: verify build output exists locally
      if [ ! -f "public/build/manifest.json" ]; then
        echo "⚠️  No manifest.json found in public/build."
        read -p "Run 'npm run build' first? (y/N): " DO_BUILD
        if [ "$DO_BUILD" = "y" ] || [ "$DO_BUILD" = "Y" ]; then
          npm run build || { echo "❌ Build failed. Aborting."; return; }
        else
          echo "Cancelled."
          return
        fi
      fi
      acquire_lock || return
      run_script ./update_frontend.sh
      release_lock
      ;;
    4)
      acquire_lock || return
      run_script ./update_build_and_deploy.sh
      release_lock
      ;;
    5)
      acquire_lock || return
      run_script ./update_production_hostinger_with_cache_clear.sh
      release_lock
      ;;
    6)
      read -p "How many lines? (default 120): " LINES
      LINES=${LINES:-120}
      test_ssh || return
      echo "📜 Fetching last $LINES lines of laravel.log..."
      ssh -p "$SSH_PORT" "$SSH_CONN" "tail -n $LINES $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null || \
        echo "⚠️  Could not read log file. It may not exist yet."
      ;;
    7)
      read -p "⚠️  This will EMPTY laravel.log on production. Continue? (y/N): " CONFIRM
      if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
        test_ssh || return
        ssh -p "$SSH_PORT" "$SSH_CONN" "truncate -s 0 $REMOTE_DIR/storage/logs/laravel.log" 2>/dev/null
        echo "✅ Production laravel.log cleared."
      else
        echo "Cancelled."
      fi
      ;;
    8)
      test_ssh || return
      acquire_lock || return
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
      acquire_lock || return
      run_script ./update_build_and_deploy.sh
      release_lock
      ;;
    10)
      test_ssh || return
      echo "🗑️  Deleting remote build files..."
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR/public/build && rm -rf assets/* manifest.json" || echo "⚠️  Delete failed or no files found."
      echo "✅ Remote build files deleted."
      ;;
    11)
      echo "🗄️  Running Laravel migrations on Hostinger..."
      test_ssh || return
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
        return
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
      test_ssh || return
      ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
        && echo \"--- Checking for route: $ROUTE_TO_CHECK ---\" \
        && php artisan route:list | grep -i \"$ROUTE_TO_CHECK\" || echo \"⚠️  Route \\\"$ROUTE_TO_CHECK\\\" not found on Hostinger\""
      
      echo ""
      echo "✅ Route check completed."
      ;;
    13)
      echo "Routes: Sync to Hostinger"
      echo ""
      if [ ! -f "./sync_routes_to_hostinger.sh" ]; then
        echo "Error: sync_routes_to_hostinger.sh script not found!"
        echo "Please ensure the script exists in the project root."
        return
      fi
      
      acquire_lock || return
      run_script ./sync_routes_to_hostinger.sh
      release_lock
      ;;
    14)
      echo "Bye 👋"
      exit 0
      ;;
  esac
}

# ── Main Menu Loop ──
main() {
  local selected=0
  local total_options=${#MENU_OPTIONS[@]}
  
  setup_terminal
  
  while true; do
    display_menu $selected
    
    # Read user input
    key=$(read_key)
    
    case $key in
      "UP")
        if [ $selected -gt 0 ]; then
          selected=$((selected - 1))
        else
          selected=$((total_options - 1))
        fi
        ;;
      "DOWN")
        if [ $selected -lt $((total_options - 1)) ]; then
          selected=$((selected + 1))
        else
          selected=0
        fi
        ;;
      "")
        # Enter key pressed
        execute_action $((selected + 1))
        echo ""
        read -p "Press [Enter] to continue..."
        ;;
      "q"|"Q")
        echo "Bye 👋"
        break
        ;;
    esac
  done
  
  restore_terminal
}

# Handle script interruption gracefully
trap 'restore_terminal; exit 1' INT TERM

# Start the menu
main
