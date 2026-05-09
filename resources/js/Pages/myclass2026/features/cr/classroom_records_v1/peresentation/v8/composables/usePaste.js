export function usePaste() {
  let element = null
  let pasteElement = null

  async function handlePaste(e) {
    e.preventDefault()
    
    try {
      const clipboardItems = await navigator.clipboard.read()
      
      for (const clipboardItem of clipboardItems) {
        for (const type of clipboardItem.types) {
          if (type === 'text/plain') {
            const text = await clipboardItem.getType('text/plain')
            const textContent = await text.text()
            
            // Try to parse as JSON first (for pasted elements)
            try {
              const jsonData = JSON.parse(textContent)
              if (jsonData.type && jsonData.x !== undefined && jsonData.y !== undefined) {
                // This is likely a pasted element
                pasteElement = jsonData
                return
              }
            } catch {
              // Not JSON, treat as plain text
              pasteElement = {
                type: 'text',
                content: textContent,
                width: 300,
                height: 'auto'
              }
              return
            }
          }
          
          if (type.startsWith('image/')) {
            const blob = await clipboardItem.getType(type)
            const reader = new FileReader()
            
            reader.onload = () => {
              pasteElement = {
                type: 'image',
                src: reader.result,
                width: 400,
                height: 300
              }
            }
            
            reader.readAsDataURL(blob)
            return
          }
          
          if (type === 'text/html') {
            const html = await clipboardItem.getType('text/html')
            const htmlContent = await html.text()
            
            pasteElement = {
              type: 'html',
              content: htmlContent,
              width: 400,
              height: 200
            }
            return
          }
        }
      }
    } catch (error) {
      console.warn('Failed to read clipboard:', error)
      // Fallback to traditional paste for older browsers
      const text = e.clipboardData?.getData('text/plain')
      if (text) {
        pasteElement = {
          type: 'text',
          content: text,
          width: 300,
          height: 'auto'
        }
      }
    }
  }

  function getPasteElement() {
    const element = pasteElement
    pasteElement = null
    return element
  }

  function hasPasteElement() {
    return !!pasteElement
  }

  return {
    handlePaste,
    getPasteElement,
    hasPasteElement
  }
}
