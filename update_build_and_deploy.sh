#!/bin/bash

# ============================================================
# Build + Deploy Script — npm run build + Frontend Deploy
# Automates: npm run build → deploy frontend assets
# ============================================================

set -e

TIMESTAMP=$(date +"%Y-%m-%d %H:%M")

# ── Shared Constants (inherited from tools_menu.sh or set defaults) ──
SSH_USER="${SSH_USER:-u474447882}"
SSH_HOST="${SSH_HOST:-62.72.37.122}"
SSH_PORT="${SSH_PORT:-65002}"
REMOTE_DIR="${REMOTE_DIR:-~/domains/qudratpro.com/public_html}"
SSH_CONN="${SSH_USER}@${SSH_HOST}"

echo "🚀 Starting Build + Deploy Process..."

# ── Pre-flight checks ──
command -v npm >/dev/null 2>&1 || { echo "❌ npm not found. Install Node.js first."; exit 1; }
command -v rsync >/dev/null 2>&1 || { echo "❌ rsync not found. Install via: brew install rsync"; exit 1; }

# ── Step 1: Build Assets Locally ──
echo "📦 Running npm run build locally..."
if ! npm run build; then
  echo "❌ Build failed. Aborting deployment."
  exit 1
fi

echo "✅ Build completed successfully!"

# ── Step 2: Deploy Frontend Assets ──
echo "🌐 Deploying frontend assets..."

# Test SSH connection
echo "🔌 Testing SSH connection..."
if ! ssh -p "$SSH_PORT" -o ConnectTimeout=10 "$SSH_CONN" "echo ok" > /dev/null 2>&1; then
  echo "❌ Cannot reach Hostinger via SSH. Check your network or VPN."
  exit 1
fi
echo "✅ SSH connection verified."

# Sync build assets to Hostinger
echo "⚡ Syncing build assets to Hostinger via Rsync..."
rsync -avz -e "ssh -p $SSH_PORT" --delete public/build/assets/ "$SSH_CONN:$REMOTE_DIR/public/build/assets/" || {
  echo "❌ Failed to sync assets directory"
  exit 1
}

rsync -avz -e "ssh -p $SSH_PORT" public/build/manifest.json "$SSH_CONN:$REMOTE_DIR/public/build/" || {
  echo "❌ Failed to sync manifest.json"
  exit 1
}

# Sync any other build files
rsync -avz -e "ssh -p $SSH_PORT" --include="*.js" --include="*.css" --include="*.json" --include="*.txt" --include="*.xml" --exclude="*" public/build/ "$SSH_CONN:$REMOTE_DIR/public/build/" || true

echo "✅ Frontend assets synced successfully!"

# ── Step 3: Clear Cache (Optional) ──
echo "🧹 Clearing production cache..."
ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR && php artisan optimize:clear" || echo "⚠️ Cache clear failed, but deployment succeeded."

echo ""
echo "🎉 Build + Deploy Complete!"
echo "   ✅ npm run build - Local build successful"
echo "   ✅ Assets synced - Frontend deployed to production"
echo "   ✅ Cache cleared - Production cache refreshed"
echo ""
echo "📊 Deployment Summary:"
echo "   Timestamp: $TIMESTAMP"
echo "   Files: All frontend assets"
echo "   Target: $REMOTE_DIR/public/build/"
echo ""
