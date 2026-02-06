# History: Disable Auto Activity Logging

**Date:** 2026-02-06
**Time:** 22:38
**Topic:** Performance / Privacy

## 📌 Context
User requested to stop the automatic recording of page visits in the `activity_logs` table by default.

## ✅ Completed Tasks

### 1. Middleware Update
*   **File:** `App\Http\Middleware\LogPageVisit`
*   **Change:** Added a conditional check for `config('activitylog.enabled', false)`.
*   **Effect:** Page visits are **NO LONGER logged** by default.

## 📋 How to Re-enable
To enable logging again (e.g., for debugging or admin tracking), add this to your `.env` file:

```bash
ACTIVITY_LOG_ENABLED=true
```

Or ensure your config file (e.g., `config/activitylog.php` if created) returns `true` for `enabled`.
