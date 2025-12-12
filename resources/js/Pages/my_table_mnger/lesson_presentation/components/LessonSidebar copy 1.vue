<template>
  <div>
    <!-- Custom Sidebar -->
    <div 
      class="fixed inset-y-0 left-0 z-40 w-72 bg-white border-r border-gray-200 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto flex flex-col h-full shadow-2xl lg:shadow-none"
      :class="showDrawer ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="px-5 py-4 border-b border-gray-100 bg-white shrink-0">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-3">
            <div class="bg-blue-50 p-2 rounded-lg">
              <q-icon name="view_sidebar" size="20px" class="text-blue-600" />
            </div>
            <div>
              <h2 class="text-base font-bold text-gray-800 leading-tight">Lesson Plan</h2>
              <p class="text-[10px] text-gray-500 font-medium mt-0.5">Manage sections & slides</p>
            </div>
          </div>
          <!-- Close button only on mobile -->
          <q-btn flat dense round icon="close" size="sm" color="grey-6" @click="$emit('update:showDrawer', false)" class="lg:hidden" />
        </div>
      </div>

      <div class="flex-1 flex flex-col overflow-y-auto p-3 custom-scrollbar">
        <q-list separator>
          <div
            v-for="section in sections" 
            :key="section.id"
            class="mb-1 overflow-hidden transition-all duration-200"
            :class="currentSection === section.id ? 'bg-blue-50/50' : 'bg-transparent'"
          >
            <!-- Section Header -->
            <q-item
              clickable
              v-ripple
              @click="$emit('update:currentSection', section.id)"
              class="py-3 px-4 border-l-4 transition-all duration-200"
              :class="currentSection === section.id ? 'border-blue-500 bg-blue-50' : 'border-transparent hover:bg-gray-50'"
            >
              <q-item-section avatar class="min-w-0 pr-3">
                <div 
                  class="w-8 h-8 rounded-full flex items-center justify-center transition-colors duration-200"
                  :class="currentSection === section.id ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'"
                >
                  <q-icon :name="section.qIcon || section.icon" size="16px" />
                </div>
              </q-item-section>
              <q-item-section>
                <q-item-label 
                  class="font-semibold text-sm transition-colors duration-200"
                  :class="currentSection === section.id ? 'text-gray-900' : 'text-gray-600'"
                >
                  {{ section.title }}
                </q-item-label>
                <q-item-label caption class="text-[10px] text-gray-400 font-medium">
                  {{ getSectionSlideCount(section.id) }} slide(s)
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="flex items-center gap-2">
                  <slot name="section-status" :section="section"></slot>
                  <q-icon 
                    :name="currentSection === section.id ? 'expand_less' : 'expand_more'" 
                    size="16px"
                    :class="currentSection === section.id ? 'text-blue-500' : 'text-gray-400'"
                  />
                </div>
              </q-item-section>
            </q-item>

            <!-- Slides List (Visible if Active) -->
            <q-slide-transition>
              <div v-if="currentSection === section.id" class="bg-gray-50/50 border-t border-gray-100/50 p-3 pl-4">
                <!-- Add Slide Button (Only if editable) -->
                <button 
                  v-if="canEdit"
                  type="button"
                  @click.stop="$emit('addSlide')"
                  class="w-full py-2 px-3 mb-3 border-2 border-dashed border-gray-200 hover:border-blue-400 hover:bg-blue-50 text-gray-400 hover:text-blue-600 rounded-lg text-xs font-bold uppercase tracking-wide transition-all duration-200 flex items-center justify-center gap-2 group"
                >
                  <div class="bg-gray-100 group-hover:bg-blue-100 rounded-full p-0.5 transition-colors">
                    <q-icon name="add" size="12px" />
                  </div>
                  <span>Add Slide</span>
                </button>

                <!-- Slides -->
                <div class="space-y-2 pl-1 border-l-2 border-gray-200 ml-1.5">
                  <div 
                    v-for="(slide, index) in getSectionSlides(section.id)" 
                    :key="slide.id || index"
                    @click.stop="$emit('selectSlide', slide)"
                    class="ml-2 p-2.5 rounded-lg cursor-pointer border transition-all duration-200 group relative"
                    :class="isSlideActive(slide) ? 'bg-white border-blue-500 shadow-sm ring-1 ring-blue-500/20 z-10' : 'bg-white border-gray-200 hover:border-blue-300 hover:shadow-sm'"
                  >
                    <div class="flex justify-between items-center mb-1.5">
                      <span 
                        class="font-bold text-[11px] uppercase tracking-wider"
                        :class="isSlideActive(slide) ? 'text-blue-700' : 'text-gray-500'"
                      >
                        Slide {{ getSlideGlobalIndex(slide) + 1 }}
                      </span>
                      <q-badge 
                        :color="isSlideActive(slide) ? 'blue' : 'grey-4'" 
                        :text-color="isSlideActive(slide) ? 'white' : 'grey-8'"
                        size="xs" 
                        class="font-medium px-1.5 py-0.5"
                      >
                        {{ slide.slide_type }}
                      </q-badge>
                    </div>
                    <div class="text-[11px] text-gray-500 truncate pl-2 border-l-2"
                      :class="isSlideActive(slide) ? 'border-blue-200' : 'border-gray-100'"
                    >
                      {{ getSlideSummary(slide) }}
                    </div>
                  </div>
                  
                  <div v-if="getSectionSlides(section.id).length === 0" class="ml-2 text-center py-6 text-gray-400 text-xs italic bg-gray-50 rounded-lg border border-dashed border-gray-200">
                    {{ canEdit ? 'No slides yet' : 'No content' }}
                  </div>
                </div>
              </div>
            </q-slide-transition>
          </div>
        </q-list>
      </div>






      
    </div>



 
    <!-- Overlay for mobile -->
    <div 
      v-if="showDrawer" 
      class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden"
      @click="$emit('update:showDrawer', false)"
    ></div>

    <!-- Mobile: Floating Toggle Button -->
    <q-btn
      round
      color="primary"
      icon="menu"
      class="fixed bottom-6 left-6 z-50 lg:hidden shadow-lg"
      @click="$emit('update:showDrawer', true)"
    >
      <q-tooltip>Show Sections</q-tooltip>
    </q-btn>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  sections: {
    type: Array,
    required: true
  },
  currentSection: {
    type: String,
    required: true
  },
  slides: {
    type: Array,
    default: () => []
  },
  showDrawer: {
    type: Boolean,
    default: false
  },
  canEdit: {
    type: Boolean,
    default: true
  },
  activeSlide: {
    type: Object,
    default: null
  }
});

const emit = defineEmits(['update:currentSection', 'update:showDrawer', 'addSlide', 'selectSlide']);

const getSectionSlides = (sectionId) => {
  return props.slides.filter(s => s.section === sectionId);
};

const getSectionSlideCount = (sectionId) => {
  return getSectionSlides(sectionId).length;
};

const getSlideGlobalIndex = (slide) => {
  return props.slides.indexOf(slide);
};

const isSlideActive = (slide) => {
  if (!props.activeSlide) return false;
  // Compare by ID if available, otherwise by reference or index if needed
  // Assuming reference equality or ID check
  return slide === props.activeSlide || (slide.id && slide.id === props.activeSlide.id);
};

const getSlideSummary = (slide) => {
  if (slide.slide_type === 'text') {
    const text = slide.slide_content?.text || '';
    return text.replace(/<[^>]*>/g, '').substring(0, 30) + (text.length > 30 ? '...' : '');
  } else if (slide.slide_type === 'question') {
    const count = slide.slide_content?.questions?.length || 0;
    return `${count} Question${count !== 1 ? 's' : ''}`;
  } else {
    return `${slide.slide_type} content`;
  }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1; 
  border-radius: 2px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8; 
}
</style>
