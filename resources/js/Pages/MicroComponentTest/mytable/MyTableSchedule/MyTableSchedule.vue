<template>
  <div class="schedule-wrapper">
    <div class="header-container">
      <h2 class="header-title">{{ isShowingAllDays ? "Full Weekly View" : "Today's View" }}</h2>
      <div class="controls">
        <button 
          v-if="showNotifyBtn" 
          @click="requestNotificationPermission" 
          class="toggle-btn btn-notify"
        >
          🔔 Enable Alerts
        </button>
        <button 
          @click="toggleViewMode" 
          class="toggle-btn"
        >
          {{ isShowingAllDays ? "Show Today Only" : "Show All Days" }}
        </button>
        <button 
          @click="showTimingManager = true" 
          class="toggle-btn"
          style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);"
        >
          ⚙️ Edit Timings
        </button>
        
        <div class="time-wrapper">
          <div class="clock">{{ currentTimeDisplay }}</div>
          <div 
            v-if="activePeriodInfo" 
            class="header-time-left"
          >
            {{ activePeriodInfo.timeLeft }} left in {{ activePeriodInfo.title || `Period ${activePeriodInfo.periodIndex + 1}` }}
          </div>
        </div>
      </div>
    </div>

    <!-- Error message display -->
    <div v-if="error" class="error-message">
      {{ error }}
      <br><br>Make sure the JSON data files are available.
    </div>

    <!-- Schedule Grid -->
    <ScheduleGrid 
      v-else
      :schedule-data="scheduleData"
      :time-slots="timeSlots"
      :is-showing-all-days="isShowingAllDays"
      :current-day-index="currentDayIndex"
      :current-total-secs="currentTotalSecs"
      @play-alert="playAlertSound"
      @notify="sendNotification"
      @active-period-update="handleActivePeriodUpdate"
    />

    <!-- Timing Editor Modal -->
    <ScheduleTimingManager
      v-if="showTimingManager"
      v-model="timeSlots"
      @update:modelValue="handleTimingsUpdate"
      @close="showTimingManager = false"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import ScheduleGrid from './components/ScheduleGrid.vue';
import ScheduleTimingManager from './components/ScheduleTimingManager.vue';
import scheduleTimingData from './schedule_timing.json';
import scheduleItemsData from './schedule_data.json';

// State
const scheduleData = ref([]);
const timeSlots = ref([]);
const error = ref('');
const isShowingAllDays = ref(true);
const currentDayIndex = ref(-1);
const currentTotalSecs = ref(0);
const currentTimeDisplay = ref('00:00:00');
const showNotifyBtn = ref(false);
const activePeriodInfo = ref(null);
const showTimingManager = ref(false);

let timerInterval = null;
let alertSound = null;

// Initialize Data & Handlers
onMounted(() => {
  // Setup audio
  try {
    // using absolute path for demo/test purposes based on original PWA
    alertSound = new Audio('/notification1.mp3'); 
  } catch (e) {
    console.warn("Could not initialize audio", e);
  }

  // Load Data
  try {
    scheduleData.value = scheduleItemsData;
    // Process time slots to have start/end in minutes from start of day (similar to the HTML version)
    timeSlots.value = scheduleTimingData.map(slot => {
      const startParts = slot.start.split(':').map(Number);
      const endParts = slot.end.split(':').map(Number);
      return {
        ...slot,
        startMin: startParts[0] * 60 + startParts[1],
        endMin: endParts[0] * 60 + endParts[1]
      };
    });
  } catch (err) {
    error.value = "Failed to load schedule data.";
    console.error(err);
  }

  checkNotificationStatus();
  updateLiveIndicator();
  
  // Start timer to update time every second
  timerInterval = setInterval(updateLiveIndicator, 1000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

// Methods
const toggleViewMode = () => {
  isShowingAllDays.value = !isShowingAllDays.value;
};

const updateLiveIndicator = () => {
  const now = new Date();
  
  // Calculate current day index (0-based, Sunday = 0)
  currentDayIndex.value = now.getDay(); 
  
  // current total seconds from midnight
  currentTotalSecs.value = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
  
  currentTimeDisplay.value = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
};

const handleActivePeriodUpdate = (info) => {
  activePeriodInfo.value = info;
};

const handleTimingsUpdate = (newSlots) => {
  timeSlots.value = newSlots.map(slot => {
    const startParts = slot.start.split(':').map(Number);
    const endParts = slot.end.split(':').map(Number);
    return {
      ...slot,
      startMin: startParts[0] * 60 + startParts[1],
      endMin: endParts[0] * 60 + endParts[1]
    };
  });
};

// --- Notifications & Audio ---
const checkNotificationStatus = () => {
  if ("Notification" in window) {
    if (Notification.permission === "default") {
      showNotifyBtn.value = true;
    }
  }
};

const requestNotificationPermission = () => {
  if ("Notification" in window) {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") {
        showNotifyBtn.value = false;
        playAlertSound(true); // Ignore error on test play
        sendNotification("Alerts Enabled", "You will be notified when classes start!");
      }
    });
  }
};

const playAlertSound = (silentFail = false) => {
  if (alertSound) {
    alertSound.play().catch(e => {
        if(!silentFail) console.log("Audio blocked by browser autoplay rules. Please interact with the page first.", e);
    });
  }
};

const sendNotification = (title, bodyText) => {
  if ("Notification" in window && Notification.permission === "granted") {
    // Attempt standard notification if service worker not available
    try {
        new Notification(title, {
            body: bodyText,
            vibrate: [200, 100, 200]
        });
    } catch(e) {
        console.warn("Notification error", e)
    }
  }
};
</script>

<style scoped>
.schedule-wrapper {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f0f2f5;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 30px 20px;
  min-height: 100vh;
}

.header-container {
  width: 100%;
  max-width: 1000px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.header-title {
  margin: 0;
  color: #333;
}

.controls {
  display: flex;
  gap: 15px;
  align-items: flex-start;
  flex-wrap: wrap;
  justify-content: flex-end;
}

.toggle-btn {
  padding: 10px 16px;
  margin-top: 2px;
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: bold;
  font-size: 0.95rem;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0,0,0,0.2), inset 0 2px 0 rgba(255,255,255,0.2);
  transition: all 0.2s;
}

.toggle-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 8px rgba(0,0,0,0.3);
}

.btn-notify {
  background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
}

.time-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
}

.clock {
  font-size: 1.2rem;
  font-weight: bold;
  color: #3b82f6;
  background: white;
  padding: 8px 16px;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.header-time-left {
  font-size: 0.85rem;
  font-weight: bold;
  color: #ff2a2a;
  background: #ffebe9;
  padding: 4px 12px;
  border-radius: 6px;
  border: 1px solid rgba(255, 42, 42, 0.2);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(255, 42, 42, 0.4); }
  70% { box-shadow: 0 0 0 6px rgba(255, 42, 42, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 42, 42, 0); }
}

.error-message {
  color: red;
  font-weight: bold;
  padding: 20px;
  background: #ffebe9;
  border: 1px solid red;
  border-radius: 8px;
  margin-top: 20px;
}
</style>
