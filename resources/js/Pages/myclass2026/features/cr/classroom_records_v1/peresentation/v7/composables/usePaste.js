import { usePresentationStore } from '../stores/presentationStore';
import { useUIStore } from '../stores/uiStore';
import { useClipboardStore } from '../stores/clipboardStore';

function sanitizeHTML(html) {
  const div = document.createElement('div');
  div.innerHTML = html;
  
  // Remove scripts and object tags
  const unsafeTags = div.querySelectorAll('script, iframe, object, embed');
  unsafeTags.forEach(el => el.remove());
  
  return div.innerHTML;
}

export function usePaste() {
  const presentation = usePresentationStore();
  const ui = useUIStore();
  const clipboard = useClipboardStore();
  
  async function handlePaste(e) {
    if (!ui.isEditMode) return;
    if (['INPUT', 'TEXTAREA'].includes(e.target.tagName) || e.target.isContentEditable) return;

    // Check if we have clipboard content first
    if (clipboard.hasClipboardContent()) {
      pasteElement();
      return;
    }

    const items = e.clipboardData?.items;
    if (!items) return;

    for (const item of items) {
      if (item.type.startsWith('image/')) {
        const file = item.getAsFile();
        const reader = new FileReader();

        reader.onload = () => {
          createImageElement(reader.result);
        };

        reader.readAsDataURL(file);
        return; // handle image and exit
      }

      if (item.type === 'text/html') {
        item.getAsString((html) => {
          createHTMLElement(html);
        });
        return; // handle html and exit
      }

      if (item.type === 'text/plain') {
        item.getAsString((text) => {
          createTextElement(text);
        });
        return; // handle text and exit
      }
    }
  }

  function getPosition() {
    const defaultX = ui.lastPointer?.x || 150;
    const defaultY = ui.lastPointer?.y || 150;
    
    const x = defaultX + ui.pasteOffset;
    const y = defaultY + ui.pasteOffset;
    
    ui.incrementPasteOffset();
    
    return { x, y };
  }

  function getBaseProps() {
    return {
      id: 'el-' + Date.now() + Math.floor(Math.random() * 1000),
      ...getPosition(),
      zIndex: presentation.currentSlide?.elements.length + 1 || 1,
      visibilityOption: 'shown-clickable',
      isVisible: true,
      hiddenOpacity: 0.05
    };
  }

  function finishPaste(elementProps) {
    presentation.addElement(elementProps);
    ui.selectElement(elementProps.id);
  }

  function createTextElement(text = 'New Text') {
    finishPaste({
      ...getBaseProps(),
      type: 'text',
      content: text,
      width: Math.max(200, Math.min(text.length * 10, 400)),
      height: 50
    });
  }

  function createMathElement() {
    finishPaste({
      ...getBaseProps(),
      type: 'math',
      content: '1. $$ \\frac{2}{3} + \\frac{1}{3} $$',
      width: 300,
      height: 100
    });
  }

  function createImageElement(src) {
    const img = new Image();
    img.onload = () => {
      let width = img.width || 300;
      let height = img.height || 200;

      // Ensure it doesn't overflow completely off the screen instantly
      if (width > 800) {
        height = (800 / width) * height;
        width = 800;
      }
      if (height > 600) {
        width = (600 / height) * width;
        height = 600;
      }

      finishPaste({
        ...getBaseProps(),
        type: 'image',
        src,
        width,
        height
      });
    };
    img.src = src;
  }

  function createHTMLElement(rawHtml) {
    // Convert HTML content to math element with MD and HTML support
    const html = rawHtml || '<div style="padding:10px; background:#fef08a; border-radius:4px;"><strong>Custom HTML</strong></div>';
    finishPaste({
      ...getBaseProps(),
      type: 'math',
      content: html,
      width: 300,
      height: 100
    });
  }

  function createRectangleElement() {
    finishPaste({
      ...getBaseProps(),
      type: 'rectangle',
      width: 150,
      height: 150
    });
  }

  function pasteElement() {
    if (!clipboard.hasClipboardContent()) return;
    
    const position = getPosition();
    const pastedElement = clipboard.pasteElement(position.x, position.y);
    
    if (pastedElement) {
      finishPaste(pastedElement);
    }
  }

  return {
    handlePaste,
    createTextElement,
    createMathElement,
    createImageElement,
    createHTMLElement,
    createRectangleElement,
    pasteElement
  };
}
