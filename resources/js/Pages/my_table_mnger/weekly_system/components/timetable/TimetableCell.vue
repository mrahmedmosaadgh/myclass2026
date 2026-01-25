 <template>
  <div
    class="timetable-cell"
    :class="cellClasses"
    @click="handleClick"
  >
    <!-- EMPTY -->
    <div v-if="!schedule?.cst_id" class="cell-empty">
      <q-icon v-if="!readonly" name="add" size="sm" />
      <span v-if="!readonly" class="assign-text">Assign</span>
      <span v-else>-</span>

      <q-badge v-if="schedule?.period_order" class="period-badge empty">
        {{ schedule.period_order }}
      </q-badge>
    </div>

    <!-- ASSIGNED -->
    <div v-else class="cell-content w-full h-full shadow-sm  " 
      :style="{ backgroundColor: subjectColor, color: textColor }"
    >
      <!-- Period -->
      <q-badge
        v-if="schedule.period_order"
        class="period-badge assigned"
        :style="{ backgroundColor: textColor, color: subjectColor }"
      >
        {{ schedule.period_order }}
      </q-badge>

      <!-- Subject -->
      <div
        class="subject text-shadow mt-4"
        :title="fullSubjectName"
      >
        {{ showFullName ? fullSubjectName : abbreviatedSubjectName }}
      </div>

      <!-- Teacher -->
      <div v-if="!hideTeacher" class="teacher text-shadow">
        <q-icon name="person" size="xs" :style="{ color: textColor }" />
        {{ abbreviatedTeacherName }}
      </div>

      <!-- Classroom -->
      <div v-if="showClassroom" class="classroom w-full flex justify-center items-center text-shadow">
          <span class="p-1 text-xl font-black">
            {{ classroomName }}
          </span>
      </div>

      <!-- Indicators -->
      <div class="indicators">
        <q-icon
          v-if="hasConflict"
          name="warning"
          color="negative"
          class="shadow-1"
        >
          <q-tooltip>{{ conflictTooltip }}</q-tooltip>
        </q-icon>

        <q-icon
          v-if="schedule.teacher_substitute_id"
          name="swap_horiz"
          color="orange"
          class="shadow-1"
        >
          <q-tooltip>Substitute</q-tooltip>
        </q-icon>

        <q-icon
          v-if="schedule.co_teacher_id"
          name="group"
          color="primary"
          class="shadow-1"
        >
          <q-tooltip>Co-teacher</q-tooltip>
        </q-icon>
      </div>

      <!-- Reward -->
      <q-btn
        v-if="readonly"
        round
        dense
        size="xs"
        icon="stars"
        :disable="rewardSystemDisabled"
        color="amber"
        class="reward-btn glass-effect"
        @click.stop="openRewardSystem"
      />
    </div>

    <!-- Context Menu -->
    <q-menu v-if="schedule?.cst_id && !readonly" context-menu>
      <q-list dense>
        <q-item clickable v-close-popup @click="$emit('edit', schedule)">
          <q-icon name="edit" class="q-mr-sm" /> Edit
        </q-item>
        <q-item clickable v-close-popup @click="copyTeacherLink">
          <q-icon name="link" class="q-mr-sm text-primary" /> Link
        </q-item>
        <q-item clickable v-close-popup @click="$emit('clear', schedule)">
          <q-icon name="clear" class="q-mr-sm text-negative" /> Clear
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
    emit('click', props.schedule)
  }
}

const openRewardSystem = (tab = 'positive') => {
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
        schedule: props.schedule,
        initialTab: tab
    })
}

</script>

<style scoped>
.timetable-cell {
  height: 100%;
  width: 100%;
  min-height: 80px;
  background: #ffffff;
  border: 1px solid #eef2f6;
  border-radius: 10px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.timetable-cell:hover:not(.is-readonly) {
  border-color: #cbd5e1;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transform: translateY(-3px);
  z-index: 10;
}

/* Base Shadow utility */
.text-shadow {
  text-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

/* EMPTY STATE */
.cell-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
  color: #64748b;
  gap: 4px;
}

.assign-text {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* ASSIGNED STATE */
.cell-content {
  flex: 1;
  padding: 10px 8px 8px;
  display: flex;
  flex-direction: column;
  border-radius: inherit;
}

.subject {
  font-size: 0.85rem;
  font-weight: 900;
  line-height: 1.1;
  margin-bottom: 4px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
}

.teacher, .classroom {
  font-size: 0.65rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.classroom {
  font-style: italic;
  margin-top: 2px;
}

/* PERIOD BADGE */
.period-badge {
  position: absolute;
  top: 4px;
  left: 4px;
  font-size: 0.6rem;
  font-weight: 900;
  padding: 2px 6px;
  min-height: auto;
  border-radius: 6px;
  z-index: 5;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.period-badge.empty {
  background: #cbd5e1;
  color: #475569;
}

.period-badge.assigned {
  border: 1px solid rgba(255,255,255,0.2);
}

/* INDICATORS */
.indicators {
  display: flex;
  gap: 4px;
  margin-top: auto;
  padding-top: 6px;
}

.indicators .q-icon {
  font-size: 0.85rem;
}

.has-conflict {
  background: #fff1f2;
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0%, 100% { border-color: #fca5a5; }
  50% { border-color: #ef4444; }
}

/* REWARD & GLASS EFFECTS */
.reward-btn {
  position: absolute;
  bottom: 6px;
  right: 6px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.glass-effect {
  background: rgba(255, 255, 255, 0.15) !important;
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
</style>