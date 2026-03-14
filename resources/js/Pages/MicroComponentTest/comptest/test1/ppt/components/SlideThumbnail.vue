<template>
  <div 
    class="slide-thumbnail mb-3 cursor-pointer group"
    :class="{ 'ring-2 ring-blue-500': isActive }"
    @click="$emit('select')"
  >
    <div 
      class="relative bg-white border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow"
      :class="{ 'border-blue-500': isActive }"
    >
      <!-- Slide preview -->
      <div 
        class="w-full h-32 bg-cover bg-center relative"
        :style="{ backgroundColor: slide.backgroundColor }"
      >
        <!-- Elements preview -->
        <div 
          v-for="element in slide.elements.slice(0, 5)" 
          :key="element.id"
          class="absolute text-xs truncate"
          :style="{
            left: `${Math.min(80, element.x / 10)}%`,
            top: `${Math.min(80, element.y / 8)}%`,
            width: `${Math.min(20, element.width / 10)}%`,
            height: `${Math.min(20, element.height / 8)}%`
          }"
        >
          <div 
            v-if="element.type === 'text'"
            class="bg-white bg-opacity-70 p-1 rounded"
            :style="{ fontSize: `${Math.min(10, element.fontSize / 3)}px` }"
          >
            {{ element.content.substring(0, 10) }}{{ element.content.length > 10 ? '...' : '' }}
          </div>
          <div 
            v-else-if="element.type === 'shape'"
            class="rounded"
            :class="{
              'rounded-full': element.shapeType === 'circle',
              'bg-blue-200': element.shapeType === 'rectangle',
              'bg-red-200': element.shapeType === 'circle',
              'bg-yellow-200': element.shapeType === 'arrow'
            }"
          ></div>
          <div 
            v-else-if="element.type === 'image'"
            class="bg-gray-200 border border-dashed flex items-center justify-center"
          >
            🖼️
          </div>
        </div>
        
        <!-- Element count indicator -->
        <div 
          v-if="slide.elements.length > 5"
          class="absolute bottom-1 right-1 bg-black bg-opacity-70 text-white text-xs px-1 rounded"
        >
          +{{ slide.elements.length - 5 }}
        </div>
      </div>
      
      <!-- Slide number and actions -->
      <div class="p-2 bg-gray-50 flex justify-between items-center">
        <span class="text-sm font-medium text-gray-700">Slide {{ index + 1 }}</span>
        <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
          <button 
            @click.stop="$emit('duplicate')"
            class="p-1 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded"
            title="Duplicate slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
          <button 
            @click.stop="$emit('delete')"
            class="p-1 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded"
            title="Delete slide"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  slide: {
    type: Object,
    required: true
  },
  index: {
    type: Number,
    required: true
  },
  isActive: {
    type: Boolean,
    default: false
  }
});

defineEmits(['select', 'duplicate', 'delete']);
</script>