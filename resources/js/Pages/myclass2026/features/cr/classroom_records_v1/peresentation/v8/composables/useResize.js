import { useSnap } from './useSnap.js'

export function useResize(element, onUpdate) {
  let startX = 0
  let startY = 0
  let startWidth = 0
  let startHeight = 0
  let startXPos = 0
  let startYPos = 0
  let direction = null

  const { snap } = useSnap()
  const MIN_WIDTH = 50
  const MIN_HEIGHT = 30

  function onMouseMove(e) {
    const dx = e.clientX - startX
    const dy = e.clientY - startY

    let newWidth = startWidth
    let newHeight = startHeight
    let newX = startXPos
    let newY = startYPos

    // Horizontal resizing
    if (direction.includes('e')) {
      newWidth = Math.max(MIN_WIDTH, startWidth + dx)
    }
    if (direction.includes('w')) {
      newWidth = Math.max(MIN_WIDTH, startWidth - dx)
      newX = startXPos + dx
    }

    // Vertical resizing
    if (direction.includes('s')) {
      newHeight = Math.max(MIN_HEIGHT, startHeight + dy)
    }
    if (direction.includes('n')) {
      newHeight = Math.max(MIN_HEIGHT, startHeight - dy)
      newY = startYPos + dy
    }

    onUpdate({
      x: snap(newX),
      y: snap(newY),
      width: snap(newWidth),
      height: snap(newHeight)
    })
  }

  function onMouseUp() {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseup', onMouseUp)
    
    // Clean up any cursor styles
    document.body.style.cursor = ''
    document.body.style.userSelect = ''
  }

  function startResize(e, dir) {
    e.preventDefault()
    e.stopPropagation()

    direction = dir
    startX = e.clientX
    startY = e.clientY

    startWidth = element.width
    startHeight = element.height
    startXPos = element.x
    startYPos = element.y

    // Set cursor styles for better UX
    document.body.style.userSelect = 'none'

    window.addEventListener('mousemove', onMouseMove)
    window.addEventListener('mouseup', onMouseUp)
  }

  function getCursorForDirection(direction) {
    const cursors = {
      'nw': 'nwse-resize',
      'n': 'ns-resize',
      'ne': 'nesw-resize',
      'e': 'ew-resize',
      'se': 'nwse-resize',
      's': 'ns-resize',
      'sw': 'nesw-resize',
      'w': 'ew-resize'
    }
    return cursors[direction] || 'default'
  }

  return { startResize, getCursorForDirection }
}
