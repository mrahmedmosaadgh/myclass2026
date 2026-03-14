<template>
  <WeeklySystemLayout>
    <div class="q-gutter-y-md">
      <!-- ToC Fullscreen Dialog -->
      <q-dialog v-model="tocDialog" persistent maximized transition-show="slide-up" transition-hide="slide-down">
        <q-card class="bg-grey-1">
          <q-card-section class="row items-center bg-primary text-white q-py-sm">
            <div class="text-h6" v-if="selectedCurriculum">{{ selectedCurriculum.name }} - Table of Contents</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
          </q-card-section>

          <q-card-section class="q-pa-lg">
            <div class="row q-mb-md q-gutter-sm justify-between">
              <div>
                <q-btn color="primary" icon="add" label="Add Lesson" @click="openLessonDialog" unelevated class="rounded-borders" />
                <q-btn outline color="primary" icon="auto_awesome" label="Parse with AI" @click="openAIParser" class="q-ml-sm rounded-borders" />
              </div>
              <div class="text-subtitle2 self-center">{{ lessons.length }} Lessons Total</div>
            </div>

            <q-table
              :rows="lessons"
              :columns="lessonColumns"
              row-key="id"
              flat
              bordered
              class="rounded-borders bg-white"
              :loading="loadingLessons"
              :pagination="{ rowsPerPage: 50 }"
            >
              <template v-slot:body-cell-actions="props">
                <q-td :props="props" class="text-right">
                  <q-btn flat round color="primary" icon="edit" size="sm" @click="editLesson(props.row)" />
                  <q-btn flat round color="negative" icon="delete" size="sm" @click="deleteLesson(props.row)" />
                </q-td>
              </template>
            </q-table>
          </q-card-section>
        </q-card>
      </q-dialog>

      <!-- AI Parser Component -->
      <AIToCParser 
        ref="aiParser" 
        :book-info="selectedCurriculum ? { name: selectedCurriculum.name, grade: selectedCurriculum.grade_name, subject: selectedCurriculum.subject_name } : {}"
        @lessons-parsed="handleBulkLessons"
      />

      <!-- Header Section -->
      <div class="row items-center justify-between">
        <div>
          <h1 class="text-h5 text-weight-bold q-ma-none text-primary">Curriculum & Lesson Plans</h1>
          <p class="text-caption text-grey-7 q-ma-none">Manage your school's curriculum books and track teacher progress.</p>
        </div>
        <q-btn 
          color="primary" 
          icon="add" 
          label="Create New Book" 
          unelevated 
          @click="openCreateDialog" 
          class="rounded-borders"
        />
      </div>

      <!-- Main Content Tabs -->
      <q-card flat bordered class="rounded-borders">
        <q-tabs
          v-model="activeTab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="left"
          narrow-indicator
        >
          <q-tab name="books" icon="library_books" label="Books & Locks" />
          <q-tab name="lessons" icon="list" label="Lessons (ToC)" />
          <q-tab name="tracking" icon="analytics" label="Submission Tracker" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="activeTab" animated>
          <!-- Books Management Panel -->
          <q-tab-panel name="books" class="q-pa-none">
            <q-table
              :rows="curricula"
              :columns="columns"
              row-key="id"
              flat
              :loading="loading"
              :pagination="{ rowsPerPage: 10 }"
            >
              <template v-slot:body-cell-grade_name="props">
                <q-td :props="props">
                  <q-chip size="sm" color="blue-1" text-color="blue-9">{{ props.value }}</q-chip>
                </q-td>
              </template>

              <template v-slot:body-cell-subject_name="props">
                <q-td :props="props">
                  <q-chip size="sm" color="green-1" text-color="green-9">{{ props.value }}</q-chip>
                </q-td>
              </template>

              <template v-slot:body-cell-edit_lock_date="props">
                <q-td :props="props" class="text-center">
                  <div v-if="props.value" class="row items-center justify-center">
                    <q-icon 
                      name="lock" 
                      :color="isPassed(props.value) ? 'negative' : 'warning'" 
                      size="xs" 
                      class="q-mr-xs" 
                    />
                    <span :class="isPassed(props.value) ? 'text-negative text-bold' : ''">
                      {{ props.value }}
                    </span>
                    <q-btn flat round color="primary" icon="edit_calendar" size="sm" @click="editLockDate(props.row)" />
                  </div>
                  <q-btn v-else outline label="Set Lock" color="primary" size="sm" icon="event" @click="editLockDate(props.row)" />
                </q-td>
              </template>

                <q-td :props="props" class="text-right">
                  <q-btn-group flat>
                    <q-btn flat round color="primary" icon="list" @click="manageLessons(props.row)">
                      <q-tooltip>Manage Table of Contents</q-tooltip>
                    </q-btn>
                    <q-btn flat round color="grey-7" icon="visibility" @click="viewTracking(props.row)" />
                    <q-btn flat round color="grey-7" icon="edit" @click="editCurriculum(props.row)" />
                    <q-btn flat round color="negative" icon="delete" @click="confirmDelete(props.row)" />
                  </q-btn-group>
                </q-td>
            </q-table>
          </q-tab-panel>

          <!-- Lessons (ToC) Panel -->
          <q-tab-panel name="lessons" class="q-pa-md">
            <div v-if="!selectedCurriculum" class="text-center q-pa-xl text-grey-7">
              <q-icon name="arrow_upward" size="lg" class="q-mb-md" />
              <div class="text-h6">Select a book from the "Books & Locks" tab to manage its lessons.</div>
            </div>
            <div v-else class="text-center q-pa-xl">
              <q-icon name="list_alt" size="xl" color="primary" class="q-mb-md" />
              <div class="text-h5 text-weight-bold">{{ selectedCurriculum.name }}</div>
              <div class="text-subtitle1 text-grey-7 q-mb-lg">Manage the table of contents for this curriculum.</div>
              
              <div class="row justify-center q-gutter-md">
                <q-btn 
                  color="primary" 
                  icon="open_in_full" 
                  label="Open Fullscreen Management" 
                  size="lg"
                  unelevated
                  class="rounded-borders"
                  @click="tocDialog = true"
                />
                <q-btn 
                  outline 
                  color="primary" 
                  icon="auto_awesome" 
                  label="Quick Parse with AI" 
                  size="lg"
                  class="rounded-borders"
                  @click="openAIParser"
                />
              </div>
              
              <div class="q-mt-xl text-grey-6">
                Current Count: {{ lessons.length }} lessons
              </div>
            </div>
          </q-tab-panel>

          <!-- Tracking Panel -->
          <q-tab-panel name="tracking">
            <div class="row q-col-gutter-md">
              <div class="col-12 col-md-4">
                <q-select
                  v-model="selectedCurriculum"
                  :options="curricula"
                  option-label="name"
                  label="Select Book to Track"
                  outlined
                  dense
                  @update:model-value="fetchProgress"
                />
              </div>
              
              <div class="col-12" v-if="selectedCurriculum">
                <div class="row q-col-gutter-lg">
                  <!-- Done -->
                  <div class="col-12 col-md-6">
                    <q-list bordered separator class="rounded-borders bg-green-1">
                      <q-item-label header class="text-green-9 text-weight-bold">
                        <q-icon name="check_circle" class="q-mr-sm" /> 
                        Submitted ({{ progress.submitted.length }})
                      </q-item-label>
                      <q-item v-for="teacher in progress.submitted" :key="teacher.id">
                        <q-item-section avatar>
                          <q-avatar color="green-2" text-color="green-9" icon="person" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{ teacher.name }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item v-if="progress.submitted.length === 0">
                        <q-item-section class="text-center text-grey-6 italic">No submissions yet.</q-item-section>
                      </q-item>
                    </q-list>
                  </div>
                  
                  <!-- Not Done -->
                  <div class="col-12 col-md-6">
                    <q-list bordered separator class="rounded-borders bg-red-1">
                      <q-item-label header class="text-red-9 text-weight-bold">
                        <q-icon name="error" class="q-mr-sm" /> 
                        Not Submitted ({{ progress.not_submitted.length }})
                      </q-item-label>
                      <q-item v-for="teacher in progress.not_submitted" :key="teacher.id">
                        <q-item-section avatar>
                          <q-avatar color="red-2" text-color="red-9" icon="person_off" />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{ teacher.name }}</q-item-label>
                        </q-item-section>
                      </q-item>
                      <q-item v-if="progress.not_submitted.length === 0">
                        <q-item-section class="text-center text-grey-6 italic">Everyone has submitted!</q-item-section>
                      </q-item>
                    </q-list>
                  </div>
                </div>
              </div>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </q-card>
    </div>

    <!-- Create/Edit Dialog -->
    <q-dialog v-model="dialog.show" persistent>
      <q-card style="min-width: 400px" class="rounded-borders">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">{{ dialog.editMode ? 'Edit Book' : 'Create New Book' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-gutter-md q-pt-md">
          <q-input 
            v-model="form.name" 
            label="Book Name (e.g. Science Year 5)" 
            outlined 
            dense 
            autofocus
          />
          <q-input 
            v-model="form.description" 
            label="Description" 
            type="textarea" 
            outlined 
            dense 
          />
          
          <div class="row q-col-gutter-sm">
            <div class="col-6">
              <q-select
                v-model="form.grade_id"
                :options="grades"
                option-label="name"
                option-value="id"
                emit-value
                map-options
                label="Grade"
                outlined
                dense
              />
            </div>
            <div class="col-6">
              <q-select
                v-model="form.subject_id"
                :options="subjects"
                option-label="name"
                option-value="id"
                emit-value
                map-options
                label="Subject"
                outlined
                dense
              />
            </div>
          </div>
          
          <q-input 
            v-model="form.edit_lock_date" 
            type="date" 
            label="Editing Lock Date (Optional)" 
            outlined 
            dense 
            hint="Teachers cannot edit plans for this book after this date."
          />
        </q-card-section>

        <q-card-actions align="right" class="q-pb-md q-pr-md">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn 
            unelevated 
            :label="dialog.editMode ? 'Update' : 'Create'" 
            color="primary" 
            @click="saveCurriculum" 
            :loading="saving"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Lesson Edit Dialog -->
    <q-dialog v-model="lessonDialog.show" persistent>
      <q-card style="min-width: 350px">
        <q-card-section class="row items-center bg-primary text-white">
          <div class="text-h6">{{ lessonDialog.editMode ? 'Edit Lesson' : 'Add New Lesson' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-gutter-md">
          <q-input v-model="lessonForm.lesson_number" label="Lesson #" outlined dense placeholder="e.g. 1-1 or Lesson 1" />
          <q-input v-model="lessonForm.lesson_title" label="Lesson Title" outlined dense />
          <q-input v-model="lessonForm.page_number" type="number" label="Page Number" outlined dense />
          <q-select
            v-model="lessonForm.type"
            :options="['main', 'revision', 'quiz', 'project', 'extra']"
            label="Lesson Type"
            outlined
            dense
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn unelevated :label="lessonDialog.editMode ? 'Update' : 'Add'" color="primary" @click="saveLesson" :loading="savingLesson" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Delete Confirm -->
    <q-dialog v-model="deleteDialog.show">
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm text-bold">Delete Curriculum?</span>
        </q-card-section>
        <q-card-section>
          This will delete the book and all its associated lesson plans. This action cannot be undone.
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn unelevated label="Delete" color="negative" @click="deleteCurriculum" :loading="saving" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </WeeklySystemLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'
import WeeklySystemLayout from '@/Pages/myclass2026/Shared/WeeklySystemLayout.vue'
import AIToCParser from '@/Components/Common/ai/AIToCParser.vue'

const $q = useQuasar()
const activeTab = ref('books')
const loading = ref(false)
const saving = ref(false)
const curricula = ref([])
const grades = ref([])
const subjects = ref([])
const selectedCurriculum = ref(null)
const progress = ref({ submitted: [], not_submitted: [] })

const lessons = ref([])
const loadingLessons = ref(false)
const savingLesson = ref(false)
const tocDialog = ref(false)
const aiParser = ref(null)
const lessonDialog = ref({ show: false, editMode: false, id: null })
const lessonForm = ref({
  lesson_number: '',
  lesson_title: '',
  page_number: null,
  type: 'main'
})

const columns = [
  { name: 'name', align: 'left', label: 'Book Name', field: 'name', sortable: true },
  { name: 'grade_name', align: 'left', label: 'Grade', field: 'grade_name', sortable: true },
  { name: 'subject_name', align: 'left', label: 'Subject', field: 'subject_name', sortable: true },
  { name: 'edit_lock_date', align: 'center', label: 'Lock Date', field: 'edit_lock_date', sortable: true },
  { name: 'actions', align: 'right', label: 'Actions' }
]

const lessonColumns = [
  { name: 'lesson_number', align: 'left', label: '#', field: 'lesson_number', sortable: true },
  { name: 'lesson_title', align: 'left', label: 'Lesson Title', field: 'lesson_title', sortable: true },
  { name: 'page_number', align: 'center', label: 'Page', field: 'page_number', sortable: true },
  { name: 'type', align: 'center', label: 'Type', field: 'type', sortable: true },
  { name: 'actions', align: 'right', label: 'Actions' }
]

const dialog = ref({ show: false, editMode: false, id: null })
const deleteDialog = ref({ show: false, id: null })
const form = ref({
  name: '',
  description: '',
  grade_id: null,
  subject_id: null,
  edit_lock_date: null
})

const fetchData = async () => {
  loading.value = true
  try {
    const [currRes, gradeRes, subjRes] = await Promise.all([
      axios.get(route('weekly-system.api.curriculum.index')),
      axios.get('/api/grades'),
      axios.get('/api/subjects')
    ])
    curricula.value = currRes.data
    grades.value = gradeRes.data.data
    subjects.value = subjRes.data.data
  } catch (error) {
    console.error(error)
    $q.notify({ type: 'negative', message: 'Failed to load data.' })
  } finally {
    loading.value = false
  }
}

const fetchProgress = async () => {
  if (!selectedCurriculum.value) return
  try {
    const res = await axios.get(route('weekly-system.api.lesson-plans.progress'), {
      params: { curriculum_id: selectedCurriculum.value.id }
    })
    progress.value = res.data
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to load progress.' })
  }
}

const openCreateDialog = () => {
  dialog.value = { show: true, editMode: false, id: null }
  form.value = { name: '', description: '', grade_id: null, subject_id: null, edit_lock_date: null }
}

const editCurriculum = (row) => {
  dialog.value = { show: true, editMode: true, id: row.id }
  form.value = { 
    name: row.name, 
    description: row.description, 
    grade_id: row.grade_id, 
    subject_id: row.subject_id,
    edit_lock_date: row.edit_lock_date
  }
}

const editLockDate = (row) => {
  editCurriculum(row)
}

const saveCurriculum = async () => {
  if (!form.value.name || !form.value.grade_id || !form.value.subject_id) {
    $q.notify({ type: 'warning', message: 'Please fill name, grade and subject.' })
    return
  }

  saving.value = true
  try {
    if (dialog.value.editMode) {
      await axios.put(route('weekly-system.api.curriculum.update', dialog.value.id), form.value)
      $q.notify({ type: 'positive', message: 'Book updated successfully.' })
    } else {
      await axios.post(route('weekly-system.api.curriculum.store'), form.value)
      $q.notify({ type: 'positive', message: 'Book created successfully.' })
    }
    dialog.value.show = false
    fetchData()
  } catch (error) {
    $q.notify({ 
      type: 'negative', 
      message: error.response?.data?.message || 'Failed to save curriculum.' 
    })
  } finally {
    saving.value = false
  }
}

const confirmDelete = (row) => {
  deleteDialog.value = { show: true, id: row.id }
}

const deleteCurriculum = async () => {
  saving.value = true
  try {
    await axios.delete(route('weekly-system.api.curriculum.destroy', deleteDialog.value.id))
    $q.notify({ type: 'positive', message: 'Book deleted.' })
    deleteDialog.value.show = false
    fetchData()
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to delete.' })
  } finally {
    saving.value = false
  }
}

const viewTracking = (row) => {
  selectedCurriculum.value = row
  activeTab.value = 'tracking'
  fetchProgress()
}

const manageLessons = (row) => {
  selectedCurriculum.value = row
  activeTab.value = 'lessons'
  fetchLessons()
}

const fetchLessons = async () => {
  if (!selectedCurriculum.value) return
  loadingLessons.value = true
  try {
    const res = await axios.get(route('weekly-system.api.curriculum-lessons.index', selectedCurriculum.value.id))
    lessons.value = res.data
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to load lessons.' })
  } finally {
    loadingLessons.value = false
  }
}

const openLessonDialog = () => {
  lessonDialog.value = { show: true, editMode: false, id: null }
  lessonForm.value = { lesson_number: '', lesson_title: '', page_number: null, type: 'main' }
}

const editLesson = (row) => {
  lessonDialog.value = { show: true, editMode: true, id: row.id }
  lessonForm.value = { ...row }
}

const saveLesson = async () => {
  if (!lessonForm.value.lesson_number || !lessonForm.value.lesson_title) {
    $q.notify({ type: 'warning', message: 'Number and title are required.' })
    return
  }

  savingLesson.value = true
  try {
    if (lessonDialog.value.editMode) {
      await axios.put(route('weekly-system.api.curriculum-lessons.update-item', lessonDialog.value.id), lessonForm.value)
      $q.notify({ type: 'positive', message: 'Lesson updated.' })
    } else {
      await axios.post(route('weekly-system.api.curriculum-lessons.store', selectedCurriculum.value.id), lessonForm.value)
      $q.notify({ type: 'positive', message: 'Lesson added.' })
    }
    lessonDialog.value.show = false
    fetchLessons()
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to save lesson.' })
  } finally {
    savingLesson.value = false
  }
}

const deleteLesson = async (row) => {
  $q.dialog({
    title: 'Confirm',
    message: 'Remove this lesson from the book?',
    cancel: true,
    persistent: true
  }).onOk(async () => {
    try {
      await axios.delete(route('weekly-system.api.curriculum-lessons.destroy-item', row.id))
      $q.notify({ type: 'positive', message: 'Lesson removed.' })
      fetchLessons()
    } catch (error) {
      $q.notify({ type: 'negative', message: 'Failed to delete.' })
    }
  })
}

const openAIParser = () => {
  aiParser.value.open()
}

const handleBulkLessons = async (parsedLessons) => {
  if (!selectedCurriculum.value) return
  
  $q.loading.show({ message: 'Saving parsed lessons...' })
  try {
    await axios.post(route('weekly-system.api.curriculum-lessons.bulk-store', selectedCurriculum.value.id), {
      lessons: parsedLessons
    })
    $q.notify({ type: 'positive', message: `${parsedLessons.length} lessons imported successfully.` })
    fetchLessons()
  } catch (error) {
    $q.notify({ type: 'negative', message: 'Failed to import lessons.' })
  } finally {
    $q.loading.hide()
  }
}

const isPassed = (date) => {
  if (!date) return false
  return new Date(date).setHours(0,0,0,0) <= new Date().setHours(0,0,0,0)
}

onMounted(fetchData)
</script>

<style scoped>
.rounded-borders {
  border-radius: 12px;
}
.italic {
  font-style: italic;
}
</style>
