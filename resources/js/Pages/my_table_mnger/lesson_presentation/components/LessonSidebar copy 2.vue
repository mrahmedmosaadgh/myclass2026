<template>
  <div>
    <!-- Custom Sidebar -->
    <div 
      class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 border-r border-gray-200 flex flex-col"
      :class="showDrawer ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <q-icon name="view_sidebar" size="24px" color="primary" />
            <h2 class="text-lg font-bold text-gray-800">Lesson Plan</h2>
          </div>
          <!-- Close button only on mobile -->
          <q-btn flat dense round icon="close" @click="$emit('update:showDrawer', false)" class="lg:hidden" />
        </div>
        <p class="text-xs text-gray-600 mt-1 flex items-center gap-1">
          <q-icon name="touch_app" size="14px" />
          Select a section to manage slides
        </p>
      </div>

      <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
        <q-list separator>
          <div
            v-for="section in sections" 
            :key="section.id"
            class="mb-3 rounded-xl overflow-hidden transition-all duration-300 border"
            :class="currentSection === section.id ? 'bg-blue-50 border-blue-200 shadow-md' : 'bg-white border-transparent hover:bg-gray-50'"
          >
            <!-- Section Header -->
            <q-item
              clickable
              v-ripple
              @click="$emit('update:currentSection', section.id)"
              class="py-3"
            >
              <q-item-section avatar>
                <q-avatar 
                  :color="currentSection === section.id ? section.borderColor : 'grey-2'" 
                  text-color="white"
                  size="40px"
                  class="shadow-sm"
                >
                  <q-icon :name="section.qIcon || section.icon" size="22px" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="font-bold text-gray-800 text-sm">
                  {{ section.title }}
                </q-item-label>
                <q-item-label caption class="text-xs text-gray-500">
                  {{ getSectionSlideCount(section.id) }} slide(s)
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="flex items-center gap-2">
                  <slot name="section-status" :section="section"></slot>
                  <q-icon 
                    :name="currentSection === section.id ? 'expand_less' : 'expand_more'" 
                    :color="currentSection === section.id ? 'primary' : 'grey'" 
                  />
                </div>
              </q-item-section>
            </q-item>

            <!-- Slides List (Visible if Active) -->
            <q-slide-transition>
              <div v-if="currentSection === section.id" class="bg-white border-t border-gray-100 p-3">
                <!-- Add Slide Button (Only if editable) -->
                <button 
                  v-if="canEdit"
                  type="button"
                  @click.stop="$emit('addSlide')"
                  class="w-full py-2 px-3 mb-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold uppercase tracking-wide shadow-sm transition-all flex items-center justify-center gap-2"
                >
                  <q-icon name="add" size="16px" />
                  Add New Slide
                </button>

                <!-- Slides -->
                <div class="space-y-2">
                  <div 
                    v-for="(slide, index) in getSectionSlides(section.id)" 
                    :key="slide.id || index"
                    @click.stop="$emit('selectSlide', slide)"
                    class="p-2 rounded-lg cursor-pointer border transition-all group relative"
                    :class="isSlideActive(slide) ? 'bg-blue-50 border-blue-400 ring-1 ring-blue-400' : 'bg-gray-50 border-gray-200 hover:border-blue-300'"
                  >
                    <div class="flex justify-between items-center mb-1">
                      <span class="font-bold text-xs text-gray-700">Slide {{ getSlideGlobalIndex(slide) + 1 }}</span>
                      <q-badge :color="isSlideActive(slide) ? 'blue' : 'grey'" size="sm" :label="slide.slide_type" />
                    </div>
                    <div class="text-[10px] text-gray-500 truncate pl-1 border-l-2 border-gray-300">
                      {{ getSlideSummary(slide) }}
                    </div>
                  </div>
                  
                  <div v-if="getSectionSlides(section.id).length === 0" class="text-center py-4 text-gray-400 text-xs italic">
                    {{ canEdit ? 'No slides yet. Click above to add one.' : 'No slides in this section.' }}
                  </div>
                </div>
              </div>
            </q-slide-transition>
          </div>
        </q-list>
      </div>






      
    </div>




          <div class="flex-1 overflow-y-auto p-3 custom-scrollbar">
        <q-list separator>
          <div
            v-for="section in sections" 
            :key="section.id"
            class="mb-3 rounded-xl overflow-hidden transition-all duration-300 border"
            :class="currentSection === section.id ? 'bg-blue-50 border-blue-200 shadow-md' : 'bg-white border-transparent hover:bg-gray-50'"
          >
            <!-- Section Header -->
            <q-item v-if="currentSection === section.id" 
              clickable
              v-ripple
              class="py-3"
              >
              <!-- @click="$emit('update:currentSection', section.id)" -->
              <q-item-section avatar>
                <q-avatar 
                  :color="currentSection === section.id ? section.borderColor : 'grey-2'" 
                  text-color="white"
                  size="40px"
                  class="shadow-sm"
                >
                  <q-icon :name="section.qIcon || section.icon" size="22px" />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="font-bold text-gray-800 text-sm">
                  {{ section.title }}
                </q-item-label>
                <q-item-label caption class="text-xs text-gray-500">
                  {{ getSectionSlideCount(section.id) }} slide(s)
                </q-item-label>
              </q-item-section>
              <q-item-section side>
                <div class="flex items-center gap-2">
                  <slot name="section-status" :section="section"></slot>
                  <q-icon 
                    :name="currentSection === section.id ? 'expand_less' : 'expand_more'" 
                    :color="currentSection === section.id ? 'primary' : 'grey'" 
                  />
                </div>
              </q-item-section>
            </q-item>

            <!-- Slides List (Visible if Active) -->
            <q-slide-transition>
              <div v-if="currentSection === section.id" class="bg-white border-t border-gray-100 p-3">
                <!-- Add Slide Button (Only if editable) -->
                <button 
                  v-if="canEdit"
                  type="button"
                  @click.stop="$emit('addSlide')"
                  class="w-full py-2 px-3 mb-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold uppercase tracking-wide shadow-sm transition-all flex items-center justify-center gap-2"
                >
                  <q-icon name="add" size="16px" />
                  Add New Slide
                </button>

                <!-- Slides -->
                <div class="space-y-2">
                  <div 
                    v-for="(slide, index) in getSectionSlides(section.id)" 
                    :key="slide.id || index"
                    @click.stop="$emit('selectSlide', slide)"
                    class="p-2 rounded-lg cursor-pointer border transition-all group relative"
                    :class="isSlideActive(slide) ? 'bg-blue-50 border-blue-400 ring-1 ring-blue-400' : 'bg-gray-50 border-gray-200 hover:border-blue-300'"
                  >
                    <div class="flex justify-between items-center mb-1">
                      <span class="font-bold text-xs text-gray-700">Slide {{ getSlideGlobalIndex(slide) + 1 }}</span>
                      <q-badge :color="isSlideActive(slide) ? 'blue' : 'grey'" size="sm" :label="slide.slide_type" />
                    </div>
                    <div class="text-[10px] text-gray-500 truncate pl-1 border-l-2 border-gray-300">
                      {{ getSlideSummary(slide) }}
                    </div>
                  </div>
                  
                  <div v-if="getSectionSlides(section.id).length === 0" class="text-center py-4 text-gray-400 text-xs italic">
                    {{ canEdit ? 'No slides yet. Click above to add one.' : 'No slides in this section.' }}
                  </div>
                </div>
              </div>
            </q-slide-transition>
          </div>
        </q-list>
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
