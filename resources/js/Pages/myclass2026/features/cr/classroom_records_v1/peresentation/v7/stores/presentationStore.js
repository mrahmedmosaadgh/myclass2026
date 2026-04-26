import { defineStore } from 'pinia';
import { ref, computed, watch, nextTick } from 'vue';
import { useIndexedDBStorage } from '../composables/useIndexedDBStorage.js';
import { useGameStore } from './gameStore';

const DEFAULT_SLIDE_HEIGHT = 600;

function generateSlideId() {
  return 'slide-' + Date.now() + Math.random().toString(36).substring(2, 9);
}

function defaultDrawingsMeta() {
  return {
    lastModified: null,
    lastSavedAt: null,
    version: 1
  };
}

function normalizeSlide(slide = {}) {
  const normalized = { ...slide };
  normalized.id = normalized.id || generateSlideId();
  normalized.elements = Array.isArray(normalized.elements) ? normalized.elements : [];
  normalized.drawings = Array.isArray(normalized.drawings) ? normalized.drawings : [];
  normalized.drawingsMeta = normalized.drawingsMeta && typeof normalized.drawingsMeta === 'object'
    ? { ...defaultDrawingsMeta(), ...normalized.drawingsMeta }
    : defaultDrawingsMeta();
  normalized.height = typeof normalized.height === 'number' ? normalized.height : DEFAULT_SLIDE_HEIGHT;
  return normalized;
}

function isValidHexColor(c) {
  return typeof c === 'string' && /^#[0-9a-fA-F]{6}$/.test(c.trim());
}

function normalizeGroups(groups) {
  if (!Array.isArray(groups)) return null;
  return groups
    .filter(g => g && typeof g === 'object')
    .map((g, idx) => {
      const id = String(g.id ?? `g${idx + 1}`).trim() || `g${idx + 1}`;
      const name = String(g.name ?? `Group ${idx + 1}`).trim() || `Group ${idx + 1}`;
      const scoreNum = Number(g.score ?? 0);
      const score = Number.isFinite(scoreNum) ? scoreNum : 0;
      const color = isValidHexColor(g.color) ? String(g.color).trim() : '#8b5cf6';
      return { id, name, score, color };
    });
}

function normalizeGameSettings(settings) {
  if (!settings || typeof settings !== 'object') return null;
  const correctPoints = Number(settings.correctPoints);
  const wrongPoints = Number(settings.wrongPoints);
  return {
    correctPoints: Number.isFinite(correctPoints) ? correctPoints : 10,
    wrongPoints: Number.isFinite(wrongPoints) ? wrongPoints : 0,
    allowNegativeScore: !!settings.allowNegativeScore
  };
}

function createSlide(overrides = {}) {
  return normalizeSlide({
    ...overrides
  });
}

function generateElementId() {
  return 'el-' + Date.now() + Math.floor(Math.random() * 1000) + Math.random().toString(36).substring(2, 6);
}

function normalizeElement(el = {}) {
  const normalized = { ...el };
  normalized.id = normalized.id ? String(normalized.id) : generateElementId();
  normalized.type = typeof normalized.type === 'string' ? normalized.type : 'text';
  normalized.x = Number.isFinite(Number(normalized.x)) ? Number(normalized.x) : 0;
  normalized.y = Number.isFinite(Number(normalized.y)) ? Number(normalized.y) : 0;
  normalized.width = Number.isFinite(Number(normalized.width)) ? Number(normalized.width) : 200;
  normalized.height = Number.isFinite(Number(normalized.height)) ? Number(normalized.height) : 80;
  normalized.zIndex = Number.isFinite(Number(normalized.zIndex)) ? Number(normalized.zIndex) : 1;
  normalized.visibilityOption = typeof normalized.visibilityOption === 'string' ? normalized.visibilityOption : 'shown-clickable';
  normalized.isVisible = normalized.isVisible !== false;
  if (normalized.hiddenOpacity !== undefined) {
    const ho = Number(normalized.hiddenOpacity);
    normalized.hiddenOpacity = Number.isFinite(ho) ? ho : 0.05;
  }
  if (normalized.content !== undefined) {
    normalized.content = typeof normalized.content === 'string' ? normalized.content : String(normalized.content ?? '');
  }
  if (normalized.src !== undefined) {
    normalized.src = typeof normalized.src === 'string' ? normalized.src : String(normalized.src ?? '');
  }
  return normalized;
}

function normalizeSlideDeep(slide = {}) {
  const s = normalizeSlide(slide);
  s.elements = Array.isArray(s.elements) ? s.elements.map(normalizeElement) : [];
  return s;
}

export const usePresentationStore = defineStore('presentation', () => {
  const saveStatus = ref('saved'); // 'saved' | 'saving'
  const indexedDBStorage = useIndexedDBStorage();
  const gameStore = useGameStore();

  const defaultSlides = [
    createSlide({
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
    })
  ];

  let initialData = {
    title: 'Untitled Presentation',
    description: '',
    showDescriptionInPresentMode: true,
    usePhases: false,
    hasInitializedPhases: false,
    slides: JSON.parse(JSON.stringify(defaultSlides)).map(normalizeSlide),
    currentSlideIndex: 0
  };

  // Try to load current presentation from IndexedDB
  const loadCurrentPresentation = async () => {
    try {
      const currentPresentation = await indexedDBStorage.getCurrentPresentation();
      
      if (currentPresentation) {
        title.value = currentPresentation.title || 'Untitled Presentation';
        description.value = typeof currentPresentation.description === 'string'
          ? currentPresentation.description
          : String(currentPresentation.description || '');
        showDescriptionInPresentMode.value = currentPresentation.showDescriptionInPresentMode !== undefined ? currentPresentation.showDescriptionInPresentMode : true; // Default to true
        usePhases.value = !!currentPresentation.usePhases;
        hasInitializedPhases.value = !!currentPresentation.hasInitializedPhases;
        slides.value = Array.isArray(currentPresentation.slides)
          ? currentPresentation.slides.map(normalizeSlide)
          : JSON.parse(JSON.stringify(defaultSlides)).map(normalizeSlide);
        currentSlideIndex.value = currentPresentation.currentSlideIndex || 0;

        const normalizedGroups = normalizeGroups(currentPresentation.groups);
        if (normalizedGroups && normalizedGroups.length) {
          gameStore.groups = normalizedGroups;
        }

        const normalizedSettings = normalizeGameSettings(currentPresentation.gameSettings);
        if (normalizedSettings) {
          gameStore.gameSettings = normalizedSettings;
        }
      }
    } catch (e) {
      console.warn('Failed to load presentation from IndexedDB, using defaults.', e);
    }
  };

  const title = ref(initialData.title);
  const description = ref(initialData.description);
  const showDescriptionInPresentMode = ref(initialData.showDescriptionInPresentMode);
  const usePhases = ref(initialData.usePhases);
  const hasInitializedPhases = ref(initialData.hasInitializedPhases);
  const slides = ref(initialData.slides);
  const currentSlideIndex = ref(initialData.currentSlideIndex || 0);

  // Load asynchronously (non-blocking)
  loadCurrentPresentation();

  let saveTimeout = null;
  let currentPresentationKey = null;
  
  function triggerAutoSave() {
    saveStatus.value = 'saving';
    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(async () => {
      try {
        const payload = {
          title: title.value,
          description: typeof description.value === 'string' ? description.value : String(description.value || ''),
          showDescriptionInPresentMode: showDescriptionInPresentMode.value,
          usePhases: usePhases.value,
          hasInitializedPhases: hasInitializedPhases.value,
          slides: JSON.parse(JSON.stringify(slides.value)), // Deep clone to avoid circular references
          currentSlideIndex: currentSlideIndex.value,
          groups: JSON.parse(JSON.stringify(gameStore.groups)),
          gameSettings: JSON.parse(JSON.stringify(gameStore.gameSettings)),
          lastSaved: new Date().toISOString()
        };

        // Save with IndexedDB storage
        if (indexedDBStorage.isStorageAvailable && indexedDBStorage.isStorageAvailable()) {
          // Check if savePresentation function exists
          if (indexedDBStorage.savePresentation) {
            const result = await indexedDBStorage.savePresentation(payload, {
              overwrite: true,
              createBackup: false
            });
            
            // Only set currentPresentationKey if result exists and has id
            if (result && result.id) {
              currentPresentationKey = result.id;
              console.log('Presentation saved successfully with ID:', result.id);
              
              // Set current presentation metadata for reload
              try {
                // Use the db instance directly since it's available in the composable
                const { db } = indexedDBStorage;
                if (db && db.offline_metadata) {
                  await db.offline_metadata.put({
                    key: 'current_presentation',
                    value: result.id,
                    updated_at: new Date().toISOString()
                  });
                  console.log('Current presentation metadata set successfully');
                } else {
                  console.warn('Database or offline_metadata not available');
                }
              } catch (metadataError) {
                console.warn('Failed to set current presentation metadata:', metadataError);
                // Continue without metadata - presentation is still saved
              }
            } else {
              console.warn('Presentation saved but no ID returned');
            }
          } else {
            console.warn('savePresentation function not available');
          }
        } else {
          console.warn('IndexedDB not available, auto-save disabled');
        }
        
        saveStatus.value = 'saved';
      } catch (error) {
        console.error('Auto-save failed:', error);
        saveStatus.value = 'error';
      }
    }, 600);
  }

  watch([title, description, usePhases, slides, currentSlideIndex], () => {
    triggerAutoSave();
  }, { deep: true });

  watch([
    () => gameStore.groups,
    () => gameStore.gameSettings
  ], () => {
    triggerAutoSave();
  }, { deep: true });

  function resetPresentation() {
    // Clear current presentation metadata
    if (indexedDBStorage.isStorageAvailable()) {
      indexedDBStorage.db.offline_metadata.delete('current_presentation');
    }
    
    title.value = 'Untitled Presentation';
    description.value = '';
    usePhases.value = false;
    hasInitializedPhases.value = false;
    slides.value = JSON.parse(JSON.stringify(defaultSlides)).map(normalizeSlide);
    currentSlideIndex.value = 0;

    gameStore.groups = [
      { id: 'g1', name: 'Group A', score: 0, color: '#ef4444' },
      { id: 'g2', name: 'Group B', score: 0, color: '#3b82f6' },
      { id: 'g3', name: 'Group C', score: 0, color: '#10b981' }
    ];
    gameStore.gameSettings = {
      correctPoints: 10,
      wrongPoints: 0,
      allowNegativeScore: false
    };
  }

  const currentSlide = computed(() => {
    if (!slides.value || slides.value.length === 0) {
      return {
        id: 'default-slide',
        elements: []
      };
    }
    return slides.value[currentSlideIndex.value] || {
      id: 'default-slide',
      elements: []
    };
  });

  function addSlide() {
    const currentSectionId = currentSlide.value?.sectionId || undefined;
    const newSlide = createSlide({
      sectionId: currentSectionId
    });
    slides.value.push(newSlide);
    currentSlideIndex.value = slides.value.length - 1;
    
    // Scroll to top when adding new slide
    nextTick(() => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      const mainContainer = document.querySelector('.v5-container') || document.body;
      mainContainer.scrollTop = 0;
    });
  }

  function addSlideToPhase(sectionId) {
    const newSlide = createSlide({
      sectionId: sectionId
    });
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
    if (idx !== -1) {
      currentSlideIndex.value = idx;
      // Scroll to top when selecting slide by ID
      nextTick(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        const mainContainer = document.querySelector('.v5-container') || document.body;
        mainContainer.scrollTop = 0;
      });
    }
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

  function getCurrentSlideJson(pretty = true) {
    const slide = slides.value?.[currentSlideIndex.value];
    if (!slide) return '';
    const payload = JSON.parse(JSON.stringify(slide));
    return JSON.stringify(payload, null, pretty ? 2 : 0);
  }

  function appendSlidesFromJson(rawJson, { insertAfterCurrent = true } = {}) {
    const text = typeof rawJson === 'string' ? rawJson.trim() : '';
    if (!text) {
      return { ok: false, error: 'Empty JSON' };
    }

    let parsed;
    try {
      parsed = JSON.parse(text);
    } catch (e) {
      return { ok: false, error: 'Invalid JSON' };
    }

    const incoming = Array.isArray(parsed) ? parsed : [parsed];
    const slidesToAdd = incoming
      .filter(s => s && typeof s === 'object')
      .map((s) => {
        const copy = JSON.parse(JSON.stringify(s));
        copy.id = generateSlideId();
        if (Array.isArray(copy.elements)) {
          copy.elements = copy.elements.map((el) => ({
            ...el,
            id: generateElementId()
          }));
        }
        return normalizeSlideDeep(copy);
      });

    if (!slidesToAdd.length) {
      return { ok: false, error: 'No valid slide objects found' };
    }

    const insertIndex = insertAfterCurrent
      ? Math.min(slides.value.length, currentSlideIndex.value + 1)
      : slides.value.length;

    slides.value.splice(insertIndex, 0, ...slidesToAdd);
    currentSlideIndex.value = insertIndex;
    return { ok: true, added: slidesToAdd.length };
  }

  function selectSlide(index) {
    if (index >= 0 && index < slides.value.length) {
      currentSlideIndex.value = index;
      // Scroll to top when changing slides
      nextTick(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // Also scroll the main container if needed
        const mainContainer = document.querySelector('.v5-container') || document.body;
        mainContainer.scrollTop = 0;
      });
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
    const newSlide = createSlide({
      sectionId: currentSlide.value?.sectionId
    });
    slides.value.splice(index, 0, newSlide);
    currentSlideIndex.value = index;
    
    // Scroll to top when adding slide at specific index
    nextTick(() => {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      const mainContainer = document.querySelector('.v5-container') || document.body;
      mainContainer.scrollTop = 0;
    });
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

  function moveSlideUp(index) {
    if (index <= 0) return;
    moveSlide(index, index - 1);
  }

  function moveSlideDown(index) {
    if (index >= slides.value.length - 1) return;
    moveSlide(index, index + 1);
  }

  function addSlideAt(index) {
    const newSlide = createSlide({
      sectionId: currentSlide.value?.sectionId
    });
    slides.value.splice(index, 0, newSlide);
    currentSlideIndex.value = index;
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
    if (!currentSlide.value || !currentSlide.value.elements) return;
    const el = currentSlide.value.elements.find(e => e.id === id);
    if (!el) return;
    Object.assign(el, changes);
  }

  function loadPresentation(data) {
    if (Array.isArray(data)) {
      slides.value = data.map(normalizeSlide);
      title.value = 'Imported Presentation';
      description.value = '';
      usePhases.value = false;
      hasInitializedPhases.value = true;
      currentSlideIndex.value = 0;
    } else if (data && data.slides && Array.isArray(data.slides)) {
      slides.value = data.slides.map(normalizeSlide);
      title.value = data.title || 'Imported Presentation';
      description.value = data.description || '';
      usePhases.value = !!data.usePhases;
      hasInitializedPhases.value = true;
      currentSlideIndex.value = 0;

      const normalizedGroups = normalizeGroups(data.groups);
      if (normalizedGroups && normalizedGroups.length) {
        gameStore.groups = normalizedGroups;
      }

      const normalizedSettings = normalizeGameSettings(data.gameSettings);
      if (normalizedSettings) {
        gameStore.gameSettings = normalizedSettings;
      }
      
      // If this presentation has an ID, set it as current
      if (data.id && indexedDBStorage.isStorageAvailable()) {
        indexedDBStorage.db.offline_metadata.put({
          key: 'current_presentation',
          value: data.id,
          updated_at: new Date().toISOString()
        }).catch(err => console.warn('Failed to set current presentation metadata:', err));
      }
    }
  }

  function updateSlideDrawings(slideId, drawings = []) {
    const slide = slides.value.find(s => s.id === slideId);
    if (!slide) return;
    slide.drawings = Array.isArray(drawings) ? drawings : [];
    slide.drawingsMeta = {
      ...defaultDrawingsMeta(),
      ...slide.drawingsMeta,
      lastModified: new Date().toISOString()
    };
  }

  function updateSlideDrawingsMeta(slideId, meta = {}) {
    const slide = slides.value.find(s => s.id === slideId);
    if (!slide) return;
    slide.drawingsMeta = {
      ...defaultDrawingsMeta(),
      ...slide.drawingsMeta,
      ...meta
    };
  }

  // Enhanced save methods
  async function savePresentationAs(name) {
    try {
      const payload = {
        title: name,
        description: typeof description.value === 'string' ? description.value : String(description.value || ''),
        usePhases: usePhases.value,
        hasInitializedPhases: hasInitializedPhases.value,
        slides: slides.value,
        currentSlideIndex: currentSlideIndex.value,
        groups: JSON.parse(JSON.stringify(gameStore.groups)),
        gameSettings: JSON.parse(JSON.stringify(gameStore.gameSettings)),
        lastSaved: new Date().toISOString()
      };

      const result = await indexedDBStorage.savePresentation(payload, {
        name,
        overwrite: false,
        createBackup: true
      });

      currentPresentationKey = result.id;
      
      // IMPORTANT: Set current presentation metadata for reload
      await indexedDBStorage.db.offline_metadata.put({
        key: 'current_presentation',
        value: result.id,
        updated_at: new Date().toISOString()
      });
      
      saveStatus.value = 'saved';
      return result;
    } catch (error) {
      console.error('Save as failed:', error);
      saveStatus.value = 'error';
      throw error;
    }
  }

  async function exportCurrentPresentation() {
    try {
      const payload = {
        title: title.value,
        description: typeof description.value === 'string' ? description.value : String(description.value || ''),
        usePhases: usePhases.value,
        hasInitializedPhases: hasInitializedPhases.value,
        slides: JSON.parse(JSON.stringify(slides.value)), // Deep clone to avoid circular references
        currentSlideIndex: currentSlideIndex.value,
        groups: JSON.parse(JSON.stringify(gameStore.groups)),
        gameSettings: JSON.parse(JSON.stringify(gameStore.gameSettings)),
        lastSaved: new Date().toISOString()
      };

      await indexedDBStorage.exportPresentation(payload);
    } catch (error) {
      console.error('Export failed:', error);
      throw error;
    }
  }

  async function getStorageInfo() {
    return await indexedDBStorage.getStorageStats();
  }

  async function saveCurrentPresentation() {
    try {
      saveStatus.value = 'saving';
      
      const presentationData = {
        title: title.value,
        description: typeof description.value === 'string' ? description.value : String(description.value || ''),
        slides: JSON.parse(JSON.stringify(slides.value)), // Deep clone to avoid circular references
        currentSlideIndex: currentSlideIndex.value,
        usePhases: usePhases.value,
        hasInitializedPhases: hasInitializedPhases.value,
        groups: JSON.parse(JSON.stringify(gameStore.groups)),
        gameSettings: JSON.parse(JSON.stringify(gameStore.gameSettings)),
        metadata: {
          lastSaved: new Date().toISOString()
        }
      };

      // Save to IndexedDB
      const result = await indexedDBStorage.savePresentation(presentationData, {
        overwrite: true,
        createBackup: false
      });

      currentPresentationKey = result.id;
      
      // IMPORTANT: Set current presentation metadata for reload
      await indexedDBStorage.db.offline_metadata.put({
        key: 'current_presentation',
        value: result.id,
        updated_at: new Date().toISOString()
      });
      
      saveStatus.value = 'saved';
      
      return result;
    } catch (error) {
      console.error('Save failed:', error);
      saveStatus.value = 'error';
      throw error;
    }
  }

  function toggleDescriptionInPresentMode() {
    showDescriptionInPresentMode.value = !showDescriptionInPresentMode.value;
    triggerAutoSave(); // Auto-save when toggled
  }

  function clearTitle() {
    title.value = '';
    triggerAutoSave();
  }

  function clearDescription() {
    description.value = '';
    triggerAutoSave();
  }

  return {
    title,
    description,
    showDescriptionInPresentMode,
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
    addSlideAt,
    moveSlide,
    moveSlideUp,
    moveSlideDown,
    selectSlide,
    selectSlideById,
    deleteSlide,
    deleteSlideById,
    loadPresentation,
    getCurrentSlideJson,
    appendSlidesFromJson,
    resetPresentation,
    saveStatus,
    // Enhanced methods
    savePresentationAs,
    exportCurrentPresentation,
    getStorageInfo,
    saveCurrentPresentation,
    // Description and title controls
    toggleDescriptionInPresentMode,
    clearTitle,
    clearDescription,
    updateSlideDrawings,
    updateSlideDrawingsMeta,
    // Storage access
    indexedDBStorage
  };
});
