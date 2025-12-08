<template> 
  <div 
       v-if="isVisible"
  class="date-time-widget " :class="{ 'collapsed': !isVisible, 'simple-mode': simpleMode }">
    <transition name="fade"  >
      <div
      v-if="isVisible"
      class="  fixed top-0  "
      :class="{ 'date-time-content': !simpleMode, 'simple-date-time-content': simpleMode }">
        <!-- Simple Mode Display -->



        <div v-if="simpleMode" class="simple-datetime-display ">
          <div class="simple-time" @click="toggleTimeFormat">
            {{ formattedDateTime.time }} {{ formattedDateTime.day }} {{ formattedDateTime.date }}
            <q-tooltip>Click to toggle 12/24 hour format</q-tooltip>
          </div>
        </div>

        <!-- Standard Mode Display -->
        <div v-else class="datetime-display">
          <div class="time-section">
            <div class="time" @click="toggleTimeFormat" :class="{ 'clickable': true }">
              {{ formattedDateTime.time }}
              <q-tooltip>Click to toggle 12/24 hour format</q-tooltip>
            </div>
            <div class="seconds">{{ formattedDateTime.seconds }}</div>
          </div>
          <div class="date-section">
            <div class="day">{{ formattedDateTime.day }}</div>
            <div class="date">{{ formattedDateTime.date }}</div>
          </div>
        </div>

        <!-- Hijri Date Section (only in standard mode) -->
        <div class="hijri-section" v-if="showHijri && !simpleMode">
          <HijraDate />
        </div>

        <!-- Additional Controls (only in standard mode) -->
        <div class="controls" v-if="!simpleMode">
          <q-btn flat round size="sm" color="grey-7" icon="calendar_today" @click="toggleHijri" />
          <q-btn flat round size="sm" color="grey-7" icon="view_agenda" @click="toggleMode">
            <q-tooltip>Switch to {{ simpleMode ? 'standard' : 'simple' }} mode</q-tooltip>
          </q-btn>
        </div>
      </div>
    </transition>

    <!-- Mode Toggle Button -->
    <div class=" scale-50 flex fixed -top-4 lef-1/2 w-fit">
      <q-btn
        flat round color="primary"
        @click="toggleMode"
        icon="view_agenda"
      >
        <q-tooltip>Switch to {{ simpleMode ? 'standard' : 'simple' }} mode</q-tooltip>
      </q-btn>
    </div>
    <div class="flex fixed bottom-0 left-0 z-50 w-full">
      <NewsTicker :news-items="newsItems" :display-time="5000" class="w-full" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import HijraDate from './HijraDate.vue';
import { useQuasar } from 'quasar';
import NewsTicker from '@/Components/NewsTicker.vue';

const $q = useQuasar();

// State
const currentDateTime = ref(new Date());
const isVisible = ref(true);
const showHijri = ref(false);
const use24HourFormat = ref(true); // Default to 24-hour format
const simpleMode = ref(false); // Default to standard mode
let timer = null;
let autoInterval = null;

// Sample news items - replace with your actual news data
const newsItems = ref([
  {
    id: 1,
    title: 'Welcome to MyClass7',
    details: 'The new school management system is now live!'
  },
  {
    id: 2,
    title: 'Upcoming Parent-Teacher Conference',
    details: 'Mark your calendars for May 15th'
  },
  {
    id: 3,
    title: 'New Learning Resources Available',
    details: 'Check out the library for new educational materials'
  },
  {
    id: 4,
    title: 'School Holiday Reminder',
    details: 'School will be closed next Monday for the national holiday'
  }
]);

// Initialize from localStorage if available
onMounted(() => {
  // Update time every second
  timer = setInterval(() => {
    currentDateTime.value = new Date();
  }, 1000);

  // Calculate delay to next 5-minute mark
  const now = new Date();
  const currentMin = now.getMinutes();
  const newMin = currentMin + (5 - currentMin % 5) % 5;
  const next5Min = new Date(now.getFullYear(), now.getMonth(), now.getDate(), now.getHours(), newMin, 0, 0);
  const delay = next5Min.getTime() - now.getTime();

  // Start auto show after delay
  setTimeout(startAutoShow, delay);

  // Load Hijri display state from localStorage
  const savedHijriState = localStorage.getItem('dateTimeHijriVisible');
  if (savedHijriState !== null) {
    showHijri.value = savedHijriState === 'true';
  }

  // Load time format preference from localStorage
  const savedTimeFormat = localStorage.getItem('dateTimeUse24HourFormat');
  if (savedTimeFormat !== null) {
    use24HourFormat.value = savedTimeFormat === 'true';
  }

  // Load display mode preference from localStorage
  const savedModeState = localStorage.getItem('dateTimeSimpleMode');
  if (savedModeState !== null) {
    simpleMode.value = savedModeState === 'true';
  }
});

const autoShow = () => {
  isVisible.value = true;
  setTimeout(() => {
    isVisible.value = false;
  }, 5000);
};

const startAutoShow = () => {
  autoShow();
  autoInterval = autoInterval = setInterval(autoShow, 5000);
};

// Save state changes to localStorage
watch(isVisible, (newValue) => {
  localStorage.setItem('dateTimeWidgetVisible', newValue.toString());
});

watch(showHijri, (newValue) => {
  localStorage.setItem('dateTimeHijriVisible', newValue.toString());
});

watch(use24HourFormat, (newValue) => {
  localStorage.setItem('dateTimeUse24HourFormat', newValue.toString());
});

watch(simpleMode, (newValue) => {
  localStorage.setItem('dateTimeSimpleMode', newValue.toString());
});

// Computed properties
const formattedDateTime = computed(() => {
  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
  const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  const d = currentDateTime.value;

  const dayName = days[d.getDay()];
  const date = String(d.getDate()).padStart(2, '0');
  const month = months[d.getMonth()];
  const year = d.getFullYear();
  const hours24 = d.getHours();
  const hours12 = hours24 % 12 || 12; // Convert 0 to 12 for 12-hour format
  const ampm = hours24 >= 12 ? 'PM' : 'AM';

  // Format hours based on selected format
  const hoursFormatted = use24HourFormat.value
    ? String(hours24).padStart(2, '0')
    : String(hours12);

  const minutes = String(d.getMinutes()).padStart(2, '0');
  const seconds = String(d.getSeconds()).padStart(2, '0');

  // Format time string based on selected format
  const timeString = use24HourFormat.value
    ? `${hoursFormatted}:${minutes}`
    : `${hoursFormatted}:${minutes} ${ampm}`;

  return {
    day: dayName,
    date: `${date} ${month} ${year}`,
    time: timeString,
    seconds: seconds
  };
});

// Methods
const toggleVisibility = () => {
  isVisible.value = !isVisible.value;

  // Show notification
  $q.notify({
    message: isVisible.value ? 'Date & Time widget shown' : 'Date & Time widget hidden',
    color: 'primary',
    position: 'bottom-right',
    timeout: 1000
  });
};

const toggleHijri = () => {
  showHijri.value = !showHijri.value;
};

const toggleTimeFormat = () => {
  use24HourFormat.value = !use24HourFormat.value;

  // Show notification
  $q.notify({
    message: use24HourFormat.value ? 'Switched to 24-hour format' : 'Switched to 12-hour format',
    color: 'primary',
    position: 'bottom-right',
    timeout: 1000
  });
};

const toggleMode = () => {
  simpleMode.value = !simpleMode.value;

  // Show notification
  $q.notify({
    message: simpleMode.value ? 'Switched to simple mode' : 'Switched to standard mode',
    color: 'primary',
    position: 'bottom-right',
    timeout: 1000
  });
};

// Cleanup
onBeforeUnmount(() => {
  // Clean up interval when component is destroyed
  if (timer) {
    clearInterval(timer);
  }
});
</script>

<style scoped>
.date-time-widget {
  /* width: 100%; */
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.3s ease;
  position: relative;
  z-index: 10;
  /* margin-bottom: 0.5rem; */
}

.date-time-content {
  /* width: 100%; */
  /* max-width: 800px; */
  background: linear-gradient(135deg, #2c3e50, #34495e);
  border-radius: 0 0 12px 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  padding: 0.75rem 0.5rem;
  /* padding: 0.75rem 1.5rem; */
  color: white;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.datetime-display {
  display: flex;
  justify-content: center;
  align-items: center;
  /* width: 100%; */
  gap: 2rem;
}

.time-section {
  display: flex;
  align-items: baseline;
}

.time {
  font-size: 2rem;
  font-weight: 700;
  letter-spacing: 1px;
}

.clickable {
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
}

.clickable:hover {
  color: #f1c40f;
  transform: scale(1.05);
}

.clickable:active {
  transform: scale(0.98);
}

.seconds {
  font-size: 1rem;
  opacity: 0.8;
  margin-left: 0.25rem;
}

.date-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.day {
  font-size: 1.2rem;
  font-weight: 600;
  color: #f1c40f;
}

.date {
  font-size: 1rem;
  opacity: 0.9;
}

.hijri-section {
  margin-top: 0.5rem;
  width: 100%;
  background-color: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  padding: 0.5rem;
}

.controls {
  margin-top: 0.5rem;
  display: flex;
  justify-content: center;
}

.toggle-container {
  margin-top: -1rem;
  z-index: 11;

}

.toggle-btn {
  background-color: white;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease;
}

.toggle-btn:hover {
  transform: translateY(-2px);
}

.collapsed {
  margin-bottom: -1rem;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Simple Mode Styles */
.simple-mode {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
}

.simple-date-time-content {
  /* width: 100%; */
  background-color: rgba(44, 62, 80, 0.9);
  color: white;
  /* padding: 0.5rem; */
  text-align: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.simple-datetime-display {
  display: flex;
  justify-content: center;
  align-items: center;
}

.simple-time {
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.simple-time:hover {
  color: #f1c40f;
}

/* Responsive styles */
@media (max-width: 768px) {
  .datetime-display {
    flex-direction: column;
    gap: 0.5rem;
    align-items: center;
  }

  .time {
    font-size: 1.5rem;
  }

  .date-section {
    align-items: center;
  }

  .date-time-content {
    padding: 0.5rem 1rem;
  }

  .simple-time {
    font-size: 0.9rem;
  }
}
</style>
