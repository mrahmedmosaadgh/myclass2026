#  Quick Reference: Test User Credentials

## ✅ Test User Created Successfully!

### Login Credentials

```
Email:    tuhn06837@example.com
Password: Test12345678!
Role:     Teacher
School:   MSC
```

---

## 🚀 Quick Start

### 1. Login to Your Application

Navigate to: `http://127.0.0.1:8000/login`

Use the credentials above to login.

### 2. Access Presentation Builder V2

After login, navigate to:
```
http://127.0.0.1:8000/classroom-records/presentation/builder-v2
```

---

## 📋 What's Been Set Up

### ✅ Files Created/Modified

1. **`.env`** - Test credentials configured
2. **`database/seeders/TestUserSeeder.php`** - User seeder
3. **`docs/TEST_USER_SETUP.md`** - Complete setup guide
4. **Database** - Test user created with teacher role

### ✅ User Details

- **Name:** Test User
- **Email:** tuhn06837@example.com
- **Role:** Teacher
- **School:** MSC
- **Status:** Active & Verified

---

## 🔧 Useful Commands

### Reset Password (if needed)

```powershell
php artisan db:seed --class=TestUserSeeder
```

### Check User Exists

```powershell
php artisan tinker
>>> User::where('email', 'tuhn06837@example.com')->exists()
```

### Create Additional Test Users

Edit and run custom seeder:
```powershell
php artisan make:seeder AdditionalTestUsersSeeder
```

---

## 🎯 Testing Scenarios

### Test Presentation Builder V2

1. Login with test credentials
2. Navigate to Presentation Builder V2
3. Test JSON export/import functionality
4. Verify slide editing works
5. Test animation settings

### Test Chrome DevTools MCP

1. Start Chrome with debugging
2. Run test script: `node test-presentation-builder.js`
3. Inspect Vue component state
4. Monitor network requests
5. Debug any issues

---

## ⚠️ Security Reminders

### ✅ DO:
- Use for local testing only
- Change passwords before production
- Document for your team
- Clear about it being a test account

### ❌ DON'T:
- Use in production environment
- Share publicly
- Use for real student data
- Commit real passwords to git

---

## 📞 Troubleshooting

### Can't Login?

1. Verify email/password above
2. Check user exists in database
3. Clear browser cache
4. Run seeder again

### User Not Found?

```powershell
# Re-run seeder
php artisan db:seed --class=TestUserSeeder
```

### Role Missing?

```powershell
# Seed roles first
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=TestUserSeeder
```

---

## 📚 Documentation

- **Full Guide:** `docs/TEST_USER_SETUP.md`
- **Chrome DevTools:** `docs/chrome-devtools-mcp-guide.md`
- **JSON Import Fix:** `docs/history/presentation-builder-v2-json-import-fix.md`

---

## ✨ Next Steps

1. ✅ **Login** with test credentials
2. ✅ **Test** Presentation Builder V2
3. ✅ **Use** Chrome DevTools MCP for debugging
4. ✅ **Report** any issues found

---

**Created:** March 19, 2026  
**Status:** ✅ Ready to use  
**Purpose:** Development & Testing Only
