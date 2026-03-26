#!/bin/bash

# Truncate (empty) Laravel log on production (Hostinger)
# Usage:
#   ./clear_production_laravel_log.sh
#
# This keeps the file but empties it (safe for permissions).

HOST="62.72.37.122"
PORT="65002"
USER="u474447882"
REMOTE_DIR="~/domains/qudratpro.com/public_html"
LOG_FILE="storage/logs/laravel.log"

ssh -p "$PORT" "$USER@$HOST" "cd $REMOTE_DIR && if [ -f $LOG_FILE ]; then : > $LOG_FILE && echo '✅ laravel.log truncated'; else echo 'laravel.log not found at storage/logs/laravel.log'; fi"
