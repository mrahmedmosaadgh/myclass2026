# Schedule App V5 — Offline-First Architecture

## Overview
Schedule App V5 is a complete rewrite with a clean separation of concerns: the main app is read-only, all settings live in a full-screen slide menu, and storage is IndexedDB-only with optional cloud sync using a local-wins rule.

## Key Features

### 🔄 Offline-First Design
- **IndexedDB only**: Single storage layer, no localStorage key mess
- **One-way data flow**: Menu writes → IDB → reactive state → view reads
- **Full offline functionality**: Works completely without internet
- **Local-wins sync**: Cloud never overwrites newer local data

### 📱 Menu-Driven Settings
- **Full-screen slide menu**: All configuration lives here
- **View-only main app**: No settings controls in the schedule view
- **Sections**: Home, Views, Timing, Data, Settings, About
- **Mobile-optimized**: Touch-friendly with safe-area support

### 🌐 PWA Features
- **Installable**: Can be installed as a standalone app
- **Service Worker**: Caches app shell for instant loading
- **Background Sync**: Syncs data when connection restored
- **App Shortcuts**: Direct access to timing and data manager

### 📊 Multiple Views
- **Card View**: Daily cards with live period indicator
- **Table View**: Full week table with period highlighting
- **List View**: Compact scrollable daily lists
- **Master Timetable**: Full school timetable with filters

## Architecture

### File Structure
```
v5/
├── ScheduleAppV5.vue              # Main shell (header + menu + viewer)
├── ScheduleViewer.vue             # View-only container
├── components/
│   ├── AppHeader.vue              # Brand, status, menu toggle
│   ├── SlideMenu.vue              # Full-screen menu container
│   ├── AdminTimingBar.vue         # Stage/day context selector
│   ├── menu/
│   │   ├── MenuHome.vue           # Today snapshot + quick actions
│   │   ├── MenuViewSelector.vue   # View mode switch
│   │   ├── MenuTimingConfig.vue   # Stage/day timing editor
│   │   ├── MenuDataManager.vue    # Import/Export/Paste JSON
│   │   ├── MenuSettings.vue       # Notifications, cache, reset
│   │   └── MenuAbout.vue          # Version info
│   └── views/
│       ├── CardView.vue           # Read-only daily cards
│       ├── TableView.vue          # Read-only week table
│       ├── ListView.vue           # Read-only daily lists
│       └── MasterTimetableView.vue # Read-only school timetable
├── composables/
│   ├── useAppStore.js             # Central reactive state
│   ├── useOfflineDB.js            # IndexedDB CRUD (single source)
│   ├── useCloudSync.js            # Cloud sync with local-wins
│   ├── useDataImportExport.js     # Import/export via IDB
│   └── useTimingResolver.js       # Timing hierarchy resolution
├── data/                          # Static default data
└── README_V5.md
```

### Data Flow
```
Menu Action → useAppStore.update() → IndexedDB.save() → reactive state → UI
Page Load → IndexedDB.load() → reactive state → UI
Cloud Sync → compare timestamps → newer wins → IDB → state → UI
```

## Storage Schema

### IndexedDB Stores
- **timings** → `{ id: 'config', default: [...], overrides: {...}, lastModified }`
- **personalSchedule** → `{ id: 'main', schedule: [...], timings: [...], lastModified }`
- **schoolTimetable** → `{ id: 'main', stages: {...}, lastModified }`
- **appSettings** → `{ key: string, value: any }`
- **syncQueue** → `{ id: auto, action, data, timestamp, synced: bool }`

### Key Improvements Over V4
- **No localStorage**: Eliminates key mismatch issues (`school-timings-v2` vs `v4` vs `schedule-v4-*`)
- **Single source of truth**: All state flows through `useAppStore`
- **Local-wins rule**: Cloud sync respects `lastModified` timestamps
- **Clean separation**: Main app is purely display; menu handles all config

## Routes

### Main Application
- `GET /my-fly-schedule-app/v5` - Main application
- `GET /my-fly-schedule-app/v5/manifest.webmanifest` - PWA manifest
- `GET /my-fly-schedule-app/v5/icon.svg` - App icon
- `GET /my-fly-schedule-app-v5-sw.js` - Service worker

### API Endpoints
- `POST /api/v5/save-data` - Save user data to server (local-wins)
- `GET /api/v5/load-data` - Load user data from server (local-wins)
- `GET /api/v5/health` - Health check with feature list
- `GET /api/v5/backups` - List user backups
- `GET /api/v5/download-backup/{filename}` - Download specific backup

## PWA Features

### Installation
- Install prompt appears on supported browsers
- Can be installed from browser menu
- Standalone mode with custom icons
- Full-screen experience

### Offline Support
- Complete offline functionality
- Cached app shell for instant loading
- Runtime caching for assets
- Network-first strategy with cache fallback

### Background Sync
- Automatic data synchronization
- Queue management for failed operations
- Periodic sync support (if browser supports)

## Development

### Key Composables

#### useAppStore
- Central reactive state using `provide/inject`
- Loads from IndexedDB on init
- Provides mutations for all data types
- Handles cloud sync coordination

#### useOfflineDB
- IndexedDB wrapper with convenience methods
- Stores: timings, personalSchedule, schoolTimetable, appSettings, syncQueue
- Export/import entire database
- Storage info and cleanup utilities

#### useCloudSync
- Cloud sync with local-wins conflict resolution
- Queue failed operations for retry
- Online/offline status tracking
- User ID management via cookie

#### useTimingResolver
- Pure computed timing resolution
- Hierarchy: stage+day override > stage default > global default > fallback
- Normalizes time slots (adds startMin/endMin)
- Returns resolved slots and custom day list

### Menu Components

#### MenuTimingConfig
- Full timing editor (stage/day overrides)
- Copy from default, add/remove periods
- Real-time validation and status messages
- Persist changes via store

#### MenuDataManager
- Export: full backup, timings only, schedule only, school only
- Import: paste JSON, file upload, target selection
- Storage info display
- Clear all data with confirmation

#### MenuSettings
- Notifications permission toggle
- Test time override controls
- Cloud sync status and manual trigger
- Cache clear and app reset

### View Components (Read-Only)

#### CardView
- Daily cards with live period indicator
- Past/current/future period styling
- Responsive grid layout
- Touch-friendly

#### TableView
- Full week table with period highlighting
- Live period indicator for current day
- Responsive with horizontal scroll
- Break period styling

#### ListView
- Compact daily lists
- Live period indicator
- Mobile-optimized spacing
- Smooth transitions

#### MasterTimetableView
- Full school timetable
- Teacher/class/subject filters
- Busy/free display modes
- Summary statistics

## Deployment

### Build Process
1. Local development with Vite/HMR
2. Build assets: `npm run build`
3. Sync to Hostinger with rsync
4. Clear Laravel caches
5. Test routes and PWA features

### Environment Requirements
- PHP 8.2+
- Laravel 10+
- Modern browser with IndexedDB support
- HTTPS required for PWA installation

### Cache Strategy
- App shell: cache-first (STATIC_CACHE)
- Runtime assets: network-first with cache fallback (RUNTIME_CACHE)
- API calls: network only (no caching)
- Service worker: no-cache header

## Testing

### Offline Mode
1. Disable network connection
2. Verify all views work
3. Verify data persistence
4. Test menu functionality

### Import/Export Cycle
1. Export full backup
2. Clear all data
3. Import backup
4. Verify data integrity

### Cloud Sync
1. Import data offline
2. Go online
3. Verify sync to server
4. Reload and verify local-wins

### PWA Installation
1. Open in supported browser
2. Trigger install prompt
3. Launch standalone
4. Test offline launch

## Troubleshooting

### Common Issues
1. **Data not loading**: Check IndexedDB permissions, verify store initialization
2. **Sync not working**: Verify network, check user ID cookie, review server logs
3. **PWA not installing**: Check HTTPS, verify manifest syntax, test in different browser
4. **Service worker errors**: Check console, verify cache names, clear caches if needed

### Debug Information
- Console logs for all operations
- Network tab for API calls
- Application tab for IndexedDB
- Service Worker status in DevTools

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

**Version**: 5.0.0  
**Build**: Offline-First • Menu-Driven • IndexedDB-Only  
**Platform**: Progressive Web App  
**URL**: https://qudratpro.com/my-fly-schedule-app/v5
