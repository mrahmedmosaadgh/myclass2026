import { useSnap } from './useSnap.js'

export function useDrag(element, onUpdate) {
  let startX = 0
  let startY = 0
  let initialX = 0
  let initialY = 0
  let isDragging = false

  const { snap } = useSnap()

  function getCoordinates(e) {
    if (e.touches && e.touches.length > 0) {
      return { x: e.touches[0].clientX, y: e.touches[0].clientY }
    }
    return { x: e.clientX, y: e.clientY }
  }

  function onMove(e) {
    if (!isDragging) return
    
    e.preventDefault()
    
    const coords = getCoordinates(e)
    const dx = coords.x - startX
    const dy = coords.y - startY

    const newX = snap(initialX + dx)
    const newY = snap(initialY + dy)

    onUpdate({
      x: newX,
      y: newY
    })
  }

  function onEnd() {
    if (!isDragging) return
    
    isDragging = false
    
    // Remove both mouse and touch event listeners
    window.removeEventListener('mousemove', onMove)
    window.removeEventListener('mouseup', onEnd)
    window.removeEventListener('touchmove', onMove, { passive: false })
    window.removeEventListener('touchend', onEnd)
    window.removeEventListener('touchcancel', onEnd)
    
    // Clean up any cursor styles
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
    document.body.style.touchAction = ''
  }

  function startDrag(e) {
    // Don't start drag if it's a right-click or on a resize handle
    if (e.button === 2 || e.target.classList.contains('resize-handle')) {
      return
    }

    const coords = getCoordinates(e)
    
    e.preventDefault()
    e.stopPropagation()

    startX = coords.x
    startY = coords.y

    initialX = element.x
    initialY = element.y
    isDragging = true

    // Set cursor styles for better UX
    document.body.style.cursor = 'grabbing'
    document.body.style.userSelect = 'none'
    document.body.style.touchAction = 'none'

    // Add both mouse and touch event listeners
    window.addEventListener('mousemove', onMove)
    window.addEventListener('mouseup', onEnd)
    window.addEventListener('touchmove', onMove, { passive: false })
    window.addEventListener('touchend', onEnd)
    window.addEventListener('touchcancel', onEnd)
  }

  return { startDrag }
}
