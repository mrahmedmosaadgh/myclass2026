#!/bin/bash

# ============================================================
# MyClass2026 - Routes Sync to Hostinger
# ============================================================
# This script copies the routes folder to Hostinger server
# ============================================================

set -u

# Reuse SSH configuration from environment (exported by tools_menu*.sh).
# Do NOT source tools_menu.sh here because it runs an interactive menu.
if [ -z "${SSH_USER:-}" ] || [ -z "${SSH_HOST:-}" ] || [ -z "${SSH_PORT:-}" ] || [ -z "${REMOTE_DIR:-}" ] || [ -z "${SSH_CONN:-}" ]; then
  export SSH_USER="u474447882"
  export SSH_HOST="62.72.37.122"
  export SSH_PORT="65002"
  export REMOTE_DIR="~/domains/qudratpro.com/public_html"
  export SSH_CONN="${SSH_USER}@${SSH_HOST}"
fi

# Function to test SSH connection
test_ssh() {
  echo "Testing SSH connection..."
  if ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_CONN" "echo ok" > /dev/null 2>&1; then
    echo "SSH connection verified."
    return 0
  else
    echo "Cannot reach Hostinger via SSH. Check your network or VPN."
    return 1
  fi
}

# Function to backup remote routes before sync
backup_remote_routes() {
  echo "Creating backup of remote routes folder..."
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
    && cp -r routes routes_backup_$(date +%Y%m%d_%H%M%S) 2>/dev/null \
    && echo 'Backup created successfully' \
    || echo 'Backup failed or no routes folder exists'"
}

# Function to sync routes folder
sync_routes() {
  echo "Syncing routes folder to Hostinger..."
  
  # Use rsync for efficient sync
  rsync -avz --delete \
    -e "ssh -p $SSH_PORT" \
    ./routes/ \
    "$SSH_CONN:$REMOTE_DIR/routes/"
  
  if [ $? -eq 0 ]; then
    echo "Routes folder synced successfully!"
    return 0
  else
    echo "Routes sync failed!"
    return 1
  fi
}

# Function to clear route cache on Hostinger
clear_route_cache() {
  echo "Clearing route cache on Hostinger..."
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
    && php artisan route:clear \
    && php artisan cache:clear \
    && echo 'Route cache cleared successfully' \
    || echo 'Failed to clear route cache'"
}

# Main execution
main() {
  echo "================================================"
  echo "MyClass2026 - Routes Sync to Hostinger"
  echo "================================================"
  echo ""
  
  # Check if routes folder exists locally
  if [ ! -d "./routes" ]; then
    echo "Error: routes folder not found in current directory!"
    exit 1
  fi
  
  # Test SSH connection
  test_ssh || exit 1
  
  echo ""
  echo "This will sync your local routes folder to Hostinger."
  echo "A backup will be created before syncing."
  echo ""
  
  read -p "Continue? (y/N): " CONFIRM
  if [ "$CONFIRM" != "y" ] && [ "$CONFIRM" != "Y" ]; then
    echo "Cancelled."
    exit 0
  fi
  
  echo ""
  echo "Starting routes sync process..."
  echo ""
  
  # Step 1: Backup remote routes
  backup_remote_routes
  echo ""
  
  # Step 2: Sync routes folder
  if sync_routes; then
    echo ""
    # Step 3: Clear route cache
    clear_route_cache
    echo ""
    echo "================================================"
    echo "Routes sync completed successfully!"
    echo "================================================"
  else
    echo ""
    echo "================================================"
    echo "Routes sync failed!"
    echo "================================================"
    exit 1
  fi
}

# Run the script
main
