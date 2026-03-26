@echo off
setlocal
:: One-Click Update Script for MyClass2026 (Windows)
:: This script automates: build -> push assets -> push source

:: Get timestamp
set TIMESTAMP=%date% %time%
echo 🚀 Starting Full Project Update...

:: 1. Build the assets
echo 📦 Building assets...
npm run build

if %ERRORLEVEL% neq 0 (
    echo ❌ Build failed. Aborting.
    pause
    exit /b %ERRORLEVEL%
)

:: 2. Update Build Repository (Submodule)
echo 📂 Updating Build Repository...
cd public\build
git add -A
git commit -m "build: auto-update assets | %TIMESTAMP% | Win"
git push origin main
cd ..\..

:: 3. Update Main Repository
echo 🖥️ Updating Main Repository...
git add .
git commit -m "feat: auto-update source & submodule | %TIMESTAMP% | Win"
git push origin production

:: 4. Remote Sync (Hostinger)
echo 🌐 Syncing to Hostinger...
set SSH_CMD="cd ~/domains/qudratpro.com/public_html && git fetch origin && git reset --hard origin/production && git submodule update --init --remote && php artisan optimize"

ssh -p 65002 u474447882@62.72.37.122 %SSH_CMD%

echo ✅ ALL DONE! Project is updated locally and on Hostinger.
pause
