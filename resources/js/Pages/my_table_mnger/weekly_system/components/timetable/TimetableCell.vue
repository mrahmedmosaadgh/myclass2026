<template>
  <div
    class="timetable-cell"
    :class="cellClasses"
    @click="$emit('click', schedule)"
  >
    <!-- Empty State -->
    <div v-if="!schedule?.cst_id" class="empty-cell">
      <q-icon name="add" size="sm" color="grey-5" />
      <span class="text-caption text-grey-6">Assign</span>
    </div>

    <!-- Assigned State -->
    <div v-else class="assigned-cell">
      <!-- Subject Badge -->
      <div
        class="subject-badge"
        :style="{ backgroundColor: subjectColor, color: textColor }"
        :title="fullSubjectName"
      >
        {{ subjectName }}
      </div>

      <!-- Teacher Name -->
      <div 
        class="teacher-name text-caption scale-75"
        :title="fullTeacherName"
      >
        <q-icon name="person" size="xs" class="q-mr-xs text-gray-500" />
        {{ abbreviatedTeacherName }}
      </div>

      <!-- Classroom (if different from context) -->
      <div v-if="showClassroom" class="classroom-name text-caption text-grey-6">
        {{ classroomName }}
      </div>

      <!-- Indicators -->
      <div class="indicators">
        <q-icon
          v-if="hasConflict"
          name="warning"
          size="sm"
          color="red"
          class="conflict-icon"
        >
          <q-tooltip class="bg-red text-white">{{ conflictTooltip }}</q-tooltip>
        </q-icon>
        <q-icon
          v-if="schedule.teacher_substitute_id"
          name="swap_horiz"
          size="xs"
          color="orange"
        >
          <q-tooltip>Has substitute teacher</q-tooltip>
        </q-icon>
        <q-icon
          v-if="schedule.co_teacher_id"
          name="group"
          size="xs"
          color="blue"
        >
          <q-tooltip>Has co-teacher</q-tooltip>
        </q-icon>
      </div>
    </div>

    <!-- Hover Overlay -->
    <q-menu touch-position context-menu v-if="schedule?.cst_id">
      <q-list dense style="min-width: 150px">
        <q-item clickable v-close-popup @click="$emit('edit', schedule)">
          <q-item-section avatar>
            <q-icon name="edit" />
          </q-item-section>
          <q-item-section>Edit</q-item-section>
        </q-item>
        <q-item clickable v-close-popup @click="copyTeacherLink">
          <q-item-section avatar>
            <q-icon name="link" color="primary" />
          </q-item-section>
          <q-item-section>Link</q-item-section>
        </q-item>
        <q-item clickable v-close-popup @click="$emit('clear', schedule)">
          <q-item-section avatar>
            <q-icon name="clear" color="negative" />
          </q-item-section>
          <q-item-section>Clear</q-item-section>
        </q-item>
      </q-list>
    </q-menu>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  schedule: { type: Object, default: null },
  showClassroom: { type: Boolean, default: false },
  conflictInfo: { type: Object, default: null }
})

defineEmits(['click', 'edit', 'clear'])

const subjectName = computed(() => {
  return props.schedule?.cst?.subject?.name ||
         props.schedule?.cst?.subject_name ||
         'Subject'
})

const teacherName = computed(() => {
  return props.schedule?.cst?.teacher?.name ||
         props.schedule?.cst?.teacher_name ||
         'Teacher'
})

const fullSubjectName = computed(() => {
  return props.schedule?.cst?.subject?.name ||
         props.schedule?.cst?.subject_name ||
         'Subject'
})

const fullTeacherName = computed(() => {
  return props.schedule?.cst?.teacher?.name ||
         props.schedule?.cst?.teacher_name ||
         'Teacher'
})

// Abbreviate subject name to first 3 characters
const abbreviatedSubjectName = computed(() => {
  const name = fullSubjectName.value;
  return name.length > 3 ? name.substring(0, 3) : name;
})

// Abbreviate teacher name to first 3 chars of first name + first 3 chars of last name
const abbreviatedTeacherName = computed(() => {
  const name = fullTeacherName.value;
  const parts = name.split(' ');
  
  if (parts.length === 1) {
    // If only one name part, take first 3 characters
    return parts[0].length > 3 ? parts[0].substring(0, 3) : parts[0];
  } else {
    // Take first 3 chars of first name and first 3 chars of last name
    const firstName = parts[0].length > 3 ? parts[0].substring(0, 3) : parts[0];
    const lastName = parts[parts.length - 1].length > 3 ? 
                    parts[parts.length - 1].substring(0, 3) : 
                    parts[parts.length - 1];
    return `${firstName} ${lastName}`;
  }
})

const classroomName = computed(() => {
  return props.schedule?.cst?.classroom?.name ||
         props.schedule?.cst?.classroom_name ||
         ''
})

const subjectColor = computed(() => {
  return props.schedule?.cst?.c_bg ||
         props.schedule?.cst?.color_custom ||
         '#e0e0e0'
})

const textColor = computed(() => {
  return props.schedule?.cst?.c_text ||
         props.schedule?.cst?.color_custom_text ||
         '#333333'
})

const cellClasses = computed(() => ({
  'is-empty': !props.schedule?.cst_id,
  'is-assigned': !!props.schedule?.cst_id,
  'has-substitute': !!props.schedule?.teacher_substitute_id,
  'has-conflict': !!props.conflictInfo,
  'cursor-pointer': true
}))

const hasConflict = computed(() => !!props.conflictInfo)

const conflictTooltip = computed(() => {
  if (!props.conflictInfo) return ''
  const others = props.conflictInfo.other_classrooms || []
  if (others.length === 0) return 'Teacher has a scheduling conflict'
  const classroomNames = others.map(c => `${c.classroom_name} (${c.subject_name})`).join(', ')
  return `⚠️ Conflict: ${props.conflictInfo.teacher_name} is also assigned to: ${classroomNames}`
})

const copyTeacherLink = () => {
    if (!props.schedule?.cst?.teacher_id) return
    
    const urlParams = new URLSearchParams(window.location.search)
    // Keep school param if exists
    const schoolId = urlParams.get('school')
    const schoolSlug = urlParams.get('school_slug')
    
    let path = window.location.origin + window.location.pathname
    const newParams = new URLSearchParams()
    if (schoolId) newParams.set('school', schoolId)
    if (schoolSlug) newParams.set('school_slug', schoolSlug)
    newParams.set('teacher_id', props.schedule.cst.teacher_id)
    
    const fullUrl = `${path}?${newParams.toString()}`
    
    navigator.clipboard.writeText(fullUrl).then(() => {
        $q.notify({
            type: 'positive',
            message: 'Teacher schedule link copied to clipboard!',
            position: 'top',
            timeout: 2000
        })
    })
}
</script>

<style scoped>
.timetable-cell {
 height: 100%;
 width: 100%;
  padding: 6px;
  border: 1px solid #e0e0e0;
  border-radius: 4px;
  transition: all 0.2s ease;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.timetable-cell:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
  transform: translateY(-1px);
}

.empty-cell {
  display: flex;
  flex-direction: column;
  align-items: center;
  opacity: 0.6;
}

.empty-cell:hover {
  opacity: 1;
}

.is-empty {
  background: repeating-linear-gradient(
    45deg,
    #fafafa,
    #fafafa 5px,
    #f0f0f0 5px,
    #f0f0f0 10px
  );
}

.assigned-cell {
  width: 100%;
  text-align: center;
}

.subject-badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 4px;
  margin-bottom: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: default;
}

.teacher-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  cursor: default;
}

.indicators {
  display: flex;
  gap: 4px;
  margin-top: 2px;
}

.has-substitute {
  border-left: 3px solid orange;
}

.has-conflict {
  border: 2px solid #f44336;
  background: linear-gradient(135deg, #ffebee 0%, white 50%);
  animation: conflict-pulse 2s ease-in-out infinite;
}

.conflict-icon {
  animation: shake 0.5s ease-in-out;
}

@keyframes conflict-pulse {
  0%, 100% { box-shadow: 0 0 0 0 rgba(244, 67, 54, 0.4); }
  50% { box-shadow: 0 0 0 4px rgba(244, 67, 54, 0.1); }
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-2px); }
  75% { transform: translateX(2px); }
}
</style>