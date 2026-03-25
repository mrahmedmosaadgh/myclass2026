import { usePresentationStore } from '@/stores/presentationStore'

export function usePaste() {
  const store = usePresentationStore()

  function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2)
  }

  async function pasteFromClipboard() {
    try {
      // Try Clipboard API for images first
      if (navigator.clipboard?.read) {
        const clipboardItems = await navigator.clipboard.read()
        
        for (const item of clipboardItems) {
          // Check for images
          for (const type of item.types) {
            if (type.startsWith('image/')) {
              const blob = await item.getType(type)
              const reader = new FileReader()
              
              reader.onload = (e) => {
                store.addElement({
                  id: generateId(),
                  type: 'image',
                  src: e.target.result,
                  x: 100,
                  y: 100,
                  width: 300,
                  height: 200,
                  visibilityOption: 'shown-clickable',
                  isVisible: true,
                  zIndex: 1
                })
              }
              
              reader.readAsDataURL(blob)
              return { success: true, type: 'image' }
            }
          }
          
          // Check for HTML content
          if (item.types.includes('text/html')) {
            const blob = await item.getType('text/html')
            const text = await blob.text()
            
            if (text) {
              store.addElement({
                id: generateId(),
                type: 'html',
                content: text,
                x: 100,
                y: 100,
                width: 400,
                height: 'auto',
                visibilityOption: 'shown-clickable',
                isVisible: true,
                zIndex: 1
              })
              return { success: true, type: 'html' }
            }
          }
        }
      }
      
      // Fallback to text
      if (navigator.clipboard?.readText) {
        const text = await navigator.clipboard.readText()
        
        if (text) {
          store.addElement({
            id: generateId(),
            type: 'text',
            content: text,
            x: 100,
            y: 100,
            width: 400,
            height: 'auto',
            fontSize: 24,
            color: '#000000',
            visibilityOption: 'shown-clickable',
            isVisible: true,
            zIndex: 1
          })
          return { success: true, type: 'text' }
        }
      }
      
      return { success: false, error: 'No supported content in clipboard' }
    } catch (error) {
      console.warn('Clipboard paste failed:', error)
      return { success: false, error: error.message }
    }
  }

  function handlePasteEvent(event) {
    event.preventDefault()
    const items = event.clipboardData?.items
    
    if (!items) return { success: false, error: 'No clipboard data' }

    // First pass: check for images
    for (let item of items) {
      if (item.type.indexOf('image') !== -1) {
        const blob = item.getAsFile()
        const reader = new FileReader()
        
        reader.onload = (e) => {
          store.addElement({
            id: generateId(),
            type: 'image',
            src: e.target.result,
            x: 100,
            y: 100,
            width: 300,
            height: 200,
            visibilityOption: 'shown-clickable',
            isVisible: true,
            zIndex: 1
          })
        }
        
        reader.readAsDataURL(blob)
        return { success: true, type: 'image' }
      }
    }
    
    // Second pass: check for HTML
    for (let item of items) {
      if (item.type === 'text/html') {
        item.getAsString((html) => {
          store.addElement({
            id: generateId(),
            type: 'html',
            content: html,
            x: 100,
            y: 100,
            width: 400,
            height: 'auto',
            visibilityOption: 'shown-clickable',
            isVisible: true,
            zIndex: 1
          })
        })
        return { success: true, type: 'html' }
      }
    }
    
    // Third pass: plain text
    for (let item of items) {
      if (item.type === 'text/plain') {
        item.getAsString((text) => {
          store.addElement({
            id: generateId(),
            type: 'text',
            content: text,
            x: 100,
            y: 100,
            width: 400,
            height: 'auto',
            fontSize: 24,
            color: '#000000',
            visibilityOption: 'shown-clickable',
            isVisible: true,
            zIndex: 1
          })
        })
        return { success: true, type: 'text' }
      }
    }
    
    return { success: false, error: 'No supported content' }
  }

  return {
    pasteFromClipboard,
    handlePasteEvent
  }
}
