import { useSnap } from './useSnap';

export function useDrag(element, onUpdate) {
  const { snap } = useSnap();
  
  let startX = 0;
  let startY = 0;
  let initialX = 0;
  let initialY = 0;

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

    onUpdate({
      x: snap(initialX + dx),
      y: snap(initialY + dy)
    });
  }

  function onEnd() {
    window.removeEventListener('mousemove', onMove);
    window.removeEventListener('mouseup', onEnd);
    window.removeEventListener('touchmove', onMove);
    window.removeEventListener('touchend', onEnd);
  }

  function startDrag(e) {
    if (e.type === 'touchstart' && e.cancelable) e.preventDefault();
    else if (e.type !== 'touchstart') e.preventDefault();

    const coords = getClientCoordinates(e);
    startX = coords.clientX;
    startY = coords.clientY;

    initialX = element.x;
    initialY = element.y;

    window.addEventListener('mousemove', onMove);
    window.addEventListener('mouseup', onEnd);
    window.addEventListener('touchmove', onMove, { passive: false });
    window.addEventListener('touchend', onEnd);
  }

  return { startDrag };
}
