<template>
  <div class="card-view">
    <!-- Header with current day indicator -->
    <div class="card-header">
      <div class="day-indicator">
        <span class="current-day">{{ currentDayName }}</span>
        <span class="current-date">{{ currentDateDisplay }}</span>
      </div>
      <div class="swipe-hint">
        <span>← Swipe →</span>
      </div>
    </div>

    <!-- Swipeable cards container -->
    <div class="cards-container" ref="cardsContainer">
      <div 
        class="cards-track"
        :style="{ transform: `translateX(${translateX}px)` }"
        @touchstart="handleTouchStart"
        @touchmove="handleTouchMove"
        @touchend="handleTouchEnd"
      >
        <div
          v-for="(dayData, index) in scheduleData"
          :key="dayData.dayIndex"
          class="day-card"
          :class="{ 'current-day': dayData.dayIndex === currentDayIndex }"
        >
          <div class="card-header-info">
            <h3 class="day-title">{{ dayData.day }}</h3>
            <div class="period-count">
              {{ dayData.classes.filter(c => c.sub).length }} periods
            </div>
          </div>

          <div class="periods-list">
            <div
              v-for="slot in timeSlots"
              :key="slot.id"
              class="period-card"
              :class="[
                getPeriodClass(slot, dayData),
                { 'is-current-period': isCurrentPeriod(slot, dayData) }
              ]"
            >
              <div class="period-time">
                <span class="time-label">{{ slot.title || `P${slot.id}` }}</span>
                <span class="time-range">{{ slot.start }} - {{ slot.end }}</span>
              </div>
              
              <div class="period-content">
                <div class="subject-info">
                  <span class="subject-name">{{ getPeriodContent(slot, dayData) }}</span>
                  <span v-if="getPeriodClassInfo(slot, dayData)?.nafs" class="nafs-indicator">
                    (NAFS M)
                  </span>
                </div>
                
                <!-- Progress indicator for current period -->
                <div v-if="isCurrentPeriod(slot, dayData)" class="progress-indicator">
                  <div class="progress-bar">
                    <div class="progress-fill" :style="{ width: `${currentPeriodProgress}%` }"></div>
                  </div>
                  <span class="time-left">{{ currentPeriodTimeLeft }} left</span>
                </div>
              </div>

              <!-- Visual indicator for break/activity -->
              <div v-if="slot.type === 'break' || slot.type === 'activity'" class="type-indicator">
                <span v-if="slot.type === 'break'">☕</span>
                <span v-else>🏃</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Day dots indicator -->
    <div class="dots-indicator">
      <div
        v-for="(dayData, index) in scheduleData"
        :key="dayData.dayIndex"
        class="dot"
        :class="{ active: dayData.dayIndex === currentDayIndex }"
        @click="scrollToDay(index)"
      />
    </div>

    <!-- Quick jump to today -->
    <button
      v-if="currentDayIndex !== todayIndex"
      @click="scrollToToday"
      class="today-btn"
    >
      Today
    </button>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  scheduleData: { type: Array, required: true },
  timeSlots: { type: Array, required: true },
  currentDayIndex: { type: Number, required: true },
  currentTotalSecs: { type: Number, required: true },
  currentTimeDisplay: { type: String, default: '00:00:00' },
  isTestTimeEnabled: { type: Boolean, default: false }
});

const emit = defineEmits(['play-alert', 'notify', 'active-period-update']);

// Touch handling
const cardsContainer = ref(null);
const translateX = ref(0);
const startX = ref(0);
const isDragging = ref(false);
const currentCardIndex = ref(0);

// Computed properties
const todayIndex = computed(() => {
  return props.currentDayIndex;
});

const currentDayName = computed(() => {
  const dayData = props.scheduleData.find(d => d.dayIndex === props.currentDayIndex);
  return dayData?.day || 'Today';
});

const currentDateDisplay = computed(() => {
  if (props.isTestTimeEnabled) {
    return `Test ${props.currentTimeDisplay}`;
  }

  const now = new Date();
  return now.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    weekday: 'short'
  });
});

const currentPeriodProgress = ref(0);
const currentPeriodTimeLeft = ref('');

// Methods
const getPeriodContent = (slot, dayData) => {
  if (slot.type === 'break' || slot.type === 'activity') {
    return slot.type === 'break' ? 'Break Time' : 'Activity';
  }
  const classInfo = dayData.classes.find(c => c.p === slot.id);
  return classInfo?.sub || 'Free';
};

const getPeriodClassInfo = (slot, dayData) => {
  return dayData.classes.find(c => c.p === slot.id) || null;
};

const getPeriodClass = (slot, dayData) => {
  if (slot.type === 'break') return 'period-break';
  if (slot.type === 'activity') return 'period-activity';
  
  const subject = getPeriodContent(slot, dayData);
  if (subject === 'Free') return 'period-free';
  if (subject.includes('7A')) return 'period-7a';
  if (subject.includes('4A')) return 'period-4a';
  return 'period-other';
};

const isCurrentPeriod = (slot, dayData) => {
  if (dayData.dayIndex !== props.currentDayIndex) return false;
  
  const startSecs = slot.startMin * 60;
  const endSecs = slot.endMin * 60;
  return props.currentTotalSecs >= startSecs && props.currentTotalSecs < endSecs;
};

// Touch handlers
const handleTouchStart = (e) => {
  startX.value = e.touches[0].clientX;
  isDragging.value = true;
};

const handleTouchMove = (e) => {
  if (!isDragging.value) return;
  
  const currentX = e.touches[0].clientX;
  const diff = currentX - startX.value;
  
  // Apply resistance at boundaries
  const maxTranslate = -(props.scheduleData.length - 1) * 100;
  let newTranslate = translateX.value + diff;
  
  if (newTranslate > 0) {
    newTranslate = newTranslate / 3; // Resistance
  } else if (newTranslate < maxTranslate) {
    newTranslate = maxTranslate + (newTranslate - maxTranslate) / 3; // Resistance
  }
  
  translateX.value = newTranslate;
};

const handleTouchEnd = (e) => {
  if (!isDragging.value) return;
  
  const endX = e.changedTouches[0].clientX;
  const diff = endX - startX.value;
  const threshold = 50; // Minimum swipe distance
  
  isDragging.value = false;
  
  // Determine which card to show
  let targetIndex = currentCardIndex.value;
  
  if (diff > threshold && currentCardIndex.value > 0) {
    targetIndex--;
  } else if (diff < -threshold && currentCardIndex.value < props.scheduleData.length - 1) {
    targetIndex++;
  }
  
  scrollToDay(targetIndex);
};

const scrollToDay = (index) => {
  currentCardIndex.value = index;
  translateX.value = -index * 100;
  
  // Update current day info
  const dayData = props.scheduleData[index];
  if (dayData) {
    // Emit day change if needed
  }
};

const scrollToToday = () => {
  const todayCardIndex = props.scheduleData.findIndex(d => d.dayIndex === todayIndex.value);
  if (todayCardIndex !== -1) {
    scrollToDay(todayCardIndex);
  }
};

// Update current period progress
const updateCurrentPeriod = () => {
  const dayData = props.scheduleData.find(d => d.dayIndex === props.currentDayIndex);
  if (!dayData) return;
  
  for (const slot of props.timeSlots) {
    if (isCurrentPeriod(slot, dayData)) {
      const startSecs = slot.startMin * 60;
      const endSecs = slot.endMin * 60;
      const totalDuration = endSecs - startSecs;
      const elapsed = props.currentTotalSecs - startSecs;
      
      currentPeriodProgress.value = (elapsed / totalDuration) * 100;
      
      const remainingSecs = endSecs - props.currentTotalSecs;
      const mins = Math.floor(remainingSecs / 60);
      const secs = remainingSecs % 60;
      currentPeriodTimeLeft.value = `${mins}:${secs.toString().padStart(2, '0')}`;
      
      // Emit notification at period start
      emit('active-period-update', {
        timeLeft: currentPeriodTimeLeft.value,
        periodIndex: slot.id,
        title: slot.title
      });
      
      break;
    }
  }
};

// Initialize
onMounted(() => {
  // Start with current day
  const todayCardIndex = props.scheduleData.findIndex(d => d.dayIndex === todayIndex.value);
  if (todayCardIndex !== -1) {
    scrollToDay(todayCardIndex);
  }
  
  // Update period progress every second
  const interval = setInterval(updateCurrentPeriod, 1000);
  onUnmounted(() => clearInterval(interval));
});
</script>

<style scoped>
.card-view {
  position: relative;
  height: 100%;
  overflow: hidden;
  background: #f8fafc;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-bottom: 1px solid #e2e8f0;
}

.day-indicator {
  display: flex;
  flex-direction: column;
}

.current-day {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1e293b;
}

.current-date {
  font-size: 0.875rem;
  color: #475569;
}

.swipe-hint {
  font-size: 0.75rem;
  color: #475569;
  background: #f1f5f9;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
}

.cards-container {
  position: relative;
  height: calc(100% - 120px);
  overflow: hidden;
}

.cards-track {
  display: flex;
  height: 100%;
  transition: transform 0.3s ease-out;
  will-change: transform;
}

.day-card {
  flex: 0 0 100%;
  width: 100%;
  padding: 1rem;
  overflow-y: auto;
  background: #f8fafc;
}

.day-card.current-day {
  background: #f0f9ff;
}

.card-header-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.day-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1e293b;
  margin: 0;
}

.period-count {
  font-size: 0.875rem;
  color: #475569;
  background: #f1f5f9;
  padding: 0.25rem 0.75rem;
  border-radius: 8px;
}

.periods-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.period-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  min-height: 80px;
}

.period-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.period-card.is-current-period {
  border: 2px solid #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.period-time {
  display: flex;
  flex-direction: column;
  min-width: 80px;
}

.time-label {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.875rem;
}

.time-range {
  font-size: 0.75rem;
  color: #475569;
}

.period-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.subject-info {
  text-align: center;
}

.subject-name {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1e293b;
}

.nafs-indicator {
  font-size: 0.75rem;
  color: #475569;
  display: block;
  margin-top: 0.25rem;
}

.progress-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
  width: 100%;
}

.progress-bar {
  width: 100%;
  height: 4px;
  background: #e2e8f0;
  border-radius: 2px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #2563eb);
  transition: width 1s linear;
}

.time-left {
  font-size: 0.75rem;
  font-weight: 600;
  color: #3b82f6;
}

.type-indicator {
  font-size: 1.5rem;
  opacity: 0.9;
}

/* Period type colors */
.period-break {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  border-left: 4px solid #3b82f6;
}

.period-activity {
  background: linear-gradient(135deg, #fed7aa, #fdba74);
  border-left: 4px solid #f97316;
}

.period-free {
  background: #f8fafc;
  border-left: 4px solid #e2e8f0;
}

.period-7a {
  background: linear-gradient(135deg, #fef3c7, #fde68a);
  border-left: 4px solid #f59e0b;
}

.period-4a {
  background: linear-gradient(135deg, #dbeafe, #bfdbfe);
  border-left: 4px solid #3b82f6;
}

.period-other {
  background: white;
  border-left: 4px solid #10b981;
}

.dots-indicator {
  position: absolute;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.9);
  border-radius: 20px;
  backdrop-filter: blur(10px);
}

.dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #cbd5e1;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot.active {
  width: 24px;
  border-radius: 4px;
  background: #3b82f6;
}

.today-btn {
  position: absolute;
  bottom: 1rem;
  right: 1rem;
  padding: 0.75rem 1.5rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border: none;
  border-radius: 24px;
  font-weight: 600;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
  transition: all 0.3s ease;
}

.today-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
}

/* Mobile optimizations */
@media (max-width: 480px) {
  .period-card {
    padding: 0.75rem;
    min-height: 70px;
  }
  
  .subject-name {
    font-size: 1rem;
  }
  
  .time-label {
    font-size: 0.75rem;
  }
  
  .day-title {
    font-size: 1.25rem;
  }
}
</style>
