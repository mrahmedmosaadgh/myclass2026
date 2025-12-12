@echo off
echo Starting Firebase Emulators...
start cmd /k "npx firebase emulators:start"

timeout /t 5

echo Starting Laravel...
start cmd /k "php artisan serve"

echo Starting Vite...
start cmd /k "npm run dev"

echo All services started!
echo.
echo Firebase Emulator UI: http://localhost:4000
echo Laravel App: http://localhost:8000
echo.
pause
