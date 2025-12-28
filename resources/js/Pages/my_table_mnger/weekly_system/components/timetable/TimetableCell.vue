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
      >
        {{ subjectName }}
      </div>

      <!-- Teacher Name -->
      <div class="teacher-name text-caption">
        <q-icon name="person" size="xs" class="q-mr-xs" />
        {{ teacherName }}
      </div>

      <!-- Classroom (if different from context) -->
      <div v-if="showClassroom" class="classroom-name text-caption text-grey-6">
        {{ classroomName }}
      </div>

      <!-- Indicators -->
      <div class="indicators">
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

const props = defineProps({
  schedule: { type: Object, default: null },
  showClassroom: { type: Boolean, default: false }
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
  'cursor-pointer': true
}))
</script>

<style scoped>
.timetable-cell {
  min-height: 70px;
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
}

.teacher-name {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.indicators {
  display: flex;
  gap: 4px;
  margin-top: 2px;
}

.has-substitute {
  border-left: 3px solid orange;
}
</style>
