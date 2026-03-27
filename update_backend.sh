#!/bin/bash

# ============================================================
# Backend-Only Update Script
# Pushes Laravel/PHP changes and syncs to Hostinger.
# Does NOT touch npm, build assets, or the public/build submodule.
# ============================================================

TIMESTAMP=$(date +"%Y-%m-%d %H:%M")

# ── Shared Constants (inherited from tools_menu.sh or set defaults) ──
SSH_USER="${SSH_USER:-u474447882}"
SSH_HOST="${SSH_HOST:-62.72.37.122}"
SSH_PORT="${SSH_PORT:-65002}"
REMOTE_DIR="${REMOTE_DIR:-~/domains/qudratpro.com/public_html}"
SSH_CONN="${SSH_USER}@${SSH_HOST}"

echo "🚀 Starting BACKEND ONLY Update..."

# ── Pre-flight ──
command -v git >/dev/null 2>&1 || { echo "❌ git not found."; exit 1; }

# ── Stage & Commit ──
echo "🖥️ Committing Backend Changes..."
# Stage everything EXCEPT the frontend submodule (public/build).
git add -- ':!public/build'

# Exit cleanly if nothing to commit
if git diff --cached --quiet; then
  echo "✅ No backend changes to commit."
  exit 0
fi

git commit -m "feat(backend): auto-update source | $TIMESTAMP"
git push origin production || echo "⚠️ Main repo push failed, but will still attempt server sync."

# ── Remote Sync ──
echo "🌐 Syncing to Hostinger..."
ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR \
&& git fetch origin production \
&& (git checkout production || git checkout -b production FETCH_HEAD) \
&& GIT_ALLOW_PROTOCOL=false git -c submodule.recurse=false reset --hard FETCH_HEAD \
&& (composer dump-autoload -o || true) \
&& php artisan optimize:clear \
&& php artisan optimize" || echo "⚠️ Remote sync had issues."

echo "✅ ALL DONE! Backend updated locally and on Hostinger."
