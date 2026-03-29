<template>
  <div class="table-view">
    <!-- Mobile-optimized header -->
    <div class="table-header">
      <div class="header-info">
        <h3 class="table-title">{{ isShowingAllDays ? "Full Week" : "Today" }}</h3>
        <div class="view-controls">
          <button
            @click="toggleViewMode"
            class="toggle-btn"
            :class="{ active: !isShowingAllDays }"
          >
            {{ isShowingAllDays ? "Today" : "Week" }}
          </button>
        </div>
      </div>
      
      <!-- Live time display -->
      <div class="live-time">
        <div class="clock">{{ currentTimeDisplay }}</div>
        <div v-if="activePeriodInfo" class="active-period">
          {{ activePeriodInfo.timeLeft }} left in {{ activePeriodInfo.title }}
        </div>
      </div>
    </div>

    <!-- Responsive table container -->
    <div class="table-container" ref="tableContainer">
      <div class="table-wrapper">
        <table class="schedule-table" :class="{ 'mobile-optimized': isMobile }">
          <thead class="table-head">
            <tr>
              <th class="day-header">Day</th>
              <th
                v-for="(slot, index) in timeSlots"
                :key="slot.id || index"
                class="period-header"
                :class="{
                  'break-header': slot.type === 'break',
                  'activity-header': slot.type === 'activity'
                }"
              >
                <div class="period-info">
                  <span class="period-title">{{ slot.title || `P${slot.id}` }}</span>
                  <span class="period-time">{{ slot.start }}-{{ slot.end }}</span>
                </div>
              </th>
            </tr>
          </thead>
          
          <tbody class="table-body">
            <tr
              v-for="dayRow in filteredSchedule"
              :key="dayRow.dayIndex"
              class="day-row"
              :class="{ 'current-day': dayRow.dayIndex === currentDayIndex }"
            >
              <td class="day-cell">
                <div class="day-info">
                  <span class="day-name">{{ dayRow.day }}</span>
                  <span v-if="dayRow.dayIndex === currentDayIndex" class="today-badge">Today</span>
                </div>
              </td>
              
              <td
                v-for="(slot, slotIndex) in timeSlots"
                :key="slot.id || slotIndex"
                class="subject-cell"
                :class="[
                  getCellClass(slot, dayRow),
                  { 'is-current-period': isCurrentPeriod(slot, dayRow) }
                ]"
                @click="handleCellClick(slot, dayRow)"
              >
                <div class="cell-content">
                  <span class="subject-text">{{ getCellContent(slot, dayRow) }}</span>
                  <span v-if="getCellInfo(slot, dayRow)?.nafs" class="nafs-text">
                    (NAFS M)
                  </span>
                </div>
                
                <!-- Progress indicator for current period -->
                <div v-if="isCurrentPeriod(slot, dayRow)" class="cell-progress">
                  <div class="progress-fill" :style="{ height: `${periodProgress}%` }"></div>
                  <div class="progress-line" :style="{ top: `${periodProgress}%` }"></div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile touch indicators -->
    <div v-if="isMobile && showScrollHint" class="scroll-hint">
      <span>Scroll horizontally →</span>
    </div>

    <!-- Zoom controls for mobile -->
    <div v-if="isMobile" class="zoom-controls">
      <button @click="zoomOut" class="zoom-btn" :disabled="zoomLevel <= 0.8">
        −
      </button>
      <span class="zoom-level">{{ Math.round(zoomLevel * 100) }}%</span>
      <button @click="zoomIn" class="zoom-btn" :disabled="zoomLevel >= 1.5">
        +
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  scheduleData: { type: Array, required: true },
  timeSlots: { type: Array, required: true },
  isShowingAllDays: { type: Boolean, default: true },
  currentDayIndex: { type: Number, required: true },
  currentTotalSecs: { type: Number, required: true },
  currentTimeDisplay: { type: String, default: '00:00:00' },
  selectedDay: { type: String, default: 'd1' }
});

const emit = defineEmits(['play-alert', 'notify', 'active-period-update', 'view-mode-change']);

// Reactive state
const isMobile = ref(false);
const zoomLevel = ref(1);
const showScrollHint = ref(false);
const tableContainer = ref(null);
const currentTimeDisplay = ref('00:00:00');
const activePeriodInfo = ref(null);
const periodProgress = ref(0);

const selectedDayIndex = computed(() => {
  const map = { d1: 0, d2: 1, d3: 2, d4: 3, d5: 4, d6: 5 };
  return map[props.selectedDay] ?? props.currentDayIndex;
});

// Computed properties
const filteredSchedule = computed(() => {
  if (props.isShowingAllDays) return props.scheduleData;
  
  let activeDay = selectedDayIndex.value;
  if (activeDay > 5) activeDay = 0;
  return props.scheduleData.filter(day => day.dayIndex === activeDay);
});

// Methods
const toggleViewMode = () => {
  emit('view-mode-change');
};

const getCellInfo = (slot, dayRow) => {
  return dayRow.classes.find(c => c.p === slot.id) || null;
};

const getCellContent = (slot, dayRow) => {
  if (slot.type === 'break' || slot.type === 'activity') {
    return slot.type === 'break' ? '☕ Break' : '🏃 Activity';
  }
  const info = getCellInfo(slot, dayRow);
  return info?.sub || 'Free';
};

const getCellClass = (slot, dayRow) => {
  if (slot.type === 'break') return 'cell-break';
  if (slot.type === 'activity') return 'cell-activity';
  
  const content = getCellContent(slot, dayRow);
  if (content === 'Free') return 'cell-free';
  if (content.includes('7A')) return 'cell-7a';
  if (content.includes('4A')) return 'cell-4a';
  return 'cell-other';
};

const isCurrentPeriod = (slot, dayRow) => {
  if (dayRow.dayIndex !== props.currentDayIndex) return false;
  
  const startSecs = slot.startMin * 60;
  const endSecs = slot.endMin * 60;
  return props.currentTotalSecs >= startSecs && props.currentTotalSecs < endSecs;
};

const handleCellClick = (slot, dayRow) => {
  // Haptic feedback on mobile
  if (isMobile.value && navigator.vibrate) {
    navigator.vibrate(50);
  }
  
  // Could show detailed modal here
  console.log('Cell clicked:', slot, dayRow);
};

// Zoom controls
const zoomIn = () => {
  if (zoomLevel.value < 1.5) {
    zoomLevel.value += 0.1;
    updateZoom();
  }
};

const zoomOut = () => {
  if (zoomLevel.value > 0.8) {
    zoomLevel.value -= 0.1;
    updateZoom();
  }
};

const updateZoom = () => {
  if (tableContainer.value) {
    tableContainer.value.style.transform = `scale(${zoomLevel.value})`;
    tableContainer.value.style.transformOrigin = 'top left';
  }
};

// Update live time and current period
const updateLiveInfo = () => {
  currentTimeDisplay.value = props.currentTimeDisplay || '00:00:00';
  
  // Update current period info
  const currentDayData = props.scheduleData.find(d => d.dayIndex === props.currentDayIndex);
  if (currentDayData) {
    for (const slot of props.timeSlots) {
      if (isCurrentPeriod(slot, currentDayData)) {
        const startSecs = slot.startMin * 60;
        const endSecs = slot.endMin * 60;
        const totalDuration = endSecs - startSecs;
        const elapsed = props.currentTotalSecs - startSecs;
        
        periodProgress.value = (elapsed / totalDuration) * 100;
        
        const remainingSecs = endSecs - props.currentTotalSecs;
        const mins = Math.floor(remainingSecs / 60);
        const secs = remainingSecs % 60;
        
        activePeriodInfo.value = {
          timeLeft: `${mins}:${secs.toString().padStart(2, '0')}`,
          title: slot.title || `Period ${slot.id}`
        };
        
        emit('active-period-update', activePeriodInfo.value);
        return;
      }
    }
  }
  
  activePeriodInfo.value = null;
  periodProgress.value = 0;
};

// Check if mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth <= 768;
  if (!isMobile.value) {
    zoomLevel.value = 1;
    updateZoom();
  }
};

// Lifecycle
onMounted(() => {
  checkMobile();
  updateLiveInfo();
  
  const interval = setInterval(updateLiveInfo, 1000);
  window.addEventListener('resize', checkMobile);
  
  // Show scroll hint on mobile
  if (isMobile.value) {
    setTimeout(() => {
      showScrollHint.value = true;
      setTimeout(() => {
        showScrollHint.value = false;
      }, 3000);
    }, 1000);
  }
  
  onUnmounted(() => {
    clearInterval(interval);
    window.removeEventListener('resize', checkMobile);
  });
});

// Watch for changes
watch(() => props.currentTotalSecs, updateLiveInfo);
watch(() => props.currentTimeDisplay, updateLiveInfo);
</script>

<style scoped>
.table-view {
  position: relative;
  background: #f8fafc;
  min-height: 100vh;
}

.table-header {
  position: sticky;
  top: 0;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem;
  z-index: 10;
  backdrop-filter: blur(10px);
}

.header-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.table-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.view-controls {
  display: flex;
  gap: 0.5rem;
}

.toggle-btn {
  padding: 0.5rem 1rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
}

.toggle-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.live-time {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.clock {
  font-size: 1.125rem;
  font-weight: 600;
  color: #3b82f6;
  background: #f0f9ff;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #bfdbfe;
}

.active-period {
  font-size: 0.875rem;
  font-weight: 600;
  color: #dc2626;
  background: #fef2f2;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #fecaca;
  animation: pulse-red 2s infinite;
}

@keyframes pulse-red {
  0%, 100% { box-shadow: 0 0 0 0 rgba(220, 38, 38, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(220, 38, 38, 0); }
}

.table-container {
  position: relative;
  overflow: auto;
  padding: 1rem;
  max-height: calc(100vh - 200px);
}

.table-wrapper {
  min-width: 800px; /* Minimum width for desktop */
}

.schedule-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.3s ease;
}

.table-head {
  position: sticky;
  top: 0;
  z-index: 5;
}

.day-header {
  position: sticky;
  left: 0;
  z-index: 6;
  background: #475569;
  color: white;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  min-width: 100px;
}

.period-header {
  padding: 0.75rem 0.5rem;
  background: #64748b;
  color: white;
  text-align: center;
  font-weight: 600;
  font-size: 0.75rem;
  min-width: 80px;
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

.day-row.current-day {
  background: #f0f9ff;
}

.day-cell {
  position: sticky;
  left: 0;
  z-index: 4;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
  padding: 0.75rem 1rem;
  font-weight: 600;
  font-size: 0.875rem;
  min-width: 100px;
}

.current-day .day-cell {
  background: #e0f2fe;
  border-left: 4px solid #3b82f6;
}

.day-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.day-name {
  font-weight: 700;
  color: #1e293b;
}

.today-badge {
  font-size: 0.65rem;
  background: #3b82f6;
  color: white;
  padding: 0.125rem 0.5rem;
  border-radius: 12px;
  font-weight: 600;
  align-self: flex-start;
}

.subject-cell {
  padding: 0.75rem 0.5rem;
  text-align: center;
  border-left: 1px solid #e2e8f0;
  position: relative;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 80px;
  min-height: 60px;
}

.subject-cell:hover {
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

.subject-text {
  font-weight: 700;
  font-size: 0.875rem;
  color: #1e293b;
}

.nafs-text {
  font-size: 0.65rem;
  color: #64748b;
  font-weight: 500;
}

/* Cell type colors */
.cell-break {
  background: #dbeafe;
  color: #1e40af;
}

.cell-activity {
  background: #fed7aa;
  color: #c2410c;
}

.cell-free {
  background: #f8fafc;
  color: #94a3b8;
}

.cell-7a {
  background: #fef3c7;
  color: #a16207;
}

.cell-4a {
  background: #dbeafe;
  color: #1e40af;
}

.cell-other {
  background: #f0fdf4;
  color: #166534;
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

.progress-line::before {
  content: '';
  position: absolute;
  left: 0;
  top: -3px;
  width: 6px;
  height: 6px;
  background: #dc2626;
  border-radius: 50%;
  box-shadow: 0 0 3px rgba(220, 38, 38, 0.8);
}

.scroll-hint {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 24px;
  font-size: 0.875rem;
  font-weight: 600;
  pointer-events: none;
  animation: fade-in-out 3s ease;
}

@keyframes fade-in-out {
  0%, 100% { opacity: 0; }
  20%, 80% { opacity: 1; }
}

.zoom-controls {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: white;
  padding: 0.5rem;
  border-radius: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  z-index: 20;
}

.zoom-btn {
  width: 32px;
  height: 32px;
  border: none;
  background: #f1f5f9;
  color: #64748b;
  border-radius: 50%;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.zoom-btn:not(:disabled):hover {
  background: #e2e8f0;
  color: #475569;
}

.zoom-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.zoom-level {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  min-width: 45px;
  text-align: center;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .table-container {
    padding: 0.5rem;
  }
  
  .subject-cell {
    min-width: 70px;
    min-height: 50px;
    padding: 0.5rem 0.25rem;
  }
  
  .subject-text {
    font-size: 0.75rem;
  }
  
  .period-header {
    min-width: 70px;
    padding: 0.5rem 0.25rem;
  }
  
  .day-cell {
    min-width: 80px;
    padding: 0.5rem 0.75rem;
  }
  
  .zoom-controls {
    bottom: 1rem;
    right: 1rem;
  }
}

@media (max-width: 480px) {
  .subject-text {
    font-size: 0.65rem;
  }
  
  .period-title {
    font-size: 0.7rem;
  }
  
  .period-time {
    font-size: 0.6rem;
  }
}
</style>
