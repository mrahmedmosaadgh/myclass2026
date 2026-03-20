import { defineStore } from 'pinia'
import { fgIdb } from '../services/fg-idb.service'
import { useFgSync } from '../composables/fg-use-sync'
import { useFgTasksStore } from './fg-tasks.store'

export const useFgSessionsStore = defineStore('fg-sessions', {
  state: () => ({
    sessions: [],
    loading: false,
    error: null,
  }),

  getters: {
    activeSession: (state) => state.sessions.find(s => s.status === 'active'),
    getById: (state) => (id) => state.sessions.find(s => s.id === id),
  },

  actions: {
    async fetchSessions() {
      this.loading = true
      try {
        const localData = await fgIdb.getAllActive('sessions')
        this.sessions = localData
        
        const sync = useFgSync()
        if (sync.isOnline.value) sync.syncAll()
        
        this.error = null
      } catch (err) {
        this.error = 'Failed to fetch sessions locally'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async createSession(payload) {
      try {
        const saved = await fgIdb.saveLocally('sessions', {
             ...payload,
             status: 'active',
             started_at: new Date().toISOString()
        })
        
        this.sessions.unshift(saved)
        
        // Ensure standard task transitions locally
        const tasksStore = useFgTasksStore()
        const parentTask = tasksStore.getById(payload.task_id)
        if (parentTask && parentTask.status !== 'active') {
             await tasksStore.updateTask(payload.task_id, { status: 'active' })
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return saved
      } catch (err) {
        console.error('Failed to create session locally', err)
        throw err
      }
    },

    async updateSession(id, payload) {
      try {
        const existing = this.sessions.find(s => s.id === id)
        if (!existing) throw new Error('Session not found locally')
        
        if (payload.status === 'completed' || payload.status === 'drifted') {
             payload.ended_at = new Date().toISOString()
             const start = new Date(existing.started_at).getTime()
             const end = new Date(payload.ended_at).getTime()
             payload.duration_seconds = Math.floor((end - start) / 1000)
        }
        
        const updated = await fgIdb.saveLocally('sessions', { ...existing, ...payload })
        
        const index = this.sessions.findIndex(s => s.id === id)
        if (index !== -1) {
             this.sessions.splice(index, 1, updated)
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return updated
      } catch (err) {
        console.error('Failed to update session locally', err)
        throw err
      }
    }
  }
})
