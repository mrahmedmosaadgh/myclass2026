import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useSectionStore = defineStore('sections', () => {
  const defaultSections = [
    { id: 'sec-1', name: 'Objectives / Goals', color: '#fcd34d' },
    { id: 'sec-2', name: 'Anticipatory Set / Hook', color: '#f9a8d4' },
    { id: 'sec-3', name: 'Introduction / Instruction', color: '#93c5fd' },
    { id: 'sec-4', name: 'Guided Practice', color: '#a7f3d0' },
    { id: 'sec-5', name: 'Independent Practice', color: '#c4b5fd' },
    { id: 'sec-6', name: 'Assessment / Check', color: '#fca5a5' },
    { id: 'sec-7', name: 'Closure / Review', color: '#5eead4' }
  ];

  const localSaved = localStorage.getItem('cr_v4_sections');
  const initialSections = localSaved ? JSON.parse(localSaved) : JSON.parse(JSON.stringify(defaultSections));
  
  const sections = ref(initialSections);

  watch(sections, (newVal) => {
    localStorage.setItem('cr_v4_sections', JSON.stringify(newVal));
  }, { deep: true });

  function resetToDefault() {
    sections.value = JSON.parse(JSON.stringify(defaultSections));
  }

  function addSection() {
    sections.value.push({
      id: 'sec-' + Date.now(),
      name: 'New Phase',
      color: '#e5e7eb'
    });
  }

  function deleteSection(index) {
    sections.value.splice(index, 1);
  }

  return {
    sections,
    defaultSections,
    resetToDefault,
    addSection,
    deleteSection
  };
});
