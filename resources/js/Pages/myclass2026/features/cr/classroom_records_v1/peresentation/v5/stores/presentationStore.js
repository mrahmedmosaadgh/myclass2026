import { defineStore } from 'pinia';
import { ref, computed, watch } from 'vue';
import { useOfflineStorage } from '../composables/useOfflineStorage.js';

export const usePresentationStore = defineStore('presentation', () => {
  const saveStatus = ref('saved'); // 'saved' | 'saving'
  const offlineStorage = useOfflineStorage();

  const defaultSlides = [
    {
      id: 'slide-1',
      elements: [
        {
          id: 'el-1',
          type: 'text',
          content: 'Hello World',
          x: 100,
          y: 100,
          width: 200,
          height: 100,
          zIndex: 1,
          visibilityOption: 'shown-clickable',
          isVisible: true,
          hiddenOpacity: 0.05
        },
        {
          id: 'el-2',
          type: 'rectangle',
          x: 400,
          y: 200,
          width: 150,
          height: 150,
          zIndex: 2,
          visibilityOption: 'shown-clickable',
          isVisible: true,
          hiddenOpacity: 0.05
        }
      ]
    }
  ];

  const localSaved = localStorage.getItem('cr_v5_presentation');
  let initialData = {
    title: 'Untitled Presentation',
    usePhases: false,
    hasInitializedPhases: false,
    slides: JSON.parse(JSON.stringify(defaultSlides)),
    currentSlideIndex: 0
  };

  if (localSaved) {
    try {
      const parsed = JSON.parse(localSaved);
      if (parsed.slides && Array.isArray(parsed.slides)) {
        initialData = parsed;
      }
    } catch (e) {
      console.warn('Failed to parse locally saved presentation.', e);
    }
  }

  const title = ref(initialData.title);
  const usePhases = ref(initialData.usePhases);
  const hasInitializedPhases = ref(initialData.hasInitializedPhases);
  const slides = ref(initialData.slides);
  const currentSlideIndex = ref(initialData.currentSlideIndex || 0);

  let saveTimeout = null;
  
  function triggerAutoSave() {
    saveStatus.value = 'saving';
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
      const payload = {
        title: title.value,
        usePhases: usePhases.value,
        hasInitializedPhases: hasInitializedPhases.value,
        slides: slides.value,
        currentSlideIndex: currentSlideIndex.value,
        lastSaved: new Date().toISOString()
      };
      localStorage.setItem('cr_v5_presentation', JSON.stringify(payload));
      saveStatus.value = 'saved';
    }, 600);
  }

  watch([title, usePhases, slides, currentSlideIndex], () => {
    triggerAutoSave();
  }, { deep: true });

  function resetPresentation() {
    localStorage.removeItem('cr_v5_presentation');
    title.value = 'Untitled Presentation';
    usePhases.value = false;
    hasInitializedPhases.value = false;
    slides.value = JSON.parse(JSON.stringify(defaultSlides));
    currentSlideIndex.value = 0;
  }

  const currentSlide = computed(() => {
    return slides.value[currentSlideIndex.value];
  });

  function addSlide() {
    const currentSectionId = currentSlide.value?.sectionId || undefined;
    const newSlide = {
      id: 'slide-' + Date.now() + Math.random().toString(36).substring(2, 9),
      sectionId: currentSectionId,
      elements: []
    };
    slides.value.push(newSlide);
    currentSlideIndex.value = slides.value.length - 1;
  }

  function addSlideToPhase(sectionId) {
    const newSlide = {
      id: 'slide-' + Date.now() + Math.random().toString(36).substring(2, 9),
      sectionId: sectionId,
      elements: []
    };
    let insertIndex = slides.value.length;
    for (let i = slides.value.length - 1; i >= 0; i--) {
      if (slides.value[i].sectionId === sectionId) {
        insertIndex = i + 1;
        break;
      }
    }
    slides.value.splice(insertIndex, 0, newSlide);
    currentSlideIndex.value = insertIndex;
  }

  function selectSlideById(id) {
    const idx = slides.value.findIndex(s => s.id === id);
    if (idx !== -1) currentSlideIndex.value = idx;
  }

  function deleteSlideById(id) {
    if (slides.value.length <= 1) return;
    const idx = slides.value.findIndex(s => s.id === id);
    if (idx !== -1) {
      slides.value.splice(idx, 1);
      if (currentSlideIndex.value >= slides.value.length) {
        currentSlideIndex.value = slides.value.length - 1;
      }
    }
  }

  function selectSlide(index) {
    if (index >= 0 && index < slides.value.length) {
      currentSlideIndex.value = index;
    }
  }

  function deleteSlide(index) {
    if (slides.value.length <= 1) return;
    slides.value.splice(index, 1);
    if (currentSlideIndex.value >= slides.value.length) {
      currentSlideIndex.value = slides.value.length - 1;
    }
  }

  function addSlideAtIndex(index) {
    const newSlide = {
      id: 'slide-' + Date.now() + Math.random().toString(36).substr(2, 5),
      elements: [],
      sectionId: currentSlide.value?.sectionId
    };
    slides.value.splice(index, 0, newSlide);
    currentSlideIndex.value = index;
  }

  function moveSlide(fromIndex, toIndex) {
    if (fromIndex === toIndex) return;
    const [movedSlide] = slides.value.splice(fromIndex, 1);
    slides.value.splice(toIndex, 0, movedSlide);
    
    // Update current slide index if needed
    if (currentSlideIndex.value === fromIndex) {
      currentSlideIndex.value = toIndex;
    } else if (fromIndex < currentSlideIndex.value && toIndex >= currentSlideIndex.value) {
      currentSlideIndex.value--;
    } else if (fromIndex > currentSlideIndex.value && toIndex <= currentSlideIndex.value) {
      currentSlideIndex.value++;
    }
  }

  function addElement(elementDetails) {
    if (!currentSlide.value) return;
    currentSlide.value.elements.push({
      id: 'el-' + Date.now() + Math.floor(Math.random() * 1000),
      ...elementDetails
    });
  }

  function deleteElement(id) {
    if (!currentSlide.value) return;
    currentSlide.value.elements = currentSlide.value.elements.filter(e => e.id !== id);
  }

  function duplicateElement(id) {
    if (!currentSlide.value) return;
    const el = currentSlide.value.elements.find(e => e.id === id);
    if (!el) return;
    
    const { id: oldId, ...elWithoutId } = el;
    
    addElement({
      ...elWithoutId,
      x: el.x + 20,
      y: el.y + 20
    });
  }

  function updateElement({ id, changes }) {
    const el = currentSlide.value.elements.find(e => e.id === id);
    if (!el) return;
    Object.assign(el, changes);
  }

  function loadPresentation(data) {
    if (Array.isArray(data)) {
      slides.value = data;
      title.value = 'Imported Presentation';
      usePhases.value = false;
      hasInitializedPhases.value = true;
      currentSlideIndex.value = 0;
    } else if (data && data.slides && Array.isArray(data.slides)) {
      slides.value = data.slides;
      title.value = data.title || 'Imported Presentation';
      usePhases.value = !!data.usePhases;
      hasInitializedPhases.value = true;
      currentSlideIndex.value = 0;
    }
  }

  return {
    title,
    usePhases,
    hasInitializedPhases,
    slides,
    currentSlideIndex,
    currentSlide,
    updateElement,
    addElement,
    deleteElement,
    duplicateElement,
    addSlide,
    addSlideToPhase,
    addSlideAtIndex,
    moveSlide,
    selectSlide,
    selectSlideById,
    deleteSlide,
    deleteSlideById,
    loadPresentation,
    resetPresentation,
    saveStatus
  };
});
