<template>
  <div class="question-type-selector bg-white rounded-lg border-2 border-gray-200 p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Question Type</h3>
    
    <!-- Category Tabs -->
    <div class="flex gap-2 mb-4 border-b border-gray-200">
      <button
        v-for="category in categories"
        :key="category.id"
        @click="selectedCategory = category.id"
        :class="[
          'px-4 py-2 font-medium transition-colors border-b-2',
          selectedCategory === category.id
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-600 hover:text-gray-900'
        ]"
      >
        {{ category.label }}
      </button>
    </div>

    <!-- Question Type Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
      <button
        v-for="type in filteredTypes"
        :key="type.value"
        @click="selectType(type)"
        class="p-4 border-2 border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-all text-left group"
      >
        <div class="flex items-start gap-3">
          <span class="text-2xl">{{ type.icon }}</span>
          <div class="flex-1 min-w-0">
            <div class="font-semibold text-gray-900 group-hover:text-blue-600 text-sm">
              {{ type.label }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ type.description }}</div>
            <div v-if="type.versions" class="text-xs text-blue-600 mt-1">
              {{ type.versions }} versions
            </div>
          </div>
        </div>
      </button>
    </div>

    <!-- Cancel Button -->
    <div class="mt-4 pt-4 border-t border-gray-200">
      <button
        @click="$emit('cancel')"
        class="px-4 py-2 text-gray-600 hover:text-gray-900 font-medium"
      >
        Cancel
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

interface QuestionType {
  value: string;
  label: string;
  description: string;
  icon: string;
  category: 'classic' | 'interactive' | 'advanced';
  versions?: number;
  isNew?: boolean;
}

const emit = defineEmits<{
  (e: 'select', type: QuestionType): void;
  (e: 'cancel'): void;
}>();

const selectedCategory = ref<'classic' | 'interactive' | 'advanced'>('classic');

const categories = [
  { id: 'classic' as const, label: 'Classic' },
  { id: 'interactive' as const, label: 'Interactive' },
  { id: 'advanced' as const, label: 'Advanced' },
];

const questionTypes: QuestionType[] = [
  // Classic Types (existing)
  {
    value: 'single_choice',
    label: 'Single Choice',
    description: 'One correct answer',
    icon: '⭕',
    category: 'classic',
  },
  {
    value: 'multiple_choice',
    label: 'Multiple Choice',
    description: 'Multiple correct answers',
    icon: '☑️',
    category: 'classic',
  },
  {
    value: 'true_false',
    label: 'True / False',
    description: 'Binary choice',
    icon: '✓✗',
    category: 'classic',
  },
  {
    value: 'short_answer',
    label: 'Short Answer',
    description: 'Text input',
    icon: '✍️',
    category: 'classic',
  },
  {
    value: 'number',
    label: 'Number',
    description: 'Numeric answer',
    icon: '🔢',
    category: 'classic',
  },
  
  // Interactive Types (new system)
  {
    value: 'labelled-diagram',
    label: 'Labelled Diagram',
    description: 'Label parts of an image',
    icon: '🏷️',
    category: 'interactive',
    versions: 3,
    isNew: true,
  },
  {
    value: 'match-up',
    label: 'Match Up',
    description: 'Connect pairs',
    icon: '🔗',
    category: 'interactive',
    versions: 3,
    isNew: true,
  },
  {
    value: 'image-quiz',
    label: 'Image Quiz',
    description: 'Select from images',
    icon: '🖼️',
    category: 'interactive',
    versions: 2,
    isNew: true,
  },
  {
    value: 'group-sort',
    label: 'Group Sort',
    description: 'Categorize items',
    icon: '📊',
    category: 'interactive',
    versions: 2,
    isNew: true,
  },
  {
    value: 'sequence',
    label: 'Sequence',
    description: 'Order items correctly',
    icon: '🔢',
    category: 'interactive',
    versions: 2,
    isNew: true,
  },
  
  // Advanced Types
  {
    value: 'speaking-cards',
    label: 'Speaking Cards',
    description: 'Audio recording practice',
    icon: '🎤',
    category: 'advanced',
    versions: 2,
    isNew: true,
  },
  {
    value: 'missing-word',
    label: 'Fill in the Blanks',
    description: 'Complete sentences',
    icon: '📝',
    category: 'advanced',
    versions: 2,
    isNew: true,
  },
  {
    value: 'anagram',
    label: 'Anagram',
    description: 'Unscramble words',
    icon: '🔤',
    category: 'advanced',
    versions: 2,
    isNew: true,
  },
  {
    value: 'step_by_step',
    label: 'Step-by-Step',
    description: 'Sequential instructions',
    icon: '📋',
    category: 'advanced',
  },
];

const filteredTypes = computed(() => {
  return questionTypes.filter(type => type.category === selectedCategory.value);
});

function selectType(type: QuestionType) {
  emit('select', type);
}
</script>

<style scoped>
button:focus {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}
</style>
