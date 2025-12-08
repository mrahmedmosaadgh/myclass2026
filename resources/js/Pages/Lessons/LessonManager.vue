<template>
  <div class="lesson-manager">
    <Head :title="'Manage Lessons - ' + curriculum.name" />

    <div class="q-pa-md">
      <div class="row q-col-gutter-md">
        <!-- Curriculum Info Card -->
        <div class="col-12">
          <q-card class="curriculum-info">
            <q-card-section>
              <div class="text-h5">{{ curriculum.name }}</div>
              <div class="text-subtitle2">
                {{ curriculum.subject.name }} | {{ curriculum.grade.name }} | {{ curriculum.school.name }}
              </div>
              <div v-if="curriculum.description" class="q-mt-sm">
                {{ curriculum.description }}
              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Lesson Management Section -->
        <div class="col-12 col-md-8">
          <q-card>
            <q-card-section>
              <div class="row justify-between items-center">
                <div>
                  <div class="text-h6">Lessons</div>
                  <div class="text-subtitle2" v-if="isReorderMode">Drag and drop to reorder</div>
                </div>
                <div v-if="!isReorderMode && lessonsList.length > 1">
                  <q-btn
                    color="primary"
                    icon="reorder"
                    label="Reorder Lessons"
                    @click="startReordering"
                  />
                </div>
                <div v-if="isReorderMode" class="row q-gutter-sm">
                  <q-btn
                    color="negative"
                    outline
                    icon="cancel"
                    label="Cancel"
                    @click="cancelReordering"
                  />
                  <q-btn
                    color="positive"
                    icon="save"
                    label="Save Order"
                    @click="saveReordering"
                    :loading="isSavingOrder"
                  />
                </div>
              </div>
            </q-card-section>

            <q-card-section>
              <!-- Draggable Lessons List -->
              <q-list bordered separator class="rounded-borders">
                <draggable
                  v-model="lessonsList"
                  group="lessons"
                  item-key="id"
                  handle=".drag-handle"
                  :disabled="!isReorderMode"
                  :animation="200"
                >
                  <template #item="{ element, index }">
                    <q-item class="lesson-item q-py-md" :class="{ 'reorder-mode': isReorderMode }">
                      <q-item-section avatar v-if="isReorderMode">
                        <div class="drag-handle cursor-move">
                          <q-icon name="drag_indicator" size="md" />
                        </div>
                      </q-item-section>

                      <q-item-section>
                        <q-item-label class="text-weight-bold">
                          {{ element.lesson_number }}. {{ element.title }}
                        </q-item-label>
                        <q-item-label caption v-if="element.page_number">
                          Page: {{ element.page_number }}
                        </q-item-label>
                        <q-item-label caption v-if="element.description">
                          {{ element.description }}
                        </q-item-label>
                      </q-item-section>

                      <q-item-section side v-if="!isReorderMode">
                        <div class="row q-gutter-sm">
                          <q-btn flat round color="primary" icon="edit" @click="editLesson(element)" />
                          <q-btn flat round color="negative" icon="delete" @click="confirmDeleteLesson(element)" />
                        </div>
                      </q-item-section>
                    </q-item>
                  </template>
                </draggable>

                <div v-if="lessonsList.length === 0" class="q-pa-md text-center text-grey">
                  No lessons yet. Add your first lesson using the form.
                </div>
              </q-list>
            </q-card-section>
          </q-card>
        </div>

        <!-- Add/Edit Lesson Form -->
        <div class="col-12 col-md-4">
          <q-card>
            <q-card-section>
              <div class="text-h6">{{ isEditing ? 'Edit Lesson' : 'Add New Lesson' }}</div>
            </q-card-section>

            <q-card-section>
              <q-form @submit="submitForm" class="q-gutter-md">
                <q-input
                  v-model="form.title"
                  label="Lesson Title"
                  :rules="[val => !!val || 'Title is required']"
                  outlined
                />

                <q-input
                  v-model.number="form.page_number"
                  label="Page Number"
                  type="number"
                  outlined
                />

                <q-input
                  v-model="form.description"
                  label="Description"
                  type="textarea"
                  outlined
                />

                <div class="row q-gutter-sm justify-end">
                  <q-btn
                    v-if="isEditing"
                    label="Cancel"
                    color="grey"
                    @click="cancelEdit"
                    outline
                  />
                  <q-btn
                    :label="isEditing ? 'Update Lesson' : 'Add Lesson'"
                    type="submit"
                    color="primary"
                    :loading="isSubmitting"
                  />
                </div>
              </q-form>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <q-dialog v-model="deleteDialog" persistent>
      <q-card>
        <q-card-section class="row items-center">
          <q-avatar icon="warning" color="negative" text-color="white" />
          <span class="q-ml-sm">Are you sure you want to delete this lesson?</span>
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="primary" v-close-popup />
          <q-btn flat label="Delete" color="negative" @click="deleteLesson" :loading="isDeleting" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';
import axios from 'axios';
import draggable from 'vuedraggable';

// Configure axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.withCredentials = true;

// Props
const props = defineProps({
  curriculum: Object,
  lessons: Array
});

// Setup
const $q = useQuasar();
const lessonsList = ref([...props.lessons]);
const originalLessonsOrder = ref([]);
const isEditing = ref(false);
const editingLessonId = ref(null);
const isSubmitting = ref(false);
const isDeleting = ref(false);
const deleteDialog = ref(false);
const lessonToDelete = ref(null);
const isReorderMode = ref(false);
const isSavingOrder = ref(false);

// Form
const form = useForm({
  curriculum_id: props.curriculum.id,
  title: '',
  page_number: null,
  description: '',
  position: null
});

// Methods
const resetForm = () => {
  form.reset();
  form.curriculum_id = props.curriculum.id;
  isEditing.value = false;
  editingLessonId.value = null;
};

const editLesson = (lesson) => {
  form.title = lesson.title;
  form.page_number = lesson.page_number;
  form.description = lesson.description || '';
  isEditing.value = true;
  editingLessonId.value = lesson.id;
};

const cancelEdit = () => {
  resetForm();
};

const submitForm = async () => {
  isSubmitting.value = true;

  try {
    if (isEditing.value) {
      // Update existing lesson
      const response = await axios.put(`/api/lessons/${editingLessonId.value}`, {
        title: form.title,
        page_number: form.page_number,
        description: form.description
      });

      // Update the lesson in the list
      const index = lessonsList.value.findIndex(l => l.id === editingLessonId.value);
      if (index !== -1) {
        lessonsList.value[index] = response.data;
      }

      $q.notify({
        color: 'positive',
        message: 'Lesson updated successfully',
        icon: 'check_circle'
      });
    } else {
      // Add new lesson
      form.position = lessonsList.value.length; // Add to the end by default

      const response = await axios.post('/api/lessons', form);
      lessonsList.value.push(response.data);

      $q.notify({
        color: 'positive',
        message: 'Lesson added successfully',
        icon: 'check_circle'
      });
    }

    resetForm();
  } catch (error) {
    console.error('Error submitting form:', error);
    $q.notify({
      color: 'negative',
      message: 'An error occurred. Please try again.',
      icon: 'error'
    });
  } finally {
    isSubmitting.value = false;
  }
};

const confirmDeleteLesson = (lesson) => {
  lessonToDelete.value = lesson;
  deleteDialog.value = true;
};

const deleteLesson = async () => {
  if (!lessonToDelete.value) return;

  isDeleting.value = true;

  try {
    await axios.delete(`/api/lessons/${lessonToDelete.value.id}`);

    // Remove from the list
    lessonsList.value = lessonsList.value.filter(l => l.id !== lessonToDelete.value.id);

    $q.notify({
      color: 'positive',
      message: 'Lesson deleted successfully',
      icon: 'check_circle'
    });
  } catch (error) {
    console.error('Error deleting lesson:', error);
    $q.notify({
      color: 'negative',
      message: 'An error occurred while deleting the lesson',
      icon: 'error'
    });
  } finally {
    isDeleting.value = false;
    lessonToDelete.value = null;
  }
};

// Reordering methods
const startReordering = () => {
  // Save the original order in case user cancels
  originalLessonsOrder.value = JSON.parse(JSON.stringify(lessonsList.value));
  isReorderMode.value = true;
};

const cancelReordering = () => {
  // Restore the original order
  lessonsList.value = JSON.parse(JSON.stringify(originalLessonsOrder.value));
  isReorderMode.value = false;
};

const saveReordering = async () => {
  isSavingOrder.value = true;

  try {
    // Get the IDs in the new order
    const lessonIds = lessonsList.value.map(lesson => lesson.id);

    // Send the new order to the server
    const response = await axios.post('/api/lessons/reorder', {
      curriculum_id: props.curriculum.id,
      lesson_ids: lessonIds
    });

    // Update the list with the response data to ensure correct ordering
    lessonsList.value = response.data;

    // Exit reorder mode
    isReorderMode.value = false;

    $q.notify({
      color: 'positive',
      message: 'Lesson order saved successfully',
      icon: 'check_circle'
    });
  } catch (error) {
    console.error('Error saving lesson order:', error);
    $q.notify({
      color: 'negative',
      message: 'Failed to save lesson order',
      icon: 'error'
    });
  } finally {
    isSavingOrder.value = false;
  }
};
</script>

<style scoped>
.lesson-manager {
  max-width: 1200px;
  margin: 0 auto;
}

.drag-handle {
  cursor: move;
}

.lesson-item {
  transition: all 0.2s;
}

.lesson-item:hover {
  background-color: rgba(0, 0, 0, 0.03);
}

.reorder-mode {
  background-color: rgba(0, 120, 212, 0.05);
  border-left: 3px solid #1976d2;
}

.reorder-mode:hover {
  background-color: rgba(0, 120, 212, 0.1);
  cursor: move;
}
</style>
