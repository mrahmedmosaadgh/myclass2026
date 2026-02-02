# دليل النشر على Hostinger - qudratpro.com
**تاريخ الإنشاء:** 2026-02-02 21:46:39
**النطاق:** qudratpro.com
**المشروع:** myclass2026 (Laravel + Vue + Inertia)

---

## 📋 قائمة التحقق قبل النشر

- [ ] خطة استضافة Hostinger نشطة
- [ ] النطاق qudratpro.com تم تكوينه
- [ ] بيانات اعتماد قاعدة البيانات جاهزة
- [ ] مستودع Git يمكن الوصول إليه
- [ ] المشروع المحلي تم اختباره ويعمل

---

## 🎯 الخطوة 1: تثبيت Laravel الأولي (لوحة Hostinger)

بناءً على لقطة الشاشة، أنت في شاشة تثبيت Laravel. إليك ما يجب ملؤه:

### بيانات اعتماد الموقع
```
عنوان الموقع: Qudrat Pro (أو الاسم المفضل لديك)
البريد الإلكتروني للمسؤول: me72025me2@gmail.com
اسم المستخدم للمسؤول: me72025me2
كلمة مرور المسؤول: [أنشئ كلمة مرور قوية]
```

### الإعدادات المتقدمة
```
مسار التثبيت: qudratpro.com/
الدليل الفرعي: [اتركه فارغًا للتثبيت في الجذر]
قاعدة البيانات: إنشاء قاعدة بيانات جديدة (موصى به) ✓
كلمة مرور قاعدة البيانات: [أنشئ كلمة مرور قوية - احفظها!]
```

**مهم:** احفظ كلمة مرور قاعدة البيانات بشكل آمن - ستحتاجها لتكوين `.env`.

---

## 🗄️ الخطوة 2: إعداد قاعدة البيانات

بعد التثبيت، سينشئ Hostinger:
- اسم قاعدة البيانات: عادة `u123456789_qudratpro` (تحقق في hPanel)
- مستخدم قاعدة البيانات: نفس اسم قاعدة البيانات
- كلمة مرور قاعدة البيانات: ما قمت بتعيينه أعلاه
- مضيف قاعدة البيانات: `localhost`

---

## 📁 الخطوة 3: رفع ملفات المشروع

### الخيار أ: استخدام Git (موصى به)

1. **الوصول إلى SSH** (إذا كان متاحًا في خطتك):
```bash
ssh u123456789@qudratpro.com
```

2. **الانتقال إلى public_html**:
```bash
cd public_html
```

3. **استنساخ المستودع الخاص بك**:
```bash
git clone https://github.com/mrahmedmosaadgh/myclass2026.git temp
mv temp/* .
mv temp/.* .
rm -rf temp
```

### الخيار ب: استخدام مدير الملفات

1. انتقل إلى **hPanel → مدير الملفات**
2. انتقل إلى `public_html`
3. ارفع مشروعك كملف ZIP
4. استخرج ملف ZIP
5. انقل جميع الملفات من المجلد الفرعي إلى جذر `public_html`

---

## ⚙️ الخطوة 4: تكوين البيئة

### 4.1 إنشاء ملف .env

SSH أو مدير الملفات → أنشئ ملف `.env`:

```bash
cp .env.example .env
```

### 4.2 تحرير تكوين .env

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
DB_PASSWORD=كلمة_مرور_قاعدة_البيانات_هنا

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

# إذا كنت تستخدم البريد
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your_email@qudratpro.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@qudratpro.com
MAIL_FROM_NAME="${APP_NAME}"
```

**استبدل:**
- `DB_DATABASE`، `DB_USERNAME`، `DB_PASSWORD` ببيانات قاعدة البيانات الفعلية
- إعدادات البريد إذا كنت تخطط لإرسال رسائل بريد إلكتروني

---

## 🔧 الخطوة 5: تثبيت التبعيات وبناء الأصول

### 5.1 أوامر SSH (إذا كان SSH متاحًا)

```bash
# الانتقال إلى المشروع
cd ~/public_html

# تثبيت تبعيات Composer
composer install --optimize-autoloader --no-dev

# إنشاء مفتاح التطبيق
php artisan key:generate

# تشغيل الترحيلات
php artisan migrate --force

# ملء قاعدة البيانات (إذا لزم الأمر)
php artisan db:seed --force

# مسح وتخزين التكوين مؤقتًا
php artisan config:cache
php artisan route:cache
php artisan view:cache

# إنشاء رابط التخزين
php artisan storage:link

# تعيين الأذونات
chmod -R 755 storage bootstrap/cache
```

### 5.2 بناء أصول الواجهة الأمامية (محليًا → رفع)

**على جهازك المحلي:**

```bash
# بناء أصول الإنتاج
npm run build

# هذا ينشئ ملفات في public/build/
```

**ثم ارفع:**
- ارفع مجلد `public/build/` بالكامل إلى الخادم
- ارفع `public/hot` إذا كان موجودًا

---

## 🌐 الخطوة 6: تكوين الدليل العام

نقطة دخول Laravel هي `public/index.php`، وليس الجذر. تحتاج إلى تكوين هذا:

### الخيار أ: .htaccess في الجذر (موصى به)

أنشئ `.htaccess` في `public_html`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### الخيار ب: نقل محتويات Public (بديل)

```bash
# نقل محتويات public إلى الجذر
mv public/* .
mv public/.htaccess .

# تحديث مسارات index.php
# غيّر: require __DIR__.'/../vendor/autoload.php';
# إلى: require __DIR__.'/vendor/autoload.php';

# غيّر: $app = require_once __DIR__.'/../bootstrap/app.php';
# إلى: $app = require_once __DIR__.'/bootstrap/app.php';
```

---

## 🔒 الخطوة 7: تكوين الأمان

### 7.1 حماية الملفات الحساسة

أنشئ/حدّث `.htaccess` في الجذر:

```apache
# منع الوصول إلى .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# منع الوصول إلى storage
RedirectMatch 404 /storage/
```

### 7.2 تعيين الأذونات المناسبة

```bash
# الملفات: 644
find . -type f -exec chmod 644 {} \;

# الدلائل: 755
find . -type d -exec chmod 755 {} \;

# التخزين والذاكرة المؤقتة: 775
chmod -R 775 storage bootstrap/cache
```

---

## 🚀 الخطوة 8: شهادة SSL (HTTPS)

1. انتقل إلى **hPanel → SSL**
2. اختر **SSL مجاني** (Let's Encrypt)
3. اختر `qudratpro.com` و `www.qudratpro.com`
4. انقر على **تثبيت**
5. انتظر 10-15 دقيقة للتفعيل

حدّث `.env`:
```env
APP_URL=https://qudratpro.com
```

---

## ✅ الخطوة 9: قائمة التحقق من التحقق

اختبر نشرك:

- [ ] زيارة `https://qudratpro.com` - الصفحة الرئيسية تُحمّل
- [ ] تحقق من `/login` - صفحة تسجيل الدخول تعمل
- [ ] اختبار اتصال قاعدة البيانات - لا توجد أخطاء
- [ ] تحقق من `/register` - التسجيل يعمل
- [ ] اختبار رفع الملفات - التخزين يعمل
- [ ] تحقق من وحدة تحكم المتصفح - لا توجد أخطاء JS
- [ ] اختبار التصميم المتجاوب - الجوال يعمل
- [ ] تحقق من SSL - القفل الأخضر يظهر

---

## 🐛 المشاكل الشائعة والحلول

### المشكلة 1: خطأ 500 خطأ داخلي في الخادم
**الحل:**
```bash
# تحقق من سجلات Laravel
cat storage/logs/laravel.log

# مسح جميع الذاكرة المؤقتة
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### المشكلة 2: Mix Manifest غير موجود
**الحل:**
- تأكد من وجود `public/build/manifest.json`
- شغّل `npm run build` محليًا وارفع

### المشكلة 3: فشل اتصال قاعدة البيانات
**الحل:**
- تحقق من بيانات اعتماد قاعدة البيانات في `.env`
- تحقق من وجود قاعدة البيانات في hPanel → قواعد بيانات MySQL
- اختبار الاتصال: `php artisan migrate:status`

### المشكلة 4: رابط التخزين لا يعمل
**الحل:**
```bash
# إزالة الرابط القديم
rm public/storage

# إعادة الإنشاء
php artisan storage:link
```

### المشكلة 5: تم رفض الإذن
**الحل:**
```bash
chmod -R 775 storage bootstrap/cache
chown -R username:username storage bootstrap/cache
```

---

## 📊 تحسين الأداء

### تمكين OPcache (إذا كان متاحًا)

في `php.ini` أو `.user.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
```

### تمكين ضغط Gzip

أضف إلى `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

---

## 🔄 سير عمل التحديثات المستقبلية

### استخدام Git (موصى به)

```bash
# SSH إلى الخادم
cd ~/public_html

# سحب أحدث التغييرات
git pull origin main

# تحديث التبعيات
composer install --no-dev

# تشغيل الترحيلات
php artisan migrate --force

# مسح الذاكرة المؤقتة
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### الرفع اليدوي

1. بناء محليًا: `npm run build`
2. رفع الملفات المتغيرة عبر FTP/مدير الملفات
3. تشغيل أوامر artisan عبر SSH أو إنشاء سكريبت نشر

---

## 📞 موارد الدعم

- **دعم Hostinger:** https://www.hostinger.com/tutorials
- **وثائق Laravel:** https://laravel.com/docs/11.x/deployment
- **سجلاتك:** `storage/logs/laravel.log`
- **سجلات الخادم:** تحقق من hPanel → سجلات الأخطاء

---

## 🎯 مرجع الأوامر السريع

```bash
# مسح جميع الذاكرة المؤقتة
php artisan optimize:clear

# إعادة بناء الذاكرة المؤقتة
php artisan optimize

# تحقق من حالة التطبيق
php artisan about

# تشغيل الترحيلات
php artisan migrate --force

# التراجع عن الترحيل
php artisan migrate:rollback

# تحقق من المسارات
php artisan route:list

# تحقق من اتصال قاعدة البيانات
php artisan migrate:status
```

---

**حالة النشر:** جاهز للنشر! 🚀

اتبع الخطوات بالترتيب، وسيكون مشروع myclass2026 الخاص بك مباشرًا على qudratpro.com!
