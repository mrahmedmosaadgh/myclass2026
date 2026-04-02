import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useDrawingStore } from '../../stores/drawingStore';
import { usePresentationStore } from '../../stores/presentationStore';
import { getStroke } from 'perfect-freehand';

const DEFAULT_PERFECT_FREEHAND_OPTIONS = {
  thinning: 0.3, // Reduced from 0.6 for more consistent width
  smoothing: 0.5, // Reduced from 0.6 for more responsive drawing
  streamline: 0.3, // Reduced from 0.4 for better fast drawing
  easing: (t) => t,
  simulatePressure: true,
  last: true,
  size: 4 // Base size multiplier
};

function hexToRgba(hex, opacity = 1) {
  const sanitized = hex.replace('#', '');
  const bigint = parseInt(sanitized, 16);
  const r = (bigint >> 16) & 255;
  const g = (bigint >> 8) & 255;
  const b = bigint & 255;
  return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}

function createShapeFromPoints(tool, startPoint, endPoint, style) {
  const base = {
    id: style.id,
    tool,
    type: 'shape',
    style,
    timestamp: new Date().toISOString()
  };

  switch (tool) {
    case 'rectangle': {
      const x = Math.min(startPoint.x, endPoint.x);
      const y = Math.min(startPoint.y, endPoint.y);
      const width = Math.abs(endPoint.x - startPoint.x);
      const height = Math.abs(endPoint.y - startPoint.y);
      return {
        ...base,
        shape: 'rectangle',
        x,
        y,
        width,
        height
      };
    }
    case 'circle': {
      const radius = Math.hypot(endPoint.x - startPoint.x, endPoint.y - startPoint.y);
      return {
        ...base,
        shape: 'circle',
        cx: startPoint.x,
        cy: startPoint.y,
        radius
      };
    }
    case 'line': {
      return {
        ...base,
        shape: 'line',
        x1: startPoint.x,
        y1: startPoint.y,
        x2: endPoint.x,
        y2: endPoint.y
      };
    }
    case 'arrow': {
      return {
        ...base,
        shape: 'arrow',
        x1: startPoint.x,
        y1: startPoint.y,
        x2: endPoint.x,
        y2: endPoint.y
      };
    }
    default:
      return null;
  }
}

function drawShape(ctx, shape) {
  if (!shape) return;
  const color = hexToRgba(shape.style.color, (shape.style.opacity || 100) / 100);
  const width = shape.style.size || 4;
  ctx.save();
  ctx.strokeStyle = color;
  ctx.fillStyle = color;
  ctx.lineWidth = width;
  ctx.lineCap = 'round';
  ctx.lineJoin = 'round';

  switch (shape.shape) {
    case 'rectangle':
      ctx.beginPath();
      ctx.strokeRect(shape.x, shape.y, shape.width, shape.height);
      break;
    case 'circle':
      ctx.beginPath();
      ctx.arc(shape.cx, shape.cy, shape.radius, 0, Math.PI * 2);
      ctx.stroke();
      break;
    case 'line':
      ctx.beginPath();
      ctx.moveTo(shape.x1, shape.y1);
      ctx.lineTo(shape.x2, shape.y2);
      ctx.stroke();
      break;
    case 'arrow': {
      const headLength = 12 + (width * 0.8);
      const angle = Math.atan2(shape.y2 - shape.y1, shape.x2 - shape.x1);
      ctx.beginPath();
      ctx.moveTo(shape.x1, shape.y1);
      ctx.lineTo(shape.x2, shape.y2);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(shape.x2, shape.y2);
      ctx.lineTo(
        shape.x2 - headLength * Math.cos(angle - Math.PI / 6),
        shape.y2 - headLength * Math.sin(angle - Math.PI / 6)
      );
      ctx.lineTo(
        shape.x2 - headLength * Math.cos(angle + Math.PI / 6),
        shape.y2 - headLength * Math.sin(angle + Math.PI / 6)
      );
      ctx.closePath();
      ctx.fill();
      break;
    }
    default:
      break;
  }
  ctx.restore();
}

function drawStroke(ctx, drawing) {
  const points = drawing.points || [];
  if (points.length < 2) return;
  const size = drawing.size || drawing.style?.size || 4;
  const options = {
    ...DEFAULT_PERFECT_FREEHAND_OPTIONS,
    size: size, // Use the captured size directly
    thinning: drawing.tool === 'highlighter' ? 0.7 : 0.3, // Less thinning for consistent width
    smoothing: drawing.tool === 'highlighter' ? 0.6 : 0.5
  };
  const outlinePoints = getStroke(points, options);
  const color = hexToRgba(drawing.color || drawing.style?.color || '#0f172a', (drawing.opacity || 100) / 100);

  if (!outlinePoints.length) return;

  ctx.save();
  ctx.fillStyle = color;
  ctx.beginPath();
  ctx.moveTo(outlinePoints[0][0], outlinePoints[0][1]);
  for (let i = 1; i < outlinePoints.length; i++) {
    const point = outlinePoints[i];
    ctx.lineTo(point[0], point[1]);
  }
  ctx.closePath();
  ctx.fill();
  ctx.restore();
}

function drawText(ctx, drawing) {
  const opacity = (drawing.opacity || 100) / 100;
  ctx.save();
  ctx.font = `${drawing.fontWeight || 500} ${drawing.fontSize || 22}px ${drawing.fontFamily || 'Inter, sans-serif'}`;
  ctx.fillStyle = hexToRgba(drawing.color || '#0f172a', opacity);
  ctx.textBaseline = 'top';
  ctx.fillText(drawing.content || '', drawing.x, drawing.y);
  ctx.restore();
}

function distanceBetween(a, b) {
  return Math.hypot(a.x - b.x, a.y - b.y);
}

export function useDrawingCanvas(options = {}) {
  const {
    requestTextInput = null,
    emitLaserMove = null,
    emitDrawEvent = null
  } = options;

  const drawingStore = useDrawingStore();
  const presentation = usePresentationStore();

  const canvasRef = ref(null);
  const contextRef = ref(null);
  const isPointerDown = ref(false);
  const activeStroke = ref(null);
  const pointerIdRef = ref(null);
  const animationFrame = ref(null);
  const laserPosition = ref(null);

  function getEventPoint(evt, canvas) {
    const rect = canvas.getBoundingClientRect();
    const cssWidth = canvas.clientWidth || 1;
    const cssHeight = canvas.clientHeight || 1;
    const scaleX = cssWidth / (rect.width || 1);
    const scaleY = cssHeight / (rect.height || 1);

    return {
      x: (evt.clientX - rect.left) * scaleX,
      y: (evt.clientY - rect.top) * scaleY
    };
  }

  const currentBuffer = computed(() => {
    const slideId = drawingStore.currentSlideId;
    return slideId ? drawingStore.slideBuffers[slideId] : null;
  });

  function initCanvas() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    contextRef.value = canvas.getContext('2d');
    resizeCanvas();
    drawAll();
  }

  function resizeCanvas() {
    const canvas = canvasRef.value;
    const ctx = contextRef.value;
    if (!canvas || !ctx) return;
    const parent = canvas.parentElement;
    if (!parent) return;
    const ratio = window.devicePixelRatio || 1;
    const cssWidth = parent.clientWidth;
    const cssHeight = parent.clientHeight;

    canvas.width = Math.round(cssWidth * ratio);
    canvas.height = Math.round(cssHeight * ratio);

    ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
    drawAll();
  }

  function scheduleDraw() {
    if (animationFrame.value) return;
    animationFrame.value = requestAnimationFrame(() => {
      animationFrame.value = null;
      drawAll();
    });
  }

  function drawAll() {
    const ctx = contextRef.value;
    const canvas = canvasRef.value;
    if (!ctx || !canvas) return;
    const clearWidth = canvas.clientWidth || canvas.width;
    const clearHeight = canvas.clientHeight || canvas.height;
    ctx.clearRect(0, 0, clearWidth, clearHeight);
    const drawings = drawingStore.slideBuffers[drawingStore.currentSlideId]?.drawings || [];
    drawings.forEach((drawing) => {
      if (drawing.type === 'shape') {
        drawShape(ctx, drawing);
      } else if (drawing.type === 'text') {
        drawText(ctx, drawing);
      } else {
        drawStroke(ctx, drawing);
      }
    });

    if (activeStroke.value) {
      if (activeStroke.value.type === 'shape') {
        drawShape(ctx, activeStroke.value.previewShape);
      } else if (activeStroke.value.type === 'stroke') {
        drawStroke(ctx, activeStroke.value);
      }
    }
  }

  function handlePointerDown(evt) {
    if (!drawingStore.isDrawingMode) return;
    if (evt.pointerType === 'mouse' && evt.button !== 0) return;
    const canvas = canvasRef.value;
    if (!canvas) return;

    const tool = drawingStore.activeTool;
    const slideId = drawingStore.currentSlideId;
    if (!slideId) return;

    canvas.setPointerCapture(evt.pointerId);
    pointerIdRef.value = evt.pointerId;
    isPointerDown.value = true;

    const point = getEventPoint(evt, canvas);

    if (tool === 'text') {
      if (typeof requestTextInput === 'function') {
        requestTextInput(point);
      }
      return;
    }

    if (tool === 'laser') {
      laserPosition.value = point;
      if (typeof emitLaserMove === 'function') {
        emitLaserMove(point);
      }
      return;
    }

    if (tool === 'eraser') {
      eraseAtPoint(point);
      return;
    }

    const stroke = {
      id: drawingStore.generateDrawingId(),
      type: 'stroke',
      tool,
      points: [point],
      size: drawingStore.brushSize, // Capture size at stroke start
      color: drawingStore.strokeColor,
      opacity: tool === 'highlighter' ? drawingStore.highlighterOpacity : drawingStore.strokeOpacity,
      timestamp: new Date().toISOString()
    };

    if (tool === 'rectangle' || tool === 'circle' || tool === 'line' || tool === 'arrow') {
      stroke.type = 'shape';
      stroke.startPoint = point;
      stroke.previewShape = createShapeFromPoints(tool, point, point, {
        id: stroke.id,
        color: drawingStore.strokeColor,
        opacity: drawingStore.strokeOpacity,
        size: drawingStore.brushSize
      });
    }

    activeStroke.value = stroke;
    scheduleDraw();
  }

  function handlePointerMove(evt) {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const tool = drawingStore.activeTool;

    if (tool === 'laser') {
      const point = getEventPoint(evt, canvas);
      laserPosition.value = point;
      if (typeof emitLaserMove === 'function') {
        emitLaserMove(point);
      }
      return;
    }

    if (!isPointerDown.value || evt.pointerId !== pointerIdRef.value) return;

    if (tool === 'eraser') {
      eraseAtPoint(getEventPoint(evt, canvas));
      return;
    }

    if (!activeStroke.value) return;
    const point = getEventPoint(evt, canvas);

    if (activeStroke.value.type === 'shape') {
      activeStroke.value.previewShape = createShapeFromPoints(tool, activeStroke.value.startPoint, point, {
        id: activeStroke.value.id,
        color: drawingStore.strokeColor,
        opacity: drawingStore.strokeOpacity,
        size: drawingStore.brushSize
      });
    } else {
      activeStroke.value.points.push(point);
    }
    scheduleDraw();
  }

  function handlePointerUp(evt) {
    const canvas = canvasRef.value;
    if (!canvas) return;

    if (drawingStore.activeTool === 'laser') {
      laserPosition.value = null;
      if (typeof emitLaserMove === 'function') {
        emitLaserMove(null);
      }
      return;
    }

    if (evt.pointerId !== pointerIdRef.value) return;
    canvas.releasePointerCapture(evt.pointerId);
    pointerIdRef.value = null;
    isPointerDown.value = false;

    if (!activeStroke.value) return;

    if (activeStroke.value.type === 'shape') {
      const shapePayload = {
        ...activeStroke.value.previewShape,
        type: 'shape'
      };
      drawingStore.appendDrawing(drawingStore.currentSlideId, shapePayload);
      if (typeof emitDrawEvent === 'function') emitDrawEvent(shapePayload);
    } else {
      drawingStore.appendDrawing(drawingStore.currentSlideId, {
        ...activeStroke.value,
        opacity: activeStroke.value.tool === 'highlighter' ? drawingStore.highlighterOpacity : drawingStore.strokeOpacity
      });
      if (typeof emitDrawEvent === 'function') emitDrawEvent(activeStroke.value);
    }

    activeStroke.value = null;
    scheduleDraw();
  }

  function handlePointerLeave() {
    if (!isPointerDown.value) return;
    handlePointerUp({ pointerId: pointerIdRef.value });
  }

  function eraseAtPoint(point) {
    const slideId = drawingStore.currentSlideId;
    if (!slideId) return;
    const buffer = drawingStore.slideBuffers[slideId];
    if (!buffer) return;

    const ERASE_RADIUS = 24;
    const targetIndex = buffer.drawings.findIndex((drawing) => {
      if (drawing.type === 'shape') {
        if (drawing.shape === 'rectangle') {
          return (
            point.x >= drawing.x - ERASE_RADIUS &&
            point.x <= drawing.x + drawing.width + ERASE_RADIUS &&
            point.y >= drawing.y - ERASE_RADIUS &&
            point.y <= drawing.y + drawing.height + ERASE_RADIUS
          );
        }
        if (drawing.shape === 'circle') {
          return distanceBetween(point, { x: drawing.cx, y: drawing.cy }) <= drawing.radius + ERASE_RADIUS;
        }
        if (drawing.shape === 'line' || drawing.shape === 'arrow') {
          const dist = distanceToLine(point, { x: drawing.x1, y: drawing.y1 }, { x: drawing.x2, y: drawing.y2 });
          return dist <= ERASE_RADIUS;
        }
        return false;
      }

      if (drawing.type === 'text') {
        const width = drawing.width || drawing.content?.length * (drawing.fontSize || 20) * 0.6 || 50;
        const height = drawing.height || drawing.fontSize || 24;
        return (
          point.x >= drawing.x - ERASE_RADIUS &&
          point.x <= drawing.x + width + ERASE_RADIUS &&
          point.y >= drawing.y - ERASE_RADIUS &&
          point.y <= drawing.y + height + ERASE_RADIUS
        );
      }

      const points = drawing.points || [];
      for (let i = 0; i < points.length; i++) {
        if (distanceBetween(point, points[i]) <= ERASE_RADIUS) {
          return true;
        }
      }
      return false;
    });

    if (targetIndex !== -1) {
      drawingStore.deleteDrawing(slideId, buffer.drawings[targetIndex].id);
      scheduleDraw();
    }
  }

  function distanceToLine(point, start, end) {
    const A = point.x - start.x;
    const B = point.y - start.y;
    const C = end.x - start.x;
    const D = end.y - start.y;
    const dot = A * C + B * D;
    const len_sq = C * C + D * D;
    let param = -1;
    if (len_sq !== 0) param = dot / len_sq;
    let xx, yy;
    if (param < 0) {
      xx = start.x;
      yy = start.y;
    } else if (param > 1) {
      xx = end.x;
      yy = end.y;
    } else {
      xx = start.x + param * C;
      yy = start.y + param * D;
    }
    return Math.hypot(point.x - xx, point.y - yy);
  }

  function attachEvents() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    canvas.addEventListener('pointerdown', handlePointerDown);
    canvas.addEventListener('pointermove', handlePointerMove);
    canvas.addEventListener('pointerup', handlePointerUp);
    canvas.addEventListener('pointerleave', handlePointerLeave);
    window.addEventListener('resize', resizeCanvas);
  }

  function detachEvents() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    canvas.removeEventListener('pointerdown', handlePointerDown);
    canvas.removeEventListener('pointermove', handlePointerMove);
    canvas.removeEventListener('pointerup', handlePointerUp);
    canvas.removeEventListener('pointerleave', handlePointerLeave);
    window.removeEventListener('resize', resizeCanvas);
  }

  onMounted(() => {
    initCanvas();
    attachEvents();
  });

  onBeforeUnmount(() => {
    detachEvents();
    if (animationFrame.value) cancelAnimationFrame(animationFrame.value);
  });

  watch(
    () => drawingStore.slideBuffers[drawingStore.currentSlideId]?.drawings,
    () => scheduleDraw(),
    { deep: true }
  );

  watch(
    () => presentation.currentSlideIndex,
    () => {
      activeStroke.value = null;
      scheduleDraw();
    }
  );

  watch(() => drawingStore.showGrid, () => scheduleDraw());

  return {
    canvasRef,
    laserPosition,
    initCanvas,
    resizeCanvas,
    drawAll,
    scheduleDraw
  };
}
