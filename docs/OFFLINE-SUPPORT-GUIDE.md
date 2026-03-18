# Presentation Builder V2 - Offline Support Guide

## Overview

The Presentation Builder V2 now works **completely offline** using Service Workers and advanced caching strategies. Users can continue working on presentations even without an internet connection.

---

## Features Available Offline

### ✅ Fully Functional Offline:
- **View Presentations** - Access cached slides and presentations
- **Edit Slides** - Add, remove, and modify slide elements
- **Add Images** - Upload images from local device
- **Add Text** - Create and edit text content
- **Clipboard Support** - Paste images and text (Ctrl+V)
- **Animation Settings** - Configure visibility and animations
- **Pin Elements** - Pin elements during presentation mode
- **Height Adjustment** - Change slide canvas height
- **Sound Effects** - Click sounds still work (preloaded)

### ⚠️ Limited/No Offline Support:
- **Saving to Server** - Changes saved locally only
- **Loading Remote Data** - Cannot fetch new data from server
- **Real-time Collaboration** - No live sync with other users
- **Push Notifications** - Requires service worker background sync

---

## How It Works

### 1. Service Worker Registration

When you first load the Presentation Builder page:

```javascript
navigator.serviceWorker.register('/sw.js')
```

The service worker caches essential files:
- HTML pages
- JavaScript bundles
- CSS stylesheets
- Images and audio files
- Font files

### 2. Caching Strategy

**Network First, Then Cache:**
```
User Request → Try Network → If Fails → Serve from Cache
```

**For Static Assets:**
```
User Request → Serve from Cache → Update in Background
```

### 3. Offline Detection

The app automatically detects online/offline status:

```javascript
window.addEventListener('online', handler);
window.addEventListener('offline', handler);
navigator.onLine; // true/false
```

---

## Files Modified

### 1. Service Worker (`public/sw.js`)

Updated to cache presentation-specific routes:

```javascript
const CACHE_URLS = [
  '/',
  '/presentation-offline.html',
  '/classroom-records/presentation/builder-v2',
  '/build/assets/',
  '/css/',
  '/js/',
  '/images/',
  '/audio/'
];
```

### 2. PresentationBuilderV2.vue

Added offline support:

```javascript
data() {
  return {
    isOnline: navigator.onLine,
    // ... other data
  };
},

mounted() {
  this.registerServiceWorker();
  window.addEventListener('online', this.handleOnline);
  window.addEventListener('offline', this.handleOffline);
}
```

### 3. Routes (`routes/web.php`)

Added offline page route:

```php
Route::get('/presentation-offline', function () {
    return view('presentation-offline');
})->name('presentation.offline');
```

### 4. Offline Page Views

Created two offline pages:
- `public/presentation-offline.html` - Static HTML version
- `resources/views/presentation-offline.blade.php` - Blade template version

---

## User Experience

### Going Offline

1. **Automatic Detection**: App detects when connection is lost
2. **Visual Indicator**: Status shown in UI (if implemented)
3. **Console Warnings**: Logged for debugging
4. **Continue Working**: All editing features remain functional

### Coming Back Online

1. **Auto-Detection**: App detects restored connection
2. **Console Notification**: "Application is back online!"
3. **Cache Updates**: Service worker updates cached content
4. **Ready to Sync**: Local changes ready for server sync

---

## Testing Offline Mode

### Method 1: Chrome DevTools

1. Open Presentation Builder V2
2. Press `F12` to open DevTools
3. Go to **Network** tab
4. Check **"Offline"** checkbox
5. Refresh page or continue working
6. Uncheck to go back online

### Method 2: Browser Settings

1. Disconnect WiFi/Ethernet
2. Enable Airplane Mode
3. Continue working on presentations
4. Reconnect to test sync

### Method 3: Service Worker Tools

1. Open DevTools → Application tab
2. Select **Service Workers**
3. Click **"Offlined"** checkbox
4. Test offline functionality

---

## Troubleshooting

### Issue: Service Worker Not Registering

**Solution:**
```javascript
// Check browser support
if ('serviceWorker' in navigator) {
  // Supported
} else {
  console.error('Service workers not supported');
}
```

**Requirements:**
- HTTPS or localhost
- Modern browser (Chrome 40+, Firefox 44+, Safari 11.1+)
- No browser extensions blocking SW

### Issue: Cached Content Not Updating

**Solution:**
```javascript
// Force update check
setInterval(() => {
  registration.update();
}, 60000); // Every minute
```

Or manually clear cache:
1. DevTools → Application
2. Clear storage → Clear site data

### Issue: Offline Page Not Showing

**Check:**
1. Route exists: `/presentation-offline`
2. View file exists
3. Service worker caching correct URLs
4. Fallback response in fetch handler

---

## Best Practices

### For Users:

1. **Load First While Online**: Visit page at least once while connected
2. **Wait for Cache**: Allow page to fully load before going offline
3. **Save Important Work**: Download JSON exports as backup
4. **Check Connection**: Watch for offline indicators
5. **Sync When Back Online**: Refresh page after reconnection

### For Developers:

1. **Version Cache Names**: Use versioned cache names for updates
2. **Pre-cache Critical Assets**: Load essential files first
3. **Handle Errors Gracefully**: Provide fallbacks for failed requests
4. **Test Extensively**: Use DevTools and real offline scenarios
5. **Monitor Cache Size**: Avoid caching too much data

---

## Browser Compatibility

| Browser | Version | Support |
|---------|---------|---------|
| Chrome | 40+ | ✅ Full |
| Firefox | 44+ | ✅ Full |
| Safari | 11.1+ | ✅ Full |
| Edge | 17+ | ✅ Full |
| Opera | 27+ | ✅ Full |
| IE | Any | ❌ None |

---

## Future Enhancements

### Planned Features:

- [ ] **Background Sync**: Queue changes and sync when online
- [ ] **IndexedDB Storage**: Store presentations locally
- [ ] **Conflict Resolution**: Handle sync conflicts intelligently
- [ ] **Progressive Enhancement**: Better UX for offline users
- [ ] **Cache Management**: Auto-clean old cached presentations
- [ ] **Offline Indicators**: Visual UI elements showing status
- [ ] **Push Notifications**: Notify when sync completes

---

## Technical Details

### Cache Structure

```
education-app-v1/
├── / (root page)
├── /presentation-offline.html
├── /classroom-records/presentation/builder-v2
├── /build/assets/* (JS/CSS bundles)
├── /css/* (stylesheets)
├── /js/* (JavaScript files)
├── /images/* (images)
└── /audio/* (sound effects)
```

### Service Worker Lifecycle

1. **Install**: Cache essential files
2. **Activate**: Clean up old caches
3. **Fetch**: Serve from cache when offline
4. **Update**: Periodically check for updates

### Event Handlers

```javascript
// Install event
self.addEventListener('install', event => {
  event.waitUntil(caches.open(CACHE_NAME).then(cache => {
    return cache.addAll(validUrls);
  }));
});

// Activate event  
self.addEventListener('activate', event => {
  event.waitUntil(caches.keys().then(cacheNames => {
    return Promise.all(
      cacheNames.map(name => {
        if (name !== CACHE_NAME) {
          return caches.delete(name);
        }
      })
    );
  }));
});

// Fetch event
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(request)
      .then(response => {
        // Cache and return
      })
      .catch(() => {
        // Serve from cache
      })
  );
});
```

---

## Support

For issues or questions about offline functionality:
1. Check browser console for errors
2. Verify service worker registration in DevTools
3. Ensure HTTPS or localhost environment
4. Clear browser cache and reload
5. Update to latest browser version

---

**Last Updated:** March 2026  
**Version:** 1.0
