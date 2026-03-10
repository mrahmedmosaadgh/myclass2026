<template>
  <div class="enhanced-question-editor border rounded-lg p-4 bg-white shadow-sm">
    <!-- Question Type Header -->
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-200">
      <div class="flex items-center gap-3">
        <span class="text-2xl">{{ questionTypeIcon }}</span>
        <div>
          <h4 class="font-semibold text-gray-900">{{ questionTypeLabel }}</h4>
          <p class="text-xs text-gray-500">{{ questionTypeDescription }}</p>
        </div>
      </div>
      <button
        @click="changeQuestionType"
        class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
      >
        Change Type
      </button>
    </div>

    <!-- Classic Question Types (existing system) -->
    <div v-if="isClassicType">
      <QuestionEditor
        :modelValue="modelValue"
        @update:modelValue="$emit('update:modelValue', $event)"
        @delete="$emit('delete')"
        :uniqueId="uniqueId"
      />
    </div>

    <!-- New Interactive Question Types -->
    <div v-else class="space-y-4">
      <p class="text-sm text-gray-600 bg-blue-50 p-3 rounded-lg border border-blue-200">
        🎉 This is a new interactive question type! Configure it using the preset JSON format or use the QuestionRenderer component for preview.
      </p>
      
      <div class="p-4 bg-gray-50 rounded-lg">
        <p class="text-sm text-gray-700 mb-2">
          <strong>Note:</strong> Full editor UI for this question type is coming soon. For now, you can:
        </p>
        <ul class="text-sm text-gray-600 list-disc list-inside space-y-1">
          <li>Use the JSON presets from the question system</li>
          <li>Edit the question data directly in your backend</li>
          <li>Preview the question using the student view</li>
        </ul>
      </div>
    </div>

    <!-- Footer Actions -->
    <div class="flex justify-between items-center pt-4 border-t border-gray-200 mt-4">
      <label class="flex items-center text-sm text-gray-600">
        <input
          type="checkbox"
          :checked="modelValue.active !== false"
          @change="update('active', ($event.target as HTMLInputElement).checked)"
          class="mr-2 rounded text-blue-600"
        />
        Active for Students
      </label>
      <button
        @click="$emit('delete')"
        class="px-4 py-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium"
      >
        Delete Question
      </button>
    </div>

    <!-- Type Selector Modal -->
    <div v-if="showTypeSelector" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <QuestionTypeSelector
            @select="handleTypeChange"
            @cancel="showTypeSelector = false"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import QuestionEditor from './QuestionEditor.vue';
import QuestionTypeSelector from './QuestionTypeSelector.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    required: true
  },
  uniqueId: {
    type: String,
    default: () => Math.random().toString(36).substr(2, 9)
  }
});

const emit = defineEmits(['update:modelValue', 'delete']);

const showTypeSelector = ref(false);

// Question type metadata
const questionTypeMetadata: Record<string, any> = {
  // Classic types
  'single_choice': { icon: '⭕', label: 'Single Choice', description: 'One correct answer', category: 'classic' },
  'multiple_choice': { icon: '☑️', label: 'Multiple Choice', description: 'Multiple correct answers', category: 'classic' },
  'true_false': { icon: '✓✗', label: 'True / False', description: 'Binary choice', category: 'classic' },
  'short_answer': { icon: '✍️', label: 'Short Answer', description: 'Text input', category: 'classic' },
  'number': { icon: '🔢', label: 'Number', description: 'Numeric answer', category: 'classic' },
  'step_by_step': { icon: '📋', label: 'Step-by-Step', description: 'Sequential instructions', category: 'classic' },
  
  // New interactive types
  'labelled-diagram': { icon: '🏷️', label: 'Labelled Diagram', description: 'Label parts of an image' },
  'match-up': { icon: '🔗', label: 'Match Up', description: 'Connect pairs' },
  'speaking-cards': { icon: '🎤', label: 'Speaking Cards', description: 'Audio recording practice' },
  'image-quiz': { icon: '🖼️', label: 'Image Quiz', description: 'Select from images' },
  'group-sort': { icon: '📊', label: 'Group Sort', description: 'Categorize items' },
  'sequence': { icon: '🔢', label: 'Sequence', description: 'Order items correctly' },
  'missing-word': { icon: '📝', label: 'Fill in the Blanks', description: 'Complete sentences' },
  'anagram': { icon: '🔤', label: 'Anagram', description: 'Unscramble words' },
};

const classicTypes = ['single_choice', 'multiple_choice', 'true_false', 'short_answer', 'number', 'step_by_step', 'long_answer'];

const isClassicType = computed(() => {
  return classicTypes.includes(props.modelValue.type);
});

const currentMetadata = computed(() => {
  return questionTypeMetadata[props.modelValue.type] || { icon: '❓', label: 'Unknown', description: '' };
});

const questionTypeIcon = computed(() => currentMetadata.value.icon);
const questionTypeLabel = computed(() => currentMetadata.value.label);
const questionTypeDescription = computed(() => currentMetadata.value.description);

function update(field: string, value: any) {
  emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function changeQuestionType() {
  showTypeSelector.value = true;
}

function handleTypeChange(type: any) {
  // Create new question with selected type
  const newQuestion: any = {
    id: props.uniqueId,
    type: type.value,
    active: true,
  };

  // Add type-specific defaults
  if (type.category === 'classic') {
    newQuestion.text = '';
    newQuestion.timer = 0;
    if (type.value === 'single_choice' || type.value === 'multiple_choice') {
      newQuestion.options = [
        { id: 'opt_1', text: 'Option 1' },
        { id: 'opt_2', text: 'Option 2' }
      ];
      newQuestion.correct_answer = type.value === 'single_choice' ? null : [];
    }
  } else {
    // New interactive types
    newQuestion.title = '';
    newQuestion.description = '';
    newQuestion.points = 10;
    newQuestion.timeLimit = 0;
    newQuestion.version = 'default';
  }

  emit('update:modelValue', newQuestion);
  showTypeSelector.value = false;
}
</script>

<style scoped>
/* Modal animations */
.fixed {
  animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>
