#!/bin/bash

# ============================================================
# Full Update Script — Build + Push + Sync
# Automates: npm build → push build submodule → push source → Hostinger sync
# ============================================================

set -e

TIMESTAMP=$(date +"%Y-%m-%d %H:%M")

# ── Shared Constants (inherited from tools_menu.sh or set defaults) ──
SSH_USER="${SSH_USER:-u474447882}"
SSH_HOST="${SSH_HOST:-62.72.37.122}"
SSH_PORT="${SSH_PORT:-65002}"
REMOTE_DIR="${REMOTE_DIR:-~/domains/qudratpro.com/public_html}"
SSH_CONN="${SSH_USER}@${SSH_HOST}"

echo "🚀 Starting Full Project Update..."

# ── Pre-flight checks ──
command -v npm >/dev/null 2>&1 || { echo "❌ npm not found. Install Node.js first."; exit 1; }
command -v git >/dev/null 2>&1 || { echo "❌ git not found."; exit 1; }
command -v rsync >/dev/null 2>&1 || { echo "❌ rsync not found. Install via: brew install rsync"; exit 1; }

# Set git identity (local to this repo, not global)
git config user.email "ahmedmosaad@example.com"
git config user.name "Ahmed Mosaad"

# Increase Git buffer to 500MB to prevent HTTP 408 timeouts
git config http.postBuffer 524288000

# ── Step 1: Build ──
echo "📦 Building assets..."
if ! npm run build; then
  echo "❌ Build failed. Aborting."
  exit 1
fi

# ── Step 2: Push Build Submodule ──
echo "📂 Updating Build Repository (public/build)..."
if [ -d "public/build/.git" ]; then
  (
    cd public/build
    git remote set-url origin https://github.com/mrahmedmosaadgh/myclass2026_build.git 2>/dev/null || true
    git add -A
    git diff --cached --quiet && echo "  ℹ️  No new build changes." || {
      git commit -m "build: auto-update assets | $TIMESTAMP"
      git push origin main || echo "  ⚠️ Build repo push failed (timeout likely). Will sync via Rsync."
    }
  )
else
  echo "  ⚠️ public/build is not a git repo. Skipping submodule push."
fi

# ── Step 3: Push Main Repository ──
echo "🖥️ Updating Main Repository..."
git add .
git diff --cached --quiet && echo "  ℹ️  No source changes to commit." || {
  git commit -m "feat: auto-update source & submodule"
  git push origin production || echo "  ⚠️ Main repo push failed."
}

# ── Step 4: Remote Sync (Hostinger) ──
echo "🌐 Syncing to Hostinger..."
ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
&& git fetch origin production \
&& (git checkout production || git checkout -b production FETCH_HEAD) \
&& GIT_ALLOW_PROTOCOL=false git -c submodule.recurse=false reset --hard FETCH_HEAD \
&& (composer dump-autoload -o || true) \
&& php artisan optimize:clear \
&& php artisan optimize" || echo "  ⚠️ Remote git sync had issues, proceeding to Rsync."

echo "⚡ Syncing build assets to Hostinger via Rsync..."
rsync -avz -e "ssh -p $SSH_PORT" public/build/assets "$SSH_CONN:$REMOTE_DIR/public/build/" || true
rsync -avz -e "ssh -p $SSH_PORT" public/build/manifest.json "$SSH_CONN:$REMOTE_DIR/public/build/" || true

echo "✅ ALL DONE! Project is updated locally and on Hostinger."
