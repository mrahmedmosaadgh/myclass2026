#!/bin/bash

set -e

echo "=============================="
echo " MyClass2026 Tools Menu"
echo "=============================="
echo "1) Deploy: Full update + sync (update.sh)"
echo "2) Deploy: Full update + sync (cache clear + route verify)"
echo "3) Logs: Show last N lines of production laravel.log"
echo "4) Logs: Clear (truncate) production laravel.log"
echo "5) Exit"
echo ""

read -p "Choose an option (1-5): " CHOICE

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
    echo "Bye."
    exit 0
    ;;
  *)
    echo "Invalid choice."
    exit 1
    ;;
esac
