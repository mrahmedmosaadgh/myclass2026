<template>
    <div>
        <!-- Sections Grid -->
        <div class="flex flex-wrap gap-3 justify-center p-1 scale-75">
            <div 
                v-for="(section, index) in store.sections"
                :key="section.id"
                class="cursor-pointer hover:scale-105 p-3 rounded-xl border-2 flex items-center gap-1 transition-all duration-200 shadow-sm"
                :class="store.currentSectionId == section.id ? 'ring-2 ring-offset-2' : 'opacity-75 hover:opacity-100'"
                :style="{ 
                    backgroundColor: store.currentSectionId == section.id ? section.bg : 'white', 
                    borderColor: section.borderColor,
                    ringColor: section.borderColor
                }"
                @click="store.setSection(section.id)"
            >
                <div class="w-9 h-9 rounded-full flex items-center justify-center shadow-sm"
                     :style="{ 
                       color: section.textColor,
                       backgroundColor: store.currentSectionId == section.id ? 'rgba(255,255,255,0.9)' : section.bg
                     }"
                >
                    <q-icon :name="section.qIcon || section.icon" size="12px" />
                </div>
                <div>
                    <div class="text-base font-bold leading-tight"
                         :style="{ color: store.currentSectionId == section.id ? section.textColor : '#374151' }"
                    >
                        <span class="inline-block w-5 h-5 text-center text-xs leading-5 rounded mr-1"
                              :style="{ backgroundColor: store.currentSectionId == section.id ? 'rgba(255,255,255,0.3)' : section.bg, color: store.currentSectionId == section.id ? section.textColor : 'black' }"
                        >{{ index + 1 }}</span>
                        {{ section.title }}
                    </div>
                    <div class="text-[10px] font-medium mt-0.5" 
                         :style="{ color: store.currentSectionId == section.id ? section.textColor : '#9CA3AF', opacity: store.currentSectionId == section.id ? 0.8 : 1 }">
                        {{ store.slides.filter(s => s.section === section.id).length }} slides  
                    </div>
                </div>
            </div>
        </div>

        <!-- Slide Navigation and Controls -->
        <div class="p-4" v-if="currentSectionData"> 
            <!-- Section Header -->
            <div class="mb-1 p-1 rounded-lg flex items-center gap-3"
                 :style="{ backgroundColor: currentSectionData.bg }">
                <div class="w-8 h-8 rounded-full flex items-center justify-center bg-white/90"
                     :style="{ color: currentSectionData.textColor }">
                    <q-icon :name="currentSectionData.qIcon || currentSectionData.icon" size="16px" />
                </div>
                <div class="flex-1">
                    <div class="text-xs font-semibold opacity-80" :style="{ color: currentSectionData.textColor }">
                        Current Section
                    </div>
                    <div class="text-sm font-bold" :style="{ color: currentSectionData.textColor }">
                        {{ currentSectionData.title }}    [{{ store.currentSectionSlides.length }} slide]
                    </div>

                    <!-- Pagination -->
                    <!-- Note: Pagination is 1-based index -->
                    <div v-if="localSlideIndex > 0" class="flex justify-center">
                        <q-pagination
                            v-model="localSlideIndex"
                            :max="store.currentSectionSlides.length"
                            :max-pages="7"
                            direction-links
                            boundary-links
                            icon-first="skip_previous"
                            icon-last="skip_next"
                            icon-prev="fast_rewind"
                            icon-next="fast_forward"
                            @update:model-value="onPaginationChange"
                            color="primary"
                            active-color="primary"
                        />
                        
                        <q-btn
                            round
                            dense
                            color="positive"
                            icon="add"
                            size="sm"
                            @click="store.addSlide()"
                        >
                            <q-tooltip>Add Slide</q-tooltip>
                        </q-btn>
                    </div>

                </div>
            </div>

            <!-- Slide Controls -->
            <div 
                v-if="store.currentSectionSlides.length > 0"
                class="bg-white rounded-lg shadow-sm border border-gray-200 ">
                <div v-if="store.currentSectionSlides.length > 0">
                    <!-- Slide Info or other controls can go here -->
                </div>
            </div>
            
            <!-- Empty State -->
            <div v-else class="text-center py-6 bg-white rounded-lg shadow-sm border border-gray-200">
                <q-icon name="layers" size="2.5rem" color="grey-4" />
                <p class="text-gray-400 text-sm mt-2 mb-3">No slides yet</p>
                <q-btn 
                    unelevated
                    color="positive"
                    icon="add"
                    label="Add First Slide"
                    size="sm"
                    @click="store.addSlide()"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from "vue";
import { useLessonPresentationStore } from '@/Stores/lessonPresentationStore';

// Initialize Store
const store = useLessonPresentationStore();

// Props are simplified
const props = defineProps({
    showDrawer: {
        type: Boolean,
        default: false,
    },
    canEdit: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits([
    "update:showDrawer",
]);

// Helper to get current section metadata (color, icon, etc)
const currentSectionData = computed(() => {
    if (!store.currentSectionId) return null;
    return store.sections.find(s => s.id === store.currentSectionId);
});

// Manage local pagination index (1-based)
const localSlideIndex = ref(1);

// Sync local index from store state
watch(() => store.activeSlideId, (newId) => {
    if (!newId) {
        localSlideIndex.value = 0;
        return;
    }
    const index = store.currentSectionSlides.findIndex(s => (s.id === newId) || (s._tempId === newId));
    if (index !== -1) {
        localSlideIndex.value = index + 1;
    } else {
        // If active slide is not in current section (shouldn't happen if store enforces logic)
        // Reset or try to find
        localSlideIndex.value = 1;
    }
}, { immediate: true });

// Handle user clicking pagination
// value is 1-based index
const onPaginationChange = (value) => {
    const slide = store.currentSectionSlides[value - 1];
    if (slide) {
        store.activeSlideId = slide.id || slide._tempId;
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
