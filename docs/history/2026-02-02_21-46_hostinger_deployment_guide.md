# Hostinger Deployment Guide - qudratpro.com
**Created:** 2026-02-02 21:46:39
**Domain:** qudratpro.com
**Project:** myclass2026 (Laravel + Vue + Inertia)

---

## 📋 Pre-Deployment Checklist

- [ ] Hostinger hosting plan active
- [ ] Domain qudratpro.com configured
- [ ] Database credentials ready
- [ ] Git repository accessible
- [ ] Local project tested and working

---
Install Laravel - qudratpro.com
Laravel is an open source application framework. Initially released in 2011, Laravel as of August 2015 is the most popular and watched PHP project on GitHub.

Website credentials
Website title
Qudrat Pro
Administrator email
me72025me2@gmail.com
Administrator username
me72025me2
Administrator password
me123456@A1
One number
One symbol
One lowercase letter
One uppercase letter
Use 8-50 characters
Only Latin letters
Advanced
Installation path
qudratpro.com
/
Enter subdirectory
Choose a database
Create new database (Recommended)

Database password
me123456@A1
One number
One symbol
One lowercase letter
One uppercase letter
Use 8-50 characters
Only Latin letters


## 🎯 Step 1: Initial Laravel Installation (Hostinger Panel)

Based on your screenshot, you're at the Laravel installation screen. Here's what to fill in:

### Website Credentials
```
Website title: Qudrat Pro (or your preferred name)
Administrator email: me72025me2@gmail.com
Administrator username: me72025me2
Administrator password: [Create a strong password]
```

### Advanced Settings
```
Installation path: qudratpro.com/
Subdirectory: [Leave empty for root installation]
Database: Create new database (Recommended) ✓
Database password: [Create a strong password - SAVE THIS!]
```

**Important:** Save your database password securely - you'll need it for `.env` configuration.

---

## 🗄️ Step 2: Database Setup

After installation, Hostinger will create:
- Database name: Usually `u123456789_qudratpro` (check in hPanel)
- Database user: Same as database name
- Database password: What you set above
- Database host: `localhost`

---

## 📁 Step 3: Upload Your Project Files

### Option A: Using Git (Recommended)

1. **Access SSH** (if available in your plan):
```bash
ssh u123456789@qudratpro.com
```

2. **Navigate to public_html**:
```bash
cd public_html
```

3. **Clone your repository**:
```bash
git clone https://github.com/mrahmedmosaadgh/myclass2026.git temp
mv temp/* .
mv temp/.* .
rm -rf temp
```

### Option B: Using File Manager

1. Go to **hPanel → File Manager**
2. Navigate to `public_html`
3. Upload your project as ZIP
4. Extract the ZIP file
5. Move all files from subfolder to `public_html` root

---

## ⚙️ Step 4: Configure Environment

### 4.1 Create .env File

SSH or File Manager → Create `.env` file:

```bash
cp .env.example .env
```

### 4.2 Edit .env Configuration

```env
APP_NAME="Qudrat Pro"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://qudratpro.com

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123456789_qudratpro
DB_USERNAME=u123456789_qudratpro
DB_PASSWORD=your_database_password_here

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# If using mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@qudratpro.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@qudratpro.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Replace:**
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` with your actual database credentials
- Mail settings if you plan to send emails

---

## 🔧 Step 5: Install Dependencies & Build Assets

### 5.1 SSH Commands (If SSH available)

```bash
# Navigate to project
cd ~/public_html

# Install Composer dependencies
composer install --optimize-autoloader --no-dev

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed database (if needed)
php artisan db:seed --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage bootstrap/cache
```

### 5.2 Build Frontend Assets (Local → Upload)

**On your local machine:**

```bash
# Build production assets
npm run build

# This creates files in public/build/
```

**Then upload:**
- Upload entire `public/build/` folder to server
- Upload `public/hot` if exists

---

## 🌐 Step 6: Configure Public Directory

Laravel's entry point is `public/index.php`, not root. You need to configure this:

### Option A: .htaccess in Root (Recommended)

Create `.htaccess` in `public_html`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### Option B: Move Public Contents (Alternative)

```bash
# Move public contents to root
mv public/* .
mv public/.htaccess .

# Update index.php paths
# Change: require __DIR__.'/../vendor/autoload.php';
# To: require __DIR__.'/vendor/autoload.php';

# Change: $app = require_once __DIR__.'/../bootstrap/app.php';
# To: $app = require_once __DIR__.'/bootstrap/app.php';
```

---

## 🔒 Step 7: Security Configuration

### 7.1 Protect Sensitive Files

Create/update `.htaccess` in root:

```apache
# Deny access to .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# Deny access to storage
RedirectMatch 404 /storage/
```

### 7.2 Set Proper Permissions

```bash
# Files: 644
find . -type f -exec chmod 644 {} \;

# Directories: 755
find . -type d -exec chmod 755 {} \;

# Storage and cache: 775
chmod -R 775 storage bootstrap/cache
```

---

## 🚀 Step 8: SSL Certificate (HTTPS)

1. Go to **hPanel → SSL**
2. Select **Free SSL** (Let's Encrypt)
3. Choose `qudratpro.com` and `www.qudratpro.com`
4. Click **Install**
5. Wait 10-15 minutes for activation

Update `.env`:
```env
APP_URL=https://qudratpro.com
```

---

## ✅ Step 9: Verification Checklist

Test your deployment:

- [ ] Visit `https://qudratpro.com` - Homepage loads
- [ ] Check `/login` - Login page works
- [ ] Test database connection - No errors
- [ ] Check `/register` - Registration works
- [ ] Test file uploads - Storage works
- [ ] Check browser console - No JS errors
- [ ] Test responsive design - Mobile works
- [ ] Check SSL - Green padlock shows

---

## 🐛 Common Issues & Solutions

### Issue 1: 500 Internal Server Error
**Solution:**
```bash
# Check Laravel logs
cat storage/logs/laravel.log

# Clear all cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Issue 2: Mix Manifest Not Found
**Solution:**
- Ensure `public/build/manifest.json` exists
- Run `npm run build` locally and upload

### Issue 3: Database Connection Failed
**Solution:**
- Verify database credentials in `.env`
- Check database exists in hPanel → MySQL Databases
- Test connection: `php artisan migrate:status`

### Issue 4: Storage Link Not Working
**Solution:**
```bash
# Remove old link
rm public/storage

# Recreate
php artisan storage:link
```

### Issue 5: Permission Denied
**Solution:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

## 📊 Performance Optimization

### Enable OPcache (if available)

In `php.ini` or `.user.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### Enable Gzip Compression

Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 🔄 Future Updates Workflow

### Using Git (Recommended)

```bash
# SSH to server
cd ~/public_html

# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev

# Run migrations
php artisan migrate --force

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Manual Upload

1. Build locally: `npm run build`
2. Upload changed files via FTP/File Manager
3. Run artisan commands via SSH or create a deploy script

---

## 📞 Support Resources

- **Hostinger Support:** https://www.hostinger.com/tutorials
- **Laravel Docs:** https://laravel.com/docs/11.x/deployment
- **Your Logs:** `storage/logs/laravel.log`
- **Server Logs:** Check hPanel → Error Logs

---

## 🎯 Quick Command Reference

```bash
# Clear all cache
php artisan optimize:clear

# Rebuild cache
php artisan optimize

# Check app status
php artisan about

# Run migrations
php artisan migrate --force

# Rollback migration
php artisan migrate:rollback

# Check routes
php artisan route:list

# Check database connection
php artisan migrate:status
```

---

**Deployment Status:** Ready to deploy! 🚀

Follow the steps in order, and your myclass2026 project will be live on qudratpro.com!
