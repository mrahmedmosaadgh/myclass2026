# Schedule App V3 Routes

## Overview

This directory contains the dedicated routes file for Schedule App V3, providing a clean separation of the advanced schedule application routes from the main application.

## File Structure

```
routes/
├── web.php                    # Main routes file (includes V3 routes)
├── schedule_app_v3.php        # V3-specific routes (this file)
└── README_SCHEDULE_V3.md      # This documentation
```

## Inclusion

The V3 routes are included in the main `web.php` file at line 425:

```php
// Include Schedule App V3 Routes
include dirname(__DIR__).'/routes/schedule_app_v3.php';
```

## Routes Defined

### Main Application Routes
- `GET /my-fly-schedule-app/v3` - Main V3 application
- `GET /my-fly-schedule-app/v3/test` - Development test interface

### PWA Resources
- `GET /my-fly-schedule-app/v3/manifest.webmanifest` - PWA manifest
- `GET /my-fly-schedule-app/v3/icon.svg` - Application icon
- `GET /my-fly-schedule-app/v3/sw.js` - Service worker
- `GET /my-fly-schedule-app-v3-sw.js` - Service worker alias

### API Endpoints
- `GET /api/v3/timing-data` - Default timing configuration
- `GET /api/v3/health` - Application health check

## Route Features

### Authentication
All V3 routes are configured as **public** (no authentication required) using:

```php
->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])
```

### Middleware
- PWA resource routes exclude `HandleInertiaRequests` middleware
- API routes return JSON responses
- Main application routes use Inertia rendering

### Response Types
- **HTML**: Main app and test page (Inertia responses)
- **JSON**: API endpoints
- **SVG**: Application icon
- **Web Manifest**: PWA manifest
- **JavaScript**: Service worker

## Development

### Adding New Routes
To add new V3-specific routes, simply add them to `schedule_app_v3.php` following the existing patterns:

```php
// New application route
Route::get('/my-fly-schedule-app/v3/new-feature', function () {
    return Inertia::render('MicroComponentTest/mytable/MyTableSchedule/v3/NewFeature');
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']);

// New API route
Route::get('/api/v3/new-endpoint', function () {
    return response()->json(['data' => 'value']);
})->withoutMiddleware(['auth:sanctum', config('jetstream.auth_session'), 'verified']);
```

### Testing Routes
Use the test interface to verify all routes are working:
- https://qudratpro.com/my-fly-schedule-app/v3/test

### Cache Busting
For development, add `?refresh=true` to bypass browser cache:
- https://qudratpro.com/my-fly-schedule-app/v3?refresh=true

## Security Considerations

- All routes are public (no authentication)
- PWA scope is limited to `/my-fly-schedule-app/v3/`
- Service worker has proper scope header
- No sensitive data exposure in API endpoints
- Input validation should be added to any future API endpoints

## Maintenance

### Regular Tasks
1. **Route Testing**: Verify all routes after deployment
2. **API Health Check**: Monitor `/api/v3/health` endpoint
3. **PWA Validation**: Test manifest and service worker
4. **Documentation**: Keep this README updated

### Troubleshooting
- **404 Errors**: Check if routes file is properly included
- **PWA Issues**: Verify service worker scope and manifest URLs
- **API Problems**: Check middleware exclusions and response formats

## Version History

- **v3.0** (April 2, 2026): Initial separate routes file creation
  - Moved all V3 routes from `web.php`
  - Added API endpoints structure
  - Included comprehensive documentation
  - Added test interface route

## Related Files

- `routes/web.php` - Main application routes (includes this file)
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v3/` - V3 components
- `public/my-schedule-app/v3/` - PWA assets (service worker, manifest)
- `resources/js/Pages/MicroComponentTest/mytable/MyTableSchedule/v3/SCHEDULE_APP_V3_ROUTES.md` - Detailed route documentation
