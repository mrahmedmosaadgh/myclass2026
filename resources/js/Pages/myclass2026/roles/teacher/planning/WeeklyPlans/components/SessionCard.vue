<template>
  <div class="session-card" :class="getCardClass()">
    <!-- Drag Handle -->
    <div class="drag-handle absolute left-2 top-1/2 transform -translate-y-1/2">
      <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
      </svg>
    </div>

    <!-- Session Content -->
    <div class="session-content pl-8">
      <!-- Header -->
      <div class="flex justify-between items-start mb-2">
        <div class="flex items-center space-x-2">
          <span class="session-index">{{ index }}</span>
          <div class="session-type-badge" :class="getTypeBadgeClass()">
            {{ getTypeLabel() }}
          </div>
          <div v-if="session.period_code" class="text-xs text-gray-500">
            {{ session.period_code }}
          </div>
        </div>
        
        <!-- Actions -->
        <div class="flex space-x-1">
          <q-btn
            size="xs"
            flat
            round
            color="blue"
            icon="edit"
            @click="$emit('edit', session)"
          >
            <q-tooltip>Edit Session</q-tooltip>
          </q-btn>
          <q-btn
            size="xs"
            flat
            round
            color="green"
            icon="content_copy"
            @click="$emit('duplicate', session)"
          >
            <q-tooltip>Duplicate Session</q-tooltip>
          </q-btn>
          <q-btn
            size="xs"
            flat
            round
            color="red"
            icon="delete"
            @click="$emit('delete', session)"
          >
            <q-tooltip>Delete Session</q-tooltip>
          </q-btn>
        </div>
      </div>

      <!-- Title -->
      <h4 class="session-title">{{ session.title }}</h4>

      <!-- Session Details -->
      <div class="session-details mt-2">
        <!-- Materials -->
        <div v-if="session.data?.materials?.length" class="detail-item">
          <span class="detail-label">Materials:</span>
          <span class="detail-value">{{ session.data.materials.join(', ') }}</span>
        </div>

        <!-- Homework -->
        <div v-if="session.data?.homework" class="detail-item">
          <span class="detail-label">Homework:</span>
          <span class="detail-value">{{ session.data.homework }}</span>
        </div>

        <!-- Skill Tags -->
        <div v-if="session.data?.skill_tags?.length" class="detail-item">
          <span class="detail-label">Skills:</span>
          <div class="skill-tags">
            <span 
              v-for="tag in session.data.skill_tags" 
              :key="tag"
              class="skill-tag"
            >
              {{ tag }}
            </span>
          </div>
        </div>

        <!-- Duration -->
        <div v-if="session.data?.duration_minutes" class="detail-item">
          <span class="detail-label">Duration:</span>
          <span class="detail-value">{{ session.data.duration_minutes }} minutes</span>
        </div>

        <!-- Zoom Link -->
        <div v-if="session.data?.zoom_link" class="detail-item">
          <span class="detail-label">Zoom:</span>
          <a 
            :href="session.data.zoom_link" 
            target="_blank"
            class="detail-link"
          >
            Join Meeting
          </a>
        </div>
      </div>

      <!-- Progress Indicator -->
      <div v-if="session.data?.completed !== undefined" class="progress-indicator mt-3">
        <div class="flex justify-between items-center">
          <span class="text-xs text-gray-600">Progress</span>
          <span class="text-xs font-medium" :class="getProgressClass()">
            {{ getProgressText() }}
          </span>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :class="getProgressFillClass()"
            :style="{ width: getProgressWidth() }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// import { QBtn, QTooltip } from 'quasar'

export default {
  name: 'SessionCard',
  components: {
    // QBtn,
    // QTooltip
  },
  props: {
    session: {
      type: Object,
      required: true
    },
    index: {
      type: Number,
      required: true
    }
  },
  emits: ['edit', 'delete', 'duplicate'],
  methods: {
    getCardClass() {
      const classes = ['session-card-base']
      
      // Add type-specific styling
      switch (this.session.type) {
        case 'quiz':
          classes.push('border-l-yellow-500')
          break
        case 'exam':
          classes.push('border-l-red-500')
          break
        case 'extra':
          classes.push('border-l-purple-500')
          break
        case 'note':
          classes.push('border-l-gray-500')
          break
        default:
          classes.push('border-l-blue-500')
      }
      
      return classes.join(' ')
    },
    
    getTypeBadgeClass() {
      switch (this.session.type) {
        case 'quiz':
          return 'bg-yellow-100 text-yellow-800'
        case 'exam':
          return 'bg-red-100 text-red-800'
        case 'extra':
          return 'bg-purple-100 text-purple-800'
        case 'note':
          return 'bg-gray-100 text-gray-800'
        default:
          return 'bg-blue-100 text-blue-800'
      }
    },
    
    getTypeLabel() {
      const labels = {
        lesson: 'Lesson',
        quiz: 'Quiz',
        exam: 'Exam',
        extra: 'Extra',
        note: 'Note'
      }
      return labels[this.session.type] || 'Session'
    },
    
    getProgressText() {
      if (this.session.data?.completed === true) {
        return 'Completed'
      } else if (this.session.data?.completed === false) {
        return 'Not Started'
      }
      return 'Planned'
    },
    
    getProgressClass() {
      if (this.session.data?.completed === true) {
        return 'text-green-600'
      } else if (this.session.data?.completed === false) {
        return 'text-red-600'
      }
      return 'text-gray-600'
    },
    
    getProgressWidth() {
      if (this.session.data?.completed === true) {
        return '100%'
      } else if (this.session.data?.completed === false) {
        return '0%'
      }
      return '50%'
    },
    
    getProgressFillClass() {
      if (this.session.data?.completed === true) {
        return 'bg-green-500'
      } else if (this.session.data?.completed === false) {
        return 'bg-red-500'
      }
      return 'bg-gray-400'
    }
  }
}
</script>

<style scoped>
.session-card {
  @apply relative;
}

.session-card-base {
  @apply bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow duration-200;
  @apply border-l-4;
}

.session-content {
  @apply relative;
}

.session-index {
  @apply inline-flex items-center justify-center w-6 h-6 bg-gray-100 text-gray-700 text-sm font-medium rounded-full;
}

.session-type-badge {
  @apply px-2 py-1 text-xs font-medium rounded-full;
}

.session-title {
  @apply text-lg font-medium text-gray-900;
}

.session-details {
  @apply space-y-1;
}

.detail-item {
  @apply flex items-start space-x-2 text-sm;
}

.detail-label {
  @apply font-medium text-gray-600 min-w-0 flex-shrink-0;
}

.detail-value {
  @apply text-gray-900;
}

.detail-link {
  @apply text-blue-600 hover:text-blue-800 underline;
}

.skill-tags {
  @apply flex flex-wrap gap-1;
}

.skill-tag {
  @apply px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full;
}

.progress-indicator {
  @apply space-y-1;
}

.progress-bar {
  @apply w-full bg-gray-200 rounded-full h-2;
}

.progress-fill {
  @apply h-2 rounded-full transition-all duration-300;
}
</style>