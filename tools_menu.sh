#!/bin/bash

set -e

echo "=============================="
echo "=============================="
echo "1) Deploy: Full update + sync (update.sh)"
echo "2) Deploy: Full update + sync (cache clear + route verify)"
echo "3) Logs: Show last N lines of production laravel.log"
echo "4) Logs: Clear (truncate) production laravel.log"
echo "5) Server: Cache clear + route verify (remote only, no deploy)"
echo "6) Build: Delete Hostinger build files (public/build)"
echo "7) Build: Push build repo & sync Hostinger build directory"
echo "8) Exit"
echo ""

read -p "Choose an option (1-8): " CHOICE

case "$CHOICE" in
  1)
    echo "Running: ./update.sh"
    bash ./update.sh
    ;;
  2)
    echo "Running: ./update_production_hostinger_with_cache_clear.sh"
    bash ./update_production_hostinger_with_cache_clear.sh
    ;;
  3)
    read -p "How many lines? (default 120): " LINES
    if [ -z "$LINES" ]; then
      LINES=120
    fi
    echo "Running: ./get_production_laravel_errors.sh $LINES"
    bash ./get_production_laravel_errors.sh "$LINES"
    ;;
  4)
    read -p "This will empty laravel.log on production. Continue? (y/N): " CONFIRM
    if [ "$CONFIRM" = "y" ] || [ "$CONFIRM" = "Y" ]; then
      echo "Running: ./clear_production_laravel_log.sh"
      bash ./clear_production_laravel_log.sh
    else
      echo "Cancelled."
    fi
    ;;
  5)
    echo "🌐 Running remote cache clear + route verify on Hostinger..."
    SSH_REMOTE_CMD="cd ~/domains/qudratpro.com/public_html \
&& php artisan optimize:clear \
&& php artisan optimize \
&& echo '--- Route check (submit-answer) ---' \
&& php artisan route:list | grep submit-answer || true"
    ssh -p 65002 u474447882@62.72.37.122 "$SSH_REMOTE_CMD"
    echo "✅ Remote cache cleared and routes verified."
    ;;
  6)
    echo "🗑️ Deleting build files on Hostinger (public/build)..."
    SSH_CMD="cd ~/domains/qudratpro.com/public_html \
&& rm -rf public/build/assets \
&& rm -f public/build/manifest.json"
    ssh -p 65002 u474447882@62.72.37.122 "$SSH_CMD"
    echo "✅ Remote build files deleted."
    ;;
  7)
    echo "📂 Pushing Build Repository (public/build)..."
    TIMESTAMP=$(date +"%Y-%m-%d %H:%M")
    cd public/build
    
    # Standard remote url
    git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026_build.git
    
    git add -A
    git commit -m "build: manual update assets | $TIMESTAMP" || true
    git push origin main || echo "⚠️ Git push to GitHub timed out (HTTP 408), skipping - will sync directly via Rsync."
    cd ../..
    
    echo "🌐 Syncing Local Build Directory directly to Hostinger via Rsync..."
    rsync -avz -e "ssh -p 65002" public/build/assets u474447882@62.72.37.122:~/domains/qudratpro.com/public_html/public/build/ || true
    rsync -avz -e "ssh -p 65002" public/build/manifest.json u474447882@62.72.37.122:~/domains/qudratpro.com/public_html/public/build/ || true
    
    SSH_CMD="cd ~/domains/qudratpro.com/public_html \
&& php artisan optimize:clear \
&& php artisan optimize"
    ssh -p 65002 u474447882@62.72.37.122 "$SSH_CMD"
    echo "✅ Build files zipped and synced to Hostinger via Rsync."
    ;;
  8)
    echo "Bye."
    exit 0
    ;;
  *)
    echo "Invalid choice."
    exit 1
    ;;
esac
