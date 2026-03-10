<template>
  <q-drawer
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    show-if-above
    :width="280"
    :breakpoint="700"
    bordered
    class="modern-sidebar"
  >
    <q-scroll-area class="fit">
      <div class="q-pa-md">
        <!-- Sections List -->
        <div class="sections-list">
          <SectionCard
            v-for="section in sections"
            :key="section.id"
            :section="section"
            :slide-count="getSectionSlideCount(section.id)"
            :is-active="currentSection === section.id"
            :is-completed="isSectionCompleted(section.id)"
            :is-locked="!canAccessSection(section.id)"
            @select="$emit('section-select', section.id)"
            class="q-mb-sm"
          />
        </div>
      </div>
    </q-scroll-area>
  </q-drawer>
</template>

<script setup>
import SectionCard from './SectionCard.vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: true
  },
  sections: {
    type: Array,
    default: () => []
  },
  currentSection: {
    type: String,
    default: ''
  },
  slides: {
    type: Array,
    default: () => []
  },
  progress: {
    type: Object,
    default: null
  },
  canAccessSection: {
    type: Function,
    required: true
  },
  isSectionCompleted: {
    type: Function,
    required: true
  }
});

defineEmits(['update:modelValue', 'section-select']);

const getSectionSlideCount = (sectionId) => {
  return props.slides.filter(s => s.section === sectionId).length;
};
</script>

<style scoped lang="scss">
.modern-sidebar {
  background: white;
}
</style>
