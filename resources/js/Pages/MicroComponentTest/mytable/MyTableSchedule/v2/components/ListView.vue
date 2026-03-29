<template>
  <div class="list-view">
    <!-- Header with current period focus -->
    <div class="list-header">
      <div class="header-top">
        <h3 class="header-title">{{ currentDayName }}</h3>
        <div class="live-status">
          <div class="current-time">{{ currentTimeDisplay }}</div>
          <div v-if="currentPeriod" class="current-period-info">
            <span class="period-name">{{ currentPeriod.title }}</span>
            <span class="time-remaining">{{ currentPeriod.timeLeft }} left</span>
          </div>
        </div>
      </div>
      
      <!-- Quick navigation -->
      <div class="day-selector">
        <button
          v-for="dayData in scheduleData"
          :key="dayData.dayIndex"
          @click="selectDay(dayData.dayIndex)"
          :class="['day-btn', { active: selectedDayIndex === dayData.dayIndex }]"
        >
          {{ dayData.day.substring(0, 3) }}
          <span v-if="dayData.dayIndex === currentDayIndex" class="today-indicator">●</span>
        </button>
      </div>
    </div>

    <!-- Current period card (always at top if exists) -->
    <div v-if="currentPeriod" class="current-period-card">
      <div class="current-period-header">
        <div class="period-badge">NOW</div>
        <div class="period-progress-info">
          <div class="progress-bar">
            <div class="progress-fill" :style="{ width: `${currentPeriodProgress}%` }"></div>
          </div>
          <span class="progress-text">{{ Math.round(currentPeriodProgress) }}% complete</span>
        </div>
      </div>
      <div class="current-period-content">
        <div class="period-details">
          <h4 class="period-title">{{ currentPeriod.title }}</h4>
          <p class="period-time">{{ currentPeriod.startTime }} - {{ currentPeriod.endTime }}</p>
          <div class="period-subject">
            <span class="subject-name">{{ currentPeriod.subject }}</span>
            <span v-if="currentPeriod.nafs" class="nafs-badge">NAFS M</span>
          </div>
        </div>
        <div class="period-actions">
          <button @click="playAlertSound" class="action-btn sound-btn">
            🔔
          </button>
        </div>
      </div>
    </div>

    <!-- Periods list -->
    <div class="periods-list">
      <div
        v-for="(period, index) in periodsList"
        :key="period.id"
        class="period-item"
        :class="{
          'is-current': period.isCurrent,
          'is-past': period.isPast,
          'is-future': period.isFuture,
          'is-break': period.type === 'break',
          'is-activity': period.type === 'activity'
        }"
      >
        <!-- Collapsible period card -->
        <div
          class="period-card"
          @click="togglePeriodExpanded(index)"
          :class="{ expanded: period.expanded }"
        >
          <div class="period-main">
            <div class="period-time-block">
              <span class="period-time">{{ period.startTime }}</span>
              <span class="period-duration">{{ period.duration }}min</span>
            </div>
            
            <div class="period-info">
              <div class="period-title-row">
                <h4 class="period-title">{{ period.title }}</h4>
                <div class="period-status">
                  <span v-if="period.isCurrent" class="status-badge current">LIVE</span>
                  <span v-else-if="period.isPast" class="status-badge past">DONE</span>
                  <span v-else class="status-badge future">UPCOMING</span>
                </div>
              </div>
              
              <div class="period-subject-row">
                <span class="subject-text">{{ period.subject }}</span>
                <span v-if="period.nafs" class="nafs-indicator">(NAFS M)</span>
              </div>
            </div>
            
            <div class="period-expand">
              <span class="expand-icon">{{ period.expanded ? '−' : '+' }}</span>
            </div>
          </div>
          
          <!-- Expanded content -->
          <div v-if="period.expanded" class="period-expanded">
            <div class="expanded-details">
              <div class="detail-row">
                <span class="detail-label">Time:</span>
                <span class="detail-value">{{ period.startTime }} - {{ period.endTime }}</span>
              </div>
              <div class="detail-row">
                <span class="detail-label">Duration:</span>
                <span class="detail-value">{{ period.duration }} minutes</span>
              </div>
              <div v-if="period.subject !== 'Free'" class="detail-row">
                <span class="detail-label">Type:</span>
                <span class="detail-value">{{ period.type === 'lesson' ? 'Lesson' : period.type }}</span>
              </div>
              <div v-if="period.isCurrent" class="detail-row">
                <span class="detail-label">Progress:</span>
                <div class="mini-progress-bar">
                  <div class="mini-progress-fill" :style="{ width: `${currentPeriodProgress}%` }"></div>
                </div>
              </div>
            </div>
            
            <div class="expanded-actions">
              <button @click.stop="setNotification(period)" class="action-btn">
                🔔 Set Alert
              </button>
              <button v-if="period.isFuture" @click.stop="addToCalendar(period)" class="action-btn">
                📅 Add to Calendar
              </button>
            </div>
          </div>
        </div>
        
        <!-- Visual timeline connector -->
        <div v-if="index < periodsList.length - 1" class="timeline-connector"></div>
      </div>
    </div>

    <!-- Jump to current period button -->
    <button
      v-if="currentPeriod && !isCurrentPeriodVisible"
      @click="scrollToCurrentPeriod"
      class="jump-to-current-btn"
    >
      ↓ Jump to Current
    </button>

    <!-- Bottom sheet for notifications -->
    <div v-if="showNotificationSheet" class="bottom-sheet" @click="closeNotificationSheet">
      <div class="sheet-content" @click.stop>
        <div class="sheet-header">
          <h3>Set Notification</h3>
          <button @click="closeNotificationSheet" class="close-btn">×</button>
        </div>
        <div class="sheet-body">
          <p>Get notified before {{ notificationPeriod?.title }} starts</p>
          <div class="notification-options">
            <button
              v-for="option in notificationOptions"
              :key="option.value"
              @click="scheduleNotification(option.value)"
              class="option-btn"
            >
              {{ option.label }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
  scheduleData: { type: Array, required: true },
  timeSlots: { type: Array, required: true },
  currentDayIndex: { type: Number, required: true },
  currentTotalSecs: { type: Number, required: true },
  currentTimeDisplay: { type: String, default: '00:00:00' },
  selectedDay: { type: String, default: 'd1' }
});

const emit = defineEmits(['play-alert', 'notify', 'active-period-update']);

// State
const selectedDayIndex = ref({ d1: 0, d2: 1, d3: 2, d4: 3, d5: 4, d6: 5 }[props.selectedDay] ?? props.currentDayIndex);
const currentTimeDisplay = ref('00:00:00');
const currentPeriod = ref(null);
const currentPeriodProgress = ref(0);
const showNotificationSheet = ref(false);
const notificationPeriod = ref(null);
const isCurrentPeriodVisible = ref(false);

// Notification options
const notificationOptions = [
  { label: '5 min before', value: 5 },
  { label: '10 min before', value: 10 },
  { label: '15 min before', value: 15 },
  { label: '30 min before', value: 30 }
];

const selectedDayPropIndex = computed(() => {
  const map = { d1: 0, d2: 1, d3: 2, d4: 3, d5: 4, d6: 5 };
  return map[props.selectedDay] ?? props.currentDayIndex;
});

// Computed properties
const currentDayName = computed(() => {
  const dayData = props.scheduleData.find(d => d.dayIndex === selectedDayIndex.value);
  return dayData?.day || 'Today';
});

const periodsList = computed(() => {
  const dayData = props.scheduleData.find(d => d.dayIndex === selectedDayIndex.value);
  if (!dayData) return [];
  
  return props.timeSlots.map(slot => {
    const classInfo = dayData.classes.find(c => c.p === slot.id);
    const isCurrent = isCurrentPeriodForSlot(slot, dayData);
    const isPast = !isCurrent && slot.endMin * 60 < props.currentTotalSecs;
    const isFuture = !isCurrent && slot.startMin * 60 > props.currentTotalSecs;
    
    return {
      id: slot.id,
      title: slot.title || `Period ${slot.id}`,
      startTime: slot.start,
      endTime: slot.end,
      duration: Math.floor((slot.endMin - slot.startMin)),
      type: slot.type || 'lesson',
      subject: getSubjectForSlot(slot, dayData),
      nafs: classInfo?.nafs || false,
      isCurrent,
      isPast,
      isFuture,
      expanded: false,
      slot,
      dayData
    };
  });
});

// Methods
const selectDay = (dayIndex) => {
  selectedDayIndex.value = dayIndex;
  // Reset expanded states
  periodsList.value.forEach(period => {
    period.expanded = false;
  });
};

const getSubjectForSlot = (slot, dayData) => {
  if (slot.type === 'break') return 'Break Time';
  if (slot.type === 'activity') return 'Activity Time';
  const classInfo = dayData.classes.find(c => c.p === slot.id);
  return classInfo?.sub || 'Free';
};

const isCurrentPeriodForSlot = (slot, dayData) => {
  if (dayData.dayIndex !== props.currentDayIndex) return false;
  const startSecs = slot.startMin * 60;
  const endSecs = slot.endMin * 60;
  return props.currentTotalSecs >= startSecs && props.currentTotalSecs < endSecs;
};

const togglePeriodExpanded = (index) => {
  periodsList.value[index].expanded = !periodsList.value[index].expanded;
  
  // Haptic feedback on mobile
  if (navigator.vibrate) {
    navigator.vibrate(50);
  }
};

const playAlertSound = () => {
  emit('play-alert');
};

const setNotification = (period) => {
  notificationPeriod.value = period;
  showNotificationSheet.value = true;
};

const closeNotificationSheet = () => {
  showNotificationSheet.value = false;
  notificationPeriod.value = null;
};

const scheduleNotification = (minutesBefore) => {
  if (!notificationPeriod.value) return;
  
  const period = notificationPeriod.value;
  const [hours, minutes] = period.startTime.split(':').map(Number);
  const notificationTime = new Date();
  notificationTime.setHours(hours, minutes - minutesBefore, 0, 0);
  
  const now = new Date();
  if (notificationTime > now) {
    const delayMs = notificationTime - now;
    setTimeout(() => {
      emit('notify', `${period.title} Starting`, `${period.subject} starts in ${minutesBefore} minutes`);
      if (navigator.vibrate) {
        navigator.vibrate([200, 100, 200]);
      }
    }, delayMs);
    
    emit('notify', 'Notification Set', `You'll be alerted ${minutesBefore} minutes before ${period.title}`);
  }
  
  closeNotificationSheet();
};

const addToCalendar = (period) => {
  // Placeholder for calendar integration
  const [hours, minutes] = period.startTime.split(':').map(Number);
  const [endHours, endMinutes] = period.endTime.split(':').map(Number);
  
  const startDate = new Date();
  startDate.setHours(hours, minutes, 0, 0);
  
  const endDate = new Date();
  endDate.setHours(endHours, endMinutes, 0, 0);
  
  // Create calendar URL (would need proper implementation)
  const calendarUrl = `https://calendar.google.com/calendar/render?action=TEMPLATE&text=${encodeURIComponent(period.title)}&dates=${startDate.toISOString().replace(/[-:]/g, '').split('.')[0]}Z/${endDate.toISOString().replace(/[-:]/g, '').split('.')[0]}Z&details=${encodeURIComponent(period.subject)}`;
  
  window.open(calendarUrl, '_blank');
};

const scrollToCurrentPeriod = () => {
  const currentElement = document.querySelector('.period-item.is-current');
  if (currentElement) {
    currentElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
    isCurrentPeriodVisible.value = true;
  }
};

const updateCurrentPeriod = () => {
  currentTimeDisplay.value = props.currentTimeDisplay || '00:00:00';
  
  // Find current period
  const dayData = props.scheduleData.find(d => d.dayIndex === props.currentDayIndex);
  if (dayData) {
    for (const slot of props.timeSlots) {
      if (isCurrentPeriodForSlot(slot, dayData)) {
        const classInfo = dayData.classes.find(c => c.p === slot.id);
        const startSecs = slot.startMin * 60;
        const endSecs = slot.endMin * 60;
        const totalDuration = endSecs - startSecs;
        const elapsed = props.currentTotalSecs - startSecs;
        
        currentPeriodProgress.value = (elapsed / totalDuration) * 100;
        
        const remainingSecs = endSecs - props.currentTotalSecs;
        const mins = Math.floor(remainingSecs / 60);
        const secs = remainingSecs % 60;
        
        currentPeriod.value = {
          title: slot.title || `Period ${slot.id}`,
          startTime: slot.start,
          endTime: slot.end,
          subject: getSubjectForSlot(slot, dayData),
          nafs: classInfo?.nafs || false,
          type: slot.type || 'lesson',
          timeLeft: `${mins}:${secs.toString().padStart(2, '0')}`
        };
        
        emit('active-period-update', currentPeriod.value);
        return;
      }
    }
  }
  
  currentPeriod.value = null;
  currentPeriodProgress.value = 0;
};

// Intersection Observer for current period visibility
const setupIntersectionObserver = () => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          isCurrentPeriodVisible.value = true;
        }
      });
    },
    { threshold: 0.5 }
  );
  
  const currentElement = document.querySelector('.period-item.is-current');
  if (currentElement) {
    observer.observe(currentElement);
  }
};

// Lifecycle
onMounted(() => {
  updateCurrentPeriod();
  const interval = setInterval(updateCurrentPeriod, 1000);
  
  // Set up intersection observer after DOM is ready
  setTimeout(setupIntersectionObserver, 100);
  
  onUnmounted(() => {
    clearInterval(interval);
  });
});

// Watch for day changes
watch(() => props.selectedDay, () => {
  selectedDayIndex.value = selectedDayPropIndex.value;
  periodsList.value.forEach(period => {
    period.expanded = false;
  });
});

watch(() => props.currentTotalSecs, updateCurrentPeriod);
watch(() => props.currentTimeDisplay, updateCurrentPeriod);
</script>

<style scoped>
.list-view {
  background: #f8fafc;
  min-height: 100vh;
  padding-bottom: 2rem;
}

.list-header {
  position: sticky;
  top: 0;
  background: white;
  border-bottom: 1px solid #e2e8f0;
  padding: 1rem;
  z-index: 10;
  backdrop-filter: blur(10px);
}

.header-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.header-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.live-status {
  text-align: right;
}

.current-time {
  font-size: 1.25rem;
  font-weight: 600;
  color: #3b82f6;
  background: #f0f9ff;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: 1px solid #bfdbfe;
}

.current-period-info {
  margin-top: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.period-name {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.875rem;
}

.time-remaining {
  font-weight: 600;
  color: #dc2626;
  font-size: 0.75rem;
  background: #fef2f2;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  display: inline-block;
}

.day-selector {
  display: flex;
  gap: 0.5rem;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.day-btn {
  position: relative;
  padding: 0.75rem 1rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 60px;
  text-align: center;
}

.day-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.today-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  color: #10b981;
  font-size: 0.5rem;
}

.current-period-card {
  margin: 1rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3);
  animation: pulse-blue 2s infinite;
}

@keyframes pulse-blue {
  0%, 100% { box-shadow: 0 8px 24px rgba(59, 130, 246, 0.3); }
  50% { box-shadow: 0 8px 32px rgba(59, 130, 246, 0.5); }
}

.current-period-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.period-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 700;
  font-size: 0.75rem;
  backdrop-filter: blur(10px);
}

.period-progress-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
}

.progress-bar {
  width: 100px;
  height: 6px;
  background: rgba(255, 255, 255, 0.3);
  border-radius: 3px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: white;
  transition: width 1s linear;
}

.progress-text {
  font-size: 0.75rem;
  font-weight: 600;
}

.current-period-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.period-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0 0 0.25rem 0;
}

.period-time {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.96);
  margin: 0 0 0.5rem 0;
}

.period-subject {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subject-name {
  font-size: 1.125rem;
  font-weight: 600;
}

.nafs-badge {
  background: rgba(255, 255, 255, 0.2);
  padding: 0.125rem 0.5rem;
  border-radius: 8px;
  font-size: 0.65rem;
  font-weight: 600;
}

.action-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 0.5rem;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1.25rem;
  transition: all 0.3s ease;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: scale(1.1);
}

.periods-list {
  padding: 0 1rem;
}

.period-item {
  margin-bottom: 1rem;
}

.period-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  transition: all 0.3s ease;
  overflow: hidden;
}

.period-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.period-card.expanded {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
}

.period-main {
  display: flex;
  align-items: center;
  padding: 1rem;
  gap: 1rem;
}

.period-time-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 60px;
}

.period-time {
  font-weight: 700;
  color: #1e293b;
  font-size: 0.875rem;
}

.period-duration {
  font-size: 0.65rem;
  color: #475569;
}

.period-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.period-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.period-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.period-status {
  display: flex;
  gap: 0.5rem;
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.current {
  background: #dc2626;
  color: white;
  animation: pulse-red 2s infinite;
}

.status-badge.past {
  background: #64748b;
  color: white;
}

.status-badge.future {
  background: #10b981;
  color: white;
}

.period-subject-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.subject-text {
  font-size: 1rem;
  color: #475569;
  font-weight: 500;
}

.nafs-indicator {
  font-size: 0.75rem;
  color: #475569;
  font-weight: 500;
}

.period-expand {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  background: #f1f5f9;
  border-radius: 50%;
  font-size: 1.25rem;
  font-weight: 700;
  color: #64748b;
  transition: all 0.3s ease;
}

.period-card.expanded .period-expand {
  background: #3b82f6;
  color: white;
}

.period-expanded {
  border-top: 1px solid #e2e8f0;
  padding: 1rem;
  background: #f8fafc;
}

.expanded-details {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
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

.mini-progress-bar {
  width: 100px;
  height: 4px;
  background: #e2e8f0;
  border-radius: 2px;
  overflow: hidden;
}

.mini-progress-fill {
  height: 100%;
  background: #3b82f6;
  transition: width 1s linear;
}

.expanded-actions {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.expanded-actions .action-btn {
  background: #3b82f6;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
}

.timeline-connector {
  width: 2px;
  height: 20px;
  background: linear-gradient(to bottom, #e2e8f0, transparent);
  margin: 0 auto;
}

/* Period type variations */
.period-item.is-break .period-card {
  border-left: 4px solid #3b82f6;
}

.period-item.is-activity .period-card {
  border-left: 4px solid #f97316;
}

.period-item.is-current .period-card {
  border-left: 4px solid #dc2626;
  background: #fef2f2;
}

.jump-to-current-btn {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border: none;
  border-radius: 24px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
  z-index: 20;
}

.jump-to-current-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

.bottom-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 100;
  display: flex;
  align-items: flex-end;
  justify-content: center;
  padding: 1rem;
}

.sheet-content {
  background: white;
  border-radius: 16px 16px 0 0;
  padding: 1.5rem;
  width: 100%;
  max-width: 400px;
  max-height: 50vh;
  overflow-y: auto;
}

.sheet-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.sheet-header h3 {
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

.sheet-body p {
  color: #64748b;
  margin-bottom: 1.5rem;
}

.notification-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.option-btn {
  padding: 1rem;
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-weight: 600;
  color: #1e293b;
  cursor: pointer;
  transition: all 0.3s ease;
}

.option-btn:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .list-header {
    padding: 0.75rem;
  }
  
  .header-title {
    font-size: 1.25rem;
  }
  
  .current-time {
    font-size: 1rem;
    padding: 0.375rem 0.75rem;
  }
  
  .current-period-card {
    margin: 0.75rem;
    padding: 1rem;
  }
  
  .period-main {
    padding: 0.75rem;
  }
  
  .period-title {
    font-size: 1rem;
  }
  
  .subject-text {
    font-size: 0.875rem;
  }
  
  .jump-to-current-btn {
    bottom: 1rem;
    right: 1rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
  }
}
</style>
