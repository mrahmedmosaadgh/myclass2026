import Dexie from 'dexie'

export const db = new Dexie('FocusGridDB')

db.version(1).stores({
  domains: 'id, name, emoji, user_id, sort_order, deleted_at, sync_status',
  tasks: 'id, domain_id, title, notes, status, sort_order, importance, due_date, is_today, user_id, deleted_at, sync_status',
  sub_tasks: 'id, task_id, title, is_completed, sort_order, deleted_at, sync_status',
  notes: 'id, domain_id, user_id, body, source, deleted_at, sync_status',
  sessions: 'id, task_id, user_id, started_at, ended_at, duration_seconds, status, sync_status'
})

export const fgIdb = {
  async init() {
    try {
      await db.open()
      console.log('Focus Grid IndexedDB Initialized')
    } catch (err) {
      console.error('Failed to open Focus Grid DB', err)
    }
  },
  
  // Generic save for any table
  async saveLocally(tableName, data) {
    if (!db[tableName]) return Promise.reject(`Table ${tableName} not found`)
    
    // Ensure sync_status is set for new changes if not already set
    if (!data.sync_status || data.sync_status === 'synced') {
       data.sync_status = 'pending_push'
    }
    
    // Fallback ID if creating offline
    if (!data.id) {
       data.id = `local_${Date.now()}_${Math.random().toString(36).substring(2, 9)}`
    }
    
    await db[tableName].put(data)
    return data
  },
  
  async getLocalChanges() {
    const changes = {
      domains: await db.domains.where('sync_status').equals('pending_push').toArray(),
      tasks: await db.tasks.where('sync_status').equals('pending_push').toArray(),
      sub_tasks: await db.sub_tasks.where('sync_status').equals('pending_push').toArray(),
      notes: await db.notes.where('sync_status').equals('pending_push').toArray(),
      sessions: await db.sessions.where('sync_status').equals('pending_push').toArray(),
    }
    return changes
  },
  
  // Mark as synced
  async markSynced(tableName, ids) {
    if (!ids || ids.length === 0) return
    if (!db[tableName]) return
    
    await db.transaction('rw', db[tableName], async () => {
       for (const id of ids) {
         await db[tableName].update(id, { sync_status: 'synced' })
       }
    })
  },
  
  // Clean sweep remote items into local
  async bulkPut(tableName, items) {
     if (!db[tableName] || !items.length) return
     await db[tableName].bulkPut(items)
  },

  // Get all active
  async getAllActive(tableName) {
     if (!db[tableName]) return []
     // Return non-deleted, or items where deleted_at is null
     const all = await db[tableName].toArray()
     return all.filter(item => !item.deleted_at)
  }
}
