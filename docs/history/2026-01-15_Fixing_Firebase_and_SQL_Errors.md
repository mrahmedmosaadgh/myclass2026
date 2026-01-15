# Fixing Firebase and SQL Errors

## Overview
Addressed two critical issues hindering development and testing:
1.  **Firebase Authentication Error**: `auth/configuration-not-found` when accessing the application via local network IP (e.g., from mobile devices).
2.  **SQL Syntax Error**: `Syntax error or access violation: 1064` when querying exams, caused by incompatible JSON query syntax.

## Changes

### 1. Firebase IP Restriction Fix
- **File**: `resources/js/firebase/init.js`
- **Issue**: Emulator connection was restricted to `localhost` and `127.0.0.1`. Accessing via `192.168.x.x` attempted to use production Firebase, which failed anonymous auth.
- **Fix**: Updated the initialization logic to allow emulator connections from local IP addresses (`192.168.x.x`).
- **Safety**: Production environments (different hostname) continue to correct use production services.

### 2. QuExam SQL Compatibility Fix
- **File**: `app/Models/QuExam.php`
- **Issue**:
    1.  The arrow operator `->` in raw SQL `whereRaw` clauses was causing syntax errors in the user's MariaDB/MySQL version.
    2.  `CAST(? AS JSON)` was also not supported.
- **Fix**:
    - Replaced `target_audience->'$.key'` with `JSON_EXTRACT(target_audience, '$.key')`.
    - Removed `CAST(? AS JSON)` wrappers and passed scalar values directly to `JSON_CONTAINS`.

## Status
- [x] Firebase local network access verified.
- [x] Exam query SQL syntax verified.
- [ ] No pending tasks for this specific issue.
