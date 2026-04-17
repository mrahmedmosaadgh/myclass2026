export function setupCanvas(canvas) {
  const ctx = canvas.getContext('2d');

  let logicalWidth = 0;
  let logicalHeight = 0;

  function resize(scale = 1) {
    const ratio = window.devicePixelRatio || 1;
    const rect = canvas.getBoundingClientRect();

    const safeScale = scale || 1;
    logicalWidth = rect.width / safeScale;
    logicalHeight = rect.height / safeScale;

    // Keep full on-screen pixel density for crisp strokes while using
    // logical (unscaled) coordinates for drawing.
    canvas.width = rect.width * ratio;
    canvas.height = rect.height * ratio;

    ctx.setTransform(ratio * safeScale, 0, 0, ratio * safeScale, 0, 0);
  }

  function clear() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
  }

  function drawStroke(stroke) {
    const { points, style } = stroke;
    if (points.length < 2) return;

    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.width;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = style.opacity || 1;

    // Handle different stroke types
    if (style.type === 'eraser') {
      ctx.globalCompositeOperation = 'destination-out';
    } else {
      ctx.globalCompositeOperation = 'source-over';
    }

    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);

    for (let i = 1; i < points.length; i++) {
      ctx.lineTo(points[i].x, points[i].y);
    }

    ctx.stroke();
    ctx.globalAlpha = 1;
    ctx.globalCompositeOperation = 'source-over';
  }

  function drawRectangle(stroke) {
    const { points, style } = stroke;
    if (points.length < 2) return;

    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.width;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = style.opacity || 1;

    const startPoint = points[0];
    const endPoint = points[points.length - 1];
    
    ctx.beginPath();
    ctx.rect(
      startPoint.x,
      startPoint.y,
      endPoint.x - startPoint.x,
      endPoint.y - startPoint.y
    );
    ctx.stroke();
    ctx.globalAlpha = 1;
  }

  function drawCircle(stroke) {
    const { points, style } = stroke;
    if (points.length < 2) return;

    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.width;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = style.opacity || 1;

    const startPoint = points[0];
    const endPoint = points[points.length - 1];
    
    const radius = Math.sqrt(
      Math.pow(endPoint.x - startPoint.x, 2) + 
      Math.pow(endPoint.y - startPoint.y, 2)
    );
    
    ctx.beginPath();
    ctx.arc(startPoint.x, startPoint.y, radius, 0, 2 * Math.PI);
    ctx.stroke();
    ctx.globalAlpha = 1;
  }

  function drawLine(stroke) {
    const { points, style } = stroke;
    if (points.length < 2) return;

    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.width;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = style.opacity || 1;

    ctx.beginPath();
    ctx.moveTo(points[0].x, points[0].y);
    ctx.lineTo(points[points.length - 1].x, points[points.length - 1].y);
    ctx.stroke();
    ctx.globalAlpha = 1;
  }

  function drawArrow(stroke) {
    const { points, style } = stroke;
    if (points.length < 2) return;

    ctx.strokeStyle = style.color;
    ctx.lineWidth = style.width;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = style.opacity || 1;

    const startPoint = points[0];
    const endPoint = points[points.length - 1];
    
    // Draw line
    ctx.beginPath();
    ctx.moveTo(startPoint.x, startPoint.y);
    ctx.lineTo(endPoint.x, endPoint.y);
    ctx.stroke();
    
    // Draw arrowhead
    const angle = Math.atan2(endPoint.y - startPoint.y, endPoint.x - startPoint.x);
    const arrowLength = 15;
    const arrowAngle = Math.PI / 6;
    
    ctx.beginPath();
    ctx.moveTo(endPoint.x, endPoint.y);
    ctx.lineTo(
      endPoint.x - arrowLength * Math.cos(angle - arrowAngle),
      endPoint.y - arrowLength * Math.sin(angle - arrowAngle)
    );
    ctx.moveTo(endPoint.x, endPoint.y);
    ctx.lineTo(
      endPoint.x - arrowLength * Math.cos(angle + arrowAngle),
      endPoint.y - arrowLength * Math.sin(angle + arrowAngle)
    );
    ctx.stroke();
    ctx.globalAlpha = 1;
  }

  function drawText(stroke) {
    const { points, style } = stroke;
    if (!points[0] || !style.text) return;

    ctx.fillStyle = style.color;
    ctx.font = `${style.fontSize || 16}px Arial`;
    ctx.globalAlpha = style.opacity || 1;

    ctx.fillText(style.text, points[0].x, points[0].y);
    ctx.globalAlpha = 1;
  }

  function render(strokes, currentStroke) {
    clear();
    
    // Render all completed strokes
    strokes.forEach(stroke => {
      switch (stroke.style.type) {
        case 'pen':
        case 'highlighter':
        case 'eraser':
          drawStroke(stroke);
          break;
        case 'rectangle':
          drawRectangle(stroke);
          break;
        case 'circle':
          drawCircle(stroke);
          break;
        case 'line':
          drawLine(stroke);
          break;
        case 'arrow':
          drawArrow(stroke);
          break;
        case 'text':
          drawText(stroke);
          break;
        default:
          drawStroke(stroke);
      }
    });
    
    // Render current stroke being drawn
    if (currentStroke) {
      switch (currentStroke.style.type) {
        case 'pen':
        case 'highlighter':
        case 'eraser':
          drawStroke(currentStroke);
          break;
        case 'rectangle':
          drawRectangle(currentStroke);
          break;
        case 'circle':
          drawCircle(currentStroke);
          break;
        case 'line':
          drawLine(currentStroke);
          break;
        case 'arrow':
          drawArrow(currentStroke);
          break;
        case 'text':
          drawText(currentStroke);
          break;
        default:
          drawStroke(currentStroke);
      }
    }
  }

  function drawGrid(spacing = 20, color = '#e5e7eb', opacity = 0.5) {
    ctx.save();
    ctx.strokeStyle = color;
    ctx.lineWidth = 1;
    ctx.globalAlpha = opacity;

    const width = logicalWidth;
    const height = logicalHeight;
    
    // Draw vertical lines
    for (let x = 0; x <= width; x += spacing) {
      ctx.beginPath();
      ctx.moveTo(x, 0);
      ctx.lineTo(x, height);
      ctx.stroke();
    }
    
    // Draw horizontal lines
    for (let y = 0; y <= height; y += spacing) {
      ctx.beginPath();
      ctx.moveTo(0, y);
      ctx.lineTo(width, y);
      ctx.stroke();
    }
    
    ctx.globalAlpha = 1;
  }

  return { 
    resize, 
    render, 
    clear, 
    drawGrid,
    drawStroke,
    drawRectangle,
    drawCircle,
    drawLine,
    drawArrow,
    drawText
  };
}
