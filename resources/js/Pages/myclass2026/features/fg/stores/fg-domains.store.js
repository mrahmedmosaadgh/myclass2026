import { defineStore } from 'pinia'
import { fgIdb } from '../services/fg-idb.service'
import { useFgSync } from '../composables/fg-use-sync'

export const useFgDomainsStore = defineStore('fg-domains', {
  state: () => ({
    domains: [],
    loading: false,
    error: null,
  }),

  getters: {
    activeDomains: (state) => state.domains.filter(d => d.is_active),
    getById: (state) => (id) => state.domains.find(d => d.id === id),
  },

  actions: {
    async fetchDomains() {
      this.loading = true
      try {
        // 1. Load from local IndexedDB first
        const localData = await fgIdb.getAllActive('domains')
        this.domains = localData
        
        // 2. Trigger sync in background
        const { isOnline, syncAll } = useFgSync()
        if (isOnline.value) {
            syncAll()
        }
        
        this.error = null
      } catch (err) {
        this.error = 'Failed to load domains from local storage'
        console.error(err)
      } finally {
        this.loading = false
      }
    },

    async createDomain(payload) {
      try {
        const savedLocal = await fgIdb.saveLocally('domains', {
            ...payload, 
            is_active: payload.is_active ?? true, 
            sort_order: payload.sort_order ?? 0 
        })
        this.domains.push(savedLocal)
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return savedLocal
      } catch (err) {
        console.error('Failed to create domain locally', err)
        throw err
      }
    },

    async updateDomain(id, payload) {
      try {
        const existing = this.domains.find(d => d.id === id)
        if (!existing) throw new Error('Domain not found locally')
        
        const updated = await fgIdb.saveLocally('domains', { ...existing, ...payload })
        
        const index = this.domains.findIndex(d => d.id === id)
        if (index !== -1) {
            this.domains.splice(index, 1, updated)
        }
        
        const { syncAll } = useFgSync()
        syncAll()
        
        return updated
      } catch (err) {
        console.error('Failed to update domain locally', err)
        throw err
      }
    },

    async deleteDomain(id) {
      try {
        const existing = this.domains.find(d => d.id === id)
        if (!existing) return
        
        await fgIdb.saveLocally('domains', { ...existing, deleted_at: new Date().toISOString() })
        
        this.domains = this.domains.filter(d => d.id !== id)
        
        const { syncAll } = useFgSync()
        syncAll()
      } catch (err) {
        console.error('Failed to soft delete domain locally', err)
        throw err
      }
    }
  }
})
