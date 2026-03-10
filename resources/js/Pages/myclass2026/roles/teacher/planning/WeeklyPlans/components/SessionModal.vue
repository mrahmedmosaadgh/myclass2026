<template>
  <div class="session-modal">
    <!-- Empty State -->
    <div v-if="!session && !isEditing" class="empty-state">
      <div class="text-center py-8">
        <div class="text-gray-400 mb-4">
          <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Session Details</h3>
        <p class="text-gray-600">Select a session to view or edit its details</p>
      </div>
    </div>

    <!-- Session Form -->
    <div v-else class="session-form">
      <div v-if="!isDrawer" class="form-header mb-6">
        <h3 class="text-lg font-medium text-gray-900">
          {{ session?.id ? 'Edit Session' : 'New Session' }}
        </h3>
        <p class="text-sm text-gray-600 mt-1">
          Configure session details and content
        </p>
      </div>

      <form @submit.prevent="handleSave" :class="isDrawer ? 'drawer-form' : 'space-y-6'">
        <!-- Basic Information -->
        <div class="form-section">
          <h4 class="form-section-title">Basic Information</h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">Session Title</label>
              <input v-model="localSession.title" type="text" class="form-input" placeholder="Enter session title"
                required />
            </div>

            <div class="form-group">
              <label class="form-label">Session Type</label>
              <select v-model="localSession.type" class="form-select" required>
                <option value="lesson">Lesson</option>
                <option value="quiz">Quiz</option>
                <option value="exam">Exam</option>
                <option value="extra">Extra Activity</option>
                <option value="note">Note/Reminder</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Period Code</label>
              <input v-model="localSession.period_code" type="text" class="form-input" placeholder="e.g., 25.1.2.3"
                pattern="^\d+\.\d+\.\d+\.\d+$" />
              <p class="form-help">Format: year.semester.week.session</p>
            </div>

            <div class="form-group">
              <label class="form-label">Duration (minutes)</label>
              <input v-model.number="localSession.data.duration_minutes" type="number" class="form-input" min="1"
                max="300" placeholder="45" />
            </div>
          </div>
        </div>

        <!-- Content Details -->
        <div class="form-section">
          <h4 class="form-section-title">Content & Materials</h4>

          <div class="space-y-4">
            <div class="form-group">
              <label class="form-label">Materials</label>
              <div class="materials-input">
                <div v-for="(material, index) in localSession.data.materials" :key="index" class="material-item">
                  <input v-model="localSession.data.materials[index]" type="text" class="form-input flex-1"
                    placeholder="Enter material name" />
                  <q-btn size="sm" flat round color="red" icon="remove" @click="removeMaterial(index)" />
                </div>
                <q-btn size="sm" outline color="primary" icon="add" label="Add Material" @click="addMaterial"
                  class="mt-2" />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Homework Assignment</label>
              <textarea v-model="localSession.data.homework" class="form-textarea" rows="3"
                placeholder="Describe homework or follow-up activities"></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Skill Tags</label>
              <div class="skill-tags-input">
                <div class="skill-tags-display">
                  <span v-for="(tag, index) in localSession.data.skill_tags" :key="index" class="skill-tag">
                    {{ tag }}
                    <button type="button" @click="removeSkillTag(index)" class="skill-tag-remove">
                      ×
                    </button>
                  </span>
                </div>
                <div class="skill-tag-input">
                  <input v-model="newSkillTag" type="text" class="form-input" placeholder="Add skill tag"
                    @keyup.enter="addSkillTag" />
                  <q-btn size="sm" outline color="primary" icon="add" @click="addSkillTag" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Online Learning -->
        <div class="form-section">
          <h4 class="form-section-title">Online Learning</h4>

          <div class="form-group">
            <label class="form-label">Zoom Meeting Link</label>
            <input v-model="localSession.data.zoom_link" type="url" class="form-input"
              placeholder="https://zoom.us/j/..." />
          </div>
        </div>

        <!-- Progress Tracking -->
        <div class="form-section">
          <h4 class="form-section-title">Progress Tracking</h4>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="form-group">
              <label class="form-label">Completion Status</label>
              <select v-model="localSession.data.completed" class="form-select">
                <option :value="undefined">Not Started</option>
                <option :value="false">In Progress</option>
                <option :value="true">Completed</option>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label">Difficulty Level</label>
              <select v-model="localSession.data.difficulty_level" class="form-select">
                <option value="">Not Set</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
          <q-btn type="submit" color="primary" icon="save" label="Save Session" :loading="saving" />
          <q-btn type="button" outline color="grey" icon="cancel" label="Cancel" @click="handleCancel" />
          <q-btn v-if="session?.id" type="button" outline color="red" icon="close" label="Close" @click="handleClose" />
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, watch, computed } from 'vue'
// import { QBtn } from 'quasar'

export default {
  name: 'SessionModal',
  components: {
    // QBtn
  },
  props: {
    session: {
      type: Object,
      default: null
    },
    isEditing: {
      type: Boolean,
      default: false
    },
    isDrawer: {
      type: Boolean,
      default: false
    }
  },
  emits: ['save', 'cancel', 'close'],
  setup(props, { emit }) {
    const saving = ref(false)
    const newSkillTag = ref('')

    // Create local copy of session data
    const localSession = ref({
      id: null,
      weekly_plan_id: null,
      session_index: 1,
      period_code: '',
      type: 'lesson',
      title: '',
      data: {
        materials: [],
        homework: '',
        skill_tags: [],
        zoom_link: '',
        duration_minutes: null,
        completed: undefined,
        difficulty_level: ''
      }
    })

    // Watch for changes in session prop
    watch(() => props.session, (newSession) => {
      if (newSession) {
        localSession.value = {
          ...newSession,
          data: {
            materials: [],
            homework: '',
            skill_tags: [],
            zoom_link: '',
            duration_minutes: null,
            completed: undefined,
            difficulty_level: '',
            ...newSession.data
          }
        }

        // Ensure materials and skill_tags are arrays
        if (!Array.isArray(localSession.value.data.materials)) {
          localSession.value.data.materials = []
        }
        if (!Array.isArray(localSession.value.data.skill_tags)) {
          localSession.value.data.skill_tags = []
        }
      }
    }, { immediate: true })

    // Methods
    const addMaterial = () => {
      localSession.value.data.materials.push('')
    }

    const removeMaterial = (index) => {
      localSession.value.data.materials.splice(index, 1)
    }

    const addSkillTag = () => {
      if (newSkillTag.value.trim()) {
        localSession.value.data.skill_tags.push(newSkillTag.value.trim())
        newSkillTag.value = ''
      }
    }

    const removeSkillTag = (index) => {
      localSession.value.data.skill_tags.splice(index, 1)
    }

    const handleSave = async () => {
      saving.value = true
      try {
        // Clean up empty materials
        localSession.value.data.materials = localSession.value.data.materials.filter(m => m.trim())

        emit('save', { ...localSession.value })
      } catch (error) {
        console.error('Error saving session:', error)
      } finally {
        saving.value = false
      }
    }

    const handleCancel = () => {
      emit('cancel')
    }

    const handleClose = () => {
      emit('close')
    }

    return {
      saving,
      newSkillTag,
      localSession,
      addMaterial,
      removeMaterial,
      addSkillTag,
      removeSkillTag,
      handleSave,
      handleCancel,
      handleClose
    }
  }
}
</script>

<style scoped>
.session-modal {
  @apply h-full;
}

.empty-state {
  @apply h-full flex items-center justify-center;
}

.session-form {
  @apply h-full overflow-y-auto;
}

.form-header {
  @apply border-b border-gray-200 pb-4;
}

.form-section {
  @apply space-y-4;
}

.form-section-title {
  @apply text-sm font-medium text-gray-900 mb-3;
}

.form-group {
  @apply space-y-1;
}

.form-label {
  @apply block text-sm font-medium text-gray-700;
}

.form-input {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-select {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-textarea {
  @apply block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm;
}

.form-help {
  @apply text-xs text-gray-500 mt-1;
}

.materials-input {
  @apply space-y-2;
}

.material-item {
  @apply flex space-x-2 items-center;
}

.skill-tags-input {
  @apply space-y-2;
}

.skill-tags-display {
  @apply flex flex-wrap gap-2;
}

.skill-tag {
  @apply inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full;
}

.skill-tag-remove {
  @apply ml-1 text-blue-600 hover:text-blue-800 font-bold;
}

.skill-tag-input {
  @apply flex space-x-2;
}

.form-actions {
  @apply flex space-x-3 pt-4 border-t border-gray-200;
}

/* Drawer-specific styles */
.drawer-form {
  @apply p-6 space-y-8;
}

.drawer-form .form-section {
  @apply bg-gray-50 rounded-lg p-6 space-y-6;
}

.drawer-form .grid {
  @apply lg:grid-cols-3;
}

.drawer-form .form-section-title {
  @apply text-base font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200;
}

.drawer-form .form-actions {
  @apply sticky bottom-0 bg-white p-6 border-t border-gray-200 flex justify-end space-x-3;
  margin: 0 -24px -24px -24px;
}
</style>