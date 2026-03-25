import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const usePresentationStore = defineStore('presentation', () => {
  const slides = ref([
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
  ]);

  const currentSlideIndex = ref(0);

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

  return {
    slides,
    currentSlideIndex,
    currentSlide,
    updateElement,
    addElement,
    deleteElement,
    duplicateElement,
    addSlide,
    selectSlide,
    deleteSlide
  };
});
