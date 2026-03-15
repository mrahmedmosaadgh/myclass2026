# Fix: Import Path Errors in Shared Components

**Date:** March 15, 2026  
**Issue:** Vite failed to resolve import paths for common components  
**Status:** ✅ FIXED

---

## Problem

Vite reported import errors when loading the Manager.vue component:

```
[plugin:vite:import-analysis] Failed to resolve import "../common/LoadingState.vue" 
from "resources/js/Pages/myclass2026/features/weekly_system_v1/weekly_plans/Manager.vue". 
Does the file exist?
```

---

## Root Cause

Incorrect relative import paths in shared components:

### Manager.vue (Weekly Plans)
```javascript
// ❌ WRONG - Extra directory level
import LoadingState from '../../components/common/LoadingState.vue'
```

### Index.vue (Curriculum Lessons)
```javascript
// ❌ WRONG - Extra directory level
import LoadingState from '../../components/common/LoadingState.vue'
```

---

## Directory Structure

```
weekly_system_v1/
├── components/
│   └── common/
│       ├── LoadingState.vue
│       ├── EmptyState.vue
│       └── StatusBadge.vue
├── curriculum_lessons/
│   └── Index.vue          ← Needs to import from ../components/common/
└── weekly_plans/
    └── Manager.vue        ← Needs to import from ../components/common/
```

**Path Analysis:**
- `curriculum_lessons/Index.vue` is at: `weekly_system_v1/curriculum_lessons/`
- Common components are at: `weekly_system_v1/components/common/`
- Need to go up **1 level** (`../`) then into `components/common/`
- Same for `weekly_plans/Manager.vue`

---

## Solution Applied

### Fixed Manager.vue
```javascript
// ✅ CORRECT - One level up to parent, then into components/common/
import LoadingState from '../components/common/LoadingState.vue'
import EmptyState from '../components/common/EmptyState.vue'
```

### Fixed Index.vue
```javascript
// ✅ CORRECT - One level up to parent, then into components/common/
import LoadingState from '../components/common/LoadingState.vue'
import EmptyState from '../components/common/EmptyState.vue'
import StatusBadge from '../components/common/StatusBadge.vue'
```

---

## Files Modified

1. **`weekly_plans/Manager.vue`**
   - Lines changed: 2 imports fixed
   - First attempt: `'../../components/common/LoadingState.vue'` (❌ Still wrong)
   - Final fix: `'../components/common/LoadingState.vue'` (✅ Correct)

2. **`curriculum_lessons/Index.vue`**
   - Lines changed: 3 imports fixed
   - First attempt: `'../../components/common/LoadingState.vue'` (❌ Still wrong)
   - Final fix: `'../components/common/LoadingState.vue'` (✅ Correct)

---

## Verification

After applying fixes:
- ✅ No more Vite import errors
- ✅ Components load correctly
- ✅ Hot reload working
- ✅ All shared components accessible

---

## Lesson Learned

When importing from nested directories in the Feature-First structure:

### Correct Pattern
```javascript
// From: weekly_system_v1/[feature]/Component.vue
// To:   weekly_system_v1/components/common/SharedComponent.vue

import SharedComponent from '../components/common/SharedComponent.vue'
//                          ↑           ↑
//                    Go up 1 level to parent, then into components/common/
```

### Why Not `../../`?
```javascript
// This would look for:
// weekly_system_v1/../components/common/LoadingState.vue
// = weekly_system_v1/components/common/LoadingState.vue  ← Actually correct!

// Wait... let me explain:
// From: weekly_system_v1/curriculum_lessons/Index.vue
// Go up ONE level: weekly_system_v1/
// Then into: components/common/
// Result: ../components/common/ ✅

// If we used ../../:
// From: weekly_system_v1/curriculum_lessons/Index.vue
// Go up TWO levels: weekly_system_v1/..
// = features/
// Then into: components/common/
// = features/components/common/  ← WRONG!
```

---

## Prevention

To avoid this issue in future components:

1. **Always check directory structure** before writing imports
2. **Count directory levels** carefully:
   - Same folder: `./Component.vue`
   - One level up: `../Component.vue`
   - Two levels up: `../../folder/Component.vue`
3. **Use VS Code path autocomplete** - it will show correct relative paths
4. **Verify with file explorer** if unsure

---

## Related Components

These components also use the same pattern and should be checked:

### Already Correct ✅
- WeekSelector.vue
- SemesterSelector.vue
- PeriodSelector.vue
- StatusBadge.vue
- LoadingState.vue
- EmptyState.vue

(These are IN the common folder, so no imports needed)

### Need Checking ⚠️
Any new components created in feature folders should use:
```javascript
import Something from '../../components/common/Something.vue'
```

---

**Status:** ✅ RESOLVED  
**Impact:** Minimal - simple path correction  
**Testing:** Browser hot reload confirmed working

---

*Last Updated: March 15, 2026*
