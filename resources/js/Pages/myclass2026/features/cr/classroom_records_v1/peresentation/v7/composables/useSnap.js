const GRID_SIZE = 10;
const SNAP_THRESHOLD = 6;

export function useSnap() {
  function snap(value) {
    const remainder = value % GRID_SIZE;

    if (remainder < SNAP_THRESHOLD) {
      return value - remainder;
    }

    if (remainder > GRID_SIZE - SNAP_THRESHOLD) {
      return value + (GRID_SIZE - remainder);
    }

    return value;
  }

  return { snap };
}
