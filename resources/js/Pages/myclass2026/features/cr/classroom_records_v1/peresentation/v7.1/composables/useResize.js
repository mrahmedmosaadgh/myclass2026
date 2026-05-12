import { useSnap } from './useSnap';

export function useResize(element, onUpdate) {
  const { snap } = useSnap();
  
  let startX = 0;
  let startY = 0;

  let startWidth = 0;
  let startHeight = 0;
  let startXPos = 0;
  let startYPos = 0;

  let direction = null;

  const MIN_WIDTH = 50;
  const MIN_HEIGHT = 30;

  function getClientCoordinates(e) {
    if (e.touches && e.touches.length > 0) {
      return { clientX: e.touches[0].clientX, clientY: e.touches[0].clientY };
    }
    return { clientX: e.clientX, clientY: e.clientY };
  }

  function onMove(e) {
    const coords = getClientCoordinates(e);
    const dx = coords.clientX - startX;
    const dy = coords.clientY - startY;

    let newWidth = startWidth;
    let newHeight = startHeight;
    let newX = startXPos;
    let newY = startYPos;

    // Horizontal resizing
    if (direction.includes('e')) {
      newWidth = Math.max(MIN_WIDTH, startWidth + dx);
    }
    if (direction.includes('w')) {
      newWidth = Math.max(MIN_WIDTH, startWidth - dx);
      newX = startXPos + dx;
    }

    // Vertical resizing
    if (direction.includes('s')) {
      newHeight = Math.max(MIN_HEIGHT, startHeight + dy);
    }
    if (direction.includes('n')) {
      newHeight = Math.max(MIN_HEIGHT, startHeight - dy);
      newY = startYPos + dy;
    }

    onUpdate({
      x: snap(newX),
      y: snap(newY),
      width: snap(newWidth),
      height: snap(newHeight)
    });
  }

  function onEnd() {
    window.removeEventListener('mousemove', onMove);
    window.removeEventListener('mouseup', onEnd);
    window.removeEventListener('touchmove', onMove);
    window.removeEventListener('touchend', onEnd);
  }

  function startResize(e, dir) {
    if (e.type === 'touchstart' && e.cancelable) e.preventDefault();
    else if (e.type !== 'touchstart') e.preventDefault();
    e.stopPropagation();

    direction = dir;

    const coords = getClientCoordinates(e);

    startX = coords.clientX;
    startY = coords.clientY;

    startWidth = element.width;
    startHeight = element.height;
    startXPos = element.x;
    startYPos = element.y;

    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onEnd);
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('touchend', onEnd);
  }

  return { startResize };
}
