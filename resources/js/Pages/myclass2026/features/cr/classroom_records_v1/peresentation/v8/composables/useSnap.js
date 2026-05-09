const GRID_SIZE = 10
const SNAP_THRESHOLD = 6

export function useSnap() {
  function snap(value) {
    if (value === 0) return 0
    
    const remainder = value % GRID_SIZE
    const absoluteRemainder = Math.abs(remainder)

    if (absoluteRemainder < SNAP_THRESHOLD) {
      return value - remainder
    }

    if (absoluteRemainder > GRID_SIZE - SNAP_THRESHOLD) {
      if (value > 0) {
        return value + (GRID_SIZE - absoluteRemainder)
      } else {
        return value - (GRID_SIZE - absoluteRemainder)
      }
    }

    return value
  }

  function snapToGrid(value, gridSize = GRID_SIZE) {
    return Math.round(value / gridSize) * gridSize
  }

  function getNearestGridLine(value, gridSize = GRID_SIZE) {
    return Math.round(value / gridSize) * gridSize
  }

  function shouldSnap(value, threshold = SNAP_THRESHOLD, gridSize = GRID_SIZE) {
    const remainder = Math.abs(value % gridSize)
    return remainder < threshold || remainder > gridSize - threshold
  }

  return {
    snap,
    snapToGrid,
    getNearestGridLine,
    shouldSnap,
    GRID_SIZE,
    SNAP_THRESHOLD
  }
}
