#!/bin/bash

# Fetch latest Laravel log lines from production (Hostinger)
# Usage:
#   ./get_production_laravel_errors.sh            # tail last 120 lines
#   ./get_production_laravel_errors.sh 300        # tail last 300 lines

LINES=${1:-120}

HOST="62.72.37.122"
PORT="65002"
USER="u474447882"
REMOTE_DIR="~/domains/qudratpro.com/public_html"

ssh -p "$PORT" "$USER@$HOST" "cd $REMOTE_DIR && if [ -f storage/logs/laravel.log ]; then tail -n $LINES storage/logs/laravel.log; else echo 'laravel.log not found at storage/logs/laravel.log'; fi"
