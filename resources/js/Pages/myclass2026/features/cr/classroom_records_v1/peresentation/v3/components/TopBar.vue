<template>
  <q-toolbar class="bg-grey-9 text-white shadow-2">
    <!-- Left: Logo and Title -->
    <div class="flex items-center q-mr-md">
      <q-avatar 
        color="primary" 
        text-color="white" 
        icon="slideshow" 
        size="md"
        class="q-mr-sm"
      />
      <q-toolbar-title class="text-body1 text-weight-medium gt-sm">
        Presentation Builder V3
      </q-toolbar-title>
    </div>

    <q-space />

    <!-- Editing Tools (show in edit mode) -->
    <div v-if="mode === 'edit'" class="flex items-center q-gutter-x-sm">
      <!-- Add Slide -->
      <q-btn
        @click="$emit('add-slide')"
        flat
        dense
        round
        icon="add"
        color="white"
      >
        <q-tooltip>Add Slide</q-tooltip>
      </q-btn>

      <!-- Add Elements Dropdown -->
      <q-btn-dropdown
        flat
        dense
        round
        icon="add_box"
        color="white"
        menu-anchor="bottom left"
        menu-self="top left"
      >
        <q-list dense>
          <q-item
            clickable
            v-close-popup
            @click="$emit('add-text')"
            class="text-grey-9"
          >
            <q-item-section side>
              <q-icon name="text_fields" />
            </q-item-section>
            <q-item-section>Add Text</q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="$emit('add-rectangle')"
            class="text-grey-9"
          >
            <q-item-section side>
              <q-icon name="crop_square" />
            </q-item-section>
            <q-item-section>Add Rectangle</q-item-section>
          </q-item>

          <q-item
            clickable
            v-close-popup
            @click="$emit('add-image')"
            class="text-grey-9"
          >
            <q-item-section side>
              <q-icon name="image" />
            </q-item-section>
            <q-item-section>Add Image</q-item-section>
          </q-item>
        </q-list>
      </q-btn-dropdown>

      <!-- Previous Slide -->
      <q-btn
        @click="$emit('prev-slide')"
        flat
        dense
        round
        icon="navigate_before"
        color="white"
        :disable="!hasPrevSlide"
      >
        <q-tooltip>Previous Slide</q-tooltip>
      </q-btn>

      <!-- Next Slide -->
      <q-btn
        @click="$emit('next-slide')"
        flat
        dense
        round
        icon="navigate_next"
        color="white"
        :disable="!hasNextSlide"
      >
        <q-tooltip>Next Slide</q-tooltip>
      </q-btn>

      <!-- Paste Button -->
      <q-btn
        @click="$emit('paste')"
        flat
        dense
        round
        icon="content_paste"
        color="white"
      >
        <q-tooltip>Paste (Ctrl+V)</q-tooltip>
      </q-btn>
    </div>

    <q-space />

    <!-- Right: Actions Only -->
    <div class="flex items-center q-gutter-x-sm">
      <!-- Present Button (only show in edit mode) -->
      <q-btn
        v-if="mode === 'edit'"
        @click="$emit('mode-change', 'present')"
        color="primary"
        round
        icon="play_arrow"
        class="text-weight-bold"
      >
        <q-tooltip>Start Presentation</q-tooltip>
      </q-btn>

      <!-- Exit Presentation Button (only show in present mode) -->
      <q-btn
        v-if="mode === 'present'"
        @click="$emit('mode-change', 'edit')"
        color="negative"
        round
        icon="close"
        class="text-weight-bold"
      >
        <q-tooltip>Exit Presentation</q-tooltip>
      </q-btn>

      <!-- Slide Size Settings (only show in edit mode) -->
      <q-btn
        v-if="mode === 'edit'"
        flat
        dense
        round
        icon="settings"
        color="white"
        @click="showSettings = !showSettings"
      >
        <q-tooltip>Slide Settings</q-tooltip>
      </q-btn>
    </div>
  </q-toolbar>

  <!-- Settings Dialog -->
  <q-dialog v-model="showSettings" position="bottom">
    <q-card class="q-pa-md" style="min-width: 350px">
      <q-card-section>
        <div class="text-h6 q-mb-md">Slide Size Settings</div>
        
        <q-option-group
          :model-value="selectedSize"
          @update:model-value="$emit('slide-size-change', { selectedSize: $event })"
          :options="sizeOptions"
          color="primary"
          class="q-mb-md"
        />
        
        <!-- Custom dimensions (show when custom is selected) -->
        <div v-if="selectedSize === 'custom'" class="row q-gutter-md q-mt-md">
          <div class="col">
            <q-input
              label="Width (px)"
              type="number"
              :model-value="customWidth"
              @update:model-value="$emit('slide-size-change', { selectedSize: 'custom', customWidth: parseInt($event) || 1920 })"
              min="100"
              max="4000"
              outlined
              dense
            />
          </div>
          <div class="col">
            <q-input
              label="Height (px)"
              type="number"
              :model-value="customHeight"
              @update:model-value="$emit('slide-size-change', { selectedSize: 'custom', customHeight: parseInt($event) || 1080 })"
              min="100"
              max="4000"
              outlined
              dense
            />
          </div>
        </div>
      </q-card-section>
      
      <q-card-actions align="right">
        <q-btn flat label="Close" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref } from 'vue'

// Props
const props = defineProps({
  mode: { type: String, default: 'edit' },
  selectedSize: { type: String, default: 'widescreen' },
  customWidth: { type: Number, default: 1920 },
  customHeight: { type: Number, default: 1080 },
  hasPrevSlide: { type: Boolean, default: false },
  hasNextSlide: { type: Boolean, default: false }
})

// Emits
const emit = defineEmits([
  'mode-change',
  'slide-size-change',
  'add-slide',
  'add-text',
  'add-rectangle',
  'add-image',
  'prev-slide',
  'next-slide',
  'paste'
])

// Refs
const showSettings = ref(false)

// Data
const sizeOptions = [
  {
    label: 'Widescreen (16:9)',
    value: 'widescreen',
    description: '1920 x 1080px'
  },
  {
    label: 'Standard (4:3)',
    value: 'standard', 
    description: '1024 x 768px'
  },
  {
    label: 'Custom',
    value: 'custom',
    description: 'Set custom dimensions'
  }
]
</script>

<style scoped>
.q-toolbar {
  min-height: 56px;
}

@media (min-width: 600px) {
  .q-toolbar {
    min-height: 64px;
  }
}
</style>