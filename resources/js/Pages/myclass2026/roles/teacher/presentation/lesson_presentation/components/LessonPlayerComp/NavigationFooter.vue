<template>
  <div>
    <q-separator />
    <q-card-actions class="q-pa-md row justify-between items-center">
      <!-- Previous Button -->
      <q-btn
        unelevated
        :disable="currentSlide === 0"
        color="grey-6"
        text-color="white"
        size="lg"
        class="nav-btn"
        @click="$emit('prev')"
      >
        <q-icon name="chevron_left" size="24px" class="q-mr-xs" />
        <span class="gt-xs">Previous</span>
      </q-btn>
      
      <!-- Slide Counter -->
      <div class="text-center">
        <q-chip color="grey-3" text-color="grey-8" class="q-px-md">
          <q-icon name="article" size="18px" class="q-mr-xs" />
          {{ currentSlide + 1 }} / {{ totalSlides }}
        </q-chip>
      </div>
      
      <!-- Next Button -->
      <q-btn
        unelevated
        :color="canProceed ? 'primary' : 'grey-4'"
        :text-color="canProceed ? 'white' : 'grey-7'"
        size="lg"
        class="nav-btn"
        @click="$emit('next')"
        :disable="!canProceed"
      >
        <span class="gt-xs">{{ isLastSlide ? 'Complete' : 'Next' }}</span>
        <q-icon :name="isLastSlide ? 'check_circle' : 'chevron_right'" size="24px" class="q-ml-xs" />
        
        <q-tooltip v-if="!canProceed">
          Complete all questions to continue
        </q-tooltip>
      </q-btn>
    </q-card-actions>
  </div>
</template>

<script setup>
defineProps({
  currentSlide: {
    type: Number,
    default: 0
  },
  totalSlides: {
    type: Number,
    default: 0
  },
  canProceed: {
    type: Boolean,
    default: true
  },
  isLastSlide: {
    type: Boolean,
    default: false
  }
});

defineEmits(['prev', 'next']);
</script>

<style scoped lang="scss">
.nav-btn {
  min-width: 120px;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
  
  &:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  }
}
</style>
