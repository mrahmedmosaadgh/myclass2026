// src/composables/useDivToClipboard.js
import html2canvas from 'html2canvas'

export function useDivToClipboard() {
  const captureAndCopy = async (element) => {
    if (!element) {
      console.error('Element not found')
      return false
    }

    try {
      // High-quality capture
      const canvas = await html2canvas(element, {
        scale: 2,                    // 2x resolution = super sharp
        useCORS: true,              // Allows loading external avatars
        allowTaint: false,
        backgroundColor: '#1a1a1a', // Matches your dark theme
        logging: false,
        windowWidth: element.scrollWidth,
        windowHeight: element.scrollHeight,
      })

      // Convert canvas → Blob → Clipboard
      canvas.toBlob(async (blob) => {
        if (!blob) return

        const item = new ClipboardItem({ 'image/png': blob })
        await navigator.clipboard.write([item])

        // Optional: Show Quasar notification
        // $q.notify({ type: 'positive', message: 'Screenshot copied to clipboard! 🖼️' })
        console.log('Leaderboard screenshot copied to clipboard!')
      }, 'image/png')

      return true
    } catch (err) {
      console.error('Capture failed:', err)
      // $q.notify({ type: 'negative', message: 'Failed to copy screenshot' })
      return false
    }
  }

  return { captureAndCopy }
}