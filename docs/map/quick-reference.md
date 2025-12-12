# MyClass8 Quick Reference Guide

## 🚀 Quick Start Commands

### Development Setup
```bash
# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed

# Start development servers
npm run dev
php artisan serve
```

### Common Commands
```bash
# Clear caches
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:clear

# Run tests
php artisan test
npm run test

# Build for production
npm run build
```

## 📁 Quick Navigation

### Documentation
| File | Purpose | Link |
|------|---------|------|
| `docs/map/README.md` | Main project map | [Open](./README.md) |
| `docs/map/course-management.md` | Course system docs | [Open](./course-management.md) |
| `docs/before_7_2025/documentation-portal.md` | Legacy docs | [Open](../before_7_2025/documentation-portal.md) |

### Key Files
| Path | Description | Status |
|------|-------------|---------|
| `routes/course_management.php` | Course routes | ✅ Active |
| `app/Http/Controllers/CourseManagement/` | Course controllers | ✅ Active |
| `resources/js/Pages/CourseManagement/` | Course pages | ✅ Active |
| `database/migrations/` | Database schema | ✅ Active |

## 🔧 Configuration Quick Reference

### Environment Variables
```bash
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=myclass8
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Queue
QUEUE_CONNECTION=redis
```

### Package.json Scripts
```json
{
  "dev": "vite",
  "build": "vite build",
  "test": "vitest",
  "lint": "eslint resources/js --ext .vue,.js,.jsx,.ts,.tsx"
}
```

## 🎯 Feature Quick Access

### Course Management
- **Create Course**: `/course-management/courses/create`
- **List Courses**: `/course-management/courses`
- **Import Courses**: `/course-management/import`
- **Manage Levels**: `/course-management/levels`

### User Management
- **User Context**: See `docs/before_7_2025/api/user-context-endpoints.md`
- **Authentication**: Laravel Fortify
- **Authorization**: Laravel Gates & Policies

### Offline System
- **Service Worker**: `resources/js/sw.js`
- **Cache Strategy**: Cache-first with network fallback
- **Sync Queue**: IndexedDB based

## 📊 Database Quick Reference

### Key Tables
| Table | Purpose | Related Files |
|-------|---------|---------------|
| `courses` | Course data | CourseManagement system |
| `users` | User accounts | Laravel default |
| `course_teacher` | Teacher assignments | Migration: `2025_07_17_000001_create_course_teacher_table.php` |
| `levels` | Course hierarchy | CourseManagement system |
| `lessons` | Lesson content | CourseManagement system |

### Common Queries
```sql
-- Get all courses with teachers
SELECT c.*, t.name as teacher_name 
FROM courses c
JOIN course_teacher ct ON c.id = ct.course_id
JOIN users t ON ct.teacher_id = t.id;

-- Get course hierarchy
SELECT c.title as course, l.title as level, s.title as section
FROM courses c
JOIN levels l ON c.id = l.course_id
JOIN sections s ON l.id = s.level_id
ORDER BY c.id, l.order, s.order;
```

## 🚨 Common Issues & Solutions

### Issue: Course import fails
**Solution**: Check Excel format and required columns
**File**: `app/Http/Controllers/CourseManagement/CourseImportController.php`

### Issue: Service worker not updating
**Solution**: Clear browser cache and unregister service worker
**Command**: `php artisan cache:clear && npm run build`

### Issue: Database connection error
**Solution**: Check `.env` database configuration
**Test**: `php artisan tinker` → `DB::connection()->getPdo()`

## 🔍 Debugging Quick Tips

### Laravel Debug
```php
// Quick debug
dd($variable);
ray($variable); // If using Ray

// Log debugging
Log::info('Debug info', ['data' => $variable]);
```

### Vue Debug
```javascript
// Console debugging
console.log('Debug:', variable);

// Vue devtools
// Install Vue.js devtools browser extension

// Pinia store debugging
import { devtools } from 'pinia'
```

### Database Debug
```php
// Query debugging
DB::enableQueryLog();
// Run queries
dd(DB::getQueryLog());

// Eloquent debugging
Course::with('levels.sections')->toSql();
```

## 📱 Browser Dev Tools

### Application Tab
- **Local Storage**: Check offline data
- **Session Storage**: Temporary data
- **Cookies**: Authentication tokens
- **Service Workers**: Offline functionality

### Network Tab
- **API Calls**: Monitor requests
- **Response Times**: Performance check
- **Caching**: Verify cache headers
- **Offline Mode**: Test service worker

## 🔄 Deployment Checklist

### Pre-deployment
- [ ] All tests passing
- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] Assets compiled
- [ ] Cache cleared

### Post-deployment
- [ ] Health check endpoint
- [ ] Error monitoring setup
- [ ] Performance monitoring
- [ ] Backup verification
- [ ] SSL certificate check

## 📞 Emergency Contacts

### Documentation
- **Main Help**: `docs/map/README.md`
- **Course Issues**: `docs/map/course-management.md`
- **Offline Issues**: `docs/map/offline-system.md`

### Code References
- **Routes**: Check `routes/course_management.php`
- **Controllers**: Check `app/Http/Controllers/CourseManagement/`
- **Models**: Check `app/Models/CourseManagement/`
- **Views**: Check `resources/js/Pages/CourseManagement/`

---
*Keep this guide handy for quick reference during development*