#!/bin/bash

# One-Click Update Script for MyClass2026 (Mac/Linux)
# This script automates: build -> push assets -> push source

# Get timestamp for commit message
TIMESTAMP=$(date +"%Y-%m-%d %H:%M")

echo "🚀 Starting Full Project Update..."

# 1. Build the assets
echo "📦 Building assets..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Build failed. Aborting."
    exit 1
fi

# 2. Update Build Repository (Submodule)
echo "📂 Updating Build Repository..."
cd public/build
git add -A
git commit -m "build: auto-update assets | $TIMESTAMP | Mac"
git push origin main
cd ../..

# 3. Update Main Repository
echo "🖥️ Updating Main Repository..."
git add .
git commit -m "feat: auto-update source & submodule | $TIMESTAMP | Mac"
git push origin production

# 4. Remote Sync (Hostinger)
echo "🌐 Syncing to Hostinger..."
# NOTE: This requires 'sshpass' installed on your Mac: brew install esolitos/ipa/sshpass
# If you don't have sshpass, you will be prompted for your password manually.

SSH_CMD="cd ~/domains/qudratpro.com/public_html && git fetch origin && git reset --hard origin/production && git submodule update --init --remote && php artisan optimize"

ssh -p 65002 u474447882@62.72.37.122 "$SSH_CMD"

echo "✅ ALL DONE! Project is updated locally and on Hostinger."
