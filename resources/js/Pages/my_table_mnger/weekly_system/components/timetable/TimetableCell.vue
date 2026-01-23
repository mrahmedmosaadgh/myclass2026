<template>
  <div
    class="timetable-cell"
    :class="cellClasses"
    @click="handleClick"
  >
    <!-- Empty State -->
    <div v-if="!schedule?.cst_id" class="empty-cell">
      <q-icon v-if="!readonly" name="add" size="sm" color="grey-5" />
      <span v-if="!readonly" class="text-caption text-grey-6">Assign</span>
      <span v-else class="text-caption text-grey-4">-</span>
      <div v-if="schedule?.period_order" class="period-order-badge">
        {{ schedule.period_order }}
      </div>
    </div>

    <!-- Assigned State -->
    <div v-else class="assigned-cell">
      <div v-if="schedule.period_order" class="period-order-badge is-assigned">
        {{ schedule.period_order }}
      </div>
      <!-- Subject Badge -->
      <div
        class="subject-badge"
        :style="{ backgroundColor: subjectColor, color: textColor }"
        :title="fullSubjectName"
      >
        {{ showFullName ? fullSubjectName : abbreviatedSubjectName }}
      </div>

      <!-- Teacher Name -->
      <div 
        v-if="!hideTeacher"
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
      
      <!-- Reward System Button (Teacher View - Visible Icon) -->
      <q-btn
        v-if="readonly"
        round
        dense
        size="xs"
        icon="stars"
        :color="rewardSystemDisabled ? 'grey' : 'amber'"
        :disable="rewardSystemDisabled"
        class="reward-btn"
        @click.stop="openRewardSystem"
      >
        <q-tooltip>{{ rewardSystemDisabled ? 'Select Date & Week first' : 'Reward System' }}</q-tooltip>
      </q-btn>
    </div>

    <!-- Hover Overlay (Only if not readonly) -->
    <q-menu touch-position context-menu v-if="schedule?.cst_id && !readonly">
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
  hideTeacher: { type: Boolean, default: false },
  conflictInfo: { type: Object, default: null },
  readonly: { type: Boolean, default: false },
  showFullName: { type: Boolean, default: false },
  rewardSystemDisabled: { type: Boolean, default: false }
})

const emit = defineEmits(['click', 'edit', 'clear', 'open-reward'])

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
  'cursor-pointer': !props.readonly, // Only pointer if not readonly
  'is-readonly': props.readonly
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
    if (!props.schedule?.cst?.teacher?.id) return
    
    // Create URL-safe slug from teacher name
    const teacherName = props.schedule.cst.teacher.name || 'teacher'
    const teacherSlug = teacherName
      .toLowerCase()
      .replace(/\s+/g, '-')           // Replace spaces with hyphens
      .replace(/[^\w\-]+/g, '')       // Remove non-word chars except hyphens
      .replace(/\-\-+/g, '-')         // Replace multiple hyphens with single hyphen
      .replace(/^-+/, '')             // Trim hyphens from start
      .replace(/-+$/, '');            // Trim hyphens from end
    
    const url = route('schedules.teacher.view', { 
        teacher_id: props.schedule.cst.teacher.id,
        teacher_name: teacherSlug
    })
    
    navigator.clipboard.writeText(url).then(() => {
        $q.notify({
            type: 'positive',
            message: 'Teacher schedule link copied to clipboard!',
            position: 'top',
            timeout: 2000
        })
    })
}

const handleClick = () => {
  if (!props.readonly) {
    // Only emit click if not readonly to prevent opening assignment dialogs
    // But maybe we want click for details? 
    // For now, assume readonly means no interaction or view-only
    // If we want details view in readonly, we can emit a different event or just let it bubble
    // But parent TimetableGrid emits 'cell-click' which opens AssignDialog in Editor.
    // So we should Block it if readonly.
    // However, for StudentView, maybe we want to see details? 
    // Let's block it for now.
    emit('click', props.schedule)
  }
}

const openRewardSystem = () => {
    if (!props.schedule?.cst) return
    
    const classroomId = props.schedule.cst.classroom_id
    const subjectId = props.schedule.cst.subject_id
    const period = props.schedule.period_number || props.schedule.period
    const today = new Date().toISOString().split('T')[0]
    
    // Emit event to parent instead of opening new tab
    emit('open-reward', {
        classroomId,
        subjectId,
        period,
        date: today,
        schedule: props.schedule
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
  position: relative; /* Ensure cell is relative for absolute positioning */
}

.period-order-badge {
  position: absolute;
  top: 2px;
  right: 2px;
  background-color: rgba(0, 0, 0, 0.05);
  color: #757575;
  font-size: 0.65rem;
  padding: 1px 4px;
  border-radius: 4px;
  font-weight: bold;
}

.period-order-badge.is-assigned {
  background-color: rgba(255, 255, 255, 0.8);
  color: #333;
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

.reward-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  z-index: 10;
  opacity: 0.8;
  transition: opacity 0.2s, transform 0.2s;
}

.reward-btn:hover {
  opacity: 1;
  transform: scale(1.1);
}

.timetable-cell:hover .reward-btn {
  opacity: 1;
}
</style>