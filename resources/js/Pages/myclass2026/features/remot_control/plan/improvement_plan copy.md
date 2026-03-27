# Improvement Plan — Remote Control System v1
> Status: AWAITING CONFIRMATION before coding starts

---

## Why Revision Is Needed

After reviewing the implementation against the actual project codebase, **4 critical bugs** were found that prevent the system from running at all. The architecture is solid — only targeted fixes are needed.

---

## Critical Bugs (System Won't Run)

### 🔴 BUG 1 — Wrong Firebase API (ALL Firebase files broken)
**Root Cause**: Used old Firebase SDK v8 global pattern.  
**Our code**: `window.firebase?.database?.()` → does not exist in this project  
**Project uses**: Firebase Modular SDK v9+

```javascript
// ✅ Correct pattern used in this project
import { database } from '@/firebase/init'
import { ref as dbRef, onValue, off, set, push } from 'firebase/database'
```

**Affected files**:
- `useRealtimeChannel.js`
- `useCommandQueue.js`
- `useEventLogger.js`

---

### 🔴 BUG 2 — Vue Refs Have No `.watch()` Method (UI is completely silent)
**Root Cause**: Used non-existent `.watch?.()` method on Vue refs.  
**Our code**: `channel.state?.watch?.((newState) => { ... })` → silently does nothing  
**Correct Vue 3 API**: `watch(() => channel.state.value, callback)`

**Affected files**:
- `useRealtimeState.js`
- `ChannelProvider.vue`
- `StateReceiver.vue`
- `CommandSender.vue`
- `TestDataDisplay.vue`
- `TestRemoteControl.vue`
- `test_remote_control_v1.vue`

---

### 🔴 BUG 3 — `keys` Naming Conflict in `useOfflineStorage`
**Root Cause**: `const keys` used inside `clear()` before it's declared (temporal dead zone).

```javascript
const clear = () => {
  const keys = keys()  // ❌ ReferenceError — cannot access 'keys' before initialization
}
const keys = () => { ... }  // declared AFTER clear
```
**Fix**: Rename `keys` → `getKeys` throughout the file.

---

### 🔴 BUG 4 — `rateLimiter` Is Always null
**Root Cause**: Snapshot of `internal.rateLimiter` captured before `initialize()` runs.

```javascript
// internal.rateLimiter is set inside initialize() → onMounted (async)
const rateLimiter = internal.rateLimiter  // ← captured as null immediately

const sendCommand = async () => {
  if (!rateLimiter?.check()) { ... }  // ← always null, rate limiting broken
}
```
**Fix**: Reference `internal.rateLimiter` directly inside `sendCommand`.

---

### 🟡 ISSUE 5 — Firebase Disabled on Localhost
From `@/firebase/init.js`:
```javascript
if (!ToolsSwitcher.isFirebaseEnabled() || (isLocal || isLocalIp)) {
  // database = null  ← Firebase is OFF during local development
}
```
**Fix**: Add `if (!database)` guard + graceful offline fallback in all Firebase calls.

---

### 🟡 ISSUE 6 — `onUnmounted` Inside `useOfflineStorage`
Called inside other composables (`useRealtimeChannel`, `useCommandQueue`).  
Lifecycle hooks in deeply nested composables register to the **component that first called the chain** — unpredictable behavior.  
**Fix**: Remove `onUnmounted` from `useOfflineStorage`. Let callers handle cleanup.

---

### 🟢 NON-ISSUE — `@apply` Warnings in `<style scoped>`
IDE false positives. Tailwind PostCSS is properly configured in the project (`tailwind.config.js` + `postcss.config.js`). No action needed.

---

## What's Solid — Keep As-Is

| File | Status |
|------|--------|
| Folder structure | ✅ Clean, no changes |
| `channel.types.js` | ✅ Correct |
| `debounce.js` | ✅ Solid, no changes |
| `validation.js` | ✅ Logic correct, minor import fix only |
| All Vue template markup | ✅ Correct HTML + Tailwind |
| Component props/emits design | ✅ Correct |
| `useCommandQueue` queue algorithm | ✅ Correct logic |
| `useEventLogger` analytics logic | ✅ Correct logic |
| `useRealtimeState` undo/redo logic | ✅ Correct logic |

---

## Fix Plan — File by File

### Phase 1: Core Composables

#### 1.1 `useOfflineStorage.js`
- [ ] Rename `keys` → `getKeys` everywhere in the file
- [ ] Remove `onUnmounted` hook (not needed, callers clean up)

#### 1.2 `useRealtimeChannel.js`
- [ ] Add import: `import { database } from '@/firebase/init'`
- [ ] Add import: `import { ref as dbRef, onValue, off, set, push } from 'firebase/database'`
- [ ] Replace `window.firebase?.database?.()` with `database` everywhere
- [ ] Replace `db.ref(path)` with `dbRef(database, path)`
- [ ] Replace `ref.on('value', cb)` with `onValue(ref, cb)`
- [ ] Replace `ref.off()` with `off(ref)`
- [ ] Replace `ref.set(data)` with `set(ref, data)`
- [ ] Replace `ref.push(data)` with `push(ref, data)` (returns promise)
- [ ] Fix `rateLimiter` → use `internal.rateLimiter` directly in `sendCommand`
- [ ] Add `if (!database) { isConnected.value = 'disconnected'; return }` guard
- [ ] Add `import { ToolsSwitcher } from '@/Utils/toolsSwitcher'` + check

#### 1.3 `useRealtimeState.js`
- [ ] Replace `channel.onStateChange?.()` callback with `watch(() => channel.state.value, cb)`
- [ ] Remove all `.watch?.()` patterns
- [ ] Add `import { watch } from 'vue'`

#### 1.4 `useCommandQueue.js`
- [ ] Same Firebase API fixes as 1.2
- [ ] Fix `internal.offlineStorage.save` called before `initialize()` guard

#### 1.5 `useEventLogger.js`
- [ ] Same Firebase API fixes as 1.2

---

### Phase 2: Generic Components

#### 2.1 `ChannelProvider.vue`
- [ ] Replace `channel.isConnected?.watch?.()` with `watch(() => channel.isConnected.value, cb)`
- [ ] Replace `channel.lastError?.watch?.()` with `watch(() => channel.lastError.value, cb)`
- [ ] Add `import { watch } from 'vue'`

#### 2.2 `StateReceiver.vue`
- [ ] Replace all `.watch?.()` with `watch(() => ..., cb)` from Vue
- [ ] Fix channel injection fallback

#### 2.3 `CommandSender.vue`
- [ ] Replace all `.watch?.()` with `watch(() => ..., cb)` from Vue

---

### Phase 3: Example Components

#### 3.1 `TestDataDisplay.vue`
- [ ] Replace `channel?.state?.watch?.()` → `watch(() => channel.state.value, cb)`
- [ ] Replace `channel?.isConnected?.watch?.()` → `watch(() => channel.isConnected.value, cb)`

#### 3.2 `TestRemoteControl.vue`
- [ ] Same as 3.1

#### 3.3 `test_remote_control_v1.vue`
- [ ] Fix `globalChannel.value.state?.watch?.()` → `watch()`
- [ ] Fix `performance.successRate` missing reactive init

---

### Phase 4: Add Route (New)
- [ ] Register `test_remote_control_v1.vue` in the appropriate route file so it's accessible in browser

---

## Summary

| Category | Files | Changes |
|----------|-------|---------|
| 🔴 Firebase API | 3 composables | Replace all Firebase calls with modular SDK |
| 🔴 Vue watch API | 7 files | Replace `.watch?.()` with `watch()` from Vue |
| 🔴 Naming conflict | 1 file | `keys` → `getKeys` |
| 🔴 Null reference | 1 file | `rateLimiter` direct ref fix |
| 🟡 Offline guard | 3 composables | Add `if (!database)` fallback |
| 🆕 Route | 1 route file | Register test page |

**Estimated fix time**: ~2 hours  
**No architecture changes needed** — fixes only.

---

> ⏳ Waiting for confirmation to start coding.
