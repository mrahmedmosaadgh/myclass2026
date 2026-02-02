<template>
  <div class="advanced-filters">
    <!-- Search Input -->
    <q-input
      v-model="localFilters.searchTerm"
      outlined
      dense
      rounded
      placeholder="Search questions..."
      bg-color="grey-1"
      class="q-mb-md"
      @update:model-value="onFilterChange"
    >
      <template v-slot:prepend>
        <q-icon name="search" color="primary" />
      </template>
      <template v-slot:append v-if="localFilters.searchTerm">
        <q-icon 
          name="clear" 
          color="grey-6" 
          class="cursor-pointer" 
          @click="clearSearch"
        />
      </template>
    </q-input>

    <!-- Cascading Filters Row 1: Grade and Subject -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6">
        <q-select
          v-model="localFilters.grade"
          outlined
          dense
          rounded
          :options="availableGrades"
          option-label="name"
          option-value="id"
          label="Grade"
          bg-color="grey-1"
          behavior="menu"
          clearable
          @update:model-value="onGradeChange"
        >
          <template v-slot:prepend>
            <q-icon name="school" color="primary" />
          </template>
        </q-select>
      </div>
      <div class="col-6">
        <q-select
          v-model="localFilters.subject"
          outlined
          dense
          rounded
          :options="filteredSubjects"
          option-label="name"
          option-value="id"
          label="Subject"
          bg-color="grey-1"
          behavior="menu"
          clearable
          :disable="!localFilters.grade"
          @update:model-value="onSubjectChange"
        >
          <template v-slot:prepend>
            <q-icon name="subject" color="primary" />
          </template>
        </q-select>
      </div>
    </div>

    <!-- Cascading Filters Row 2: Topic and Question Type -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6">
        <q-select
          v-model="localFilters.topic"
          outlined
          dense
          rounded
          :options="filteredTopics"
          option-label="name"
          option-value="id"
          label="Topic"
          bg-color="grey-1"
          behavior="menu"
          clearable
          :disable="!localFilters.subject"
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="topic" color="primary" />
          </template>
        </q-select>
      </div>
      <div class="col-6">
        <q-select
          v-model="localFilters.questionType"
          outlined
          dense
          rounded
          :options="questionTypes"
          option-label="name"
          option-value="id"
          label="Question Type"
          bg-color="grey-1"
          behavior="menu"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="quiz" color="primary" />
          </template>
        </q-select>
      </div>
    </div>

    <!-- Filters Row 3: Difficulty and Bloom's Taxonomy -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6">
        <q-select
          v-model="localFilters.difficulty"
          outlined
          dense
          rounded
          :options="difficultyOptions"
          label="Difficulty"
          bg-color="grey-1"
          behavior="menu"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="trending_up" color="primary" />
          </template>
        </q-select>
      </div>
      <div class="col-6">
        <q-select
          v-model="localFilters.bloomsLevel"
          outlined
          dense
          rounded
          :options="bloomsOptions"
          option-label="label"
          option-value="value"
          label="Bloom's Level"
          bg-color="grey-1"
          behavior="menu"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="psychology" color="primary" />
          </template>
        </q-select>
      </div>
    </div>

    <!-- Filters Row 4: Author and Usage Status -->
    <div class="row q-col-gutter-sm q-mb-md">
      <div class="col-6">
        <q-select
          v-model="localFilters.author"
          outlined
          dense
          rounded
          :options="authors"
          option-label="name"
          option-value="id"
          label="Author"
          bg-color="grey-1"
          behavior="menu"
          clearable
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="person" color="primary" />
          </template>
        </q-select>
      </div>
      <div class="col-6">
        <q-select
          v-model="localFilters.usedInQuiz"
          outlined
          dense
          rounded
          :options="usageOptions"
          option-label="label"
          option-value="value"
          label="Usage Status"
          bg-color="grey-1"
          behavior="menu"
          @update:model-value="onFilterChange"
        >
          <template v-slot:prepend>
            <q-icon name="assignment" color="primary" />
          </template>
        </q-select>
      </div>
    </div>

    <!-- Filter Actions -->
    <div class="row q-gutter-sm">
      <q-btn
        flat
        rounded
        color="primary"
        icon="filter_list"
        :label="`${activeFilterCount} Active`"
        size="sm"
        :disable="activeFilterCount === 0"
        class="text-weight-medium"
      />
      <q-space />
      <q-btn
        flat
        rounded
        color="negative"
        icon="clear"
        label="Clear All"
        size="sm"
        :disable="activeFilterCount === 0"
        @click="clearAllFilters"
        class="text-weight-medium"
      />
    </div>

    <!-- Active Filters Summary -->
    <div v-if="activeFilterCount > 0" class="q-mt-md">
      <div class="text-caption text-grey-6 q-mb-xs">Active Filters:</div>
      <div class="row q-gutter-xs">
        <q-chip
          v-for="filter in activeFilterSummary"
          :key="filter"
          removable
          color="primary"
          text-color="white"
          size="sm"
          @remove="removeFilter(filter)"
        >
          {{ filter }}
        </q-chip>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useFilterStore } from '@/composables/useFilterStore'

// Props and Emits
const props = defineProps({
  availableGrades: {
    type: Array,
    default: () => []
  },
  availableSubjects: {
    type: Array,
    default: () => []
  },
  availableTopics: {
    type: Array,
    default: () => []
  },
  authors: {
    type: Array,
    default: () => []
  },
  questionTypes: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['update:modelValue', 'filter-changed', 'filters-cleared'])

// Composables
const filterStore = useFilterStore()

// Local state for v-model binding
const localFilters = ref({ ...props.modelValue })

// Static options
const difficultyOptions = ['Easy', 'Medium', 'Hard']

const bloomsOptions = [
  { label: '1 - Remember', value: '1' },
  { label: '2 - Understand', value: '2' },
  { label: '3 - Apply', value: '3' },
  { label: '4 - Analyze', value: '4' },
  { label: '5 - Evaluate', value: '5' },
  { label: '6 - Create', value: '6' }
]

const usageOptions = [
  { label: 'All Questions', value: 'all' },
  { label: 'Used in Quizzes', value: 'used' },
  { label: 'Unused Questions', value: 'unused' }
]

// Computed properties for cascading filters
const filteredSubjects = computed(() => {
  if (!localFilters.value.grade) return props.availableSubjects
  return props.availableSubjects.filter(subject => subject.gradeId === localFilters.value.grade)
})

const filteredTopics = computed(() => {
  if (!localFilters.value.subject) return props.availableTopics
  return props.availableTopics.filter(topic => topic.subjectId === localFilters.value.subject)
})

// Active filter tracking
const activeFilterCount = computed(() => {
  let count = 0
  if (localFilters.value.grade) count++
  if (localFilters.value.subject) count++
  if (localFilters.value.topic) count++
  if (localFilters.value.difficulty) count++
  if (localFilters.value.bloomsLevel) count++
  if (localFilters.value.author) count++
  if (localFilters.value.questionType) count++
  if (localFilters.value.searchTerm) count++
  if (localFilters.value.usedInQuiz && localFilters.value.usedInQuiz !== 'all') count++
  return count
})

const activeFilterSummary = computed(() => {
  const active = []
  
  if (localFilters.value.grade) {
    const grade = props.availableGrades.find(g => g.id === localFilters.value.grade)
    if (grade) active.push(`Grade: ${grade.name}`)
  }
  
  if (localFilters.value.subject) {
    const subject = props.availableSubjects.find(s => s.id === localFilters.value.subject)
    if (subject) active.push(`Subject: ${subject.name}`)
  }
  
  if (localFilters.value.topic) {
    const topic = props.availableTopics.find(t => t.id === localFilters.value.topic)
    if (topic) active.push(`Topic: ${topic.name}`)
  }
  
  if (localFilters.value.difficulty) {
    active.push(`Difficulty: ${localFilters.value.difficulty}`)
  }
  
  if (localFilters.value.bloomsLevel) {
    const blooms = bloomsOptions.find(b => b.value === localFilters.value.bloomsLevel)
    if (blooms) active.push(`Bloom's: ${blooms.label}`)
  }
  
  if (localFilters.value.author) {
    const author = props.authors.find(a => a.id === localFilters.value.author)
    if (author) active.push(`Author: ${author.name}`)
  }
  
  if (localFilters.value.questionType) {
    const type = props.questionTypes.find(t => t.id === localFilters.value.questionType)
    if (type) active.push(`Type: ${type.name}`)
  }
  
  if (localFilters.value.usedInQuiz && localFilters.value.usedInQuiz !== 'all') {
    const usage = usageOptions.find(u => u.value === localFilters.value.usedInQuiz)
    if (usage) active.push(`Usage: ${usage.label}`)
  }
  
  if (localFilters.value.searchTerm) {
    active.push(`Search: "${localFilters.value.searchTerm}"`)
  }

  return active
})

// Methods
const onFilterChange = () => {
  emit('update:modelValue', { ...localFilters.value })
  emit('filter-changed', { ...localFilters.value })
}

const onGradeChange = () => {
  // Clear dependent filters when grade changes
  localFilters.value.subject = undefined
  localFilters.value.topic = undefined
  onFilterChange()
}

const onSubjectChange = () => {
  // Clear dependent filters when subject changes
  localFilters.value.topic = undefined
  onFilterChange()
}

const clearSearch = () => {
  localFilters.value.searchTerm = ''
  onFilterChange()
}

const clearAllFilters = () => {
  localFilters.value = {
    grade: undefined,
    subject: undefined,
    topic: undefined,
    bloomsLevel: undefined,
    author: undefined,
    usedInQuiz: 'all',
    searchTerm: '',
    questionType: undefined,
    difficulty: undefined
  }
  emit('filters-cleared')
  onFilterChange()
}

const removeFilter = (filterText) => {
  // Parse the filter text to determine which filter to remove
  if (filterText.startsWith('Grade:')) {
    localFilters.value.grade = undefined
    localFilters.value.subject = undefined
    localFilters.value.topic = undefined
  } else if (filterText.startsWith('Subject:')) {
    localFilters.value.subject = undefined
    localFilters.value.topic = undefined
  } else if (filterText.startsWith('Topic:')) {
    localFilters.value.topic = undefined
  } else if (filterText.startsWith('Difficulty:')) {
    localFilters.value.difficulty = undefined
  } else if (filterText.startsWith('Bloom\'s:')) {
    localFilters.value.bloomsLevel = undefined
  } else if (filterText.startsWith('Author:')) {
    localFilters.value.author = undefined
  } else if (filterText.startsWith('Type:')) {
    localFilters.value.questionType = undefined
  } else if (filterText.startsWith('Usage:')) {
    localFilters.value.usedInQuiz = 'all'
  } else if (filterText.startsWith('Search:')) {
    localFilters.value.searchTerm = ''
  }
  
  onFilterChange()
}

// Watch for external changes to modelValue
watch(
  () => props.modelValue,
  (newValue) => {
    localFilters.value = { ...newValue }
  },
  { deep: true }
)

// Initialize filter store with available options
onMounted(() => {
  filterStore.setAvailableOptions({
    grades: props.availableGrades,
    subjects: props.availableSubjects,
    topics: props.availableTopics,
    authors: props.authors
  })
})
</script>

<style scoped lang="scss">
.advanced-filters {
  .q-field--dense .q-field__control {
    height: 40px;
  }
  
  .q-chip {
    font-size: 11px;
  }
}
</style>