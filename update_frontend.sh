#!/bin/bash

# ============================================================
# Frontend-Only Update Script
# Assumes you have ALREADY run `npm run build` locally.
# Syncs public/build assets to Hostinger via Rsync.
# ============================================================

TIMESTAMP=$(date +"%Y-%m-%d %H:%M")

# ── Shared Constants (inherited from tools_menu.sh or set defaults) ──
SSH_USER="${SSH_USER:-u474447882}"
SSH_HOST="${SSH_HOST:-62.72.37.122}"
SSH_PORT="${SSH_PORT:-65002}"
REMOTE_DIR="${REMOTE_DIR:-~/domains/qudratpro.com/public_html}"
SSH_CONN="${SSH_USER}@${SSH_HOST}"

echo "🚀 Starting FRONTEND ONLY Update..."

# ── Pre-flight ──
command -v rsync >/dev/null 2>&1 || { echo "❌ rsync not found. Install via: brew install rsync"; exit 1; }

if [ ! -f "public/build/manifest.json" ]; then
  echo "❌ No manifest.json found in public/build."
  echo "   Run 'npm run build' first, then re-run this script."
  exit 1
fi

# Increase Git buffer for large asset pushes
git config http.postBuffer 524288000 2>/dev/null || true

# ── Push Build Submodule (optional — Rsync is the real sync mechanism) ──
echo "📂 Updating Build Repository (public/build)..."
if [ -d "public/build/.git" ]; then
  (
    cd public/build
    git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026_build.git 2>/dev/null || true
    git add -A
    if git diff --cached --quiet; then
      echo "  ℹ️  No new build changes to commit."
    else
      git commit -m "build(frontend): update assets | $TIMESTAMP"
      git push origin main || echo "  ⚠️ Build repo push failed (timeout likely). Proceeding with Rsync."
    fi
  )
else
  echo "  ⚠️ public/build is not a git repo. Skipping submodule push."
fi

# ── Rsync to Hostinger ──
echo "🌐 Syncing build assets to Hostinger via Rsync..."
rsync -avz --delete -e "ssh -p $SSH_PORT" public/build/assets "$SSH_CONN:$REMOTE_DIR/public/build/" || {
  echo "❌ Rsync failed for assets directory."
  exit 1
}
rsync -avz -e "ssh -p $SSH_PORT" public/build/manifest.json "$SSH_CONN:$REMOTE_DIR/public/build/" || {
  echo "❌ Rsync failed for manifest.json."
  exit 1
}

echo "✅ ALL DONE! Frontend assets synced to Hostinger via Rsync."
