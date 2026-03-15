<script setup>
/**
 * SessionContextBar - Classroom Records Session Context Selector
 * 
 * Displays and manages session context (classroom, subject, date, period)
 * Supports two modes:
 * - interactive: Full dropdowns for selecting context (standalone mode)
 * - readonly: Display-only badges (when opened from schedule deep link)
 */

import { ref, computed, watch } from 'vue';
import { generatePeriodCode } from '@/utils/periodCode';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      classroom_id: null,
      subject_id: null,
      teacher_id: null,
      date: new Date().toISOString().split('T')[0],
      day_number: 1,
      period_number: 1,
      period_code: '',
    }),
  },
  mode: {
    type: String,
    default: 'interactive', // 'interactive' | 'readonly'
    validator: (value) => ['interactive', 'readonly'].includes(value),
  },
  source: {
    type: String,
    default: 'standalone', // 'standalone' | 'teacher_schedule'
    validator: (value) => ['standalone', 'teacher_schedule'].includes(value),
  },
  options: {
    type: Object,
    default: () => ({
      classrooms: [],
      subjects: [],
    }),
  },
  readOnly: {
    type: Boolean,
    default: false,
  },
  academicContext: {
    type: Object,
    default: () => ({
      year_id: 1,
      semester: 1,
    }),
  },
});

const emit = defineEmits(['update:modelValue', 'context-ready']);

// Local state
const localValue = ref({ ...props.modelValue });

// DEBUG: Log options on mount
import { onMounted } from 'vue';
onMounted(() => {
  console.log('🔍 SessionContextBar Options:', {
    options: props.options,
    mode: props.mode,
    readOnly: props.readOnly,
    isInteractive: props.mode === 'interactive' && !props.readOnly,
  });
});

// Computed properties
const isInteractive = computed(() => props.mode === 'interactive' && !props.readOnly);
const isReadonly = computed(() => props.mode === 'readonly' || props.readOnly);
const isStandalone = computed(() => props.source === 'standalone');

// Generate period code when all fields are filled
const updatePeriodCode = () => {
  const { date, day_number, period_number } = localValue.value;
  const { year_id, semester } = props.academicContext;
  
  console.log('🔍 updatePeriodCode check:', {
    has_year_id: !!year_id,
    has_semester: !!semester,
    has_date: !!date,
    has_day_number: !!day_number,
    has_period_number: !!period_number,
    has_classroom_id: !!localValue.value.classroom_id,
    has_subject_id: !!localValue.value.subject_id,
    has_teacher_id: !!localValue.value.teacher_id,
  });
  
  if (year_id && semester && date && day_number && period_number) {
    try {
      localValue.value.period_code = generatePeriodCode(
        year_id,
        semester,
        date,
        day_number,
        period_number
      );
      
      console.log('✅ Period code generated:', localValue.value.period_code);
      
      // Emit update
      emit('update:modelValue', localValue.value);
      
      // Check if context is complete
      if (localValue.value.classroom_id && 
          localValue.value.subject_id && 
          localValue.value.teacher_id &&
          localValue.value.period_code) {
        console.log('🚀 Context ready! Emitting context-ready event');
        emit('context-ready', localValue.value);
      } else {
        console.log('⚠️ Context NOT ready. Missing:', {
          classroom_id: localValue.value.classroom_id,
          subject_id: localValue.value.subject_id,
          teacher_id: localValue.value.teacher_id,
          period_code: localValue.value.period_code,
        });
      }
    } catch (error) {
      console.error('Error generating period code:', error);
    }
  }
};

// Watch for changes in interactive mode
watch(() => localValue.value, () => {
  if (isInteractive.value) {
    updatePeriodCode();
  }
}, { deep: true });

// Sync with parent
watch(() => props.modelValue, (newValue) => {
  localValue.value = { ...newValue };
}, { deep: true });

// Handle field changes
const onFieldChange = (field, value) => {
  localValue.value[field] = value;
  updatePeriodCode();
};
</script>

<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
        {{ isReadonly ? 'Session Context' : 'Select Session' }}
      </h3>
      <span v-if="isReadonly" class="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded">
        {{ isStandalone ? 'Standalone' : 'From Schedule' }}
      </span>
    </div>

    <!-- Interactive Mode: Show Dropdowns -->
    <div v-if="isInteractive" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Classroom Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Classroom
        </label>
        <select
          v-model="localValue.classroom_id"
          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="onFieldChange('classroom_id', $event.target.value)"
        >
          <option value="">Select Classroom</option>
          <option
            v-for="classroom in options.classrooms"
            :key="classroom.id"
            :value="classroom.id"
          >
            {{ classroom.name }}
          </option>
        </select>
      </div>

      <!-- Subject Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Subject
        </label>
        <select
          v-model="localValue.subject_id"
          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="onFieldChange('subject_id', $event.target.value)"
        >
          <option value="">Select Subject</option>
          <option
            v-for="subject in options.subjects"
            :key="subject.id"
            :value="subject.id"
          >
            {{ subject.name }}
          </option>
        </select>
      </div>

      <!-- Date Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Date
        </label>
        <input
          type="date"
          v-model="localValue.date"
          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="onFieldChange('date', $event.target.value)"
        />
      </div>

      <!-- Period Number -->
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Period
        </label>
        <select
          v-model="localValue.period_number"
          class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          @change="onFieldChange('period_number', $event.target.value)"
        >
          <option value="">Select Period</option>
          <option v-for="n in 8" :key="n" :value="n">
            Period {{ n }}
          </option>
        </select>
      </div>
    </div>

    <!-- Readonly Mode: Show Badges -->
    <div v-else class="flex flex-wrap gap-2">
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
        <span class="font-medium">Classroom:</span>
        <span class="ml-1">{{ props.modelValue.classroom_name || 'N/A' }}</span>
      </span>
      
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
        <span class="font-medium">Subject:</span>
        <span class="ml-1">{{ props.modelValue.subject_name || 'N/A' }}</span>
      </span>
      
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
        <span class="font-medium">Date:</span>
        <span class="ml-1">{{ props.modelValue.date }}</span>
      </span>
      
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200">
        <span class="font-medium">Period:</span>
        <span class="ml-1">{{ props.modelValue.period_number }}</span>
      </span>
      
      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-mono">
        <span class="font-medium">Code:</span>
        <span class="ml-1">{{ props.modelValue.period_code }}</span>
      </span>
    </div>

    <!-- Status Indicator -->
    <div v-if="isInteractive" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <span class="text-sm text-gray-600 dark:text-gray-400">
          Period Code:
        </span>
        <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono">
          {{ localValue.period_code || 'Waiting for all fields...' }}
        </code>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles if needed */
</style>
