import { ref } from 'vue'
import axios from 'axios'
import { fgIdb } from '../services/fg-idb.service'
import { Notify } from 'quasar'

export function useFgSync() {
  const isSyncing = ref(false)
  const isOnline = ref(navigator.onLine)
  const lastSyncTime = ref(null)
  
  const updateOnlineStatus = () => {
    const wasOffline = !isOnline.value
    isOnline.value = navigator.onLine
    
    if (isOnline.value && wasOffline) {
      Notify.create({ type: 'positive', message: 'You are back online. Syncing...', position: 'bottom' })
      syncAll()
    } else if (!isOnline.value) {
      Notify.create({ type: 'warning', message: 'You are offline. Changes saved locally.', position: 'bottom' })
    }
  }

  const syncAll = async () => {
    if (!isOnline.value || isSyncing.value) return
    
    isSyncing.value = true
    try {
      // 1. Get all pending local changes
      const pendingChanges = await fgIdb.getLocalChanges()
      
      const hasChanges = Object.values(pendingChanges).some(arr => arr.length > 0)
      
      // 2. Push to server if needed
      if (hasChanges) {
          const { data } = await axios.post('/api/fg/sync', pendingChanges)
          
          // 3. Mark successful pushes as synced AND update IDs if mapping provided
          if (data.synced_ids) {
              for (const [entity, ids] of Object.entries(data.synced_ids)) {
                 // FgSyncService might return a map of {local_id: server_id} or just [local_id]
                 // We handle both: if value is string, it's the new ID.
                 await fgIdb.markSynced(entity, ids)
              }
          }
      }
      
      // 4. Pull latest state from server 
      const { data: serverState } = await axios.get('/api/fg/sync')
      
      if (serverState) {
          // 5. Save incoming to local DB
          await fgIdb.bulkPut('domains', serverState.domains || [])
          await fgIdb.bulkPut('tasks', serverState.tasks || [])
          await fgIdb.bulkPut('sub_tasks', serverState.sub_tasks || [])
          await fgIdb.bulkPut('notes', serverState.notes || [])
          await fgIdb.bulkPut('sessions', serverState.sessions || [])
      }

      lastSyncTime.value = new Date()
    } catch (err) {
      console.warn('Sync failed, will retry later', err)
    } finally {
      isSyncing.value = false
    }
  }

  const initSyncListeners = () => {
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    
    // Auto-sync on load if online
    if (isOnline.value) {
       syncAll()
    }

    return () => {
      window.removeEventListener('online', updateOnlineStatus)
      window.removeEventListener('offline', updateOnlineStatus)
    }
  }

  return {
    isOnline,
    isSyncing,
    lastSyncTime,
    syncAll,
    initSyncListeners
  }
}
