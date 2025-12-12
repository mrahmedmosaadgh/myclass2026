# 🛠️ Tools Switcher Documentation

## Overview

The **Tools Switcher** is a localStorage-based system that allows users to enable/disable various system features for better performance and debugging. It helps make the application lighter by turning off unused background services and Firebase features.

## 📁 File Structure

```
resources/js/
├── utils/
│   └── toolsSwitcher.js          # Core ToolsSwitcher class
├── Components/
│   └── ToolsSwitcherPanel.vue    # GUI control panel
├── firebase/
│   └── init.js                   # Firebase initialization with switcher
└── Components/Chat/
    └── NotificationListener.vue  # Notifications with switcher
```

## 🎯 Features Controlled

### 1. Firebase Services
- **Firebase Core** - Complete Firebase initialization
- **Authentication** - Firebase Auth service
- **Database** - Realtime Database connection
- **Emulators** - Development emulator connections
- **Notifications** - Firebase-based notifications

### 2. Background Services
- **Notifications** - Background notification processing
- **Sync** - Background data synchronization
- **Realtime** - Real-time updates and listeners

## 🔧 Implementation Details

### Core Class: `ToolsSwitcher`

**Location:** `resources/js/utils/toolsSwitcher.js`

```javascript
import { ToolsSwitcher } from '@/utils/toolsSwitcher';

// Check if feature is enabled
ToolsSwitcher.isFirebaseEnabled()
ToolsSwitcher.isNotificationsEnabled()
ToolsSwitcher.isEnabled('firebase', 'auth')

// Toggle features
ToolsSwitcher.toggle('firebase')
ToolsSwitcher.toggle('firebase', 'notifications')

// Get/Save config
const config = ToolsSwitcher.getConfig()
ToolsSwitcher.saveConfig(config)
```

### Default Configuration

```javascript
const DEFAULT_CONFIG = {
  firebase: {
    enabled: true,
    auth: true,
    database: true,
    emulators: true,
    notifications: true
  },
  backgroundServices: {
    notifications: true,
    sync: true,
    realtime: true
  },
  lastUpdated: new Date().toISOString()
};
```

## 🎮 Usage Examples

### 1. Console Commands (Developer)

```javascript
// Global access via window.toolsSwitcher
toolsSwitcher.toggle('firebase')                    // Disable Firebase
toolsSwitcher.toggle('firebase', 'notifications')   // Disable Firebase notifications
toolsSwitcher.toggle('backgroundServices', 'sync')  // Disable background sync

// Check status
toolsSwitcher.isFirebaseEnabled()        // true/false
toolsSwitcher.isNotificationsEnabled()   // true/false

// Get full config
toolsSwitcher.getConfig()
```

### 2. In Components

```vue
<script setup>
import { ToolsSwitcher } from '@/utils/toolsSwitcher';

// Check before initializing expensive services
if (ToolsSwitcher.isFirebaseEnabled()) {
  // Initialize Firebase
}

if (ToolsSwitcher.isNotificationsEnabled()) {
  // Set up notification listeners
}
</script>
```

### 3. Firebase Initialization

**File:** `resources/js/firebase/init.js`

```javascript
// Firebase only initializes if enabled
if (!ToolsSwitcher.isFirebaseEnabled()) {
  console.log('🚫 Firebase disabled by toolsSwitcher');
  export const app = null;
  export const auth = null;
  export const database = null;
} else {
  // Normal Firebase initialization
}
```

### 4. Notification Listeners

**File:** `resources/js/Components/Chat/NotificationListener.vue`

```javascript
onMounted(() => {
  if (!ToolsSwitcher.isNotificationsEnabled()) {
    console.log('🚫 Notifications disabled by toolsSwitcher');
    return;
  }
  // Set up notification listeners
});
```

## 🎨 GUI Control Panel

### Component: `ToolsSwitcherPanel.vue`

**Location:** `resources/js/Components/ToolsSwitcherPanel.vue`

**Features:**
- ✅ Toggle Firebase services
- ✅ Control background services
- ✅ Reset to defaults
- ✅ Apply changes with page reload
- ✅ Visual feedback with notifications

**Usage in Layout:**

```vue
<template>
  <div>
    <!-- Add to admin area or sidebar -->
    <ToolsSwitcherPanel />
  </div>
</template>

<script setup>
import ToolsSwitcherPanel from '@/Components/ToolsSwitcherPanel.vue';
</script>
```

**Already integrated in:** `resources/js/Layouts/comp/SidebarMenu.vue`

## 🚀 Performance Benefits

### When Firebase is Disabled:
- ⚡ **Faster page load** - No Firebase SDK loading
- 🔋 **Less memory usage** - No Firebase services running
- 🌐 **No network calls** - No emulator connections
- 🛠️ **Easier debugging** - Cleaner console output

### When Notifications are Disabled:
- 🔇 **No background processing** - Notification listeners stopped
- 📱 **Less browser permissions** - No notification API calls
- 🔋 **Better battery life** - Reduced background activity

### When Background Services are Disabled:
- 🔄 **No auto-sync** - Manual data refresh only
- 📡 **No realtime updates** - Static data display
- 🎯 **Focused testing** - Test specific features only

## 🐛 Debugging Scenarios

### Scenario 1: Firebase Connection Issues
```javascript
// Disable Firebase to test without it
toolsSwitcher.toggle('firebase')
location.reload()
```

### Scenario 2: Notification Problems
```javascript
// Disable notifications to isolate issue
toolsSwitcher.toggle('firebase', 'notifications')
toolsSwitcher.toggle('backgroundServices', 'notifications')
```

### Scenario 3: Performance Testing
```javascript
// Disable all background services
toolsSwitcher.toggle('backgroundServices', 'sync')
toolsSwitcher.toggle('backgroundServices', 'realtime')
toolsSwitcher.toggle('backgroundServices', 'notifications')
```

## 📊 Storage Structure

**localStorage key:** `toolsSwitcher`

```json
{
  "firebase": {
    "enabled": true,
    "auth": true,
    "database": true,
    "emulators": true,
    "notifications": true
  },
  "backgroundServices": {
    "notifications": true,
    "sync": true,
    "realtime": true
  },
  "lastUpdated": "2025-01-17T10:30:00.000Z"
}
```

## 🔄 Integration Checklist

### ✅ Completed Integrations:
- [x] Firebase initialization (`init.js`)
- [x] Notification listeners (`NotificationListener.vue`)
- [x] GUI control panel (`ToolsSwitcherPanel.vue`)
- [x] Sidebar menu integration (`SidebarMenu.vue`)

### 🔄 Potential Future Integrations:
- [ ] Laravel Echo connections
- [ ] Service Worker registration
- [ ] PWA features
- [ ] Background sync workers
- [ ] WebSocket connections
- [ ] Auto-save features

## 🎯 Best Practices

### 1. Always Check Before Initializing
```javascript
// ❌ Bad
initializeExpensiveService();

// ✅ Good
if (ToolsSwitcher.isEnabled('category', 'feature')) {
  initializeExpensiveService();
}
```

### 2. Provide Fallbacks
```javascript
// ✅ Good
if (!ToolsSwitcher.isFirebaseEnabled()) {
  // Use localStorage or alternative
  return localStorageBackup;
}
```

### 3. Log Status Changes
```javascript
// ✅ Good
if (ToolsSwitcher.isFirebaseEnabled()) {
  console.log('🔥 Firebase enabled');
} else {
  console.log('🚫 Firebase disabled by toolsSwitcher');
}
```

## 🆘 Troubleshooting

### Issue: Changes Not Applied
**Solution:** Reload the page after changing settings
```javascript
toolsSwitcher.toggle('firebase');
location.reload(); // Required for some changes
```

### Issue: localStorage Corrupted
**Solution:** Reset to defaults
```javascript
localStorage.removeItem('toolsSwitcher');
location.reload();
```

### Issue: Feature Still Running
**Solution:** Check all integration points
- Verify the feature checks `ToolsSwitcher.isEnabled()`
- Ensure proper import of the class
- Check console for error messages

## 📝 Adding New Features

### Step 1: Update Default Config
```javascript
const DEFAULT_CONFIG = {
  // ... existing config
  newCategory: {
    enabled: true,
    feature1: true,
    feature2: false
  }
};
```

### Step 2: Add GUI Controls
```vue
<q-expansion-item label="New Category" icon="new_icon">
  <q-toggle
    v-model="config.newCategory.enabled"
    label="Enable New Category"
    @update:model-value="saveConfig"
  />
</q-expansion-item>
```

### Step 3: Implement Checks
```javascript
if (ToolsSwitcher.isEnabled('newCategory', 'feature1')) {
  // Initialize feature
}
```

---

## 📚 Related Documentation

- [Firebase Integration Guide](../firebase/README.md)
- [Notification System](../Components/Chat/README.md)
- [Performance Optimization](../docs/performance.md)

---

**Last Updated:** January 17, 2025  
**Version:** 1.0  
**Status:** ✅ Production Ready