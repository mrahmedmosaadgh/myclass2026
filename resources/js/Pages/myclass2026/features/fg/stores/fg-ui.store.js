import { defineStore } from 'pinia'

export const useFgUiStore = defineStore('fg-ui', {
  state: () => ({
    isPlanningMode: false,
    isReviewMode: false,
    
    // Quick Capture state
    quickCapture: {
      isOpen: false,
      text: ''
    },
    
    // AI Modal state
    aiModal: {
      isOpen: false,
      loading: false,
      parsedData: null // { tasks: [], notes: [] }
    },
    
    // Active filters
    filters: {
      domainId: null,
      status: 'active', // default
      search: ''
    }
  }),

  actions: {
    toggleMode(mode) {
      if (mode === 'planning') {
        this.isPlanningMode = !this.isPlanningMode
        this.isReviewMode = false
      } else if (mode === 'review') {
        this.isReviewMode = !this.isReviewMode
        this.isPlanningMode = false
      }
    },
    
    openQuickCapture() {
      this.quickCapture.isOpen = true
    },
    
    closeQuickCapture() {
      this.quickCapture.isOpen = false
      this.quickCapture.text = ''
    },
    
    openAiModal(data = null) {
      this.aiModal.isOpen = true
      if (data) this.aiModal.parsedData = data
    },
    
    closeAiModal() {
      this.aiModal.isOpen = false
      this.aiModal.parsedData = null
      this.aiModal.loading = false
    },
    
    setFilter(key, value) {
      this.filters[key] = value
    }
  }
})
