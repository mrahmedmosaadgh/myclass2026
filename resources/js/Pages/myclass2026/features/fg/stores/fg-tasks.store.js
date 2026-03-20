import { defineStore } from 'pinia'
import { fgIdb } from '../services/fg-idb.service'
import { useFgSync } from '../composables/fg-use-sync'

export const useFgTasksStore = defineStore('fg-tasks', {
  state: () => ({
    tasks: [],
    loading: false,
    error: null,
  }),

  getters: {
    activeTasks: (state) => state.tasks.filter(t => t.status === 'active'),
    inboxTasks: (state) => state.tasks.filter(t => t.status === 'inbox'),
    doneTasks: (state) => state.tasks.filter(t => t.status === 'done'),
    getById: (state) => (id) => state.tasks.find(t => t.id === id),
  },

  actions: {
    async fetchTasks(params = {}) {
      this.loading = true
      try {
        // Load from IDB
        let localData = await fgIdb.getAllActive('tasks')
        
        // Very basic frontend filtering 
        if (params.status) {
            localData = localData.filter(t => t.status === params.status)
        }
        if (params.is_today) {
            localData = localData.filter(t => t.is_today === true)
        }
        
        this.tasks = localData
        
        // Background sync
        const { isOnline, syncAll } = useFgSync()
        if (isOnline.value) syncAll()
        
        this.error = null
      } catch (err) {
        this.error = 'Failed to load tasks from local storage'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async createTask(payload) {
      try {
        const savedLocal = await fgIdb.saveLocally('tasks', {
            ...payload, 
            status: payload.status ?? 'inbox',
            importance: payload.importance ?? 0,
            is_today: payload.is_today ?? false,
            sort_order: payload.sort_order ?? 0 
        })
        
        this.tasks.push(savedLocal)
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return savedLocal
      } catch (err) {
        console.error('Failed to create task locally', err)
        throw err
      }
    },

    async updateTask(id, payload) {
      try {
        const existing = this.tasks.find(t => t.id === id)
        if (!existing) throw new Error('Task not found locally')
        
        if (payload.status === 'done' && existing.status !== 'done') {
            payload.completed_at = new Date().toISOString()
        }
        
        const updated = await fgIdb.saveLocally('tasks', { ...existing, ...payload })
        
        const index = this.tasks.findIndex(t => t.id === id)
        if (index !== -1) {
            this.tasks.splice(index, 1, updated)
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return updated
      } catch (err) {
        console.error('Failed to update task locally', err)
        throw err
      }
    },

    async deleteTask(id) {
      try {
        const existing = this.tasks.find(t => t.id === id)
        if (!existing) return
        
        await fgIdb.saveLocally('tasks', { ...existing, deleted_at: new Date().toISOString() })
        
        this.tasks = this.tasks.filter(t => t.id !== id)
        
        const { syncAll } = useFgSync()
        syncAll()
      } catch (err) {
        console.error('Failed to soft delete task locally', err)
        throw err
      }
    }
  }
})
