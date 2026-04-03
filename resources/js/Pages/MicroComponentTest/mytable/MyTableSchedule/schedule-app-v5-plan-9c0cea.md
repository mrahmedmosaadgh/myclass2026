# Schedule App V5 — Clean Rewrite Plan

V5 is a clean rewrite: main app = read-only viewer, all settings live in a full-screen slide menu, IndexedDB is the single storage layer, cloud sync kept with local-wins rule.

---

## Lessons From V2→V4

- **V2**: PWA install, timing overrides, import/export. Problem: mixed concerns, no structured DB.
- **V3**: Separate routes, stage/day hierarchy. Problem: localStorage-only, inconsistent keys.
- **V4**: IndexedDB composable, cloud auto-save. Problem: localStorage key mess, cloud overwrites imports, 2200-line monolith shell, settings mixed into main view.

### V5 Must Fix
1. **Single storage** — IndexedDB only, zero localStorage
2. **One-way data flow** — menu writes → IDB → reactive state → view reads
3. **Separation** — main app = viewer, menu = all config
4. **Local-wins sync** — cloud never overwrites newer local data

---

## File Structure

```
v5/
├── ScheduleAppV5.vue              # Shell (header + menu + viewer)
├── ScheduleViewer.vue             # View-only (card/table/list/master)
├── components/
│   ├── AppHeader.vue              # Brand, status, menu toggle
│   ├── SlideMenu.vue              # Full-screen menu container
│   ├── menu/
│   │   ├── MenuHome.vue           # Quick actions, today snapshot
│   │   ├── MenuViewSelector.vue   # View mode switch
│   │   ├── MenuTimingConfig.vue   # Stage/day timing editor
│   │   ├── MenuDataManager.vue    # Import/Export/Paste
│   │   ├── MenuSettings.vue       # Notifications, cache, reset
│   │   └── MenuAbout.vue          # Version info
│   ├── views/
│   │   ├── CardView.vue           # Read-only
│   │   ├── TableView.vue          # Read-only
│   │   ├── ListView.vue           # Read-only
│   │   └── MasterTimetableView.vue
│   ├── AdminTimingBar.vue         # Stage/day context selector
│   └── TestTimeOverride.vue       # Dev-only
├── composables/
│   ├── useAppStore.js             # Central reactive state
│   ├── useOfflineDB.js            # IndexedDB CRUD
│   ├── useCloudSync.js            # Cloud sync, local-wins
│   ├── useDataImportExport.js     # Import/export via IDB
│   ├── useTimingResolver.js       # Timing hierarchy resolution
│   └── useServiceWorker.js        # SW registration
├── data/                          # Static JSON defaults
└── README_V5.md
```

---

## Phases

### Phase 1: Foundation
- Create `v5/` folder, copy static data JSON files
- Rewrite `useOfflineDB.js` — convenience methods, no localStorage
- Create `useAppStore.js` — central provide/inject, loads from IDB on init
- Create `useTimingResolver.js` — pure computed timing resolution
- Create `useCloudSync.js` — local-wins via `lastModified` timestamps

### Phase 2: Shell + Menu
- `ScheduleAppV5.vue` — minimal shell: header + menu + viewer slot
- `AppHeader.vue` — brand, status badges, menu toggle only
- `SlideMenu.vue` — full-screen overlay with sections
- Menu pages: ViewSelector, TimingConfig, DataManager, Settings, About
- All config writes go through `useAppStore` → IDB

### Phase 3: View-Only Main App
- `ScheduleViewer.vue` — reads store, renders correct view
- Rewrite CardView, TableView, ListView, MasterTimetableView — read-only props only
- `AdminTimingBar.vue` — stage/day context switch (no editing)

### Phase 4: PWA + Service Worker
- `useServiceWorker.js` — fix V4's 206 partial response cache bug
- New service worker file with proper cache strategy
- `routes/schedule_app_v5.php` — public routes, no auth
- PWA manifest

### Phase 5: Testing + Polish
- Test offline mode, import/export cycle, cloud sync, PWA install
- Write `README_V5.md`

---

## Key Decisions

### IndexedDB Schema
- `timings` → `{ id, default, overrides, lastModified }`
- `personalSchedule` → `{ id, schedule, timings, lastModified }`
- `schoolTimetable` → `{ id, stages, lastModified }`
- `appSettings` → `{ key, value }`
- `syncQueue` → `{ id, action, data, timestamp, synced }`

### Data Flow
```
Menu action → useAppStore.update() → IDB.save() → reactive state → UI
Page load → IDB.load() → reactive state → UI
Cloud sync → compare lastModified → newer wins → IDB → state → UI
```

### Local-Wins Rule
On cloud load: if `local.lastModified > server.lastModified`, keep local and push to server. Otherwise accept server data into IDB.
