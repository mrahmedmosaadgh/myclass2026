# Schedule App V5 — Development History

## Overview
Schedule App V5 is a complete architectural rewrite focused on offline-first design, clean separation of concerns, and menu-driven settings. This document tracks the development journey, key decisions, and implementation details.

---

## 2026-04-03 — Initial Implementation (Phase 1-5 Complete)

### Phase 1: Foundation Architecture
**Date**: 2026-04-03  
**Status**: ✅ Complete

#### 1.1 Project Structure Created
- Created `/v5/` folder as clean slate
- Copied static data files from V4:
  - `schedule_data.json` — Default personal schedule
  - `schedule_timing.json` — Default timing configuration
  - `data/stage_day_timings.json` — Stage/day timing overrides
  - `data/master_timetable_data.json` — School timetable
  - `icon.png` — App icon
  - `notification1.mp3` — Notification sound

#### 1.2 IndexedDB-Only Storage (`useOfflineDB.js`)
**Key Decision**: Eliminated localStorage completely to avoid V4's key mismatch issues

```javascript
// Schema design
stores: {
  timings: { id: 'config', default: [...], overrides: {...} },
  personalSchedule: { id: 'main', schedule: [...], timings: [...] },
  schoolTimetable: { id: 'main', stages: {...} },
  appSettings: { key: string, value: any },
  syncQueue: { id: auto, action, data, timestamp, synced }
}
```

**Features**:
- Generic CRUD operations
- Convenience methods for each store
- Export/import entire database
- Storage info and cleanup utilities

#### 1.3 Central Reactive State (`useAppStore.js`)
**Architecture Pattern**: Single source of truth with provide/inject

```javascript
// Core state
const timingsConfig = ref(defaultTimings)
const scheduleData = ref(defaultSchedule)
const schoolTimetable = ref(defaultMaster)
const selectedStage = ref('prim')
const selectedDay = ref('d1')
const currentViewMode = ref('card')
```

**Key Features**:
- Loads from IndexedDB on init
- Provides mutations for all data types
- Coordinates cloud sync
- Handles live time ticker
- Test time override support

#### 1.4 Timing Resolution (`useTimingResolver.js`)
**Design**: Pure computed resolution with hierarchy

```javascript
// Resolution priority
1. Stage + Day Override
2. Stage Default  
3. Global Default
4. Static Fallback
```

**Features**:
- Computed `resolvedTimeSlots`
- Normalizes slots (adds startMin/endMin)
- Returns custom timing days list

#### 1.5 Cloud Sync (`useCloudSync.js`)
**Strategy**: Local-wins conflict resolution

```javascript
// Sync logic
if (serverLastModified > localLastModified) {
  // Server is newer → apply
} else {
  // Local is newer or equal → push to server
}
```

**Features**:
- Queue failed operations for retry
- Online/offline status tracking
- Anonymous user ID management
- Background sync support

---

### Phase 2: Shell + Menu Architecture
**Date**: 2026-04-03  
**Status**: ✅ Complete

#### 2.1 Main Shell (`ScheduleAppV5.vue`)
**Design**: Minimal container with header, menu, and viewer

**Features**:
- PWA manifest and service worker registration
- Install prompt handling
- Scroll-to-top FAB
- Responsive layout

#### 2.2 App Header (`AppHeader.vue`)
**Design**: Brand, status, and menu toggle only

**Features**:
- Live sync status indicator
- Online/offline status
- Install button (when available)
- Compact scroll state

#### 2.3 Full-Screen Slide Menu (`SlideMenu.vue`)
**UX Pattern**: Full-screen overlay with tab navigation

**Features**:
- 6 main sections: Home, Views, Timing, Data, Settings, About
- Smooth transitions
- Safe area support
- Blur effect on main content

#### 2.4 Menu Pages
**Home (`MenuHome.vue`)**:
- Today's snapshot (time, day, stage, view, status)
- Quick actions (Go to Today, View Schedule)

**View Selector (`MenuViewSelector.vue`)**:
- 4 view modes: Card, Table, List, Master
- Visual descriptions
- One-tap switching

**Timing Config (`MenuTimingConfig.vue`)**:
- Stage/day timing editor
- Add/remove periods
- Copy from default
- Real-time validation

**Data Manager (`MenuDataManager.vue`)**:
- Export: Full backup, timings only, schedule only, school only
- Import: Paste JSON, file upload, target selection
- Storage info display
- Clear all data

**Settings (`MenuSettings.vue`)**:
- Notifications toggle
- Test time override
- Cloud sync status/manual trigger
- Cache clear and app reset

**About (`MenuAbout.vue`)**:
- Version info
- Feature list
- Technology stack
- Privacy policy

---

### Phase 3: View-Only Main App
**Date**: 2026-04-03  
**Status**: ✅ Complete

#### 3.1 Schedule Viewer (`ScheduleViewer.vue`)
**Design**: Container with admin bar and view switcher

**Features**:
- Provides resolved timing slots to all views
- Smooth view transitions
- Responsive layout

#### 3.2 Admin Timing Bar (`AdminTimingBar.vue`)
**Design**: Context selector for stage and day

**Features**:
- Stage selector (P, M, S)
- Day selector (D1-D6)
- "Today" quick action
- Live time display

#### 3.3 Read-Only Views
**Card View (`CardView.vue`)**:
- Daily cards with live period indicator
- Past/current/future styling
- Responsive grid

**Table View (`TableView.vue`)**:
- Full week table
- Live period highlighting
- Horizontal scroll

**List View (`ListView.vue`)**:
- Compact daily lists
- Mobile-optimized
- Smooth transitions

**Master Timetable (`MasterTimetableView.vue`)**:
- Full school timetable
- Teacher/class/subject filters
- Busy/free display modes
- Summary statistics

---

### Phase 4: PWA + Routes
**Date**: 2026-04-03  
**Status**: ✅ Complete

#### 4.1 Service Worker (`my-fly-schedule-app-v5-sw.js`)
**Strategy**: App shell caching + network-first runtime

```javascript
// Cache strategy
STATIC_CACHE: App shell (cache-first)
RUNTIME_CACHE: Runtime assets (network-first)
API: No caching
```

**Features**:
- Background sync support
- Cache management
- Message handling
- Periodic sync

#### 4.2 PWA Manifest (`manifest.webmanifest`)
**Features**:
- Standalone display
- Custom icons
- App shortcuts
- Screenshots
- Categories

#### 4.3 Routes (`schedule_app_v5.php`)
**Design**: Public routes without authentication

```php
// Main routes
GET /my-fly-schedule-app/v5 - Main app
GET /my-fly-schedule-app/v5/manifest.webmanifest - PWA manifest
GET /my-fly-schedule-app/v5/icon.png - App icon
GET /my-fly-schedule-app-v5-sw.js - Service worker

// API routes
POST /api/v5/save-data - Save with local-wins
GET /api/v5/load-data - Load with local-wins
GET /api/v5/health - Health check
GET /api/v5/backups - List backups
GET /api/v5/download-backup/{filename} - Download backup
```

---

### Phase 5: Documentation & Testing
**Date**: 2026-04-03  
**Status**: ✅ Complete

#### 5.1 Documentation (`README_V5.md`)
**Content**:
- Architecture overview
- File structure
- Data flow diagram
- Storage schema
- PWA features
- Development guide
- Deployment instructions
- Troubleshooting

#### 5.2 Development History (`DEVELOPMENT_HISTORY.md`)
**This document** - Tracking all decisions and implementation details

---

## Key Architectural Decisions

### 1. IndexedDB Only
**Problem**: V4 had localStorage key mismatches (`school-timings-v2` vs `v4` vs `schedule-v4-*`)
**Solution**: Single IndexedDB store with clear schema
**Benefit**: No more key conflicts, structured data, better performance

### 2. Local-Wins Sync
**Problem**: Cloud sync could overwrite local changes
**Solution**: Compare timestamps, newer data wins
**Benefit**: Users never lose their local changes

### 3. Menu-Driven Settings
**Problem**: Settings scattered throughout UI
**Solution**: Full-screen menu with all settings
**Benefit**: Clean main app, focused configuration experience

### 4. View-Only Main App
**Problem**: Edit controls cluttered the schedule view
**Solution**: Main app is read-only, all edits in menu
**Benefit**: Better UX, clear separation of concerns

### 5. Composable Architecture
**Problem**: V4 had mixed concerns in components
**Solution**: Pure composables for each feature
**Benefit**: Reusable, testable, maintainable code

---

## Technical Improvements Over V4

### Storage
- **V4**: Mixed localStorage + IndexedDB, key conflicts
- **V5**: IndexedDB only, structured schema

### State Management
- **V4**: Reactive props scattered in components
- **V5**: Central store with provide/inject

### Sync Strategy
- **V4**: Cloud could overwrite local
- **V5**: Local-wins with timestamp comparison

### UI Architecture
- **V4**: Settings mixed with views
- **V5**: Menu-driven, view-only main app

### PWA Features
- **V4**: Basic PWA support
- **V5**: Full PWA with shortcuts, background sync

### Code Organization
- **V4**: Large components with mixed concerns
- **V5**: Small composables, focused components

---

## Deployment Timeline

### 2026-04-03 15:00 - Initial Implementation Complete
- All 5 phases implemented
- Documentation created
- Ready for deployment

### 2026-04-03 15:30 - Route Configuration
- Added V5 routes to `web.php`
- Fixed middleware exclusions
- Resolved route name conflicts

### 2026-04-03 16:00 - Bug Fixes
- Fixed missing `provide` import in `ScheduleViewer.vue`
- Updated icon from SVG to PNG
- Added CSRF middleware exclusions to API routes
- Fixed PWA manifest icon references

### 2026-04-03 16:30 - Deployment Ready
- All files prepared for sync
- Routes configured
- Cache clearing procedures documented

---

## Testing Checklist

### ✅ Offline Functionality
- [ ] Disable network
- [ ] Verify all views work
- [ ] Test menu functionality
- [ ] Verify data persistence

### ✅ Import/Export Cycle
- [ ] Export full backup
- [ ] Clear all data
- [ ] Import backup
- [ ] Verify data integrity

### ✅ Cloud Sync
- [ ] Import data offline
- [ ] Go online
- [ ] Verify sync to server
- [ ] Test local-wins behavior

### ✅ PWA Installation
- [ ] Install prompt appears
- [ ] Launch standalone
- [ ] Test offline launch
- [ ] Verify shortcuts work

### ✅ All View Modes
- [ ] Card view displays correctly
- [ ] Table view shows live indicators
- [ ] List view is mobile-optimized
- [ ] Master timetable loads school data

---

## Future Enhancements

### Planned Features
- Real-time collaboration (WebSockets)
- Advanced analytics dashboard
- Calendar integration
- Multi-language support
- Teacher-specific views

### Technical Improvements
- Web Workers for heavy operations
- Advanced caching strategies
- Better error boundaries
- Performance monitoring

---

## Version History

### v5.0.0 (2026-04-03)
- Initial release
- Complete architectural rewrite
- Offline-first design
- Menu-driven settings
- IndexedDB-only storage
- Local-wins cloud sync
- PWA with background sync

---

## Contributors

- **Ahmed Mosaad** — Architecture, Implementation, Documentation

---

**Last Updated**: 2026-04-03  
**Version**: 5.0.0  
**Status**: Production Ready
