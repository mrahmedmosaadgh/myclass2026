<template>
    <div>
        <!-- Custom Sidebar -->
 

         
        






<!--
               <select 
                               @change="  currentSection = currentSection_data2.id; currentSection_index = index; $emit('update:currentSection_data', currentSection_data2)"

                v-model="currentSection_data2"
                class="text-sm border-gray-300 rounded-lg text-gray-700 py-1.5 px-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option :value="null" disabled>sction</option>
                <option 
                  v-for="(section,index ) in sections" 
                  :key="section.id" 
                  :value="section"
                >
                  {{ section.title }}
                </option>
              </select> -->






            <!-- ------------------------------------ -->
    



   <div class="flex flex-wrap gap-3 justify-center p-1 scale-75">
              <div 
                v-for="(section, index) in sections"
                :key="section.id"
                class="cursor-pointer hover:scale-105 p-3 rounded-xl border-2 flex items-center gap-1 transition-all duration-200 shadow-sm"
                :class="currentSection == section.id ? 'ring-2 ring-offset-2' : 'opacity-75 hover:opacity-100'"
                :style="{ 
                  backgroundColor: currentSection == section.id ? section.bg : 'white', 
                  borderColor: section.borderColor,
                  ringColor: section.borderColor
                }"
                @click="currentSection_data2 = section; currentSection = section.id; currentSection_index = index; $emit('update:currentSection_data', section)"
              >
                <div class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm"
                     :style="{ 
                       color: section.textColor,
                       backgroundColor: currentSection == section.id ? 'rgba(255,255,255,0.9)' : section.bg
                     }"
                >
                  <q-icon :name="section.qIcon || section.icon" size="12px" />
                </div>
                <div>
                  <div class="text-base font-bold leading-tight"
                       :style="{ color: currentSection == section.id ? section.textColor : '#374151' }"
                  >
                    <span class="inline-block w-5 h-5 text-center text-xs leading-5 rounded mr-1"
                          :style="{ backgroundColor: currentSection == section.id ? 'rgba(255,255,255,0.3)' : section.bg, color: currentSection == section.id ? section.textColor : 'black' }"
                    >{{ index + 1 }}</span>
                    {{ section.title }}
                  </div>
                  <div class="text-[10px] font-medium mt-0.5" 
                       :style="{ color: currentSection == section.id ? section.textColor : '#9CA3AF', opacity: currentSection == section.id ? 0.8 : 1 }">
                    {{ getSectionSlideCount(section.id) }} slides  
                  </div>
                </div>
              </div>
            </div>


 


  


 





 
            <!-- =============================== -->

                          <!-- Slide Navigation and Controls -->
                          <div class="p-4" v-if="currentSection_data2"> 
                            <!-- Section Header -->
                            <div class="mb-1 p-1 rounded-lg flex items-center gap-3"
                                 :style="{ backgroundColor: currentSection_data2.bg }">
                              <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/90"
                                   :style="{ color: currentSection_data2.textColor }">
                                <q-icon :name="currentSection_data2.qIcon || currentSection_data2.icon" size="16px" />
                              </div>
                              <div class="flex-1">
                                <div class="text-xs font-semibold opacity-80" :style="{ color: currentSection_data2.textColor }">
                                  Current Section
                                </div>
                                <div class="text-sm font-bold" :style="{ color: currentSection_data2.textColor }">
                                  {{ currentSection_data2.title }}    [{{ getSectionSlideCount(currentSection_data2.id)  }} slide]
                                </div>



                            
                                

                                <!-- Pagination -->
                                <div  v-if="currentSlideIndex" class="flex justify-center">
                                  <q-pagination
                                    v-model="currentSlideIndex"
                                    :max="currentSectionSlides.length"
                                    :max-pages="7"
                                    direction-links
                                    boundary-links
                                    icon-first="skip_previous"
                                    icon-last="skip_next"
                                    icon-prev="fast_rewind"
                                    icon-next="fast_forward"
                                    @update:model-value="onSlideChange"
                                    color="primary"
                                    active-color="primary"
                                  />
                                  
                                  
                                  



                                  <q-btn
                                  round
                                  dense
                                  color="positive"
                                  icon="add"
                                  size="sm"
                                  @click="addSlide"
                                  >
                                  <q-tooltip>Add Slide</q-tooltip>
                                </q-btn>
                            </div>

                              </div>
                            </div>

                            <!-- Slide Controls -->
                            <div 
                            v-if="currentSlideIndex"
                            class="bg-white rounded-lg shadow-sm border border-gray-200 ">
                              <div v-if=" currentSectionSlides.length > 0">
                                <!-- Slide Counter -->
                                <!-- <div class="flex items-center justify-between mb-3">
                                  <div class="text-sm font-semibold text-gray-700">
                                    Slide {{ currentSlideIndex }} of {{ currentSectionSlides.length }}
                                  </div>
                                </div> -->


                                <!-- Slide Info -->
                                <!-- <div class="mt-3 pt-3 border-t border-gray-100">
                                  <div class="text-xs text-gray-600 flex items-center gap-2">
                                    <q-icon name="category" size="14px" />
                                    <span class="font-semibold">Type:</span>
                                    <span class="capitalize">{{ currentSlideData?.slide_type || 'text' }}</span>
                                  </div>
                                </div> -->
                              </div>

                              <!-- Empty State -->
                              <div v-else class="text-center py-6">
                                <q-icon name="layers" size="2.5rem" color="grey-4" />
                                <p class="text-gray-400 text-sm mt-2 mb-3">No slides yet</p>
                                <q-btn 
                                  unelevated
                                  color="positive"
                                  icon="add"
                                  label="Add First Slide"
                                  size="sm"
                                  @click="addSlide"
                                />
                              </div>
                            </div>
                          </div>



<!-- =============================== -->            




    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";

 
const props = defineProps({
    sections: {
        type: Array,
        required: true,
    },
    currentSection: {
        type: String,
        default: '',
    },
    slides: {
        type: Array,
        default: () => [],
    },
    showDrawer: {
        type: Boolean,
        default: false,
    },
    canEdit: {
        type: Boolean,
        default: true,
    },
    activeSlide: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits([
    "update:currentSection", 
    "update:currentSection_data",
    "update:showDrawer",
    "addSlide",
    "deleteSlide",
    "selectSlide",
]);
const currentSection = computed({
    get: () => props.currentSection,
    set: (value) => emit('update:currentSection', value)
});
const currentSection_index = ref(0); // Default to 'learn' section
const currentSlideIndex = ref(null); // Pagination is 1-based
const currentSection_data2 = ref({}); // Pagination is 1-based

 
const getSectionSlides = (sectionId) => {
    return props.slides.filter((s) => s.section === sectionId);
};

// Get slides for current section
const currentSectionSlides = computed(() => {
    return getSectionSlides(currentSection.value);
});

// When slide changes via pagination
const onSlideChange = (newIndex) => {
    // newIndex is 1-based from q-pagination
    const slideIndex = newIndex - 1; // Convert to 0-based
    const slide = currentSectionSlides.value[slideIndex];
    if (slide) {
        emit('selectSlide', slide);
    }
};

// Watch for active slide changes to update pagination
watch(() => props.activeSlide, (newSlide) => {
    if (newSlide && currentSectionSlides.value.length > 0) {
        const index = currentSectionSlides.value.findIndex(s => 
            s === newSlide || (s.id && s.id === newSlide.id)
        );
        if (index !== -1) {
            currentSlideIndex.value = index + 1; // Convert to 1-based for pagination
        }
    }
}, { immediate: true });

// Reset slide index when section changes
watch(() => props.currentSection, () => {
    currentSlideIndex.value = 1;
    // Select first slide of new section
    if (currentSectionSlides.value.length > 0) {
        emit('selectSlide', currentSectionSlides.value[0]);
    }
});

// Get current slide data
const currentSlideData = computed(() => {
    if (currentSectionSlides.value.length === 0) return null;
    return currentSectionSlides.value[currentSlideIndex.value - 1];
});

// Add slide to current section
const addSlide = () => {
    emit('addSlide');
    // After adding, pagination will auto-update via watcher
};

// Delete current slide
// const deleteCurrentSlide = () => {
//     if (currentSectionSlides.value.length === 0) return;
//     const slide = currentSectionSlides.value[currentSlideIndex.value - 1];
//     emit('deleteSlide', slide);
// };

// Get slide preview text
const getSlidePreview = (slide) => {
    if (!slide) return 'No content';
    
    if (slide.slide_type === 'text') {
        const text = slide.slide_content?.text || '';
        const stripped = text.replace(/<[^>]*>/g, '').trim();
        return stripped.substring(0, 30) + (stripped.length > 30 ? '...' : '') || 'Empty text';
    } else if (slide.slide_type === 'question') {
        const count = slide.slide_content?.questions?.length || 0;
        return `${count} question${count !== 1 ? 's' : ''}`;
    } else if (slide.slide_type === 'image') {
        return 'Image content';
    } else if (slide.slide_type === 'video') {
        return 'Video content';
    } else if (slide.slide_type === 'pdf') {
        return 'PDF content';
    } else {
        return `${slide.slide_type} content`;
    }
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
    return (
        slide === props.activeSlide ||
        (slide.id && slide.id === props.activeSlide.id)
    );
};

const getSlideSummary = (slide) => {
    if (slide.slide_type === "text") {
        const text = slide.slide_content?.text || "";
        return (
            text.replace(/<[^>]*>/g, "").substring(0, 30) +
            (text.length > 30 ? "..." : "")
        );
    } else if (slide.slide_type === "question") {
        const count = slide.slide_content?.questions?.length || 0;
        return `${count} Question${count !== 1 ? "s" : ""}`;
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

/* Transition animations */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
