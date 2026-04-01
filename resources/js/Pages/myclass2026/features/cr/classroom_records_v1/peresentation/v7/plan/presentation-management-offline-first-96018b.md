# Presentation Management System - Offline-First with Online Sync

Create a comprehensive presentation management system with offline-first IndexedDB storage, online MySQL backup using hybrid approach (metadata in DB + JSON files for slide data), and an intuitive UI for creating, finding, and managing presentations with categories.

## Current State Analysis

### ✅ Already Implemented
- **IndexedDB Storage**: `useIndexedDBStorage.js` composable with full CRUD operations
- **MySQL Schema**: Database migrations for `presentations`, `presentation_categories`, and `presentation_backups` tables
- **API Controllers**: `PresentationController` and `PresentationCategoryController` with full REST API
- **Sync System**: `usePresentationSync.js` for offline/online synchronization
- **Enhanced Manager Component**: `EnhancedPresentationManager.vue` with categories, search, and filters
- **Presentation Store**: `presentationStore.js` with auto-save functionality
- **API Routes**: `/api/presentations` and `/api/categories` endpoints configured

### ❌ Missing Components
1. **Hybrid Storage Implementation**: Need to modify backend to store slides as JSON files instead of JSON column
2. **Management Page Route**: No dedicated route for presentation management page
3. **Integration with Toolbar**: No "Save As" or "Load" buttons in the toolbar
4. **File Storage System**: Laravel file storage for presentation JSON files
5. **Migration from Current System**: No migration path from existing localStorage presentations

## Implementation Plan

### Phase 1: Backend - Hybrid Storage System (JSON Files)

**1.1 Update Database Migration**
- Modify `presentations` table to remove `slides` JSON column
- Add `slides_file_path` string column to store file path
- Keep metadata in database for fast queries

**1.2 Create File Storage Service**
- Create `app/Services/PresentationFileService.php`
- Methods: `saveSlides()`, `loadSlides()`, `deleteSlides()`
- Store files in `storage/app/presentations/{user_id}/{presentation_id}.json`
- Handle file compression for large presentations

**1.3 Update API Controllers**
- Modify `PresentationController` to use file storage
- Update `store()` method to save slides to file
- Update `show()` method to load slides from file
- Update `update()` method to update file
- Update `destroy()` method to delete file

### Phase 2: Frontend - Management Page

**2.1 Create Management Page Route**
- Add route in `routes/myclass2026/cr/web.php`
- Route: `/classroom-records/presentation/manage`
- Render: `myclass2026/features/cr/classroom_records_v1/peresentation/v5/PresentationManage`

**2.2 Create Management Page Component**
- Create `v5/PresentationManage.vue` (main page wrapper)
- Use existing `EnhancedPresentationManager.vue` component
- Add page layout with header and navigation
- Integrate with Inertia.js for Laravel integration

**2.3 Update Sync Composable**
- Fix file upload/download for JSON files
- Update `syncPresentationToServer()` to handle file storage
- Update `loadPresentationFromServer()` to fetch from file endpoint

### Phase 3: Toolbar Integration

**3.1 Add Quick Actions to Toolbar**
- Add "💾 Save" button (save current presentation)
- Add "📂 Manage" button (link to management page)
- Add save status indicator (saved/saving/error)

**3.2 Update Presentation Store**
- Add `saveCurrentPresentation()` method
- Add `loadPresentationById()` method
- Integrate with sync system

### Phase 4: Migration & Polish

**4.1 Data Migration**
- Create migration script for existing localStorage data
- Detect old presentations on first load
- Offer to import into new system

**4.2 User Experience**
- Add loading states for all operations
- Add success/error notifications
- Add keyboard shortcuts (Ctrl+S to save)
- Add auto-save indicator in toolbar

**4.3 Documentation**
- Update README with new features
- Add user guide for presentation management
- Document API endpoints

## File Structure

```
app/
├── Services/
│   └── PresentationFileService.php (NEW)
├── Http/Controllers/API/
│   ├── PresentationController.php (UPDATE)
│   └── PresentationCategoryController.php (EXISTS)
└── Models/
    ├── Presentation.php (UPDATE - add file methods)
    └── PresentationCategory.php (EXISTS)

database/migrations/
└── 2026_03_28_000004_update_presentations_for_file_storage.php (NEW)

resources/js/Pages/myclass2026/features/cr/classroom_records_v1/peresentation/v5/
├── PresentationManage.vue (NEW - main management page)
├── Index.vue (UPDATE - add toolbar buttons)
├── components/
│   ├── EnhancedPresentationManager.vue (EXISTS)
│   └── Toolbar.vue (UPDATE - add save/manage buttons)
├── composables/
│   ├── useIndexedDBStorage.js (EXISTS)
│   └── usePresentationSync.js (UPDATE - file handling)
└── stores/
    └── presentationStore.js (UPDATE - add save methods)

routes/
└── myclass2026/cr/web.php (UPDATE - add management route)

storage/app/
└── presentations/ (NEW - file storage directory)
    └── {user_id}/
        └── {presentation_id}.json
```

## Key Features

### Offline-First Workflow
1. **Create/Edit**: All changes saved to IndexedDB immediately
2. **Auto-Sync**: When online, sync to server automatically
3. **Queue System**: Offline changes queued and synced when online
4. **Conflict Resolution**: Server timestamp wins on conflicts

### Hybrid Storage Benefits
- **Fast Queries**: Metadata in MySQL (title, category, dates)
- **Large Files**: Slide data in JSON files (no DB size limits)
- **Easy Export**: Direct file download for presentations
- **Backup**: Automatic file backups on changes

### User Interface
- **Management Page**: Full CRUD with categories and search
- **Toolbar Actions**: Quick save and access to management
- **Visual Feedback**: Save status, sync status, storage stats
- **Keyboard Shortcuts**: Ctrl+S to save, Ctrl+O to open

## Success Criteria

✅ Teachers can create and save presentations offline
✅ Presentations sync to server when online
✅ Categories help organize presentations
✅ Search and filter work across all presentations
✅ File storage handles large presentations (100+ slides)
✅ Migration from old localStorage system works
✅ Auto-save prevents data loss
✅ Sync queue handles offline changes

## Technical Decisions

**Why Hybrid Storage?**
- MySQL: Fast queries for listing, searching, filtering
- JSON Files: No size limits, easy export, better performance
- Best of both worlds for offline-first architecture

**Why Separate Management Page?**
- Cleaner separation of concerns
- Better UX for browsing all presentations
- Easier to add advanced features later
- Toolbar stays focused on editing

**Why IndexedDB + MySQL?**
- IndexedDB: Offline-first, large storage (50MB-1GB)
- MySQL: Server backup, multi-device sync, sharing
- Automatic sync provides seamless experience
