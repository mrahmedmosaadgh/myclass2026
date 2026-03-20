import { defineStore } from 'pinia'
import { fgIdb } from '../services/fg-idb.service'
import { useFgSync } from '../composables/fg-use-sync'

export const useFgNotesStore = defineStore('fg-notes', {
  state: () => ({
    notes: [],
    loading: false,
    error: null,
  }),

  getters: {
    getById: (state) => (id) => state.notes.find(n => n.id === id),
  },

  actions: {
    async fetchNotes() {
      this.loading = true
      try {
        const localData = await fgIdb.getAllActive('notes')
        this.notes = localData
        
        const sync = useFgSync()
        if (sync.isOnline.value) sync.syncAll()
        
        this.error = null
      } catch (err) {
        this.error = 'Failed to fetch notes locally'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async createNote(payload) {
      try {
        const saved = await fgIdb.saveLocally('notes', { ...payload })
        this.notes.unshift(saved) 
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return saved
      } catch (err) {
        console.error('Failed to create note locally', err)
        throw err
      }
    },

    async updateNote(id, payload) {
      try {
        const existing = this.notes.find(n => n.id === id)
        if (!existing) throw new Error('Note not found locally')
        
        const updated = await fgIdb.saveLocally('notes', { ...existing, ...payload })
        
        const index = this.notes.findIndex(n => n.id === id)
        if (index !== -1) {
            this.notes.splice(index, 1, updated)
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return updated
      } catch (err) {
        console.error('Failed to update note', err)
        throw err
      }
    },

    async deleteNote(id) {
      try {
        const existing = this.notes.find(n => n.id === id)
        if (!existing) return
        
        await fgIdb.saveLocally('notes', { ...existing, deleted_at: new Date().toISOString() })
        
        this.notes = this.notes.filter(n => n.id !== id)
        
        const { syncAll } = useFgSync()
        syncAll()
      } catch (err) {
        console.error('Failed to delete note', err)
        throw err
      }
    }
  }
})
