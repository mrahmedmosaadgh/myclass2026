# Hostinger to MyClass2026 Migration Guide
**Created:** 2026-02-02 23:14:06
**Source:** Hostinger Laravel Installation (public_html)
**Target:** MyClass2026 Project Deployment

---

## 🎯 Critical Files to KEEP from Hostinger

### 1. **`.env` File** 🔴 CRITICAL
**Location:** `/public_html/.env`
**Why Keep:** Contains your database credentials and APP_KEY

**Important Values:**
```env
DB_DATABASE=u474447882_VcHbj
DB_USERNAME=u474447882_rP9FV
DB_PASSWORD=9]3Mkk~F/~H)]iW%
APP_KEY=9372856f561040e2bf81f8e1ef292816
```

**Action:** 
- ✅ **BACKUP THIS FILE** before doing anything
- Copy database credentials to your myclass2026 `.env`
- Keep the APP_KEY or generate a new one

---

### 2. **`.htaccess` Files** 🔴 CRITICAL
**Locations:**
- `/public_html/.htaccess` (root)
- `/public_html/public/.htaccess` (public folder)

**Why Keep:** Hostinger-specific server configuration

**Action:**
- ✅ **KEEP** the root `.htaccess` (redirects to public folder)
- ✅ **MERGE** with your myclass2026 `.htaccess` if you have custom rules

---

### 3. **`vendor/` Folder** ⚠️ CONDITIONAL
**Location:** `/public_html/vendor/`

**Action:**
- ❌ **DELETE** - You'll reinstall with `composer install`
- This folder is auto-generated and should not be transferred

---

### 4. **`storage/` Folder** ⚠️ PARTIAL KEEP
**Location:** `/public_html/storage/`

**What to Keep:**
- ✅ `storage/logs/` - Keep existing logs for debugging
- ❌ `storage/framework/` - Delete, will be regenerated
- ❌ `storage/app/` - Delete if empty

**Action:**
- Backup logs if needed
- Set proper permissions after migration: `chmod -R 775 storage`

---

## 🗑️ Files to DELETE from Hostinger Installation

These are default Laravel files that will be replaced by your myclass2026 project:

```
❌ app/                    (Replace with your app/)
❌ bootstrap/              (Replace with yours)
❌ config/                 (Replace with yours)
❌ database/               (Replace with yours)
❌ resources/              (Replace with yours)
❌ routes/                 (Replace with yours)
❌ tests/                  (Replace with yours)
❌ vendor/                 (Will regenerate)
❌ composer.json           (Replace with yours)
❌ composer.lock           (Replace with yours)
❌ package.json            (Replace with yours)
❌ vite.config.js          (Replace with yours)
❌ artisan                 (Replace with yours)
❌ README.md               (Replace with yours)
❌ default.php             (Hostinger file, delete)
❌ index.php               (Root, delete - use public/index.php)
```

---

## 📋 Step-by-Step Migration Process

### Step 1: Backup Critical Files from Hostinger

**SSH to Hostinger:**
```bash
ssh u474447882@qudratpro.com
cd ~/public_html
```

**Create backup folder:**
```bash
mkdir ~/backup_hostinger
cp .env ~/backup_hostinger/.env
cp .htaccess ~/backup_hostinger/.htaccess
cp public/.htaccess ~/backup_hostinger/public.htaccess
```

**Save database credentials:**
```bash
cat .env | grep DB_ > ~/backup_hostinger/db_credentials.txt
```

---

### Step 2: Clean Hostinger Installation

**Delete everything EXCEPT .env and .htaccess:**
```bash
cd ~/public_html

# Delete directories
rm -rf app bootstrap config database resources routes tests vendor node_modules

# Delete files (keep .env and .htaccess)
rm -f artisan composer.json composer.lock package.json vite.config.js README.md default.php index.php phpunit.xml
```

---

### Step 3: Upload Your MyClass2026 Project

**Option A: Using Git (Recommended)**
```bash
cd ~/public_html
git clone https://github.com/mrahmedmosaadgh/myclass2026.git temp
mv temp/* .
mv temp/.* . 2>/dev/null
rm -rf temp
```

**Option B: Using FTP/File Manager**
1. Zip your myclass2026 project locally
2. Upload to `/public_html/`
3. Extract the zip
4. Move files from subfolder to root

---

### Step 4: Merge .env Configuration

**Edit your new .env file:**
```bash
nano .env
```

**Update these values from Hostinger backup:**
```env
APP_NAME="Qudrat Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qudratpro.com

# Use Hostinger database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u474447882_VcHbj
DB_USERNAME=u474447882_rP9FV
DB_PASSWORD=9]3Mkk~F/~H)]iW%

# Keep or generate new APP_KEY
APP_KEY=9372856f561040e2bf81f8e1ef292816
```

---

### Step 5: Install Dependencies

```bash
cd ~/public_html

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Generate APP_KEY (if you want a new one)
php artisan key:generate

# Install NPM dependencies (if SSH allows)
npm install

# Build assets (or build locally and upload)
npm run build
```

---

### Step 6: Run Migrations

```bash
# Check database connection
php artisan migrate:status

# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force
```

---

### Step 7: Set Permissions

```bash
# Set proper permissions
chmod -R 755 ~/public_html
chmod -R 775 ~/public_html/storage
chmod -R 775 ~/public_html/bootstrap/cache

# Create storage link
php artisan storage:link
```

---

### Step 8: Cache Configuration

```bash
# Clear all cache
php artisan optimize:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Final Directory Structure

After migration, your `/public_html/` should look like:

```
public_html/
├── .env                    ← Merged (Hostinger DB + Your config)
├── .htaccess              ← From Hostinger (or merged)
├── app/                   ← Your myclass2026 app
├── bootstrap/             ← Your myclass2026 bootstrap
├── config/                ← Your myclass2026 config
├── database/              ← Your myclass2026 database
├── public/                ← Your myclass2026 public
│   ├── .htaccess         ← From Hostinger
│   ├── index.php         ← Your myclass2026 entry point
│   ├── build/            ← Your compiled assets
│   └── storage/          ← Symlink (created by artisan)
├── resources/             ← Your myclass2026 resources
├── routes/                ← Your myclass2026 routes
├── storage/               ← Regenerated, proper permissions
├── vendor/                ← Regenerated by composer
├── artisan                ← Your myclass2026 artisan
├── composer.json          ← Your myclass2026 composer.json
└── package.json           ← Your myclass2026 package.json
```

---

## 🔍 What Makes Each File Important

### From Hostinger (KEEP)

| File | Why Important | Action |
|------|---------------|--------|
| `.env` | Database credentials, APP_KEY | Merge with yours |
| `.htaccess` (root) | Server routing to public/ | Keep or merge |
| `public/.htaccess` | Laravel routing rules | Keep Hostinger version |

### From MyClass2026 (USE)

| File/Folder | Why Important | Action |
|-------------|---------------|--------|
| `app/` | Your application logic | Replace entirely |
| `resources/` | Your Vue components, views | Replace entirely |
| `routes/` | Your application routes | Replace entirely |
| `config/` | Your app configuration | Replace entirely |
| `database/` | Your migrations, seeders | Replace entirely |
| `public/build/` | Your compiled assets | Upload after build |
| `composer.json` | Your dependencies | Replace entirely |
| `package.json` | Your frontend deps | Replace entirely |

---

## ✅ Verification Checklist

After migration, verify:

- [ ] Visit `https://qudratpro.com` - Homepage loads
- [ ] Check database connection - No errors
- [ ] Test login/register - Works correctly
- [ ] Check browser console - No JS errors
- [ ] Test file uploads - Storage works
- [ ] Check responsive design - Mobile works
- [ ] Verify SSL - Green padlock
- [ ] Check logs - `storage/logs/laravel.log`

---

## 🚨 Common Issues After Migration

### Issue 1: 500 Error
**Cause:** Wrong .env configuration or permissions
**Fix:**
```bash
php artisan config:clear
chmod -R 775 storage bootstrap/cache
```

### Issue 2: Mix Manifest Not Found
**Cause:** Missing compiled assets
**Fix:**
```bash
# Build locally
npm run build

# Upload public/build/ folder to server
```

### Issue 3: Database Connection Failed
**Cause:** Wrong database credentials
**Fix:**
```bash
# Verify credentials
cat .env | grep DB_

# Test connection
php artisan migrate:status
```

### Issue 4: Routes Not Working
**Cause:** .htaccess not configured
**Fix:**
```bash
# Check .htaccess exists in public/
ls -la public/.htaccess

# Verify mod_rewrite is enabled (contact Hostinger if not)
```

---

## 📦 Quick Migration Script

Save as `migrate_to_hostinger.sh` on your local machine:

```bash
#!/bin/bash

echo "🚀 Preparing MyClass2026 for Hostinger deployment..."

# Build assets locally
echo "📦 Building assets..."
npm run build

# Create deployment package
echo "📁 Creating deployment package..."
mkdir -p deploy_package

# Copy essential files
cp -r app deploy_package/
cp -r bootstrap deploy_package/
cp -r config deploy_package/
cp -r database deploy_package/
cp -r public deploy_package/
cp -r resources deploy_package/
cp -r routes deploy_package/
cp -r storage deploy_package/
cp artisan deploy_package/
cp composer.json deploy_package/
cp composer.lock deploy_package/
cp package.json deploy_package/
cp vite.config.js deploy_package/
cp .env.example deploy_package/
cp .gitignore deploy_package/

# Create zip
echo "🗜️ Creating zip file..."
cd deploy_package
zip -r ../myclass2026_deploy.zip .
cd ..

echo "✅ Deployment package created: myclass2026_deploy.zip"
echo "📤 Upload this to Hostinger and extract in public_html/"
echo "⚠️ Remember to merge .env with Hostinger database credentials!"
```

**Run:**
```bash
chmod +x migrate_to_hostinger.sh
./migrate_to_hostinger.sh
```

---

## 🎯 Summary: What to Keep vs Replace

### KEEP from Hostinger ✅
1. `.env` database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
2. `.htaccess` files (root and public/)
3. APP_KEY (or generate new)

### REPLACE with MyClass2026 ✅
1. All application code (app/, resources/, routes/, config/)
2. All dependencies (composer.json, package.json)
3. All compiled assets (public/build/)
4. Database migrations (database/migrations/)

### DELETE from Hostinger ❌
1. Default Laravel installation files
2. vendor/ folder (regenerate)
3. node_modules/ folder (regenerate)
4. default.php (Hostinger file)

---

**Migration Status:** Ready to deploy! 🚀

Follow the steps in order, and your myclass2026 project will replace the Hostinger installation safely!
