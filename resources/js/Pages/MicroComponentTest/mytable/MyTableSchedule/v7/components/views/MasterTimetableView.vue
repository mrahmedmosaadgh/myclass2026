<template>
  <div class="master-timetable-view">
    <!-- Stage Selector -->
    <div class="stage-selector">
      <button
        v-for="stage in stages"
        :key="stage.id"
        class="stage-btn"
        :class="{ active: selectedStage === stage.id }"
        @click="selectedStage = stage.id"
      >
        {{ stage.label }}
      </button>
    </div>

    <!-- Day Selector -->
    <div class="day-selector">
      <button
        v-for="day in days"
        :key="day.id"
        class="day-btn"
        :class="{ active: selectedDay === day.id }"
        @click="selectedDay = day.id"
      >
        {{ day.label }}
      </button>
    </div>

    <!-- Filter Controls -->
    <div class="filter-controls">
      <div class="filter-group">
        <label class="filter-label">Filter by</label>
        <select v-model="filterType" class="filter-select">
          <option value="">All</option>
          <option value="teacher">Teacher</option>
          <option value="class">Class</option>
          <option value="subject">Subject</option>
        </select>
        <input
          v-if="filterType"
          v-model="filterValue"
          type="text"
          class="filter-input"
          :placeholder="`Enter ${filterType}...`"
        />
      </div>
      <div class="filter-group">
        <label class="filter-label">Show</label>
        <select v-model="displayMode" class="filter-select">
          <option value="all">All</option>
          <option value="busy">Busy Only</option>
          <option value="free">Free Only</option>
        </select>
      </div>
    </div>

    <!-- Timetable Grid -->
    <div class="timetable-container">
      <table class="timetable-table">
        <thead>
          <tr>
            <th class="header-teacher">Teacher</th>
            <th
              v-for="slot in resolvedTimeSlots.value"
              :key="slot.id"
              class="header-period"
              :class="{ 'break-header': slot.type === 'break' }"
            >
              <div class="period-header">
                <span class="period-title">{{ slot.title }}</span>
                <span class="time-range">{{ slot.start }} - {{ slot.end }}</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="teacher in filteredTeachers"
            :key="teacher.id"
            class="teacher-row"
          >
            <td class="teacher-cell">
              <div class="teacher-info">
                <span class="teacher-name">{{ teacher.name }}</span>
                <span class="teacher-id">{{ teacher.id }}</span>
              </div>
            </td>
            <td
              v-for="slot in resolvedTimeSlots.value"
              :key="`${teacher.id}-${slot.id}`"
              class="assignment-cell"
              :class="getAssignmentClass(slot.id, teacher)"
            >
              <div v-if="getAssignment(slot.id, teacher)" class="assignment">
                <span class="class-name">{{ getAssignment(slot.id, teacher)?.class }}</span>
                <span class="subject-name">{{ getAssignment(slot.id, teacher)?.subject }}</span>
              </div>
              <span v-else class="free-indicator">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Summary Stats -->
    <div class="stats-summary">
      <div class="stat-item">
        <span class="stat-label">Total Teachers</span>
        <span class="stat-value">{{ filteredTeachers.length }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Busy Slots</span>
        <span class="stat-value">{{ busySlotCount }}</span>
      </div>
      <div class="stat-item">
        <span class="stat-label">Free Slots</span>
        <span class="stat-value">{{ freeSlotCount }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, inject } from 'vue';
import { useAppStore } from '../../composables/useAppStore';

const store = useAppStore();
const resolvedTimeSlots = inject('resolvedTimeSlots');

const stages = [
  { id: 'prim', label: 'Primary' },
  { id: 'middle', label: 'Middle' },
  { id: 'sec', label: 'Secondary' }
];

const days = [
  { id: 'd1', label: 'Day 1' },
  { id: 'd2', label: 'Day 2' },
  { id: 'd3', label: 'Day 3' },
  { id: 'd4', label: 'Day 4' },
  { id: 'd5', label: 'Day 5' },
  { id: 'd6', label: 'Day 6' }
];

const selectedStage = ref('prim');
const selectedDay = ref('d1');
const filterType = ref('');
const filterValue = ref('');
const displayMode = ref('all');

const currentDayData = computed(() => {
  const stageData = store.schoolTimetable.value?.stages?.[selectedStage.value];
  return stageData?.days?.[selectedDay.value]?.teachers || [];
});

const filteredTeachers = computed(() => {
  let teachers = currentDayData.value;

  if (filterType.value && filterValue.value) {
    const filter = filterValue.value.toLowerCase();
    teachers = teachers.filter(teacher => {
      if (filterType.value === 'teacher') {
        return teacher.name.toLowerCase().includes(filter);
      }
      // For class/subject, we need to check assignments
      return Object.values(teacher.assignments[selectedDay.value] || {}).some(assignment => {
        if (!assignment) return false;
        if (filterType.value === 'class') {
          return assignment.class?.toLowerCase().includes(filter);
        }
        if (filterType.value === 'subject') {
          return assignment.subject?.toLowerCase().includes(filter);
        }
        return false;
      });
    });
  }

  return teachers;
});

const getAssignment = (periodId, teacher) => {
  const assignments = teacher.assignments[selectedDay.value] || {};
  return assignments[periodId];
};

const getAssignmentClass = (periodId, teacher) => {
  const assignment = getAssignment(periodId, teacher);
  if (!assignment) return 'free';
  return 'busy';
};

const busySlotCount = computed(() => {
  let count = 0;
  filteredTeachers.value.forEach(teacher => {
    resolvedTimeSlots.value.forEach(slot => {
      if (getAssignment(slot.id, teacher)) count++;
    });
  });
  return count;
});

const freeSlotCount = computed(() => {
  return filteredTeachers.value.length * resolvedTimeSlots.value.length - busySlotCount.value;
});

const shouldShowAssignment = (assignment) => {
  if (!assignment) return displayMode.value === 'free';
  if (displayMode.value === 'busy') return true;
  if (displayMode.value === 'free') return false;
  return true;
};
</script>

<style scoped>
.master-timetable-view {
  padding: 1rem;
  overflow-x: auto;
}

.stage-selector,
.day-selector {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.stage-btn,
.day-btn {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #475569;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.stage-btn:hover,
.day-btn:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
}

.stage-btn.active,
.day-btn.active {
  background: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.filter-controls {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 600;
  white-space: nowrap;
}

.filter-select,
.filter-input {
  padding: 0.4rem 0.6rem;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: white;
  color: #1e293b;
  font-size: 0.85rem;
}

.filter-input { min-width: 120px; }

.timetable-container {
  min-width: 800px;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  margin-bottom: 1rem;
}

.timetable-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.8rem;
}

.header-teacher {
  width: 140px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.75rem;
  text-align: left;
  font-weight: 700;
  color: #1e293b;
}

.header-period {
  min-width: 80px;
  background: #f8fafc;
  border-bottom: 2px solid #e2e8f0;
  padding: 0.5rem;
  text-align: center;
  font-weight: 600;
  color: #475569;
}

.header-period.break-header {
  background: #f1f5f9;
}

.period-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.15rem;
}

.period-title {
  font-size: 0.75rem;
}

.time-range {
  font-size: 0.6rem;
  opacity: 0.7;
}

.teacher-row {
  border-bottom: 1px solid #f1f5f9;
}

.teacher-cell {
  padding: 0.75rem;
  background: #f8fafc;
  border-right: 1px solid #e2e8f0;
  font-weight: 600;
  color: #1e293b;
}

.teacher-info {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.teacher-name {
  font-size: 0.85rem;
}

.teacher-id {
  font-size: 0.65rem;
  color: #64748b;
  font-family: monospace;
}

.assignment-cell {
  padding: 0.5rem;
  text-align: center;
  border-right: 1px solid #f1f5f9;
  vertical-align: middle;
}

.assignment-cell:last-child {
  border-right: none;
}

.assignment-cell.busy {
  background: #f0f9ff;
  color: #0369a1;
}

.assignment-cell.free {
  background: #f8fafc;
  color: #94a3b8;
}

.assignment {
  display: flex;
  flex-direction: column;
  gap: 0.1rem;
}

.class-name {
  font-weight: 600;
  font-size: 0.75rem;
}

.subject-name {
  font-size: 0.65rem;
  opacity: 0.8;
}

.free-indicator {
  color: #cbd5e1;
  font-size: 0.9rem;
}

.stats-summary {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.stat-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.stat-label {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
}

@media (max-width: 640px) {
  .master-timetable-view {
    padding: 0.5rem;
  }

  .filter-controls {
    flex-direction: column;
    gap: 0.75rem;
  }

  .filter-group {
    flex-wrap: wrap;
  }

  .stats-summary {
    flex-direction: column;
    gap: 0.75rem;
  }
}

@media (prefers-color-scheme: dark) {
  .timetable-container {
    background: #1e293b;
  }

  .header-teacher,
  .teacher-cell {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }

  .header-period {
    background: #334155;
    border-color: #475569;
    color: #cbd5e1;
  }

  .header-period.break-header {
    background: #475569;
  }

  .teacher-row {
    border-color: #334155;
  }

  .assignment-cell {
    border-color: #334155;
  }

  .assignment-cell.busy {
    background: #1e3a8a;
    color: #7dd3fc;
  }

  .assignment-cell.free {
    background: #334155;
    color: #64748b;
  }

  .stage-btn,
  .day-btn,
  .filter-select,
  .filter-input {
    background: #334155;
    border-color: #475569;
    color: #f1f5f9;
  }

  .stats-summary {
    background: #1e293b;
  }

  .stat-value { color: #f1f5f9; }
}
</style>
