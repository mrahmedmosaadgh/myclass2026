<template>
  <WeeklySystemLayout>
    <div class="q-gutter-y-md">
      <!-- Header Section -->
      <div class="row items-center justify-between">
        <div>
          <h1 class="text-h5 text-weight-bold q-ma-none text-primary">My Lesson Plans</h1>
          <p class="text-caption text-grey-7 q-ma-none">Manage your lesson plans for assigned curricula.</p>
        </div>
        <q-btn 
          color="primary" 
          icon="add" 
          label="New Plan" 
          unelevated 
          :disabled="!selectedCurriculum || isLocked(selectedCurriculum)"
          @click="openCreateDialog" 
          class="rounded-borders"
        >
          <q-tooltip v-if="!selectedCurriculum">Select a book first</q-tooltip>
          <q-tooltip v-else-if="isLocked(selectedCurriculum)">Editing is closed for this book</q-tooltip>
        </q-btn>
      </div>

      <!-- Selection Section -->
      <q-card flat bordered class="rounded-borders">
        <q-card-section class="row q-col-gutter-md items-center">
          <div class="col-12 col-md-4">
            <q-select
              v-model="selectedCurriculum"
              :options="filteredCurricula"
              option-label="name"
              label="Select Book / Curriculum"
              outlined
              dense
              @update:model-value="fetchPlans"
              :loading="loadingCurricula"
            >
              <template v-slot:no-option>
                <q-item>
                  <q-item-section class="text-grey">No matching curricula found for your assigned subjects.</q-item-section>
                </q-item>
              </template>
            </q-select>
          </div>
          
          <div class="col-12 col-md-8 row q-gutter-sm" v-if="selectedCurriculum">
            <q-chip outline color="blue" text-color="blue" icon="school">{{ selectedCurriculum.grade_name }}</q-chip>
            <q-chip outline color="green" text-color="green" icon="book">{{ selectedCurriculum.subject_name }}</q-chip>
            <q-chip 
              v-if="selectedCurriculum.edit_lock_date" 
              outline 
              :color="isPassed(selectedCurriculum.edit_lock_date) ? 'negative' : 'warning'" 
              icon="lock_clock"
            >
              Lock Date: {{ selectedCurriculum.edit_lock_date }}
            </q-chip>
          </div>
        </q-card-section>
      </q-card>

      <!-- Plans Table -->
      <q-table
        v-if="selectedCurriculum"
        :rows="plans"
        :columns="columns"
        row-key="id"
        flat
        bordered
        :loading="loadingPlans"
        class="rounded-borders"
      >
        <template v-slot:body-cell-status="props">
          <q-td :props="props">
            <q-chip 
              size="sm" 
              :color="props.value === 1 ? 'green-1' : 'orange-1'" 
              :text-color="props.value === 1 ? 'green-9' : 'orange-9'"
            >
              {{ props.value === 1 ? 'Final' : 'Draft' }}
            </q-chip>
          </q-td>
        </template>

        <template v-slot:body-cell-actions="props">
          <q-td :props="props" class="text-right">
            <q-btn-group flat>
              <q-btn 
                flat round color="primary" 
                :icon="canEdit(props.row) ? 'edit' : 'visibility'" 
                @click="editPlan(props.row)"
              />
              <q-btn 
                v-if="canEdit(props.row)" 
                flat round color="negative" 
                icon="delete" 
                @click="confirmDelete(props.row)" 
              />
            </q-btn-group>
          </q-td>
        </template>
      </q-table>
      
      <div v-else class="text-center q-pa-xl text-grey-6 border-dashed">
        <q-icon name="arrow_upward" size="lg" class="q-mb-md" />
        <div class="text-h6">Please select a book to view or manage your lesson plans.</div>
      </div>
    </div>

    <!-- Create/Edit Plan Dialog -->
    <q-dialog v-model="dialog.show" persistent maximized>
      <q-card class="column">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">{{ dialog.editMode ? (canEdit(currentPlan) ? 'Edit Plan' : 'View Plan') : 'Create New Plan' }}</div>
          <q-space />
          
          <!-- AI Generate Button -->
          <q-btn 
            v-if="!dialog.editMode || canEdit(currentPlan)"
            flat 
            dense 
            label="Generate with AI" 
            icon="auto_awesome" 
            @click="openAIGenerator"
            class="q-mr-md"
          >
            <q-tooltip>Use AI to generate lesson content</q-tooltip>
          </q-btn>

          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="col scroll q-pa-lg">
          <div class="row q-col-gutter-lg">
            <div class="col-12 col-md-8">
              <div class="row q-col-gutter-md q-mb-md">
                <div class="col-12 col-md-4">
                  <q-select
                    v-model="form.curriculum_lesson_id"
                    :options="curriculumLessons"
                    label="Link to Lesson (ToC)"
                    option-label="lesson_title"
                    option-value="id"
                    emit-value
                    map-options
                    outlined
                    dense
                    @update:model-value="onLessonSelect"
                    :readonly="!canEdit(currentPlan) && dialog.editMode"
                  >
                    <template v-slot:option="scope">
                      <q-item v-bind="scope.itemProps">
                        <q-item-section>
                          <q-item-label>{{ scope.opt.lesson_number }}. {{ scope.opt.lesson_title }}</q-item-label>
                          <q-item-label caption>Page {{ scope.opt.page_number }}</q-item-label>
                        </q-item-section>
                      </q-item>
                    </template>
                  </q-select>
                </div>
                <div class="col-12 col-md-8">
                  <q-input 
                    v-model="form.title" 
                    label="Plan Title" 
                    outlined 
                    dense
                    :readonly="!canEdit(currentPlan) && dialog.editMode"
                  />
                </div>
              </div>
              <q-editor 
                v-model="form.cw" 
                min-height="400px" 
                :readonly="!canEdit(currentPlan) && dialog.editMode"
                placeholder="Write your lesson content here..."
              />
            </div>
            
            <div class="col-12 col-md-4 q-gutter-md">
              <q-input 
                v-model="form.planned_date" 
                type="date" 
                label="Planned Date" 
                outlined 
                stack-label
                :readonly="!canEdit(currentPlan) && dialog.editMode"
              />
              <q-input 
                v-model="form.objectives" 
                label="Objectives" 
                type="textarea" 
                outlined 
                :readonly="!canEdit(currentPlan) && dialog.editMode"
              />
              <q-input 
                v-model="form.hw" 
                label="Homework" 
                type="textarea" 
                outlined 
                :readonly="!canEdit(currentPlan) && dialog.editMode"
              />
              <q-select
                v-model="form.status"
                :options="[{label: 'Draft', value: 0}, {label: 'Final', value: 1}]"
                label="Status"
                outlined
                emit-value
                map-options
                :readonly="!canEdit(currentPlan) && dialog.editMode"
              />
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="bg-grey-2 q-pa-md" v-if="!dialog.editMode || canEdit(currentPlan)">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn unelevated label="Save Plan" color="primary" @click="savePlan" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirm -->
    <q-dialog v-model="deleteDialog.show">
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm text-bold">Delete Lesson Plan?</span>
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn unelevated label="Delete" color="negative" @click="deletePlan" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- AI Lesson Plan Generator -->
    <AILessonPlanGenerator
      ref="aiLessonGenerator"
      :lesson-config="lessonConfigForAI"
      @plan-accepted="handleAIPlanAccepted"
    />
  </WeeklySystemLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import WeeklySystemLayout from '@/Pages/myclass2026/Shared/WeeklySystemLayout.vue'
import AILessonPlanGenerator from '@/Components/Common/ai/AILessonPlanGenerator.vue'

const $q = useQuasar()
const loadingCurricula = ref(false)
const loadingPlans = ref(false)
const saving = ref(false)

const allCurricula = ref([])
const myAssignments = ref([])
const plans = ref([])
const curriculumLessons = ref([])
const selectedCurriculum = ref(null)
const aiLessonGenerator = ref(null)

const columns = [
  { name: 'title', align: 'left', label: 'Lesson Title', field: 'title', sortable: true },
  { name: 'planned_date', align: 'left', label: 'Date', field: 'planned_date', sortable: true },
  { name: 'status', align: 'center', label: 'Status', field: 'status', sortable: true },
  { name: 'actions', align: 'right', label: 'Actions' }
]

const dialog = ref({ show: false, editMode: false, id: null })
const currentPlan = ref(null)
const deleteDialog = ref({ show: false, id: null })
const form = ref({
  curriculum_id: null,
  subject_id: null,
  grade_id: null,
  title: '',
  cw: '',
  objectives: '',
  hw: '',
  planned_date: '',
  status: 0,
  curriculum_lesson_id: null
})

const sections = [
  { id: 'objectives', title: 'Learning Objectives', icon: '🎯' },
  { id: 'learn', title: 'Content & Explanation', icon: '📚' },
  { id: 'practice', title: 'Guided Practice', icon: '✏️' },
  { id: 'assessment', title: 'Assessment & Quiz', icon: '📝' }
]

const lessonConfigForAI = computed(() => ({
  lessonTitle: form.value.title || 'New Lesson',
  subject: selectedCurriculum.value?.subject_name || 'General',
  grade: selectedCurriculum.value?.grade_name || 'General',
  sections: sections
}))

const filteredCurricula = computed(() => {
  // Filter curricula that match teacher's assigned subjects and grades
  return allCurricula.value.filter(curr => {
    return myAssignments.value.some(assign => 
      assign.subject_id === curr.subject_id && assign.grade_id === curr.grade_id
    )
  })
})

const fetchData = async () => {
  loadingCurricula.value = true
  try {
    const [currRes, assignRes] = await Promise.all([
      axios.get(route('weekly-system.api.curriculum.index')),
      axios.get('/api/classroom-subject-teachers/my-assignments')
    ])
    allCurricula.value = currRes.data
    myAssignments.value = assignRes.data
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to load teacher context.' })
  } finally {
    loadingCurricula.value = false
  }
}

const fetchLessons = async () => {
  if (!selectedCurriculum.value) return
  try {
    const res = await axios.get(route('weekly-system.api.curriculum-lessons.index', selectedCurriculum.value.id))
    curriculumLessons.value = res.data
  } catch (error) {
    console.error('Failed to load ToC lessons')
  }
}

const fetchPlans = async () => {
  if (!selectedCurriculum.value) return
  loadingPlans.value = true
  try {
    const res = await axios.get(route('weekly-system.api.lesson-plans.index'), {
      params: { 
        curriculum_id: selectedCurriculum.value.id,
        teacher_only: true 
      }
    })
    plans.value = res.data
    fetchLessons()
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to load plans.' })
  } finally {
    loadingPlans.value = false
  }
}

const openCreateDialog = () => {
  if (isLocked(selectedCurriculum.value)) return
  dialog.value = { show: true, editMode: false, id: null }
  currentPlan.value = null
  form.value = {
    curriculum_id: selectedCurriculum.value.id,
    subject_id: selectedCurriculum.value.subject_id,
    grade_id: selectedCurriculum.value.grade_id,
    title: '',
    cw: '',
    objectives: '',
    hw: '',
    planned_date: new Date().toISOString().split('T')[0],
    status: 0,
    curriculum_lesson_id: null
  }
}

const editPlan = (row) => {
  dialog.value = { show: true, editMode: true, id: row.id }
  currentPlan.value = row
  form.value = { ...row }
}

const savePlan = async () => {
  if (!form.value.title) {
    $q.notify({ type: 'warning', message: 'Title is required.' })
    return
  }
  saving.value = true
  try {
    if (dialog.value.editMode) {
      await axios.put(route('weekly-system.api.lesson-plans.update', dialog.value.id), form.value)
      $q.notify({ type: 'positive', message: 'Plan updated.' })
    } else {
      await axios.post(route('weekly-system.api.lesson-plans.store'), form.value)
      $q.notify({ type: 'positive', message: 'Plan created.' })
    }
    dialog.value.show = false
    fetchPlans()
  } catch (error) {
    $q.notify({ type: 'negative', message: error.response?.data?.message || 'Failed to save.' })
  } finally {
    saving.value = false
  }
}

const confirmDelete = (row) => {
  deleteDialog.value = { show: true, id: row.id }
}

const deletePlan = async () => {
  saving.value = true
  try {
    await axios.delete(route('weekly-system.api.lesson-plans.destroy', deleteDialog.value.id))
    $q.notify({ type: 'positive', message: 'Plan deleted.' })
    deleteDialog.value.show = false
    fetchPlans()
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to delete.' })
  } finally {
    saving.value = false
  }
}

const isLocked = (curriculum) => {
  if (!curriculum) return false
  return isPassed(curriculum.edit_lock_date)
}

const canEdit = (plan) => {
  if (!plan) return true
  // Locked if status is final (1) OR curriculum date passed
  if (plan.status !== 0) return false
  return !isLocked(selectedCurriculum.value)
}

const isPassed = (date) => {
  if (!date) return false
  return new Date(date).setHours(0,0,0,0) <= new Date().setHours(0,0,0,0)
}

const openAIGenerator = () => {
  if (!form.value.title) {
    $q.notify({ type: 'warning', message: 'Please enter a lesson title first.' })
    return
  }
  aiLessonGenerator.value.open()
}

const onLessonSelect = (lessonId) => {
  const lesson = curriculumLessons.value.find(l => l.id === lessonId)
  if (lesson && !form.value.title) {
    form.value.title = lesson.lesson_title
  }
}

const handleAIPlanAccepted = (plan) => {
  // 1. Extract Objectives
  const objSection = plan.sections.find(s => s.sectionId === 'objectives')
  if (objSection) {
    form.value.objectives = objSection.slides
      .filter(s => s.slide_type === 'text')
      .map(s => s.slide_content.text.replace(/<[^>]*>/g, '').trim())
      .join('\n')
  }

  // 2. Extract Homework/Assessment
  const assessSection = plan.sections.find(s => s.sectionId === 'assessment')
  if (assessSection) {
    let hwText = ''
    assessSection.slides.forEach(slide => {
      if (slide.slide_type === 'text') {
        hwText += slide.slide_content.text + '\n'
      } else if (slide.slide_type === 'question') {
        slide.slide_content.questions.forEach((q, i) => {
          hwText += `Q: ${q.text}\n`
        })
      }
    })
    form.value.hw = hwText.trim()
  }

  // 3. Flatten everything else into 'cw' (Class Work)
  let fullContent = ''
  plan.sections.forEach(section => {
    if (section.sectionId === 'objectives') return // Already handled

    fullContent += `<div class="ai-section"><h3>${section.sectionId.toUpperCase()}</h3>`
    section.slides.forEach(slide => {
      if (slide.slide_type === 'text') {
        fullContent += `<div class="ai-slide-text">${slide.slide_content.text}</div>`
      } else if (slide.slide_type === 'question') {
        fullContent += `<div class="ai-slide-question" style="background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 10px 0;">`
        slide.slide_content.questions.forEach((q, qIdx) => {
          fullContent += `<p><strong>Q${qIdx + 1}:</strong> ${q.text}</p>`
          if (q.options) {
            fullContent += '<ul>'
            q.options.forEach(opt => {
              fullContent += `<li>${opt.text} ${opt.id === q.correct_answer ? '✅' : ''}</li>`
            })
            fullContent += '</ul>'
          }
          if (q.explanation) {
            fullContent += `<p><small><em>Explanation: ${q.explanation}</em></small></p>`
          }
        })
        fullContent += `</div>`
      }
    })
    fullContent += `</div><hr/>`
  })

  form.value.cw = fullContent
  $q.notify({ type: 'positive', message: 'AI Content generated and applied!' })
}

onMounted(fetchData)
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
.border-dashed {
  border: 2px dashed #ddd;
  border-radius: 20px;
}
</style>
