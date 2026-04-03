#!/bin/bash

# Schedule App V4 Deployment Script
# This script deploys only the Schedule App V4 files to the correct public directory

# Get deployment settings from main tools menu
source ./tools_menu.sh

echo "🚀 Deploying Schedule App V4..."

# Test SSH connection first
test_ssh || exit 1

# Create remote directory if it doesn't exist
echo "📁 Creating remote directory structure..."
ssh -p "$SSH_PORT" "$SSH_CONN" "mkdir -p $REMOTE_DIR/public/my-schedule-app/v4"

# Sync the schedule app files
echo "📤 Syncing Schedule App V4 files to server..."
rsync -avz --delete \
  -e "ssh -p $SSH_PORT" \
  --exclude 'node_modules' \
  --exclude '.git' \
  --exclude '*.log' \
  public/my-schedule-app/v4/ \
  "$SSH_CONN:$REMOTE_DIR/public/my-schedule-app/v4/"

if [ $? -eq 0 ]; then
  echo "✅ Schedule App V4 files synced successfully!"
  
  # Set correct permissions
  echo "🔐 Setting file permissions..."
  ssh -p "$SSH_PORT" "$SSH_CONN" "cd $REMOTE_DIR/public/my-schedule-app/v4 && find . -type f -exec chmod 644 {} \; && find . -type d -exec chmod 755 {} \;"
  
  echo "✅ Schedule App V4 deployment complete!"
  echo "🌐 Available at: https://qudratpro.com/my-schedule-app/v4"
else
  echo "❌ Failed to sync Schedule App V4 files!"
  exit 1
fi
