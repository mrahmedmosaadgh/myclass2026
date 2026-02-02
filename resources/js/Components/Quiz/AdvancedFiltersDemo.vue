<template>
  <div class="advanced-filters-demo q-pa-md">
    <h4>Advanced Filters Demo</h4>
    
    <div class="row q-col-gutter-lg">
      <!-- Left Panel: Filters -->
      <div class="col-4">
        <q-card class="rounded-xl shadow-2">
          <q-card-section class="bg-blue-1 text-primary">
            <div class="text-h6 text-weight-bold">Advanced Filters</div>
          </q-card-section>
          
          <q-card-section>
            <AdvancedFilters
              v-model="filters"
              :available-grades="sampleGrades"
              :available-subjects="sampleSubjects"
              :available-topics="sampleTopics"
              :authors="sampleAuthors"
              :question-types="sampleQuestionTypes"
              @filter-changed="onFilterChanged"
              @filters-cleared="onFiltersCleared"
            />
          </q-card-section>
        </q-card>
      </div>
      
      <!-- Right Panel: Filter Results -->
      <div class="col-8">
        <q-card class="rounded-xl shadow-2">
          <q-card-section class="bg-green-1 text-green-9">
            <div class="text-h6 text-weight-bold">Filter Results</div>
          </q-card-section>
          
          <q-card-section>
            <div class="q-mb-md">
              <strong>Current Filters:</strong>
              <pre class="bg-grey-1 q-pa-sm rounded-borders">{{ JSON.stringify(filters, null, 2) }}</pre>
            </div>
            
            <div class="q-mb-md">
              <strong>Filter Summary:</strong>
              <div v-if="filterSummary.length === 0" class="text-grey-6">No active filters</div>
              <div v-else>
                <q-chip
                  v-for="summary in filterSummary"
                  :key="summary"
                  color="primary"
                  text-color="white"
                  size="sm"
                  class="q-mr-xs q-mb-xs"
                >
                  {{ summary }}
                </q-chip>
              </div>
            </div>
            
            <div>
              <strong>Simulated Question Results:</strong>
              <div class="text-caption text-grey-6 q-mb-sm">
                (This would show filtered questions from the question pool)
              </div>
              <div class="bg-grey-1 q-pa-md rounded-borders">
                <div v-if="!hasActiveFilters" class="text-center text-grey-6">
                  <q-icon name="filter_list" size="48px" class="q-mb-sm" />
                  <div>Apply filters to see results</div>
                </div>
                <div v-else>
                  <div class="text-positive q-mb-sm">
                    <q-icon name="check_circle" class="q-mr-xs" />
                    Filters applied successfully!
                  </div>
                  <div class="text-caption">
                    In a real implementation, this would show the filtered questions from your question bank.
                  </div>
                </div>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import AdvancedFilters from './AdvancedFilters.vue'
import { useFilterStore } from '@/composables/useFilterStore'

// Sample data for demonstration
const sampleGrades = [
  { id: '1', name: 'Grade 1', level: 1 },
  { id: '2', name: 'Grade 2', level: 2 },
  { id: '3', name: 'Grade 3', level: 3 }
]

const sampleSubjects = [
  { id: 's1', name: 'Mathematics', gradeId: '1' },
  { id: 's2', name: 'Science', gradeId: '1' },
  { id: 's3', name: 'Mathematics', gradeId: '2' },
  { id: 's4', name: 'English', gradeId: '2' },
  { id: 's5', name: 'Mathematics', gradeId: '3' },
  { id: 's6', name: 'History', gradeId: '3' }
]

const sampleTopics = [
  { id: 't1', name: 'Addition', subjectId: 's1' },
  { id: 't2', name: 'Subtraction', subjectId: 's1' },
  { id: 't3', name: 'Plants', subjectId: 's2' },
  { id: 't4', name: 'Animals', subjectId: 's2' },
  { id: 't5', name: 'Multiplication', subjectId: 's3' },
  { id: 't6', name: 'Reading Comprehension', subjectId: 's4' }
]

const sampleAuthors = [
  { id: 'a1', name: 'John Doe', email: 'john@example.com' },
  { id: 'a2', name: 'Jane Smith', email: 'jane@example.com' },
  { id: 'a3', name: 'Bob Johnson', email: 'bob@example.com' }
]

const sampleQuestionTypes = [
  { id: 'qt1', name: 'Multiple Choice' },
  { id: 'qt2', name: 'True/False' },
  { id: 'qt3', name: 'Short Answer' }
]

// Filter state
const filters = ref({
  grade: undefined,
  subject: undefined,
  topic: undefined,
  bloomsLevel: undefined,
  author: undefined,
  usedInQuiz: 'all',
  searchTerm: '',
  questionType: undefined,
  difficulty: undefined
})

// Use the filter store
const filterStore = useFilterStore()

// Computed properties
const hasActiveFilters = computed(() => {
  return !!(
    filters.value.grade ||
    filters.value.subject ||
    filters.value.topic ||
    filters.value.bloomsLevel ||
    filters.value.author ||
    filters.value.searchTerm ||
    filters.value.questionType ||
    filters.value.difficulty ||
    (filters.value.usedInQuiz && filters.value.usedInQuiz !== 'all')
  )
})

const filterSummary = computed(() => {
  const active = []
  
  if (filters.value.grade) {
    const grade = sampleGrades.find(g => g.id === filters.value.grade)
    if (grade) active.push(`Grade: ${grade.name}`)
  }
  
  if (filters.value.subject) {
    const subject = sampleSubjects.find(s => s.id === filters.value.subject)
    if (subject) active.push(`Subject: ${subject.name}`)
  }
  
  if (filters.value.topic) {
    const topic = sampleTopics.find(t => t.id === filters.value.topic)
    if (topic) active.push(`Topic: ${topic.name}`)
  }
  
  if (filters.value.difficulty) {
    active.push(`Difficulty: ${filters.value.difficulty}`)
  }
  
  if (filters.value.bloomsLevel) {
    active.push(`Bloom's Level: ${filters.value.bloomsLevel}`)
  }
  
  if (filters.value.author) {
    const author = sampleAuthors.find(a => a.id === filters.value.author)
    if (author) active.push(`Author: ${author.name}`)
  }
  
  if (filters.value.questionType) {
    const type = sampleQuestionTypes.find(t => t.id === filters.value.questionType)
    if (type) active.push(`Type: ${type.name}`)
  }
  
  if (filters.value.usedInQuiz && filters.value.usedInQuiz !== 'all') {
    active.push(`Usage: ${filters.value.usedInQuiz}`)
  }
  
  if (filters.value.searchTerm) {
    active.push(`Search: "${filters.value.searchTerm}"`)
  }

  return active
})

// Event handlers
const onFilterChanged = (newFilters) => {
  console.log('Filters changed:', newFilters)
}

const onFiltersCleared = () => {
  console.log('Filters cleared')
}
</script>

<style scoped lang="scss">
.advanced-filters-demo {
  max-width: 1200px;
  margin: 0 auto;
}

pre {
  font-size: 12px;
  max-height: 200px;
  overflow-y: auto;
}
</style>