<template>
  <tr :class="{ 'is-current-day': isCurrentDay }">
    <td class="day-col">{{ dayRow.day }}</td>
    <td 
      v-for="(slot, index) in timeSlots" 
      :key="slot.id || index"
      class="subject-cell"
      :class="[
        getSlotColorClass(slot),
        { 'is-current-class': activeCellIndex === index }
      ]"
    >
      <span class="main-text">{{ getSlotContent(slot) }}</span>
      <span v-if="getSlotClass(slot)?.nafs" class="nafs-text">(NAFS M)</span>

      <!-- Visual Progress Indicators for Active Class -->
      <template v-if="activeCellIndex === index">
        <div class="cell-progress-fill" :style="{ height: `${percentDone}%` }"></div>
        <div class="cell-progress-line" :style="{ top: `${percentDone}%` }"></div>
      </template>
    </td>
  </tr>
</template>

<script setup>
import { computed, watch, ref } from 'vue';

const props = defineProps({
  dayRow: { type: Object, required: true },
  timeSlots: { type: Array, required: true },
  isCurrentDay: { type: Boolean, default: false },
  currentTotalSecs: { type: Number, required: true }
});

const emit = defineEmits(['active-period-update', 'play-alert', 'notify']);

// State tracking current active period inside this row
const activeCellIndex = ref(-1);
const percentDone = ref(0);
let notifiedPeriodKey = null;

// Logic to extract data for a specific slot
const getSlotClass = (slot) => {
  return props.dayRow.classes.find(c => c.p == slot.id) || null;
};

const getSlotContent = (slot) => {
  if (slot.type === 'break' || slot.type === 'activity') {
    return '☕'; // Icon representing break/activity
  }
  const c = getSlotClass(slot);
  return c ? c.sub : '';
};

// Determine colors based on subject string and slot type
const getSlotColorClass = (slot) => {
  if (slot.type === 'break') return 'sub-break';
  if (slot.type === 'activity') return 'sub-activity';
  
  const subject = getSlotContent(slot);
  if (!subject || subject.trim() === '') return 'sub-free';
  if (subject === '7A') return 'sub-7A';
  if (subject === '4A') return 'sub-4A';
  return 'sub-other';
};

// Check Time Logic every time currentTotalSecs updates
watch(() => props.currentTotalSecs, (newSecs) => {
  if (!props.isCurrentDay) {
    activeCellIndex.value = -1;
    return;
  }

  let foundActive = false;

  for (let i = 0; i < props.timeSlots.length; i++) {
    const slot = props.timeSlots[i];
    const startSecs = slot.startMin * 60;
    const endSecs = slot.endMin * 60;

    if (newSecs >= startSecs && newSecs < endSecs) {
      activeCellIndex.value = i;
      foundActive = true;

      // Calculate Progress
      const totalDurationSecs = endSecs - startSecs;
      const elapsedSecs = newSecs - startSecs;
      percentDone.value = (elapsedSecs / totalDurationSecs) * 100;

      const timeRemainingSecs = endSecs - newSecs;
      const minsLeft = Math.floor(timeRemainingSecs / 60);
      const secsLeft = timeRemainingSecs % 60;

      // Update Header Data via Event
      emit('active-period-update', {
        timeLeft: `${minsLeft}:${secsLeft.toString().padStart(2, '0')}`,
        periodIndex: i,
        title: slot.title
      });

      // Handle Notifications (only trigger once per period start)
      const periodKey = `${props.dayRow.dayIndex}-${i}`;
      if (notifiedPeriodKey !== periodKey) {
        notifiedPeriodKey = periodKey;
        
        emit('play-alert'); // fire sound event
        
        const slot = props.timeSlots[i];
        const subjectText = getSlotContent(slot);
        const titleText = slot.title || `Period ${i + 1}`;
        const isFree = subjectText.trim() === "";
        
        if (slot.type === 'break' || slot.type === 'activity') {
          emit('notify', `${titleText} Started!`, `Time for a break!`);
        } else if (!isFree) {
          emit('notify', `${titleText} Started!`, `Time for ${subjectText}`);
        } else {
          emit('notify', `${titleText} Started`, `You have a free period.`);
        }
      }
      break; 
    }
  }

  if (!foundActive && activeCellIndex.value !== -1) {
    activeCellIndex.value = -1;
    emit('active-period-update', null);
  }
});

</script>

<style scoped>
.subject-cell {
  font-size: 1.2rem;
  font-weight: 900;
  color: #111;
  position: relative;
  transition: all 0.3s;
  overflow: hidden; 
  border: 1px solid #222;
  text-align: center;
  padding: 12px 5px;
  width: 11%;
}

.day-col {
  background-color: #5a5a5a;
  color: #fce4ce;
  font-size: 1.1rem;
  font-weight: bold;
  text-align: left;
  padding-left: 15px;
  border: 1px solid #222;
  width: 12%;
}

.nafs-text {
  display: block;
  font-size: 0.7rem;
  font-weight: normal;
  color: #444;
  margin-top: 5px;
  position: relative;
  z-index: 5; 
}

.subject-cell span.main-text {
  position: relative;
  z-index: 5;
}

/* Colors matching exact spec */
.sub-7A { background-color: #ffff00; }
.sub-4A { background-color: #dbeafe; }
.sub-other { background-color: #ffffff; }
.sub-free { background-color: white; }
.sub-break { background-color: #ebf8ff; color: #3182ce; }
.sub-activity { background-color: #fffaf0; color: #dd6b20; }

.is-current-class {
  box-shadow: inset 0 0 0 3px #ff2a2a, 0 0 15px rgba(255, 42, 42, 0.4);
  z-index: 10;
}

.is-current-day .day-col {
  border-left: 5px solid #ff2a2a;
}

.cell-progress-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255, 42, 42, 0.8);
  z-index: 1;
  pointer-events: none;
  box-shadow: 0 0 8px rgba(255, 42, 42, 0.6);
}

.cell-progress-line::before {
  content: '';
  position: absolute;
  left: 0;
  top: -4px;
  width: 10px;
  height: 10px;
  background: #ff2a2a;
  border-radius: 50%;
  box-shadow: 0 0 5px rgba(255, 42, 42, 0.8);
}

.cell-progress-fill {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(255, 42, 42, 0.1);
  z-index: 0;
  pointer-events: none;
}
</style>
