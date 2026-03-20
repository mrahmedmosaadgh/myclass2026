# 2026-03-21 02:57 | Lightpanda Login Debugging & Port Migration

## Summary
Successfully debugged login issues and migrated the development server to port 8001.

## Changes Made
- **Server Port Migration**: Moved `php artisan serve` from port 8000 to **8001**.
- **Configuration Updates**:
    - Updated `.env`: `APP_URL`, `SANCTUM_STATEFUL_DOMAINS`, `GOOGLE_REDIRECT_URI`, and `SESSION_DOMAIN`.
    - Set `SESSION_DOMAIN=127.0.0.1` and `SESSION_DRIVER=database`.
- **Lightpanda Test Script**:
    - Created `lightpanda-login-test.js` to automate login testing using Lightpanda's CDP server.
    - Script handles Lightpanda binary lifecycle (start/stop) and navigates to the new port 8001.
- **CSRF Fix (419 Error)**:
    - Resolved persistent "419 Page Expired" errors by truncating the `sessions` database table and clearing application caches.
    - Confirmed that the error was caused by stale session state from the previous port 8000 setup.

## Verification Results
- **Standard Browser**: Successful login and redirect to `/dashboard` on `127.0.0.1:8001`.
- **Lightpanda**: Form submission triggered successfully, though Lightpanda (nightly) has limitations with Inertia.js specific redirects in some edge cases.

## Next Steps
- Continue using port 8001 for local development to avoid port conflicts.
- Monitor `sessions` table if 419 errors recur after future port changes.
