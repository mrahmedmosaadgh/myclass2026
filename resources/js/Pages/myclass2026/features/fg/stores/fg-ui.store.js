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
    },

    // Layout preference: 'v1' (default) | 'v2' | 'v3'
    layout: localStorage.getItem('fg-layout') || 'v1',

    // V2/V3 Active Tab (0=Now, 1=Plan, 2=Review)
    activeTab: parseInt(localStorage.getItem('fg-active-tab') || '0', 10)
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
    },

    setLayout(layoutId) {
      this.layout = layoutId
      localStorage.setItem('fg-layout', layoutId)
    },

    setActiveTab(idx) {
      this.activeTab = idx
      localStorage.setItem('fg-active-tab', idx)
    }
  }
})
