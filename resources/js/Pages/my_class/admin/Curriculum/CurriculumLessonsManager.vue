<template>
  <q-card class="full-height">
    <!-- Header -->
    <q-card-section class="row items-center q-pb-none">
      <div class="col">
        <h5 class="q-my-none">{{ curriculum.name }} - Topics & Lessons</h5>
        <p class="text-grey-6 q-mb-none">Manage topics and lessons for this curriculum</p>
      </div>
      <div class="col-auto">
        <q-btn
          flat
          round
          dense
          icon="close"
          @click="$emit('close')"
        />
      </div>
    </q-card-section>

    <q-separator />

    <!-- Add Topic Button -->
    <q-card-section class="q-pb-none">
      <q-btn
        color="primary"
        icon="add"
        label="Add Topic"
        @click="openAddTopicDialog"
        unelevated
      />
      <q-btn
        color="primary"
        icon="upload"
        label="Import Excel"
        @click="openExcelDialog('import')"
        unelevated
        class="q-ml-sm"
      />
      <q-btn
        color="primary"
        icon="download"
        label="Export to Excel"
        @click="openExcelDialog('export')"
        unelevated
        class="q-ml-sm"
      />
      <q-space />
      <q-toggle
        v-model="editMode"
        label="Edit Mode"
        color="orange"
        icon="edit"
      />
    </q-card-section>

    <!-- Topics and Lessons List -->
    <q-card-section class="scroll" style="height: calc(100% - 200px)">
      <div v-if="topics.length === 0" class="text-center q-py-lg text-grey-6">
        No topics yet. Click "Add Topic" to create one.
      </div>

      <div v-for="topic in topics" :key="topic.id" class="q-mb-lg">
        <!-- Topic Header -->
        <div class="row items-center q-mb-md bg-grey-2 q-pa-md rounded-borders">
          <div class="col">
            <h6 class="q-my-none">{{ topic.number }} - {{ topic.title }}</h6>
            <p class="text-grey-6 q-mb-none text-caption">{{ topic.lessons?.length || 0 }} lessons</p>
          </div>
          <div class="col-auto q-gutter-xs" v-if="editMode">
            <q-btn
              size="sm"
              flat
              dense
              round
              icon="add"
              color="primary"
              @click="openAddLessonDialog(topic)"
            >
              <q-tooltip>Add Lesson</q-tooltip>
            </q-btn>
            <q-btn
              size="sm"
              flat
              dense
              round
              icon="edit"
              color="primary"
              @click="editTopic(topic)"
            >
              <q-tooltip>Edit Topic</q-tooltip>
            </q-btn>
            <q-btn
              size="sm"
              flat
              dense
              round
              icon="delete"
              color="negative"
              @click="deleteTopic(topic)"
            >
              <q-tooltip>Delete Topic</q-tooltip>
            </q-btn>
          </div>
        </div>

        <!-- Lessons List -->
        <q-list bordered separator class="q-ml-lg">
          <draggable
            v-model="topic.lessons"
            item-key="id"
            @change="onLessonsReordered(topic)"
            class="w-full"
          >
            <template #item="{ element: lesson }">
              <q-item clickable>
                <q-item-section avatar v-if="editMode">
                  <q-icon name="drag_indicator" color="grey" />
                </q-item-section>

                <q-item-section>
                  <q-item-label>{{ lesson.lesson_number }} - {{ lesson.lesson_title }}</q-item-label>
                  <q-item-label caption>{{ lesson.description }}</q-item-label>
                </q-item-section>

                <q-item-section side top v-if="editMode">
                  <div class="text-grey-8 q-gutter-xs">
                    <q-btn
                      size="sm"
                      flat
                      dense
                      round
                      icon="edit"
                      color="primary"
                      @click.stop="editLesson(lesson, topic)"
                    >
                      <q-tooltip>Edit</q-tooltip>
                    </q-btn>
                    <q-btn
                      size="sm"
                      flat
                      dense
                      round
                      icon="delete"
                      color="negative"
                      @click.stop="deleteLesson(lesson)"
                    >
                      <q-tooltip>Delete</q-tooltip>
                    </q-btn>
                  </div>
                </q-item-section>
              </q-item>
            </template>
          </draggable>

          <div v-if="!topic.lessons || topic.lessons.length === 0" class="text-center q-py-md text-grey-6">
            No lessons in this topic
          </div>
        </q-list>
      </div>
    </q-card-section>

    <!-- Footer Actions -->
    <q-separator />
    <q-card-actions align="right">
      <q-btn flat label="Close" @click="$emit('close')" />
    </q-card-actions>

    <!-- Add/Edit Topic Dialog -->
    <q-dialog v-model="showTopicDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingTopic ? 'Edit Topic' : 'Add New Topic' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="q-gutter-md">
            <q-select
              v-model="topicForm.number"
              :options="numberOptions"
              label="Topic Number *"
              outlined
              :rules="[val => val !== null && val !== '' || 'Topic number is required']"
              emit-value
              map-options
            />

            <q-input
              v-model="topicForm.title"
              label="Topic Title *"
              outlined
              :rules="[val => !!val || 'Title is required']"
            />

            <q-input
              v-model="topicForm.description"
              label="Description"
              type="textarea"
              rows="3"
              outlined
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="closeTopicDialog" />
          <q-btn
            color="primary"
            label="Save"
            @click="saveTopic"
            :loading="savingTopic"
            unelevated
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Add/Edit Lesson Dialog -->
    <q-dialog v-model="showLessonDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-h6">{{ editingLesson ? 'Edit Lesson' : 'Add New Lesson' }}</div>
        </q-card-section>

        <q-card-section class="q-pt-none">
          <div class="q-gutter-md">
            <q-select
              v-model="lessonForm.lesson_number"
              :options="numberOptions"
              label="Lesson Number *"
              outlined
              :rules="[val => val !== null && val !== '' || 'Lesson number is required']"
              emit-value
              map-options
            />

            <q-input
              v-model="lessonForm.lesson_title"
              label="Lesson Title *"
              outlined
              :rules="[val => !!val || 'Title is required']"
            />

            <q-input
              v-model="lessonForm.description"
              label="Description"
              type="textarea"
              rows="2"
              outlined
            />

            <q-input
              v-model="lessonForm.content"
              label="Content"
              type="textarea"
              rows="4"
              outlined
            />

            <q-select
              v-model="lessonForm.type"
              :options="lessonTypes"
              label="Lesson Type"
              outlined
            />
          </div>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" @click="closeLessonDialog" />
          <q-btn
            color="primary"
            label="Save"
            @click="saveLesson"
            :loading="savingLesson"
            unelevated
          />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- Excel Import Dialog -->
    <q-dialog v-model="showExcelDialog" full-width>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Import Topics & Lessons</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-separator />

        <q-card-section>
          <ExcelManager
            :export-file-name="`${curriculum.name}_topics_lessons.xlsx`"
            :export-data="fullCurriculumData"
            :initial-tab="excelTab"
            @imported-json="handleImportedData"
          />
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import draggable from 'vuedraggable'
import ExcelManager from '@/Components/import_excel_sys/ExcelManager.vue'

const props = defineProps({
  curriculum: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])

const $q = useQuasar()

// State
const topics = ref([])
const showTopicDialog = ref(false)
const showLessonDialog = ref(false)
const editingTopic = ref(null)
const editingLesson = ref(null)
const currentTopic = ref(null)
const savingTopic = ref(false)
const savingLesson = ref(false)
const loadingTopics = ref(false)
const showExcelDialog = ref(false)
const excelTab = ref('import')
const editMode = ref(false)

const lessonTypes = ['main', 'revision', 'quiz', 'project', 'extra']

// Generate number options 0-20
const numberOptions = Array.from({ length: 21 }, (_, i) => ({
  label: i.toString(),
  value: i.toString()
}))

// Forms
const topicForm = ref({
  number: '',
  title: '',
  description: ''
})

const lessonForm = ref({
  lesson_number: '',
  lesson_title: '',
  description: '',
  content: '',
  type: 'main'
})

// Full Template for export
const fullCurriculumData = computed(() => {
  if (topics.value.length === 0) {
    return [
      {
        topic_number: '1',
        topic_title: 'Introduction',
        topic_description: 'Basic concepts',
        lesson_number: '1.1',
        lesson_title: 'What is it?',
        lesson_description: 'Overview',
        lesson_content: 'Some content here...',
        lesson_type: 'main',
        page_number: 1,
        standard: '',
        strand: '',
        skill: '',
        activities: '',
        assignment: '',
        assessment: '',
        objective: ''
      }
    ]
  }

  const data = []
  topics.value.forEach(topic => {
    if (!topic.lessons || topic.lessons.length === 0) {
      data.push({
        topic_number: topic.number,
        topic_title: topic.title,
        topic_description: topic.description || '',
        lesson_number: '',
        lesson_title: '',
        lesson_description: '',
        lesson_content: '',
        lesson_type: '',
        page_number: '',
        standard: '',
        strand: '',
        skill: '',
        activities: '',
        assignment: '',
        assessment: '',
        objective: ''
      })
    } else {
      topic.lessons.forEach(lesson => {
        data.push({
          topic_number: topic.number,
          topic_title: topic.title,
          topic_description: topic.description || '',
          lesson_number: lesson.lesson_number,
          lesson_title: lesson.lesson_title,
          lesson_description: lesson.description || '',
          lesson_content: lesson.content || '',
          lesson_type: lesson.type || 'main',
          page_number: lesson.page_number || '',
          standard: lesson.standard || '',
          strand: lesson.strand || '',
          skill: lesson.skill || '',
          activities: lesson.activities || '',
          assignment: lesson.assignment || '',
          assessment: lesson.assessment || '',
          objective: lesson.objective || ''
        })
      })
    }
  })
  return data
})

// Methods
const openExcelDialog = (tab = 'import') => {
  excelTab.value = tab
  showExcelDialog.value = true
}

const loadTopics = async () => {
  loadingTopics.value = true
  try {
    const response = await axios.get(`/api/curriculum/${props.curriculum.id}/topics`)
    topics.value = response.data
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to load topics'
    })
  } finally {
    loadingTopics.value = false
  }
}

const openAddTopicDialog = () => {
  resetTopicForm()
  editingTopic.value = null
  // Auto-generate next topic number
  if (topics.value.length > 0) {
    const lastTopic = topics.value[topics.value.length - 1]
    const lastNumber = parseInt(lastTopic.number) || 0
    topicForm.value.number = (lastNumber + 1).toString()
  } else {
    topicForm.value.number = '1'
  }
  showTopicDialog.value = true
}

const editTopic = (topic) => {
  editingTopic.value = topic
  topicForm.value = {
    number: topic.number,
    title: topic.title,
    description: topic.description || ''
  }
  showTopicDialog.value = true
}

const closeTopicDialog = () => {
  showTopicDialog.value = false
  editingTopic.value = null
  resetTopicForm()
}

const resetTopicForm = () => {
  topicForm.value = {
    number: '',
    title: '',
    description: ''
  }
}

const saveTopic = async () => {
  if (!topicForm.value.number || !topicForm.value.title) {
    $q.notify({
      type: 'negative',
      message: 'Please fill in all required fields'
    })
    return
  }

  savingTopic.value = true
  try {
    const data = {
      number: topicForm.value.number,
      title: topicForm.value.title,
      description: topicForm.value.description
    }

    if (editingTopic.value) {
      await axios.put(`/api/curriculum/topics/${editingTopic.value.id}`, data)
      $q.notify({
        type: 'positive',
        message: 'Topic updated successfully'
      })
    } else {
      data.curriculum_id = props.curriculum.id
      await axios.post('/api/curriculum/topics', data)
      $q.notify({
        type: 'positive',
        message: 'Topic created successfully'
      })
    }

    closeTopicDialog()
    loadTopics()
    emit('updated')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save topic'
    })
  } finally {
    savingTopic.value = false
  }
}

const deleteTopic = (topic) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Are you sure you want to delete "${topic.title}"? All lessons in this topic will also be deleted.`,
    cancel: true,
    persistent: true,
    color: 'negative'
  }).onOk(async () => {
    try {
      await axios.delete(`/api/curriculum/topics/${topic.id}`)
      $q.notify({
        type: 'positive',
        message: 'Topic deleted successfully'
      })
      loadTopics()
      emit('updated')
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || 'Failed to delete topic'
      })
    }
  })
}

const openAddLessonDialog = (topic) => {
  currentTopic.value = topic
  resetLessonForm()
  editingLesson.value = null
  // Auto-generate next lesson number
  if (topic.lessons && topic.lessons.length > 0) {
    const lastLesson = topic.lessons[topic.lessons.length - 1]
    const lastNumber = parseInt(lastLesson.lesson_number) || 0
    lessonForm.value.lesson_number = (lastNumber + 1).toString()
  } else {
    lessonForm.value.lesson_number = '1'
  }
  showLessonDialog.value = true
}

const editLesson = (lesson, topic) => {
  currentTopic.value = topic
  editingLesson.value = lesson
  lessonForm.value = {
    lesson_number: lesson.lesson_number,
    lesson_title: lesson.lesson_title,
    description: lesson.description || '',
    content: lesson.content || '',
    type: lesson.type || 'main'
  }
  showLessonDialog.value = true
}

const closeLessonDialog = () => {
  showLessonDialog.value = false
  editingLesson.value = null
  currentTopic.value = null
  resetLessonForm()
}

const resetLessonForm = () => {
  lessonForm.value = {
    lesson_number: '',
    lesson_title: '',
    description: '',
    content: '',
    type: 'main'
  }
}

const saveLesson = async () => {
  if (!lessonForm.value.lesson_number || !lessonForm.value.lesson_title) {
    $q.notify({
      type: 'negative',
      message: 'Please fill in all required fields'
    })
    return
  }

  savingLesson.value = true
  try {
    const data = {
      lesson_number: lessonForm.value.lesson_number,
      lesson_title: lessonForm.value.lesson_title,
      description: lessonForm.value.description,
      content: lessonForm.value.content,
      type: lessonForm.value.type,
      school_id: props.curriculum.school_id
    }

    if (editingLesson.value) {
      await axios.put(`/api/curriculum/lessons/${editingLesson.value.id}`, data)
      $q.notify({
        type: 'positive',
        message: 'Lesson updated successfully'
      })
    } else {
      data.topic_id = currentTopic.value.id
      await axios.post('/api/curriculum/lessons', data)
      $q.notify({
        type: 'positive',
        message: 'Lesson created successfully'
      })
    }

    closeLessonDialog()
    loadTopics()
    emit('updated')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to save lesson'
    })
  } finally {
    savingLesson.value = false
  }
}

const deleteLesson = (lesson) => {
  $q.dialog({
    title: 'Confirm Deletion',
    message: `Are you sure you want to delete "${lesson.lesson_title}"? This action cannot be undone.`,
    cancel: true,
    persistent: true,
    color: 'negative'
  }).onOk(async () => {
    try {
      await axios.delete(`/api/curriculum/lessons/${lesson.id}`)
      $q.notify({
        type: 'positive',
        message: 'Lesson deleted successfully'
      })
      loadTopics()
      emit('updated')
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || 'Failed to delete lesson'
      })
    }
  })
}

const onLessonsReordered = async (topic) => {
  try {
    const reorderData = topic.lessons.map((lesson) => ({
      id: lesson.id,
      lesson_number: lesson.lesson_number
    }))

    await axios.post(`/api/curriculum/topics/${topic.id}/lessons/reorder`, {
      lessons: reorderData
    })

    $q.notify({
      type: 'positive',
      message: 'Lessons reordered successfully'
    })
    emit('updated')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to reorder lessons'
    })
    loadTopics()
  }
}

const handleImportedData = async (data) => {
  try {
    // Basic validation
    const validData = data.filter(item => 
      item.topic_number && 
      item.topic_title && 
      item.lesson_number && 
      item.lesson_title
    )

    if (validData.length === 0) {
      $q.notify({
        type: 'negative',
        message: 'No valid data found. Ensure topic_number, topic_title, lesson_number, and lesson_title are present.'
      })
      return
    }

    const response = await axios.post(`/api/curriculum/${props.curriculum.id}/bulk-import-topics-lessons`, {
      data: validData
    })

    $q.notify({
      type: 'positive',
      message: response.data.message
    })

    showExcelDialog.value = false
    loadTopics()
    emit('updated')
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to import data'
    })
  }
}

// Initialize
onMounted(() => {
  loadTopics()
})
</script>

<style scoped>
.scroll {
  overflow-y: auto;
}

.w-full {
  width: 100%;
}
</style>
