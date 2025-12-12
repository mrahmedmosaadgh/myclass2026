<script setup>
import { ref, computed, watch } from 'vue';
import { toast } from 'vue3-toastify';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      width: 320,
      height: 420,
      colors: {
        start: 'rgba(249, 115, 22, 0.9)',
        end: 'rgb(194, 65, 12)'
      }
    })
  }
});

const emit = defineEmits(['update:modelValue', 'update:options']);

const options = ref(props.modelValue);

// Watch for changes and emit updates
watch(options, (newValue) => {
  emit('update:modelValue', newValue);
  emit('update:options', newValue);
}, { deep: true });

// Watch for prop changes
watch(() => props.modelValue, (newValue) => {
  if (JSON.stringify(newValue) !== JSON.stringify(options.value)) {
    options.value = newValue;
  }
}, { deep: true });

const previewStyle = computed(() => ({
  width: `${options.value.width}px`,
  height: `${options.value.height}px`,
  background: `linear-gradient(145deg, ${options.value.colors.start}, ${options.value.colors.end})`
}));

const copyToClipboard = async () => {
  try {
    const optionsString = JSON.stringify(options.value, null, 2);
    await navigator.clipboard.writeText(optionsString);
    toast.success('Options copied to clipboard!');
  } catch (err) {
    toast.error('Failed to copy options');
  }
};

const resetOptions = () => {
  options.value = {
    width: 320,
    height: 420,
    colors: {
      start: 'rgba(249, 115, 22, 0.9)',
      end: 'rgb(194, 65, 12)'
    }
  };
};
</script>

<template>
  <div class="card-options-editor bg-white rounded-lg shadow-lg p-6 max-w-2xl mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Preview Section -->
      <div class="preview-section">
        <h3 class="text-lg font-semibold mb-4">Preview</h3>
        <div
          class="preview-box rounded-lg shadow-lg transition-all duration-300"
          :style="previewStyle"
        ></div>
      </div>

      <!-- Controls Section -->
      <div class="controls-section space-y-6">
        <div>
          <h3 class="text-lg font-semibold mb-4">Dimensions</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Width: {{ options.width }}px</label>
              <input
                type="range"
                v-model="options.width"
                min="200"
                max="600"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Height: {{ options.height }}px</label>
              <input
                type="range"
                v-model="options.height"
                min="200"
                max="600"
                class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
              >
            </div>
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Colors</h3>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Start Color</label>
              <div class="flex items-center space-x-2">
                <input
                  type="color"
                  v-model="options.colors.start"
                  class="h-10 w-20"
                >
                <input
                  type="text"
                  v-model="options.colors.start"
                  class="flex-1 border rounded px-3 py-2 text-sm"
                >
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">End Color</label>
              <div class="flex items-center space-x-2">
                <input
                  type="color"
                  v-model="options.colors.end"
                  class="h-10 w-20"
                >
                <input
                  type="text"
                  v-model="options.colors.end"
                  class="flex-1 border rounded px-3 py-2 text-sm"
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex justify-end space-x-4">
      <button
        @click="resetOptions"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
      >
        Reset
      </button>
      <button
        @click="copyToClipboard"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" />
          <path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z" />
        </svg>
        <span>Copy Options</span>
      </button>
    </div>

    <!-- JSON Preview -->
    <div class="mt-6">
      <h3 class="text-lg font-semibold mb-2">Generated Options</h3>
      <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">{{ JSON.stringify(options, null, 2) }}</pre>
    </div>
  </div>
</template>

<style scoped>
.preview-box {
  transition: all 0.3s ease;
}

input[type="range"] {
  -webkit-appearance: none;
  height: 8px;
  background: #e5e7eb;
  border-radius: 5px;
  background-image: linear-gradient(#3b82f6, #3b82f6);
  background-repeat: no-repeat;
}

input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  height: 20px;
  width: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 2px 0 #555;
  transition: background .3s ease-in-out;
}

input[type="range"]::-webkit-slider-thumb:hover {
  background: #2563eb;
}

input[type="color"] {
  -webkit-appearance: none;
  border: none;
  padding: 0;
  border-radius: 4px;
  overflow: hidden;
}

input[type="color"]::-webkit-color-swatch-wrapper {
  padding: 0;
}

input[type="color"]::-webkit-color-swatch {
  border: none;
}
</style>
