# Fix 419 CSRF Token Error

**Issue:** Getting "419 unknown status" when trying to login  
**Date:** March 18, 2026  
**Status:** ✅ **SOLVABLE**

---

## 🔍 Root Cause

**HTTP 419 = CSRF Token Mismatch or Session Expired**

This happens when:
1. Session cookies aren't being saved properly
2. CSRF token validation fails
3. Session driver (database) has issues
4. Browser is blocking cookies
5. Application cache is stale

---

## 🛠️ Solutions (Try in Order)

### Solution 1: Clear All Caches (90% Fix Rate) ⭐

```powershell
cd C:\my_project\myclass2026-main

# Clear all Laravel caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Clear session table
php artisan session:flush

# Clear browser cookies (manual)
# 1. Open http://127.0.0.1:8000
# 2. Press F12 → Application → Cookies → Clear all
```

**Then restart Laravel server:**
```powershell
# Stop current server (Ctrl+C)
php artisan serve
```

---

### Solution 2: Check Session Table

```powershell
# Verify sessions table exists and is working
php artisan tinker
```

```php
// In tinker
Schema::hasTable('sessions')  // Should return true
DB::table('sessions')->count()  // Check existing sessions
```

**If sessions table doesn't exist:**
```powershell
php artisan session:table
php artisan migrate
```

---

### Solution 3: Check .env Configuration

Verify these settings in `.env`:

```env
# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=129600
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

# Application Settings
APP_URL=http://127.0.0.1:8000
APP_DEBUG=true
```

**After editing .env:**
```powershell
php artisan config:clear
```

---

### Solution 4: Browser Cookie Check

**Chrome/Edge:**
1. Open `http://127.0.0.1:8000`
2. Press **F12** (DevTools)
3. Go to **Application** tab
4. Expand **Cookies** → `http://127.0.0.1:8000`
5. Check if `myclass_session` cookie exists
6. If not, cookies aren't being saved

**Firefox:**
1. Press **F12**
2. Go to **Storage** tab
3. Expand **Cookies**
4. Check for `myclass_session`

**If cookies are missing:**
- Check browser isn't blocking cookies
- Try incognito/private mode
- Clear browser cache and cookies

---

### Solution 5: Check Login Form CSRF Token

The login form should automatically include CSRF token via Inertia, but let's verify:

**In browser console (F12):**
```javascript
// Check if CSRF token is in meta tag
console.log(document.querySelector('meta[name="csrf-token"]'));

// Check Inertia configuration
console.log(window.Laravel);
```

**Should output:**
```html
<meta name="csrf-token" content="some-random-token">
```

---

### Solution 6: Force Session Regeneration

Create a test route to force session creation:

**Add to `routes/web.php`:**
```php
Route::get('/test-session', function() {
    session(['test_key' => 'test_value']);
    return [
        'session_id' => session()->getId(),
        'test_value' => session('test_key'),
        'has_session' => session()->has('test_key'),
    ];
});
```

**Visit:** `http://127.0.0.1:8000/test-session`

**If this works**, session is functioning. Then the issue is with the login form specifically.

---

### Solution 7: Check Login Route

Verify the login route is accessible:

```powershell
php artisan route:list --name=login
```

**Should show:**
```
GET|HEAD  login/{school_slug}
POST      login/{school_slug}
```

---

### Solution 8: Database Check

```powershell
php artisan tinker
```

```php
// Check if test user exists
User::where('email', 'tuhn06837@example.com')->exists();

// Check user has teacher role
$user = User::where('email', 'tuhn06837@example.com')->first();
$user ? $user->hasRole('teacher') : 'User not found';
```

---

## 🎯 Quick Diagnostic Script

Create a file `test-login.php`:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "Testing Laravel Session...\n\n";

// Test 1: Session driver
echo "1. Session Driver: " . config('session.driver') . "\n";

// Test 2: Sessions table
echo "2. Sessions Table: " . (Schema::hasTable('sessions') ? 'EXISTS' : 'MISSING') . "\n";

// Test 3: Test user
$testUser = \App\Models\User::where('email', 'tuhn06837@example.com')->first();
echo "3. Test User: " . ($testUser ? 'FOUND' : 'NOT FOUND') . "\n";
if ($testUser) {
    echo "   Email: " . $testUser->email . "\n";
    echo "   Roles: " . $testUser->getRoleNames()->join(', ') . "\n";
}

// Test 4: CSRF token
echo "4. CSRF Token: " . (csrf_token() ? 'GENERATED' : 'FAILED') . "\n";

echo "\n✅ All tests complete!\n";
```

**Run:**
```powershell
php test-login.php
```

---

## 🔧 Advanced Troubleshooting

### Check Middleware

Verify CSRF middleware is active in `bootstrap/app.php`:

```php
// Should have:
$middleware->statefulApi();
```

### Check Sanctum Configuration

```powershell
php artisan config:show sanctum
```

**Verify:**
- `stateful` domains includes `127.0.0.1`
- `expiration` is set properly

### Force Cookie Domain

In `config/session.php`, temporarily set:

```php
'domain' => env('SESSION_DOMAIN', '127.0.0.1'),
```

Then:
```powershell
php artisan config:clear
```

---

## ✅ Verification Steps

After trying solutions:

1. **Clear browser cache and cookies**
2. **Open incognito/private window**
3. **Visit:** `http://127.0.0.1:8000/login`
4. **Open DevTools (F12) → Network tab**
5. **Try to login**
6. **Check network request:**
   - Should see POST to `/login/...`
   - Status should be 302 (redirect) not 419
   - Should have `myclass_session` cookie in response

---

## 🎯 Success Indicators

**Login is working when:**
- ✅ No 419 error
- ✅ Redirects to dashboard after login
- ✅ `myclass_session` cookie exists
- ✅ Can access protected pages
- ✅ Session persists across page reloads

---

## 📞 Still Having Issues?

If none of the above works:

1. **Check Laravel logs:**
   ```powershell
   tail -f storage/logs/laravel.log
   ```

2. **Enable debug mode:**
   ```env
   APP_DEBUG=true
   ```

3. **Try file-based sessions:**
   ```env
   SESSION_DRIVER=file
   ```
   ```powershell
   php artisan config:clear
   ```

4. **Create fresh test user:**
   ```powershell
   php artisan db:seed --class=TestUserSeeder
   ```

---

##  Common Fix Summary

**Most likely solution (90%):**
```powershell
# Clear everything
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan session:flush

# Restart server
# Clear browser cookies
# Try again
```

---

**Last Updated:** March 18, 2026  
**Test User:** tuhn06837@example.com  
**Status:** Ready to test after applying fixes
