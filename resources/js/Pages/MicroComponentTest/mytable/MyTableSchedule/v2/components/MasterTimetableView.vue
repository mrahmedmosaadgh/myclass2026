<template>
  <div class="master-timetable-view">
    <!-- Stage and Day Selectors -->
    <div class="selectors-header">
      <StageSelector 
        v-model="selectedStage"
        @stage-change="handleStageChange"
      />
      <DaySelector 
        v-model="selectedDay"
        :stage="selectedStage"
        @day-change="handleDayChange"
      />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-state">
      <div class="loading-spinner"></div>
      <p>Loading school timetable...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <div class="error-icon">⚠️</div>
      <h3>Unable to load timetable</h3>
      <p>{{ error }}</p>
      <button @click="loadData" class="retry-btn">Retry</button>
    </div>

    <!-- Master Table -->
    <div v-else class="master-table-container">
      <div class="table-header">
        <div class="header-info">
          <h3 class="table-title">
            {{ getStageLabel(selectedStage) }} - {{ getDayLabel(selectedDay) }}
          </h3>
          <div class="period-count">
            {{ teachers.length }} teachers • {{ resolvedPeriods.length }} periods
          </div>
        </div>
        
        <!-- Actions -->
        <div class="header-actions">
          <button
            @click="showTimingManager = true"
            class="action-btn timing-btn"
            :class="{ 'has-custom': hasCustomTiming }"
          >
            ⚙️ Timing
            <span v-if="hasCustomTiming" class="custom-indicator">●</span>
          </button>
          <button @click="exportTable" class="action-btn export-btn">
            📥 Export
          </button>
        </div>
      </div>

      <!-- Responsive Table -->
      <div class="table-wrapper" ref="tableWrapper">
        <table class="master-table">
          <!-- Header Row -->
          <thead class="table-head">
            <tr>
              <th class="teacher-header">Teacher</th>
              <th
                v-for="period in resolvedPeriods"
                :key="period.id"
                class="period-header"
                :class="{
                  'break-header': period.type === 'break',
                  'activity-header': period.type === 'activity'
                }"
              >
                <div class="period-info">
                  <span class="period-title">{{ period.title }}</span>
                  <span class="period-time">{{ period.start }}-{{ period.end }}</span>
                </div>
              </th>
            </tr>
          </thead>

          <!-- Teacher Rows -->
          <tbody class="table-body">
            <tr
              v-for="teacher in teachers"
              :key="teacher.id"
              class="teacher-row"
              :class="{ 'has-current-period': hasCurrentPeriod(teacher) }"
            >
              <!-- Teacher Name Column -->
              <td class="teacher-cell">
                <div class="teacher-info">
                  <span class="teacher-name">{{ teacher.name }}</span>
                  <div class="teacher-stats">
                    <span class="busy-count">{{ getBusyPeriodsCount(teacher) }}</span>
                    <span class="free-count">{{ getFreePeriodsCount(teacher) }}</span>
                  </div>
                </div>
              </td>

              <!-- Period Cells -->
              <td
                v-for="period in resolvedPeriods"
                :key="period.id"
                class="period-cell"
                :class="[
                  getCellClass(period, teacher),
                  { 'is-current-period': isCurrentPeriod(period, teacher) }
                ]"
                @click="handleCellClick(period, teacher)"
              >
                <div class="cell-content">
                  <div v-if="getAssignment(period, teacher)" class="assignment-info">
                    <span class="class-name">{{ getAssignment(period, teacher).class }}</span>
                    <span class="subject-name">{{ getAssignment(period, teacher).subject }}</span>
                  </div>
                  <div v-else class="free-indicator">
                    <span class="free-text">Free</span>
                  </div>
                </div>

                <!-- Current Period Progress -->
                <div v-if="isCurrentPeriod(period, teacher)" class="cell-progress">
                  <div class="progress-fill" :style="{ height: `${currentPeriodProgress}%` }"></div>
                  <div class="progress-line" :style="{ top: `${currentPeriodProgress}%` }"></div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Table Footer -->
      <div class="table-footer">
        <div class="footer-stats">
          <div class="stat-item">
            <span class="stat-label">Total Teachers:</span>
            <span class="stat-value">{{ teachers.length }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Busy Now:</span>
            <span class="stat-value">{{ getCurrentBusyCount() }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Free Now:</span>
            <span class="stat-value">{{ getCurrentFreeCount() }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Timing Manager Modal -->
    <StageDayTimingManager
      v-if="showTimingManager"
      v-model="timings"
      :stage="selectedStage"
      :day="selectedDay"
      @update:modelValue="handleTimingUpdate"
      @close="showTimingManager = false"
    />

    <!-- Cell Detail Modal -->
    <div v-if="showCellDetail" class="modal-overlay" @click="closeCellDetail">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ selectedTeacher?.name }}</h3>
          <button @click="closeCellDetail" class="close-btn">×</button>
        </div>
        <div class="modal-body">
          <div class="detail-row">
            <span class="detail-label">Period:</span>
            <span class="detail-value">{{ selectedPeriod?.title }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Time:</span>
            <span class="detail-value">{{ selectedPeriod?.start }} - {{ selectedPeriod?.end }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Assignment:</span>
            <span class="detail-value">
              {{ getAssignment(selectedPeriod, selectedTeacher)?.class || 'Free' }}
            </span>
          </div>
          <div v-if="getAssignment(selectedPeriod, selectedTeacher)?.subject" class="detail-row">
            <span class="detail-label">Subject:</span>
            <span class="detail-value">
              {{ getAssignment(selectedPeriod, selectedTeacher)?.subject }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import StageSelector from './StageSelector.vue';
import DaySelector from './DaySelector.vue';
import StageDayTimingManager from './StageDayTimingManager.vue';
import { useSchoolTimetable } from '../composables/useSchoolTimetable.js';

const emit = defineEmits(['play-alert', 'notify']);

// Component state
const selectedStage = ref('prim');
const selectedDay = ref('d1');
const showTimingManager = ref(false);
const showCellDetail = ref(false);
const selectedTeacher = ref(null);
const selectedPeriod = ref(null);

// Use composable for data management
const {
  teachers,
  timings,
  resolvedPeriods,
  isLoading,
  error,
  currentPeriodProgress,
  loadData,
  loadTimingsFromStorage,
  hasCustomTiming
} = useSchoolTimetable(selectedStage, selectedDay);

const nowTick = ref(Date.now());
let progressInterval = null;

// Computed properties
const getStageLabel = (stage) => {
  const labels = {
    prim: 'Primary',
    middle: 'Middle',
    sec: 'Secondary'
  };
  return labels[stage] || stage;
};

const getDayLabel = (day) => {
  const labels = {
    d1: 'Day 1',
    d2: 'Day 2',
    d3: 'Day 3',
    d4: 'Day 4',
    d5: 'Day 5',
    d6: 'Day 6'
  };
  return labels[day] || day;
};

// Methods
const handleStageChange = (newStage) => {
  selectedStage.value = newStage;
};

const handleDayChange = (newDay) => {
  selectedDay.value = newDay;
};

const handleTimingUpdate = (newTimings) => {
  timings.value = newTimings;
  emit('notify', 'Timing Updated', `${getStageLabel(selectedStage.value)} timing has been updated`);
};

const getAssignment = (period, teacher) => {
  if (!teacher.assignments || !teacher.assignments[selectedDay.value]) {
    return null;
  }
  return teacher.assignments[selectedDay.value][period.id] || null;
};

const getCellClass = (period, teacher) => {
  const assignment = getAssignment(period, teacher);
  if (!assignment) return 'cell-free';
  if (period.type === 'break') return 'cell-break';
  if (period.type === 'activity') return 'cell-activity';
  
  // Color coding by class
  const classCode = assignment.class;
  if (classCode.includes('1') || classCode.includes('2')) return 'cell-primary';
  if (classCode.includes('3') || classCode.includes('4')) return 'cell-middle';
  if (classCode.includes('5') || classCode.includes('6')) return 'cell-secondary';
  return 'cell-other';
};

const isCurrentPeriod = (period, teacher) => {
  nowTick.value;

  if (!getAssignment(period, teacher)) {
    return false;
  }

  const now = new Date();
  const currentTotalMinutes = (now.getHours() * 60) + now.getMinutes();
  const startParts = period.start.split(':').map(Number);
  const endParts = period.end.split(':').map(Number);
  const startMinutes = (startParts[0] * 60) + startParts[1];
  const endMinutes = (endParts[0] * 60) + endParts[1];

  return currentTotalMinutes >= startMinutes && currentTotalMinutes < endMinutes;
};

const hasCurrentPeriod = (teacher) => {
  return resolvedPeriods.value.some(period => isCurrentPeriod(period, teacher));
};

const getBusyPeriodsCount = (teacher) => {
  if (!teacher.assignments || !teacher.assignments[selectedDay.value]) return 0;
  return Object.values(teacher.assignments[selectedDay.value]).filter(Boolean).length;
};

const getFreePeriodsCount = (teacher) => {
  return resolvedPeriods.value.length - getBusyPeriodsCount(teacher);
};

const getCurrentBusyCount = () => {
  return teachers.value.filter(teacher => hasCurrentPeriod(teacher)).length;
};

const getCurrentFreeCount = () => {
  return teachers.value.length - getCurrentBusyCount();
};

const handleCellClick = (period, teacher) => {
  selectedPeriod.value = period;
  selectedTeacher.value = teacher;
  showCellDetail.value = true;
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const closeCellDetail = () => {
  showCellDetail.value = false;
  selectedPeriod.value = null;
  selectedTeacher.value = null;
};

const exportTable = () => {
  // Export functionality
  const csvContent = generateCSV();
  downloadFile(csvContent, `timetable-${selectedStage.value}-${selectedDay.value}.csv`, 'text/csv');
  emit('notify', 'Export Complete', 'Timetable has been exported as CSV');
};

const generateCSV = () => {
  const headers = ['Teacher', ...resolvedPeriods.value.map(p => p.title)];
  const rows = teachers.value.map(teacher => {
    const teacherData = [teacher.name];
    resolvedPeriods.value.forEach(period => {
      const assignment = getAssignment(period, teacher);
      teacherData.push(assignment ? `${assignment.class} · ${assignment.subject}` : 'Free');
    });
    return teacherData;
  });
  
  return [headers, ...rows].map(row => row.join(',')).join('\n');
};

const downloadFile = (content, filename, mimeType) => {
  const blob = new Blob([content], { type: mimeType });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

// Lifecycle
onMounted(() => {
  loadTimingsFromStorage();
  loadData();

  progressInterval = window.setInterval(() => {
    nowTick.value = Date.now();
  }, 1000);
});

onUnmounted(() => {
  if (progressInterval) {
    clearInterval(progressInterval);
  }
});

// Watch for stage/day changes
watch([selectedStage, selectedDay], () => {
  loadData();
});
</script>

<style scoped>
.master-timetable-view {
  background: #f8fafc;
  min-height: 100vh;
}

.selectors-header {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  flex-wrap: wrap;
  align-items: flex-start;
}

.loading-state,
.error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 400px;
  padding: 2rem;
  text-align: center;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e2e8f0;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-state h3 {
  color: #dc2626;
  margin: 0 0 0.5rem 0;
}

.retry-btn {
  padding: 0.5rem 1.5rem;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 1rem;
}

.master-table-container {
  padding: 1rem;
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.header-info {
  flex: 1;
}

.table-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0 0 0.5rem 0;
}

.period-count {
  font-size: 0.875rem;
  color: #475569;
  background: #f1f5f9;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  display: inline-block;
}

.header-actions {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.action-btn {
  padding: 0.5rem 1rem;
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
}

.action-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
  transform: translateY(-1px);
}

.action-btn.has-custom {
  background: #fef3c7;
  border-color: #f59e0b;
  color: #a16207;
}

.custom-indicator {
  color: #f59e0b;
  font-size: 0.75rem;
}

.table-wrapper {
  overflow-x: auto;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.master-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  min-width: 800px;
}

.table-head {
  position: sticky;
  top: 0;
  z-index: 5;
}

.teacher-header {
  position: sticky;
  left: 0;
  z-index: 6;
  background: #475569;
  color: white;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  min-width: 150px;
}

.period-header {
  padding: 0.75rem 0.5rem;
  background: #64748b;
  color: white;
  text-align: center;
  font-weight: 600;
  font-size: 0.75rem;
  min-width: 100px;
  border-left: 1px solid #475569;
}

.period-header.break-header {
  background: #3b82f6;
}

.period-header.activity-header {
  background: #f97316;
}

.period-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.period-title {
  font-weight: 700;
  font-size: 0.8rem;
}

.period-time {
  font-size: 0.65rem;
  color: rgba(255, 255, 255, 0.92);
}

.teacher-row {
  transition: background 0.3s ease;
}

.teacher-row:hover {
  background: #f8fafc;
}

.teacher-row.has-current-period {
  background: #fef2f2;
}

.teacher-cell {
  position: sticky;
  left: 0;
  z-index: 4;
  background: white;
  border-right: 2px solid #e2e8f0;
  padding: 0.75rem 1rem;
  font-weight: 600;
  font-size: 0.875rem;
  min-width: 150px;
}

.teacher-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.teacher-name {
  font-weight: 700;
  color: #1e293b;
}

.teacher-stats {
  display: flex;
  gap: 0.5rem;
  font-size: 0.65rem;
}

.busy-count {
  color: #10b981;
  background: #d1fae5;
  padding: 0.125rem 0.375rem;
  border-radius: 4px;
}

.free-count {
  color: #64748b;
  background: #f1f5f9;
  padding: 0.125rem 0.375rem;
  border-radius: 4px;
}

.period-cell {
  padding: 0.75rem 0.5rem;
  text-align: center;
  border-left: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 100px;
  min-height: 60px;
  position: relative;
}

.period-cell:hover {
  background: #f8fafc;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.cell-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.assignment-info {
  text-align: center;
}

.class-name {
  font-weight: 700;
  font-size: 0.875rem;
  color: #1e293b;
  display: block;
}

.subject-name {
  font-size: 0.65rem;
  color: #475569;
  font-weight: 500;
}

.free-indicator {
  color: #475569;
  font-size: 0.75rem;
  font-weight: 600;
}

/* Cell type colors */
.cell-free {
  background: #f8fafc;
}

.cell-break {
  background: #dbeafe;
  color: #1e40af;
}

.cell-activity {
  background: #fed7aa;
  color: #c2410c;
}

.cell-primary {
  background: #fef3c7;
  color: #a16207;
}

.cell-middle {
  background: #dbeafe;
  color: #1e40af;
}

.cell-secondary {
  background: #f0fdf4;
  color: #166534;
}

.cell-other {
  background: #f0f9ff;
  color: #0369a1;
}

.is-current-period {
  border: 2px solid #dc2626;
  box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.2);
  z-index: 3;
}

.cell-progress {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  pointer-events: none;
  overflow: hidden;
}

.progress-fill {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(220, 38, 38, 0.1);
  transition: height 1s linear;
}

.progress-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
  background: #dc2626;
  box-shadow: 0 0 4px rgba(220, 38, 38, 0.6);
}

.table-footer {
  margin-top: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.footer-stats {
  display: flex;
  justify-content: space-around;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
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
  font-weight: 500;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-content {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  max-width: 400px;
  width: 100%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.modal-header h3 {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #64748b;
  cursor: pointer;
  padding: 0.25rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f1f5f9;
}

.detail-label {
  font-size: 0.875rem;
  color: #64748b;
  font-weight: 500;
}

.detail-value {
  font-size: 0.875rem;
  color: #1e293b;
  font-weight: 600;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .selectors-header {
    padding: 0.75rem;
    gap: 0.75rem;
  }
  
  .master-table-container {
    padding: 0.75rem;
  }
  
  .table-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-actions {
    width: 100%;
  }

  .action-btn {
    flex: 1 1 140px;
    justify-content: center;
  }
  
  .period-cell {
    min-width: 80px;
    min-height: 50px;
    padding: 0.5rem 0.25rem;
  }
  
  .class-name {
    font-size: 0.75rem;
  }
  
  .subject-name {
    font-size: 0.6rem;
  }
  
  .teacher-cell {
    min-width: 120px;
    padding: 0.5rem 0.75rem;
  }
  
  .teacher-name {
    font-size: 0.8rem;
  }
  
  .period-header {
    min-width: 80px;
    padding: 0.5rem 0.25rem;
  }
  
  .footer-stats {
    flex-direction: column;
    gap: 0.75rem;
  }
}

@media (max-width: 480px) {
  .selectors-header {
    gap: 0.5rem;
    padding: 0.5rem;
  }

  .table-title {
    font-size: 1.25rem;
  }

  .period-count {
    font-size: 0.8rem;
  }

  .action-btn {
    flex: 1 1 calc(50% - 0.25rem);
    padding: 0.625rem 0.75rem;
    font-size: 0.85rem;
  }

  .class-name {
    font-size: 0.65rem;
  }
  
  .subject-name {
    font-size: 0.55rem;
  }
  
  .period-title {
    font-size: 0.7rem;
  }
  
  .period-time {
    font-size: 0.6rem;
  }
}
</style>
