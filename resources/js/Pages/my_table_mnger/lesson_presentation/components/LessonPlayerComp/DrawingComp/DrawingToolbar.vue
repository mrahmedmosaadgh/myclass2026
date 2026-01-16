<template>
  <div class="drawing-toolbar" :class="{ 'active': isActive }">
    <q-card flat bordered class="toolbar-card">
      <q-card-section class="q-pa-sm">
        <div class="row items-center q-gutter-sm">
          <!-- Toggle Drawing Mode -->
          <q-btn
            :color="isActive ? 'deep-orange' : 'primary'"
            :icon="isActive ? 'close' : 'brush'"
            unelevated
            round
            @click="$emit('toggle')"
            size="lg"
            class="toggle-btn"
          >
            <q-tooltip>{{ isActive ? 'Exit Drawing Mode' : 'Start Drawing Mode' }}</q-tooltip>
          </q-btn>

          <!-- Divider -->
          <q-separator vertical inset v-if="isActive" class="q-mx-sm" />

          <!-- Drawing Tools (only show when active) -->
          <template v-if="isActive">
            <!-- Tool Selection -->
            <q-btn-toggle
              :model-value="drawingTool"
              @update:model-value="$emit('update:drawingTool', $event)"
              toggle-color="primary"
              flat
              dense
              :options="[
                {label: '', value: 'pen', icon: 'brush', slot: 'pen'},
                {label: '', value: 'eraser', icon: 'cleaning_services', slot: 'eraser'}
              ]"
            >
              <template v-slot:pen>
                <q-icon name="brush" />
                <q-tooltip>Pen Tool</q-tooltip>
              </template>
              <template v-slot:eraser>
                <q-icon name="cleaning_services" />
                <q-tooltip>Eraser Tool - Removes entire strokes</q-tooltip>
              </template>
            </q-btn-toggle>

            <q-separator vertical inset class="q-mx-sm" />
            
            <!-- Undo Button -->
            <q-btn
              flat
              round
              icon="undo"
              @click="$emit('undo')"
              color="primary"
              size="md"
            >
              <q-tooltip>Undo last stroke</q-tooltip>
            </q-btn>

            <!-- Clear Button -->
            <q-btn
              flat
              round
              icon="delete_sweep"
              @click="$emit('clear')"
              color="negative"
              size="md"
            >
              <q-tooltip>Clear all drawings</q-tooltip>
            </q-btn>

            <q-separator vertical inset class="q-mx-sm" />

            <!-- Color Picker -->
            <div class="color-section">
              <q-btn
                flat
                round
                :style="{ 
                  backgroundColor: penColor, 
                  border: '3px solid #fff',
                  boxShadow: '0 2px 8px rgba(0,0,0,0.2)'
                }"
                size="md"
              >
                <q-popup-proxy>
                  <q-color :model-value="penColor" @update:model-value="$emit('update:penColor', $event)" no-header no-footer />
                </q-popup-proxy>
                <q-tooltip>Choose custom color</q-tooltip>
              </q-btn>

              <!-- Quick Colors -->
              <div class="quick-colors q-ml-sm">
                <q-btn
                  v-for="color in quickColors"
                  :key="color"
                  flat
                  round
                  :style="{ 
                    backgroundColor: color, 
                    border: penColor === color ? '3px solid #000' : '2px solid #ddd',
                    transform: penColor === color ? 'scale(1.1)' : 'scale(1)'
                  }"
                  size="sm"
                  @click="$emit('update:penColor', color)"
                  class="color-btn"
                >
                  <q-tooltip>{{ getColorName(color) }}</q-tooltip>
                </q-btn>
              </div>
            </div>

            <q-separator vertical inset class="q-mx-sm" />

            <!-- Pen Size -->
            <div class="pen-size-control">
              <q-slider
                :model-value="penSize"
                @update:model-value="$emit('update:penSize', $event)"
                :min="1"
                :max="20"
                :step="1"
                label
                label-always
                color="primary"
                style="min-width: 100px"
              >
                <template v-slot:label="{ value }">
                  {{ value }}px
                </template>
              </q-slider>
            </div>

            <q-separator vertical inset class="q-mx-sm" />

            <!-- Background Color -->
            <div class="background-section">
              <q-btn
                flat
                round
                icon="format_color_fill"
                size="md"
              >
                <q-tooltip>Background Color</q-tooltip>
                <q-menu>
                  <q-list dense style="min-width: 180px">
                    <q-item clickable @click="$emit('update:backgroundColor', 'transparent')">
                      <q-item-section avatar>
                        <div class="transparent-preview"></div>
                      </q-item-section>
                      <q-item-section>Transparent</q-item-section>
                      <q-item-section side v-if="backgroundColor === 'transparent'">
                        <q-icon name="check" color="positive" />
                      </q-item-section>
                    </q-item>
                    <q-item
                      v-for="color in bgColors"
                      :key="color"
                      clickable
                      @click="$emit('update:backgroundColor', color)"
                    >
                      <q-item-section avatar>
                        <div class="color-preview" :style="{ backgroundColor: color }"></div>
                      </q-item-section>
                      <q-item-section>{{ getBgColorName(color) }}</q-item-section>
                      <q-item-section side v-if="backgroundColor === color">
                        <q-icon name="check" color="positive" />
                      </q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-btn>
            </div>

            <q-separator vertical inset class="q-mx-sm" />

            <!-- Page Navigation -->
            <div class="page-navigation">
              <q-btn
                flat
                dense
                round
                icon="chevron_left"
                @click="$emit('prev-page')"
                :disable="currentPage === 1"
                size="sm"
              >
                <q-tooltip>Previous Page</q-tooltip>
              </q-btn>
              
              <div class="page-indicator">
                {{ currentPage }} / {{ totalPages }}
              </div>
              
              <q-btn
                flat
                dense
                round
                icon="chevron_right"
                @click="$emit('next-page')"
                size="sm"
              >
                <q-tooltip>Next Page</q-tooltip>
              </q-btn>
              
              <q-btn
                flat
                dense
                round
                icon="add"
                @click="$emit('add-page')"
                color="positive"
                size="sm"
              >
                <q-tooltip>Add New Page</q-tooltip>
              </q-btn>
            </div>
          </template>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
defineProps({
  isActive: {
    type: Boolean,
    default: false
  },
  drawingTool: {
    type: String,
    default: 'pen'
  },
  penColor: {
    type: String,
    default: '#FF0000'
  },
  penSize: {
    type: Number,
    default: 3
  },
  backgroundColor: {
    type: String,
    default: 'transparent'
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  }
});

defineEmits([
  'toggle', 'undo', 'clear', 
  'update:drawingTool', 'update:penColor', 'update:penSize', 'update:backgroundColor',
  'prev-page', 'next-page', 'add-page'
]);

const quickColors = ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#000000', '#FFFFFF'];
const bgColors = ['#FFFFFF', '#000000', '#F5F5F5', '#E3F2FD', '#FFF3E0', '#E8F5E9'];

const getColorName = (color) => {
  const names = {
    '#FF0000': 'Red',
    '#00FF00': 'Green',
    '#0000FF': 'Blue',
    '#FFFF00': 'Yellow',
    '#FF00FF': 'Magenta',
    '#00FFFF': 'Cyan',
    '#000000': 'Black',
    '#FFFFFF': 'White'
  };
  return names[color] || 'Color';
};

const getBgColorName = (color) => {
  const names = {
    '#FFFFFF': 'White',
    '#000000': 'Black',
    '#F5F5F5': 'Light Gray',
    '#E3F2FD': 'Light Blue',
    '#FFF3E0': 'Light Orange',
    '#E8F5E9': 'Light Green'
  };
  return names[color] || 'Custom';
};
</script>

<style scoped lang="scss">
.drawing-toolbar {
  position: fixed;
  bottom: 30px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 250;
  transition: all 0.3s ease;
  pointer-events: all;
  
  &.active {
    .toolbar-card {
      border-color: #ff5722;
      box-shadow: 0 6px 24px rgba(255, 87, 34, 0.4);
    }
  }
}

.toolbar-card {
  background: rgba(255, 255, 255, 0.98);
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 20px rgba(0,0,0,0.25);
  border-radius: 16px;
  border: 2px solid rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.toggle-btn {
  font-weight: 600;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.15);
  
  &:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.25);
  }
}

.color-section {
  display: flex;
  align-items: center;
  gap: 4px;
}

.quick-colors {
  display: flex;
  gap: 4px;
}

.color-btn {
  transition: all 0.2s ease;
  
  &:hover {
    transform: scale(1.15) !important;
  }
}

.pen-size-control {
  min-width: 120px;
}

.transparent-preview {
  width: 24px;
  height: 24px;
  background: 
    linear-gradient(45deg, #ccc 25%, transparent 25%),
    linear-gradient(-45deg, #ccc 25%, transparent 25%),
    linear-gradient(45deg, transparent 75%, #ccc 75%),
    linear-gradient(-45deg, transparent 75%, #ccc 75%);
  background-size: 8px 8px;
  background-position: 0 0, 0 4px, 4px -4px, -4px 0px;
  border: 1px solid #999;
  border-radius: 4px;
}

.color-preview {
  width: 24px;
  height: 24px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.background-section {
  display: flex;
  align-items: center;
}

.page-navigation {
  display: flex;
  align-items: center;
  gap: 4px;
  
  .page-indicator {
    font-size: 12px;
    font-weight: 600;
    color: #666;
    min-width: 50px;
    text-align: center;
  }
}
</style>
