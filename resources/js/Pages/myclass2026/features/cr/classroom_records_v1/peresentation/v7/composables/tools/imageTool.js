export default {
  getMenuActions(element, presentationStore) {
    const slideWidth = 1000;
    const slideHeight = presentationStore.currentSlide?.height || 600;

    function updateSize(changes) {
      presentationStore.updateElement({ id: element.id, changes });
    }

    function setSlideHeight(h) {
      const idx = presentationStore.currentSlideIndex;
      const slide = presentationStore.slides?.[idx];
      if (!slide) return;
      slide.height = h;
    }

    return [
      {
        id: 'replace-image',
        title: 'Replace Image',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7"></path><line x1="16" y1="5" x2="22" y2="5"></line><line x1="19" y1="2" x2="19" y2="8"></line><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>',
        action: () => {
           const url = prompt('Enter new image URL:', element.src);
           if (url) {
             presentationStore.updateElement({ id: element.id, changes: { src: url } });
           }
        }
      },
      {
        id: 'fit-slide-width',
        title: 'Fit to Slide Width',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M4 17h16"/><path d="M7 10v4"/><path d="M17 10v4"/></svg>',
        action: () => {
          const w = slideWidth;
          const ratio = element.width ? (element.height / element.width) : 0.75;
          const h = Math.round(w * (Number.isFinite(ratio) ? ratio : 0.75));
          updateSize({ x: 0, y: element.y || 0, width: w, height: h });
        }
      },
      {
        id: 'fit-slide-height',
        title: 'Fit to Slide Height',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 4v16"/><path d="M17 4v16"/><path d="M10 7h4"/><path d="M10 17h4"/></svg>',
        action: () => {
          const h = slideHeight;
          const ratio = element.height ? (element.width / element.height) : 1.333;
          const w = Math.round(h * (Number.isFinite(ratio) ? ratio : 1.333));
          updateSize({ x: element.x || 0, y: 0, width: w, height: h });
        }
      },
      {
        id: 'fit-slide-both',
        title: 'Fit to Slide (Contain)',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>',
        action: () => {
          const iw = element.width || 1;
          const ih = element.height || 1;
          const scale = Math.min(slideWidth / iw, slideHeight / ih);
          const w = Math.round(iw * scale);
          const h = Math.round(ih * scale);
          updateSize({ x: Math.round((slideWidth - w) / 2), y: Math.round((slideHeight - h) / 2), width: w, height: h });
        }
      },
      {
        id: 'slide-fit-image',
        title: 'Slide Fits Image',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7h18"/><path d="M3 17h18"/><rect x="7" y="9" width="10" height="6" rx="1"/></svg>',
        action: () => {
          const newH = Math.max(200, Math.round(element.height || slideHeight));
          setSlideHeight(newH);
          updateSize({ x: Math.round((slideWidth - (element.width || 0)) / 2), y: 0 });
        }
      }
    ];
  }
};
