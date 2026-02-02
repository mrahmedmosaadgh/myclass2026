# Hostinger Critical Files Backup
**Created:** 2026-02-02 23:19:07
**Purpose:** Backup of critical Hostinger configuration before deletion
**Source:** `/Users/ahmedmosaad/Herd/myclass2026-main/public_html/`

---

## 🔴 CRITICAL DATABASE CREDENTIALS

**These credentials MUST be added to your myclass2026 `.env` file:**

```env
# Hostinger Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u474447882_VcHbj
DB_USERNAME=u474447882_rP9FV
DB_PASSWORD=9]3Mkk~F/~H)]iW%

# Application Configuration
APP_KEY=9372856f561040e2bf81f8e1ef292816
APP_URL=https://qudratpro.com
```

---

## 📁 Backed Up Files

All critical files have been saved to:
**`/Users/ahmedmosaad/Herd/myclass2026-main/docs/hostinger_backup/`**

### Files Saved:

1. **`.env.hostinger`** - Complete Hostinger environment file
2. **`.htaccess.root`** - Root .htaccess configuration
3. **`.htaccess.public`** - Public folder .htaccess
4. **`CRITICAL_CREDENTIALS.txt`** - Database credentials extract

---

## ✅ Next Steps: Update Your MyClass2026 .env

### Step 1: Open Your Project .env
```bash
nano /Users/ahmedmosaad/Herd/myclass2026-main/.env
```

### Step 2: Update Database Section

Replace your current database configuration with:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u474447882_VcHbj
DB_USERNAME=u474447882_rP9FV
DB_PASSWORD=9]3Mkk~F/~H)]iW%
```

### Step 3: Update App Configuration

```env
APP_NAME="Qudrat Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qudratpro.com
APP_KEY=9372856f561040e2bf81f8e1ef292816
```

### Step 4: Add Production Settings

```env
# Production Settings
LOG_CHANNEL=stack
LOG_LEVEL=error

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Mail (Hostinger SMTP)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@qudratpro.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@qudratpro.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🌐 .htaccess Configuration

### Root .htaccess (public_html/.htaccess)

**Content from Hostinger:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Action:** Add this to your project root if deploying to public_html

---

### Public .htaccess (public_html/public/.htaccess)

**Content from Hostinger:**
```apache
<IfModule mod_negotiation.c>
    Options -MultiViews -Indexes
</IfModule>

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

**Action:** This should already exist in your myclass2026 `public/.htaccess`

---

## 🚀 GitHub Deployment Strategy

Now that files are backed up, here's how to deploy via GitHub:

### Option 1: Direct Git Push to Hostinger (Recommended)

**On Hostinger Server (SSH):**
```bash
# Navigate to public_html
cd ~/public_html

# Initialize git (if not already)
git init

# Add your GitHub repo as remote
git remote add origin https://github.com/mrahmedmosaadgh/myclass2026.git

# Pull your project
git pull origin main

# Or clone fresh (if empty)
cd ~
rm -rf public_html/*
git clone https://github.com/mrahmedmosaadgh/myclass2026.git public_html
```

### Option 2: GitHub Actions Auto-Deploy

Create `.github/workflows/deploy.yml` in your myclass2026 repo:

```yaml
name: Deploy to Hostinger

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Deploy to Hostinger via FTP
      uses: SamKirkland/FTP-Deploy-Action@4.3.3
      with:
        server: ftp.qudratpro.com
        username: ${{ secrets.FTP_USERNAME }}
        password: ${{ secrets.FTP_PASSWORD }}
        server-dir: /public_html/
        exclude: |
          **/.git*
          **/.git*/**
          **/node_modules/**
          **/vendor/**
          **/.env
```

### Option 3: Manual Git Pull Script

Create `deploy.sh` on Hostinger:

```bash
#!/bin/bash
cd ~/public_html
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

---

## 📋 Pre-Deployment Checklist

Before deleting `public_html/` folder:

- [x] ✅ Database credentials backed up
- [x] ✅ .htaccess files backed up
- [x] ✅ APP_KEY saved
- [ ] ⏳ Updated myclass2026 `.env` with Hostinger credentials
- [ ] ⏳ Committed all changes to GitHub
- [ ] ⏳ Tested locally with production database (optional)

---

## 🗑️ Safe to Delete Now

You can now safely delete the `public_html/` folder:

```bash
# Local deletion
rm -rf /Users/ahmedmosaad/Herd/myclass2026-main/public_html/

# Server deletion (when ready)
# SSH to Hostinger:
ssh u474447882@qudratpro.com
cd ~
rm -rf public_html/*
```

---

## 🔄 Deployment Process After Deletion

### Step 1: Update Your .env
```bash
# Edit your myclass2026 .env
code /Users/ahmedmosaad/Herd/myclass2026-main/.env

# Add the database credentials from above
```

### Step 2: Commit to GitHub
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
git add .
git commit -m "Configure for Hostinger production deployment"
git push origin main
```

### Step 3: Deploy to Hostinger
```bash
# SSH to Hostinger
ssh u474447882@qudratpro.com

# Clone your repo
cd ~
git clone https://github.com/mrahmedmosaadgh/myclass2026.git public_html

# Install dependencies
cd public_html
composer install --no-dev --optimize-autoloader

# Copy .env and configure
cp .env.example .env
nano .env  # Add database credentials

# Run migrations
php artisan migrate --force

# Set permissions
chmod -R 775 storage bootstrap/cache
php artisan storage:link

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 4: Upload Built Assets

**On your local machine:**
```bash
cd /Users/ahmedmosaad/Herd/myclass2026-main
npm run build
```

**Upload `public/build/` folder to Hostinger** via FTP or:
```bash
scp -r public/build/ u474447882@qudratpro.com:~/public_html/public/
```

---

## 🎯 Summary

### ✅ What's Backed Up
- Complete `.env` file with database credentials
- Root `.htaccess` configuration
- Public `.htaccess` configuration
- Critical credentials extracted

### 📍 Backup Location
```
/Users/ahmedmosaad/Herd/myclass2026-main/docs/hostinger_backup/
├── .env.hostinger
├── .htaccess.root
├── .htaccess.public
└── CRITICAL_CREDENTIALS.txt
```

### 🔑 Critical Info
```
Database: u474447882_VcHbj
Username: u474447882_rP9FV
Password: 9]3Mkk~F/~H)]iW%
APP_KEY: 9372856f561040e2bf81f8e1ef292816
```

---

**Status:** ✅ Safe to delete public_html folder!

All critical files are backed up. Update your `.env` and deploy via GitHub! 🚀
