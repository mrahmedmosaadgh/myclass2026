# 📚 API Reference

Complete reference for all methods, parameters, and return values in the offline-first system.

## 📋 Table of Contents

- [useOfflineResource()](#useofflineresource)
- [Core Methods](#core-methods)
- [Configuration Options](#configuration-options)
- [Return Values](#return-values)
- [Error Handling](#error-handling)
- [Type Definitions](#type-definitions)

## 🎯 useOfflineResource()

The main composable for managing offline resources.

### Syntax
```javascript
const resource = useOfflineResource(resourceName, options)
```

### Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `resourceName` | `string` | ✅ | Name of the resource (e.g., 'lessons', 'students') |
| `options` | `object` | ❌ | Configuration options |

### Options Object

```javascript
const options = {
  // API configuration
  endpoint: '/api/custom-endpoint',     // Custom API endpoint
  baseURL: 'https://api.example.com',  // Custom base URL
  
  // Sync configuration
  syncStrategy: 'last-write-wins',     // 'last-write-wins', 'append-only', 'merge'
  syncPriority: 'high',                // 'low', 'medium', 'high', 'critical'
  
  // Cache configuration
  cacheDuration: 3600000,              // Cache duration in milliseconds (1 hour)
  maxCacheSize: 1000,                  // Maximum number of records to cache
  
  // Offline capabilities
  offlineCapabilities: ['read', 'create', 'update', 'delete'], // Allowed offline operations
  
  // Validation
  validator: (data) => validateData(data), // Custom validation function
  
  // Transformation
  transformer: (data) => transformData(data) // Data transformation function
};
```

### Return Value

```javascript
const {
  // Reactive data
  data,              // ref([]) - Array of resource items
  loading,           // ref(false) - Loading state
  error,             // ref(null) - Error state
  syncStatus,        // computed() - Current sync status
  
  // CRUD operations
  loadAll,           // () => Promise - Load all items
  loadById,          // (id) => Promise - Load specific item
  create,            // (data) => Promise - Create new item
  update,            // (id, data) => Promise - Update existing item
  delete,            // (id) => Promise - Delete item
  
  // Sync operations
  sync,              // () => Promise - Manual sync
  clearCache,        // () => Promise - Clear local cache
  
  // Status checks
  isOnline,          // ref(true) - Network status
  hasUnsyncedChanges, // computed() - Has pending changes
  lastSyncTime       // ref(null) - Last successful sync
} = useOfflineResource('lessons');
```

## 🔧 Core Methods

### loadAll()

Load all items for the resource.

```javascript
/**
 * Load all items from server (if online) or cache (if offline)
 * @param {Object} options - Loading options
 * @param {boolean} options.forceRefresh - Skip cache and fetch from server
 * @param {boolean} options.includeDeleted - Include soft-deleted items
 * @returns {Promise<Array>} Promise resolving to array of items
 */
loadAll(options = {})
  .then(items => {
    console.log('Loaded items:', items);
  })
  .catch(error => {
    console.error('Failed to load items:', error);
  });
```

### loadById()

Load a specific item by ID.

```javascript
/**
 * Load a specific item by ID
 * @param {number|string} id - Item ID
 * @param {Object} options - Loading options
 * @param {boolean} options.forceRefresh - Skip cache and fetch from server
 * @returns {Promise<Object>} Promise resolving to the item
 */
loadById(123, { forceRefresh: true })
  .then(item => {
    console.log('Loaded item:', item);
  })
  .catch(error => {
    console.error('Item not found:', error);
  });
```

### create()

Create a new item.

```javascript
/**
 * Create a new item
 * @param {Object} data - Item data
 * @param {Object} options - Creation options
 * @param {boolean} options.optimistic - Apply changes immediately (default: true)
 * @returns {Promise<Object>} Promise resolving to created item
 */
create({
  title: 'New Lesson',
  content: 'Lesson content',
  course_id: 123
}, { optimistic: true })
  .then(item => {
    console.log('Created item:', item);
  })
  .catch(error => {
    console.error('Failed to create item:', error);
  });
```

### update()

Update an existing item.

```javascript
/**
 * Update an existing item
 * @param {number|string} id - Item ID
 * @param {Object} data - Updated data
 * @param {Object} options - Update options
 * @param {boolean} options.optimistic - Apply changes immediately (default: true)
 * @param {boolean} options.merge - Merge with existing data (default: true)
 * @returns {Promise<Object>} Promise resolving to updated item
 */
update(123, {
  title: 'Updated Lesson Title'
}, { optimistic: true, merge: true })
  .then(item => {
    console.log('Updated item:', item);
  })
  .catch(error => {
    console.error('Failed to update item:', error);
  });
```

### delete()

Delete an item.

```javascript
/**
 * Delete an item
 * @param {number|string} id - Item ID
 * @param {Object} options - Deletion options
 * @param {boolean} options.soft - Soft delete (mark as deleted, default: true)
 * @param {boolean} options.optimistic - Apply changes immediately (default: true)
 * @returns {Promise<boolean>} Promise resolving to success status
 */
delete(123, { soft: true, optimistic: true })
  .then(success => {
    console.log('Item deleted:', success);
  })
  .catch(error => {
    console.error('Failed to delete item:', error);
  });
```

### sync()

Manually trigger synchronization.

```javascript
/**
 * Manually trigger sync for this resource
 * @param {Object} options - Sync options
 * @param {boolean} options.force - Force sync even if recently synced
 * @param {string} options.direction - 'up', 'down', or 'both' (default: 'both')
 * @returns {Promise<Object>} Promise resolving to sync result
 */
sync({ force: true, direction: 'both' })
  .then(result => {
    console.log('Sync completed:', result);
    // result: { uploaded: 5, downloaded: 3, conflicts: 0 }
  })
  .catch(error => {
    console.error('Sync failed:', error);
  });
```

## 📊 Status Properties

### syncStatus

Computed property showing current sync status.

```javascript
const { syncStatus } = useOfflineResource('lessons');

// Possible values:
console.log(syncStatus.value); 
// 'synced' - All data is synced
// 'pending' - Changes waiting to sync
// 'syncing' - Currently syncing
// 'failed' - Last sync failed
// 'conflict' - Conflicts need resolution
```

### hasUnsyncedChanges

Computed property indicating if there are pending changes.

```javascript
const { hasUnsyncedChanges } = useOfflineResource('lessons');

if (hasUnsyncedChanges.value) {
  console.log('There are unsynced changes');
}
```

### isOnline

Reactive property showing network status.

```javascript
const { isOnline } = useOfflineResource('lessons');

watch(isOnline, (online) => {
  if (online) {
    console.log('Back online - syncing...');
  } else {
    console.log('Gone offline - queuing changes...');
  }
});
```

## ⚠️ Error Handling

### Error Types

```javascript
// Network errors
class NetworkError extends Error {
  constructor(message, status) {
    super(message);
    this.name = 'NetworkError';
    this.status = status;
  }
}

// Validation errors
class ValidationError extends Error {
  constructor(message, field) {
    super(message);
    this.name = 'ValidationError';
    this.field = field;
  }
}

// Sync conflicts
class ConflictError extends Error {
  constructor(message, localData, serverData) {
    super(message);
    this.name = 'ConflictError';
    this.localData = localData;
    this.serverData = serverData;
  }
}
```

### Error Handling Patterns

```javascript
const { create } = useOfflineResource('lessons');

create(lessonData)
  .then(lesson => {
    // Success
    console.log('Lesson created:', lesson);
  })
  .catch(error => {
    if (error instanceof ValidationError) {
      console.error('Validation failed:', error.field, error.message);
    } else if (error instanceof NetworkError) {
      console.error('Network error:', error.status, error.message);
    } else if (error instanceof ConflictError) {
      console.error('Conflict detected:', error.localData, error.serverData);
    } else {
      console.error('Unknown error:', error);
    }
  });
```

## 🔧 Advanced Configuration

### Custom Validators

```javascript
const lessons = useOfflineResource('lessons', {
  validator: (data) => {
    if (!data.title || data.title.length < 3) {
      throw new ValidationError('Title must be at least 3 characters', 'title');
    }
    if (!data.content) {
      throw new ValidationError('Content is required', 'content');
    }
    return true;
  }
});
```

### Custom Transformers

```javascript
const lessons = useOfflineResource('lessons', {
  transformer: (data) => {
    // Transform data before storing/sending
    return {
      ...data,
      title: data.title.trim(),
      content: data.content.replace(/\s+/g, ' '),
      updated_at: new Date().toISOString()
    };
  }
});
```

### Custom Sync Strategies

```javascript
const quizAnswers = useOfflineResource('quiz_answers', {
  syncStrategy: 'append-only', // Never overwrite, only add new records
  conflictResolver: (local, server) => {
    // Custom conflict resolution
    return {
      ...server,
      answers: { ...server.answers, ...local.answers }
    };
  }
});
```

---

**Next Steps:**
- See [Basic Usage](./basic-usage.md) for practical examples
- Check [Advanced Usage](./advanced-usage.md) for complex scenarios
- Review [Configuration](./configuration.md) for customization options
