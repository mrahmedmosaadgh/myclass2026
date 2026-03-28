#!/bin/bash

# Fix PSR-4 folder naming issues
# This script renames API folders to Api to match the namespace

echo "🔧 Fixing PSR-4 folder naming issues..."

# Step 1: Rename API to Api_temp (intermediate step)
echo "Step 1: Renaming API folders to temporary names..."
if [ -d "app/Http/Requests/API" ]; then
    git mv app/Http/Requests/API app/Http/Requests/Api_temp
    echo "✓ Renamed app/Http/Requests/API to Api_temp"
fi

# Step 2: Rename Api_temp to Api (final name)
echo "Step 2: Renaming temporary folders to final names..."
if [ -d "app/Http/Requests/Api_temp" ]; then
    git mv app/Http/Requests/Api_temp app/Http/Requests/Api
    echo "✓ Renamed app/Http/Requests/Api_temp to Api"
fi

# Step 3: Check for puzzle1Controller.php and rename if needed
echo "Step 3: Checking Puzzle1Controller filename..."
if [ -f "app/Http/Controllers/puzzle1Controller.php" ]; then
    git mv app/Http/Controllers/puzzle1Controller.php app/Http/Controllers/Puzzle1Controller_temp.php
    git mv app/Http/Controllers/Puzzle1Controller_temp.php app/Http/Controllers/Puzzle1Controller.php
    echo "✓ Fixed Puzzle1Controller.php filename"
else
    echo "✓ Puzzle1Controller.php already has correct capitalization"
fi

echo ""
echo "✅ Folder renaming complete!"
echo ""
echo "Next steps:"
echo "1. Run: composer dump-autoload"
echo "2. Commit changes: git add . && git commit -m 'fix: PSR-4 folder naming - rename API to Api'"
echo "3. Push to deploy"
