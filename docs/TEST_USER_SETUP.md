# Test User Setup Guide

**Date:** March 19, 2026  
**Purpose:** Setup test user account for development and testing  
**Status:** ✅ **CONFIGURED**

---

## 🔐 Test User Credentials

### Environment Variables (Set in `.env`)

```env
# Test User Credentials (For Development/Testing Only)
TEST_USER_EMAIL=tuhn06837@example.com
TEST_USER_PASSWORD=Test12345678!
```

### ⚠️ Security Notice

**These credentials are for LOCAL TESTING ONLY!**

- ❌ Never use in production
- ❌ Never commit real passwords to version control
- ✅ Change passwords before deploying
- ✅ Use strong, unique passwords for real accounts

---

## 🚀 Quick Setup

### Step 1: Run the Seeder

```powershell
cd C:\my_project\myclass2026-main
php artisan db:seed --class=TestUserSeeder
```

### Step 2: Login

Navigate to your application and login with:
- **Email:** `tuhn06837@example.com`
- **Password:** `Test12345678!`

---

## 📋 What the Seeder Does

The `TestUserSeeder` creates:

1. **User Account**
   - Name: Test User
   - Email: tuhn06837@example.com
   - Password: Test12345678! (hashed)
   - Email verified: Yes

2. **Role Assignment**
   - Assigns `teacher` role

3. **Teacher Record**
   - Links user to teacher profile
   - Associates with first available school
   - Creates default school if none exists

---

## 🎯 Usage Examples

### Create Fresh Test User

```powershell
# Run the seeder
php artisan db:seed --class=TestUserSeeder
```

**Output:**
```
Creating test user account...
✓ Test user created successfully!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Email:    tuhn06837@example.com
Password: Test12345678!
Role:     teacher
School:   Test School
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️  SECURITY REMINDER:
   - These credentials are for LOCAL TESTING ONLY
   - Never use these in production
   - Change passwords before deploying
```

### Reset Test User Password

```powershell
# Re-run seeder to reset password
php artisan db:seed --class=TestUserSeeder
```

When prompted:
```
✓ Test user already exists: tuhn06837@example.com
Would you like to reset the password to the one in .env? (yes/no) [no]:
> yes

✓ Test user password updated
```

### Change Test Credentials

1. **Edit `.env` file:**
   ```env
   TEST_USER_EMAIL=your-new-email@example.com
   TEST_USER_PASSWORD=YourNewSecurePassword123!
   ```

2. **Clear config cache:**
   ```powershell
   php artisan config:clear
   ```

3. **Re-run seeder:**
   ```powershell
   php artisan db:seed --class=TestUserSeeder
   ```

---

## 🛠️ Advanced Usage

### Create Multiple Test Users

Create a custom seeder for multiple test accounts:

```php
// database/seeders/MultipleTestUsersSeeder.php
public function run(): void
{
    $testUsers = [
        [
            'email' => 'teacher1@test.com',
            'password' => 'Test12345678!',
            'role' => 'teacher',
        ],
        [
            'email' => 'teacher2@test.com',
            'password' => 'Test12345678!',
            'role' => 'teacher',
        ],
        [
            'email' => 'admin@test.com',
            'password' => 'Test12345678!',
            'role' => 'admin',
        ],
    ];

    foreach ($testUsers as $userData) {
        User::firstOrCreate(
            ['email' => $userData['email']],
            [
                'name' => 'Test ' . ucfirst($userData['role']),
                'password' => Hash::make($userData['password']),
                'email_verified_at' => now(),
            ]
        )->assignRole($userData['role']);
    }
}
```

### Test Authentication

```powershell
# Test login via tinker
php artisan tinker
```

```php
>>> Auth::attempt(['email' => 'tuhn06837@example.com', 'password' => 'Test12345678!'])
=> true

>>> Auth::user()->name
=> "Test User"

>>> Auth::user()->hasRole('teacher')
=> true
```

---

## 🔍 Troubleshooting

### Issue: "User already exists"

**Solution:** The seeder will detect this and offer to reset the password.

### Issue: "School not found"

**Solution:** The seeder will automatically create a default school named "Test School".

### Issue: "Role not found"

**Solution:** Make sure roles are seeded first:
```powershell
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=TestUserSeeder
```

### Issue: "Cannot login"

**Solutions:**
1. Verify credentials in `.env`
2. Clear config cache: `php artisan config:clear`
3. Check user exists: 
   ```powershell
   php artisan tinker
   >>> User::where('email', 'tuhn06837@example.com')->exists()
   ```

---

## 📝 Database Cleanup

### Delete Test User

```powershell
php artisan tinker
```

```php
>>> $user = User::where('email', 'tuhn06837@example.com')->first();
>>> $user->delete();
```

### Reset Everything

```powershell
# Fresh migration with test user
php artisan migrate:fresh --seed
php artisan db:seed --class=TestUserSeeder
```

---

## 🎯 Integration with Testing

### PHPUnit Tests

```php
// tests/Feature/ExampleTest.php
public function test_teacher_can_access_dashboard()
{
    $user = User::factory()->create([
        'email' => 'tuhn06837@example.com',
    ]);
    
    $user->assignRole('teacher');
    
    $response = $this->actingAs($user)
        ->get('/teacher/dashboard');
    
    $response->assertStatus(200);
}
```

### Browser Tests (Dusk)

```php
// tests/Browser/ExampleTest.php
public function test_login_with_test_user()
{
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'tuhn06837@example.com')
            ->type('password', 'Test12345678!')
            ->press('Login')
            ->assertPathIs('/teacher/dashboard');
    });
}
```

---

## 📚 Related Files

- **Seeder:** `database/seeders/TestUserSeeder.php`
- **Environment:** `.env` (TEST_USER_* variables)
- **User Model:** `app/Models/User.php`
- **Teacher Model:** `app/Models/Teacher.php`

---

## 🔐 Security Best Practices

### For Development

✅ Use environment variables for credentials  
✅ Seed test users with clear expiration  
✅ Use different credentials than production  
✅ Document test accounts for team  

### For Production

❌ Never use test credentials  
❌ Never commit passwords to git  
❌ Never use weak passwords like "12345678"  
✅ Use strong password policies  
✅ Enable 2FA for all accounts  
✅ Rotate passwords regularly  

---

## 🎉 Success Checklist

- ✅ `.env` configured with test credentials
- ✅ `TestUserSeeder` created
- ✅ Test user created in database
- ✅ Teacher role assigned
- ✅ Can login successfully
- ✅ Team documented the credentials

---

## 📞 Need Help?

If you encounter issues:

1. **Check `.env` file** - Verify credentials are set
2. **Clear config cache** - `php artisan config:clear`
3. **Check database** - Verify user exists
4. **Review logs** - `storage/logs/laravel.log`

---

**Setup completed:** March 19, 2026  
**Status:** ✅ Ready for testing  
**Credentials:** See `.env` file
