import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { fgIdb } from '../services/fg-idb.service'
import { useQuasar } from 'quasar'

export function useFgSync() {
  const $q = useQuasar()
  const isSyncing = ref(false)
  const isOnline = ref(navigator.onLine)
  const lastSyncTime = ref(null)
  
  const updateOnlineStatus = () => {
    const wasOffline = !isOnline.value
    isOnline.value = navigator.onLine
    
    if (isOnline.value && wasOffline) {
      $q.notify({ type: 'positive', message: 'You are back online. Syncing...', position: 'bottom' })
      syncAll()
    } else if (!isOnline.value) {
      $q.notify({ type: 'warning', message: 'You are offline. Changes saved locally.', position: 'bottom' })
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
          // We assume a generic POST /api/fg/sync endpoint handles this payload
          const { data } = await axios.post('/api/fg/sync', pendingChanges)
          
          // 3. Mark successful pushes as synced
          if (data.synced_ids) {
              await fgIdb.markSynced('domains', data.synced_ids.domains)
              await fgIdb.markSynced('tasks', data.synced_ids.tasks)
              await fgIdb.markSynced('sub_tasks', data.synced_ids.sub_tasks)
              await fgIdb.markSynced('notes', data.synced_ids.notes)
              await fgIdb.markSynced('sessions', data.synced_ids.sessions)
          }
      }
      
      // 4. Pull latest state from server 
      // This could be optimized to only pull since last sync time, but keeping simple for v1.2
      const { data: serverState } = await axios.get('/api/fg/sync')
      
      if (serverState) {
          // 5. Save incoming to local DB (overwrites unless there's logic to merge, bulkPut does blind overwrite)
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

  onMounted(() => {
    window.addEventListener('online', updateOnlineStatus)
    window.addEventListener('offline', updateOnlineStatus)
    
    // Auto-sync on load if online
    if (isOnline.value) {
       syncAll()
    }
  })

  onUnmounted(() => {
    window.removeEventListener('online', updateOnlineStatus)
    window.removeEventListener('offline', updateOnlineStatus)
  })

  return {
    isOnline,
    isSyncing,
    lastSyncTime,
    syncAll
  }
}
