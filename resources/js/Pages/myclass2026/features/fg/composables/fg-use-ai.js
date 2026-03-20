import { ref } from 'vue'
import { fgApi } from '../services/fg-api.service'
import { useFgUiStore } from '../stores/fg-ui.store'

export function useFgAi() {
  const uiStore = useFgUiStore()
  const isVenting = ref(false)
  const error = ref(null)

  const processVent = async (text) => {
    isVenting.value = true
    error.value = null
    try {
      const { data } = await fgApi.vent(text)
      
      // Open the AI Review Modal with the results
      uiStore.openAiModal(data)
      
      return data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to process vent'
      console.error('Focus Grid AI error:', err)
      throw err
    } finally {
      isVenting.value = false
    }
  }

  return {
    isVenting,
    error,
    processVent
  }
}
