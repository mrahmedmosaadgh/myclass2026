<template>
  <div class="timeline-view">
    <!-- KaTeX CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css" integrity="sha384-n8MVd4RsNIU0tAv4ct0nTaAbDJwPJzDEaqSD1odI+WdtXRGWt2kTvGFasHpSy3SV" crossorigin="anonymous">
    
    <div class="timeline-controls">
      <div class="control-group">
        <label class="control-label">Select Stages:</label>
        <div class="stage-checkboxes">
          <label v-for="stage in allStages" :key="stage.id" class="stage-checkbox">
            <input 
              type="checkbox" 
              :value="stage.id" 
              v-model="selectedStages"
              class="checkbox-input"
            >
            <span class="checkbox-label">{{ stage.title }}</span>
          </label>
        </div>
      </div>
      
      <div class="control-group">
        <label class="control-label">Day:</label>
        <select v-model="selectedDay" class="day-select">
          <option value="today">Today</option>
          <option value="d1">Monday</option>
          <option value="d2">Tuesday</option>
          <option value="d3">Wednesday</option>
          <option value="d4">Thursday</option>
          <option value="d5">Friday</option>
          <option value="d6">Saturday</option>
        </select>
      </div>

      <div class="control-group">
        <label class="control-label">My Events:</label>
        <div class="event-controls">
          <button @click="showAddEventDialog = true" class="btn-add-event">
            + Add Event
          </button>
          <button @click="removeAllUserEvents" class="btn-clear-events" v-if="userEvents.length > 0">
            Clear All
          </button>
          <button @click="showLatexHelp = true" class="btn-latex-help">
            LaTeX Help
          </button>
        </div>
      </div>
    </div>

    <div class="timeline-container">
      <!-- Debug Info -->
      <div class="debug-info" style="padding: 10px; background: #f0f0f0; margin-bottom: 10px; font-size: 12px;">
        <div>TimeSlots count: {{ timeSlots.length }}</div>
        <div>DisplayStages count: {{ displayStages.length }}</div>
        <div>Selected day: {{ selectedDay }}</div>
        <div>Store initialized: {{ store.isInitialized.value }}</div>
      </div>
      
      <!-- Header Row -->
      <div class="timeline-header">
        <div class="time-column-header">Time</div>
        <div v-for="stage in displayStages" :key="stage.id" class="stage-header">
          <div class="stage-title">{{ stage.title }}</div>
          <div class="stage-subtitle">{{ getDayLabel(selectedDay) }}</div>
        </div>
      </div>

      <!-- Timeline Grid -->
      <div class="timeline-grid" ref="timelineGrid">
        <!-- Time Grid Background -->
        <div class="time-grid-background">
          <!-- Hour markers -->
          <div v-for="hour in hours" :key="hour" class="hour-marker" :style="{ top: `${getTimePosition(hour * 60)}px` }">
            <div class="hour-line"></div>
            <div class="hour-label">{{ String(hour).padStart(2, '0') }}:00</div>
          </div>
        </div>

        <!-- Horizontal Grid Lines (span across all columns) -->
        <div class="horizontal-grid">
          <div v-for="hour in hours" :key="`grid-${hour}`" class="horizontal-line" :style="{ top: `${getTimePosition(hour * 60)}px` }"></div>
        </div>

        <!-- Stage Columns -->
        <div v-for="stage in displayStages" :key="stage.id" class="stage-column">
          <div class="stage-content">
            <!-- Period Blocks positioned by actual time -->
            <div 
              v-for="timeSlot in timeSlots" 
              :key="`${stage.id}-${timeSlot.id}`"
              class="period-block"
              :class="{
                'current-period': isCurrentPeriod(timeSlot, stage.id, selectedDay),
                'lesson-period': timeSlot.type === 'lesson',
                'break-period': timeSlot.type === 'break',
                'activity-period': timeSlot.type === 'activity',
                'has-content': hasPeriodContent(timeSlot, stage.id, selectedDay),
                'has-nafs': hasNafs(timeSlot, stage.id, selectedDay),
                'editing': isEditingPeriod(timeSlot.id, stage.id, selectedDay)
              }"
              :style="{
                top: `${getTimePosition(timeSlot.startMin)}px`,
                height: `${getTimeHeight(timeSlot.startMin, timeSlot.endMin)}px`,
                backgroundColor: getPeriodColor(timeSlot, stage.id, selectedDay)
              }"
              @dblclick="startEditingPeriod(timeSlot, stage.id, selectedDay)"
            >
              <!-- Edit Mode -->
              <div v-if="isEditingPeriod(timeSlot.id, stage.id, selectedDay)" class="period-edit-mode">
                <div class="edit-form">
                  <input 
                    v-model="editingPeriod.subject"
                    type="text" 
                    placeholder="Subject"
                    class="edit-input edit-subject"
                    @keyup.esc="cancelEditingPeriod"
                    @keyup.enter="saveEditingPeriod"
                  >
                  <input 
                    v-model="editingPeriod.teacher"
                    type="text" 
                    placeholder="Teacher"
                    class="edit-input edit-teacher"
                    @keyup.esc="cancelEditingPeriod"
                    @keyup.enter="saveEditingPeriod"
                  >
                  <div class="edit-actions">
                    <button @click="saveEditingPeriod" class="edit-btn save-btn">✓</button>
                    <button @click="cancelEditingPeriod" class="edit-btn cancel-btn">×</button>
                  </div>
                </div>
              </div>
              
              <!-- Display Mode -->
              <div v-else class="period-content">
                <div class="period-title" v-html="getPeriodTitleWithMath(timeSlot, stage.id, selectedDay)"></div>
                <div class="period-teacher" v-html="getPeriodTeacherWithMath(timeSlot, stage.id, selectedDay)"></div>
                <div v-if="hasNafs(timeSlot, stage.id, selectedDay)" class="nafs-indicator">N</div>
                <div class="edit-hint">Double-click to edit</div>
              </div>
              
              <!-- Progress indicator for current period -->
              <div v-if="isCurrentPeriod(timeSlot, stage.id, selectedDay)" class="progress-indicator">
                <div class="progress-fill" :style="{ height: `${getPeriodProgress(timeSlot)}%` }"></div>
                <div class="progress-line" :style="{ bottom: `${getPeriodProgress(timeSlot)}%` }"></div>
              </div>
            </div>

            <!-- User Event Blocks -->
            <div 
              v-for="userEvent in getUserEventsForStage(stage.id)" 
              :key="`user-${userEvent.id}`"
              class="user-event-block"
              :style="{
                top: `${getTimePosition(userEvent.startMin)}px`,
                height: `${getTimeHeight(userEvent.startMin, userEvent.endMin)}px`,
                backgroundColor: userEvent.color
              }"
            >
              <div class="user-event-content">
                <div class="user-event-title" v-html="renderLatexMath(userEvent.title)"></div>
                <div class="user-event-time">{{ userEvent.startTime }} - {{ userEvent.endTime }}</div>
                <button @click="removeUserEvent(userEvent.id)" class="btn-remove-event">×</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Time Indicator -->
    <div v-if="showCurrentTimeIndicator" class="current-time-line" :style="getCurrentTimeStyle()">
      <div class="time-label">Now: {{ currentTimeDisplay }}</div>
      <div class="time-dot"></div>
    </div>

    <!-- Add Event Dialog -->
    <div v-if="showAddEventDialog" class="dialog-overlay" @click="showAddEventDialog = false">
      <div class="dialog-content" @click.stop>
        <h3>Add Custom Event</h3>
        <form @submit.prevent="addUserEvent">
          <div class="form-group">
            <label>Event Title:</label>
            <input v-model="newEvent.title" type="text" placeholder="e.g., Meeting, Study time, or $E=mc^2$" required>
            <small class="latex-hint">Use $...$ for inline math: $x^2 + y^2 = z^2$</small>
          </div>
          
          <div class="form-group">
            <label>Stage:</label>
            <select v-model="newEvent.stageId" required>
              <option value="">Select Stage</option>
              <option v-for="stage in allStages" :key="stage.id" :value="stage.id">
                {{ stage.title }}
              </option>
            </select>
          </div>
          
          <div class="form-group">
            <label>Start Time:</label>
            <input v-model="newEvent.startTime" type="time" required>
          </div>
          
          <div class="form-group">
            <label>End Time:</label>
            <input v-model="newEvent.endTime" type="time" required>
          </div>
          
          <div class="form-group">
            <label>Color:</label>
            <div class="color-picker">
              <div 
                v-for="color in eventColors" 
                :key="color"
                class="color-option"
                :class="{ active: newEvent.color === color }"
                :style="{ backgroundColor: color }"
                @click="newEvent.color = color"
              ></div>
            </div>
          </div>
          
          <div class="form-actions">
            <button type="button" @click="showAddEventDialog = false" class="btn-cancel">Cancel</button>
            <button type="submit" class="btn-add">Add Event</button>
          </div>
        </form>
      </div>
    </div>

    <!-- LaTeX Help Dialog -->
    <div v-if="showLatexHelp" class="dialog-overlay" @click="showLatexHelp = false">
      <div class="dialog-content latex-help-content" @click.stop>
        <h3>LaTeX Math Syntax Help</h3>
        <div class="latex-examples">
          <div class="example-section">
            <h4>Inline Math (single $)</h4>
            <div class="example-item">
              <code>$x^2 + y^2 = z^2$</code>
              <div class="rendered-example" v-html="renderLatexMath('$x^2 + y^2 = z^2$')"></div>
            </div>
            <div class="example-item">
              <code>$E = mc^2$</code>
              <div class="rendered-example" v-html="renderLatexMath('$E = mc^2$')"></div>
            </div>
            <div class="example-item">
              <code>$\\frac{a}{b}$</code>
              <div class="rendered-example" v-html="renderLatexMath('$\\frac{a}{b}$')"></div>
            </div>
          </div>
          
          <div class="example-section">
            <h4>Display Math (double $$)</h4>
            <div class="example-item">
              <code>$$\\sum_{i=1}^{n} x_i$$</code>
              <div class="rendered-example" v-html="renderLatexMath('$$\\sum_{i=1}^{n} x_i$$')"></div>
            </div>
            <div class="example-item">
              <code>$$\\int_{0}^{\\infty} e^{-x} dx$$</code>
              <div class="rendered-example" v-html="renderLatexMath('$$\\int_{0}^{\\infty} e^{-x} dx$$')"></div>
            </div>
          </div>
          
          <div class="example-section">
            <h4>Common Symbols</h4>
            <div class="symbols-grid">
              <div class="symbol-item">
                <code>\\alpha</code> → <span v-html="renderLatexMath('$\\alpha$')"></span>
              </div>
              <div class="symbol-item">
                <code>\\beta</code> → <span v-html="renderLatexMath('$\\beta$')"></span>
              </div>
              <div class="symbol-item">
                <code>\\pi</code> → <span v-html="renderLatexMath('$\\pi$')"></span>
              </div>
              <div class="symbol-item">
                <code>\\infty</code> → <span v-html="renderLatexMath('$\\infty$')"></span>
              </div>
              <div class="symbol-item">
                <code>\\sqrt{x}</code> → <span v-html="renderLatexMath('$\\sqrt{x}$')"></span>
              </div>
              <div class="symbol-item">
                <code>\\pm</code> → <span v-html="renderLatexMath('$\\pm$')"></span>
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-actions">
          <button type="button" @click="showLatexHelp = false" class="btn-cancel">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, inject } from 'vue';
import { useAppStore } from '../../composables/useAppStore.js';
import defaultTimingData from '../../schedule_timing.json';

// Color schemes from TableViewV2
const colorSchemes = {
  default: ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#06b6d4', '#a855f7', '#f43f5e', '#22c55e', '#eab308'],
  pastel: ['#93c5fd', '#fca5a5', '#86efac', '#fcd34d', '#c4b5fd', '#f9a8d4', '#5eead4', '#fdba74', '#a5b4fc', '#bef264', '#67e8f9', '#d8b4fe', '#fda4af', '#86efac', '#fde047'],
  vibrant: ['#2563eb', '#dc2626', '#059669', '#d97706', '#7c3aed', '#db2777', '#0d9488', '#ea580c', '#4f46e5', '#65a30d', '#0891b2', '#9333ea', '#e11d48', '#16a34a', '#ca8a04'],
  monochrome: ['#1e293b', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0', '#f1f5f9', '#f8fafc', '#0f172a', '#475569', '#64748b', '#94a3b8', '#cbd5e1', '#e2e8f0']
};

const props = defineProps({
  showProgress: { type: Boolean, default: true },
  showCurrentTime: { type: Boolean, default: true },
  autoRefresh: { type: Boolean, default: true }
});

const store = useAppStore();

// Inject resolved time slots from parent
const resolvedTimeSlots = inject('resolvedTimeSlots', ref([]));

// Color system from TableViewV2
const currentColorScheme = computed(() => colorSchemes.default);
const subjectColorMap = ref(new Map());

// Initialize color map
const initializeColorMap = () => {
  const allSubjects = new Set();
  
  // Collect all unique subjects from schedule data
  if (Array.isArray(store.scheduleData.value)) {
    store.scheduleData.value.forEach(day => {
      if (day.classes) {
        day.classes.forEach(period => {
          if (period.sub && period.sub.trim()) {
            allSubjects.add(period.sub);
          }
        });
      }
    });
  }
  
  // Assign colors to all subjects
  const colors = currentColorScheme.value;
  let colorIndex = 0;
  
  allSubjects.forEach(subject => {
    if (!subjectColorMap.value.has(subject)) {
      subjectColorMap.value.set(subject, colors[colorIndex % colors.length]);
      colorIndex++;
    }
  });
};

// Get consistent color for subject
const getSubjectColor = (subject) => {
  if (!subject) return 'transparent';
  
  if (subjectColorMap.value.has(subject)) {
    return subjectColorMap.value.get(subject);
  }
  
  const colors = currentColorScheme.value;
  const existingColors = Array.from(subjectColorMap.value.values());
  const availableColors = colors.filter(color => !existingColors.includes(color));
  
  const colorToUse = availableColors.length > 0 ? 
    availableColors[0] : 
    colors[subjectColorMap.value.size % colors.length];
  
  subjectColorMap.value.set(subject, colorToUse);
  return colorToUse;
};

// State
const selectedStages = ref(['prim', 'middle', 'sec']); // Default to all stages selected
const selectedDay = ref('today');
const currentTimeDisplay = ref('');
const timelineGrid = ref(null);
let timeUpdateInterval = null;

// User Events State
const userEvents = ref([]);
const showAddEventDialog = ref(false);
const showLatexHelp = ref(false);
const newEvent = ref({
  title: '',
  stageId: '',
  startTime: '',
  endTime: '',
  color: '#8b5cf6'
});

// Editing State
const editingPeriod = ref({
  timeSlotId: null,
  stageId: null,
  dayId: null,
  subject: '',
  teacher: '',
  originalSubject: '',
  originalTeacher: ''
});

// Available colors for user events
const eventColors = [
  '#8b5cf6', '#ef4444', '#10b981', '#f59e0b', '#ec4899', 
  '#14b8a6', '#f97316', '#6366f1', '#84cc16', '#06b6d4'
];

// Stage definitions
const allStages = [
  { id: 'prim', title: 'Primary' },
  { id: 'middle', title: 'Middle' },
  { id: 'sec', title: 'Secondary' }
];

// Computed properties
const displayStages = computed(() => {
  return allStages.filter(stage => selectedStages.value.includes(stage.id));
});

const timeSlots = computed(() => {
  const slots = resolvedTimeSlots.value || [];
  console.log('TimeLineView - timeSlots:', slots);
  console.log('TimeLineView - displayStages:', displayStages.value);
  console.log('TimeLineView - scheduleData:', store.scheduleData.value);
  
  // Fallback data if no slots are provided
  if (slots.length === 0) {
    console.log('Using fallback timing data');
    return [
      { id: 1, title: 'Period 1', type: 'lesson', start: '09:00', end: '09:30', startMin: 540, endMin: 570 },
      { id: 2, title: 'Period 2', type: 'lesson', start: '09:30', end: '10:00', startMin: 570, endMin: 600 },
      { id: 'b1', title: 'First Break', type: 'break', start: '10:00', end: '10:30', startMin: 600, endMin: 630 },
      { id: 3, title: 'Period 3', type: 'lesson', start: '10:30', end: '11:00', startMin: 630, endMin: 660 },
      { id: 4, title: 'Period 4', type: 'lesson', start: '11:00', end: '11:30', startMin: 660, endMin: 690 },
      { id: 'b2', title: 'Second Break', type: 'break', start: '11:30', end: '12:00', startMin: 690, endMin: 720 },
      { id: 5, title: 'Period 5', type: 'lesson', start: '12:00', end: '12:25', startMin: 720, endMin: 745 },
      { id: 6, title: 'Period 6', type: 'lesson', start: '12:25', end: '12:50', startMin: 745, endMin: 770 }
    ];
  }
  
  return slots;
});

// Generate hours for the timeline (6:00 to 22:00)
const hours = computed(() => {
  const hourList = [];
  for (let i = 6; i <= 22; i++) {
    hourList.push(i);
  }
  return hourList;
});

const showCurrentTimeIndicator = computed(() => {
  return props.showCurrentTime && selectedDay.value === 'today';
});

// Time positioning functions
const getTimePosition = (minutes) => {
  // Convert minutes to pixels (2px per minute, starting from 6:00)
  const startOfDay = 6 * 60; // 6:00 AM
  return (minutes - startOfDay) * 2; // 2px per minute for better visibility
};

const getTimeHeight = (startMin, endMin) => {
  return (endMin - startMin) * 2; // 2px per minute for better visibility
};

// Helper functions
const getTodayDayId = () => {
  const dayIndex = new Date().getDay();
  const mapping = [null, 'd1', 'd2', 'd3', 'd4', 'd5', 'd6', 'd1'];
  return mapping[dayIndex] || 'd1';
};

const getDayLabel = (dayId) => {
  if (dayId === 'today') {
    dayId = getTodayDayId();
  }
  const dayLabels = {
    d1: 'Monday',
    d2: 'Tuesday',
    d3: 'Wednesday',
    d4: 'Thursday',
    d5: 'Friday',
    d6: 'Saturday'
  };
  return dayLabels[dayId] || 'Unknown';
};

const getDayIndexFromId = (dayId) => {
  const mapping = { d1: 1, d2: 2, d3: 3, d4: 4, d5: 5, d6: 6 };
  return mapping[dayId] || 1;
};

const getActualDayId = () => {
  return selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value;
};

const getPeriodTitle = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const scheduleData = store.scheduleData.value;
  
  if (!Array.isArray(scheduleData)) {
    return `${timeSlot.title} from ${timeSlot.start} to ${timeSlot.end}`;
  }
  
  const daySchedule = scheduleData.find(item => item.day === actualDayId || item.dayIndex === getDayIndexFromId(actualDayId));
  
  let subjectInfo = '';
  if (daySchedule && daySchedule.classes) {
    const period = daySchedule.classes.find(p => p.p === timeSlot.id);
    if (period && period.sub) {
      subjectInfo = ` [${period.sub}]`;
    }
  }
  
  return `${timeSlot.title} from ${timeSlot.start} to ${timeSlot.end}${subjectInfo}`;
};

const getPeriodSubject = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const scheduleData = store.scheduleData.value;
  
  if (!Array.isArray(scheduleData)) {
    return '';
  }
  
  const daySchedule = scheduleData.find(item => item.day === actualDayId || item.dayIndex === getDayIndexFromId(actualDayId));
  
  if (daySchedule && daySchedule.classes) {
    const period = daySchedule.classes.find(p => p.p === timeSlot.id);
    if (period && period.sub) {
      return period.sub;
    }
  }
  
  return '';
};

const getPeriodColor = (timeSlot, stageId, dayId) => {
  const subject = getPeriodSubject(timeSlot, stageId, dayId);
  return getSubjectColor(subject);
};

const hasNafs = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const scheduleData = store.scheduleData.value;
  
  if (!Array.isArray(scheduleData)) {
    return false;
  }
  
  const daySchedule = scheduleData.find(item => item.day === actualDayId || item.dayIndex === getDayIndexFromId(actualDayId));
  
  if (daySchedule && daySchedule.classes) {
    const period = daySchedule.classes.find(p => p.p === timeSlot.id);
    return period?.nafs || false;
  }
  
  return false;
};

const getPeriodTeacher = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const schoolTimetable = store.schoolTimetable.value;
  const stageData = schoolTimetable.stages?.[stageId];
  
  // Handle the nested structure with teachers and assignments
  if (stageData && stageData.teachers && Array.isArray(stageData.teachers)) {
    for (const teacher of stageData.teachers) {
      const assignments = teacher.assignments?.[actualDayId];
      if (assignments && assignments[timeSlot.id]) {
        return teacher.name;
      }
    }
  }
  
  return '';
};

const hasPeriodContent = (timeSlot, stageId, dayId) => {
  const title = getPeriodTitle(timeSlot, stageId, dayId);
  const subject = getPeriodSubject(timeSlot, stageId, dayId);
  const teacher = getPeriodTeacher(timeSlot, stageId, dayId);
  return title || subject || teacher;
};

const isCurrentPeriod = (timeSlot, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const todayDayId = getTodayDayId();
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  
  return actualDayId === todayDayId && 
         currentMinutes >= timeSlot.startMin && 
         currentMinutes < timeSlot.endMin;
};

const getPeriodProgress = (timeSlot) => {
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  const start = timeSlot.startMin || 0;
  const end = timeSlot.endMin || 0;
  
  if (currentMinutes < start) return 0;
  if (currentMinutes >= end) return 100;
  
  const elapsed = currentMinutes - start;
  const total = end - start;
  return Math.min(100, Math.max(0, (elapsed / total) * 100));
};

const getCurrentTimeStyle = () => {
  const now = new Date();
  const currentMinutes = now.getHours() * 60 + now.getMinutes();
  
  return {
    top: `${60 + getTimePosition(currentMinutes)}px` // 60px for header
  };
};

const updateCurrentTime = () => {
  const now = new Date();
  currentTimeDisplay.value = now.toLocaleTimeString([], { 
    hour: '2-digit', 
    minute: '2-digit',
    second: '2-digit'
  });
};

// User Event Functions
const timeToMinutes = (timeStr) => {
  const [hours, minutes] = timeStr.split(':').map(Number);
  return hours * 60 + minutes;
};

const addUserEvent = () => {
  const event = {
    id: Date.now(),
    title: newEvent.value.title,
    stageId: newEvent.value.stageId,
    startTime: newEvent.value.startTime,
    endTime: newEvent.value.endTime,
    color: newEvent.value.color,
    startMin: timeToMinutes(newEvent.value.startTime),
    endMin: timeToMinutes(newEvent.value.endTime),
    dayId: selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value
  };
  
  userEvents.value.push(event);
  saveUserEvents();
  
  // Reset form
  newEvent.value = {
    title: '',
    stageId: '',
    startTime: '',
    endTime: '',
    color: '#8b5cf6'
  };
  showAddEventDialog.value = false;
};

const removeUserEvent = (eventId) => {
  userEvents.value = userEvents.value.filter(event => event.id !== eventId);
  saveUserEvents();
};

const removeAllUserEvents = () => {
  if (confirm('Are you sure you want to remove all your custom events?')) {
    userEvents.value = [];
    saveUserEvents();
  }
};

const getUserEventsForStage = (stageId) => {
  const actualDayId = selectedDay.value === 'today' ? getTodayDayId() : selectedDay.value;
  return userEvents.value.filter(event => 
    event.stageId === stageId && event.dayId === actualDayId
  );
};

const saveUserEvents = () => {
  try {
    localStorage.setItem('timeline-user-events', JSON.stringify(userEvents.value));
  } catch (e) {
    console.warn('Failed to save user events:', e);
  }
};

const loadUserEvents = () => {
  try {
    const saved = localStorage.getItem('timeline-user-events');
    if (saved) {
      userEvents.value = JSON.parse(saved);
    }
  } catch (e) {
    console.warn('Failed to load user events:', e);
  }
};

// Period Editing Functions
const isEditingPeriod = (timeSlotId, stageId, dayId) => {
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  return editingPeriod.value.timeSlotId === timeSlotId && 
         editingPeriod.value.stageId === stageId && 
         editingPeriod.value.dayId === actualDayId;
};

const startEditingPeriod = (timeSlot, stageId, dayId) => {
  console.log('Double-click detected!', { timeSlot, stageId, dayId });
  const actualDayId = dayId === 'today' ? getTodayDayId() : dayId;
  const currentSubject = getPeriodSubject(timeSlot, stageId, dayId);
  const currentTeacher = getPeriodTeacher(timeSlot, stageId, dayId);
  
  editingPeriod.value = {
    timeSlotId: timeSlot.id,
    stageId: stageId,
    dayId: actualDayId,
    subject: currentSubject || '',
    teacher: currentTeacher || '',
    originalSubject: currentSubject || '',
    originalTeacher: currentTeacher || ''
  };
  
  // Focus on the subject input after DOM update
  nextTick(() => {
    const subjectInput = document.querySelector('.edit-subject');
    if (subjectInput) {
      subjectInput.focus();
      subjectInput.select();
    }
  });
};

const saveEditingPeriod = () => {
  const { timeSlotId, stageId, dayId, subject, teacher, originalSubject, originalTeacher } = editingPeriod.value;
  
  // Only update if something changed
  if (subject !== originalSubject || teacher !== originalTeacher) {
    updatePeriodData(timeSlotId, stageId, dayId, subject, teacher);
  }
  
  // Reset editing state
  editingPeriod.value = {
    timeSlotId: null,
    stageId: null,
    dayId: null,
    subject: '',
    teacher: '',
    originalSubject: '',
    originalTeacher: ''
  };
};

const cancelEditingPeriod = () => {
  editingPeriod.value = {
    timeSlotId: null,
    stageId: null,
    dayId: null,
    subject: '',
    teacher: '',
    originalSubject: '',
    originalTeacher: ''
  };
};

const updatePeriodData = (timeSlotId, stageId, dayId, newSubject, newTeacher) => {
  const scheduleData = store.scheduleData.value;
  
  if (!Array.isArray(scheduleData)) {
    console.warn('No schedule data available for editing');
    return;
  }
  
  // Find the day schedule
  const daySchedule = scheduleData.find(item => item.day === dayId || item.dayIndex === getDayIndexFromId(dayId));
  
  if (daySchedule && daySchedule.classes) {
    // Find the period to update
    const period = daySchedule.classes.find(p => p.p === timeSlotId);
    
    if (period) {
      // Update the period data
      if (newSubject.trim()) {
        period.sub = newSubject.trim();
      } else {
        delete period.sub;
      }
      
      if (newTeacher.trim()) {
        // Update teacher in the school timetable
        updateTeacherAssignment(stageId, dayId, timeSlotId, newTeacher.trim());
      }
      
      // Save to store
      store.updateScheduleData(scheduleData);
      
      // Reinitialize color map to reflect changes
      initializeColorMap();
    }
  }
};

const updateTeacherAssignment = (stageId, dayId, timeSlotId, teacherName) => {
  const schoolTimetable = store.schoolTimetable.value;
  const stageData = schoolTimetable.stages?.[stageId];
  
  if (stageData && stageData.teachers && Array.isArray(stageData.teachers)) {
    // Find existing teacher assignment for this period
    let teacherFound = false;
    
    for (const teacher of stageData.teachers) {
      const assignments = teacher.assignments?.[dayId];
      if (assignments && assignments[timeSlotId]) {
        // Update existing assignment
        assignments[timeSlotId] = teacherName;
        teacherFound = true;
        break;
      }
    }
    
    // If no existing assignment found, create a new one
    if (!teacherFound) {
      if (!stageData.teachers[0].assignments) {
        stageData.teachers[0].assignments = {};
      }
      if (!stageData.teachers[0].assignments[dayId]) {
        stageData.teachers[0].assignments[dayId] = {};
      }
      stageData.teachers[0].assignments[dayId][timeSlotId] = teacherName;
    }
    
    // Update school timetable in store
    store.updateSchoolTimetable(schoolTimetable);
  }
};

// Lifecycle
onMounted(() => {
  updateCurrentTime();
  if (props.autoRefresh) {
    timeUpdateInterval = setInterval(updateCurrentTime, 1000);
  }
  // Initialize color mapping
  initializeColorMap();
  // Load user events
  loadUserEvents();
  // Load KaTeX for math rendering
  loadKaTeX();
});

onUnmounted(() => {
  if (timeUpdateInterval) {
    clearInterval(timeUpdateInterval);
  }
});

// KaTeX Math Rendering
const loadKaTeX = () => {
  // Load KaTeX script
  const script = document.createElement('script');
  script.src = 'https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js';
  script.integrity = 'sha384-XjKyOOlGwcjNTAIQHIpgOno0Hl1YQqzUOEleOLALmuqehneUG+vnGctmUbKyIyUH';
  script.crossOrigin = 'anonymous';
  script.onload = () => {
    console.log('KaTeX loaded successfully');
  };
  document.head.appendChild(script);
};

const renderLatexMath = (text) => {
  if (!text || typeof text !== 'string') return text;
  
  // Check if text contains LaTeX patterns (between $...$ or $$...$$)
  const latexPattern = /\$\$([^$]+)\$\$|\$([^$]+)\$/g;
  
  if (!latexPattern.test(text)) return text;
  
  // Reset regex lastIndex
  latexPattern.lastIndex = 0;
  
  let rendered = text;
  const matches = [];
  let match;
  
  // Collect all matches
  while ((match = latexPattern.exec(text)) !== null) {
    matches.push({
      full: match[0],
      latex: match[1] || match[2],
      displayMode: !!match[1] // $$...$$ is display mode
    });
  }
  
  // Replace each LaTeX expression with rendered math
  matches.forEach((matchInfo) => {
    try {
      if (typeof katex !== 'undefined') {
        const renderedMath = katex.renderToString(matchInfo.latex, {
          displayMode: matchInfo.displayMode,
          throwOnError: false,
          errorColor: '#ef4444'
        });
        rendered = rendered.replace(matchInfo.full, renderedMath);
      }
    } catch (error) {
      console.warn('LaTeX rendering error:', error);
      // Keep original text if rendering fails
    }
  });
  
  return rendered;
};

// Enhanced period content functions with LaTeX support
const getPeriodTitleWithMath = (timeSlot, stageId, dayId) => {
  const title = getPeriodTitle(timeSlot, stageId, dayId);
  return renderLatexMath(title);
};

const getPeriodTeacherWithMath = (timeSlot, stageId, dayId) => {
  const teacher = getPeriodTeacher(timeSlot, stageId, dayId);
  return renderLatexMath(teacher);
};
</script>

<style scoped>
.timeline-view {
  padding: 1rem;
  background: #f8fafc;
  border-radius: 8px;
  overflow: hidden;
  position: relative;
}

/* Controls */
.timeline-controls {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.control-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.view-mode-select,
.stage-select,
.day-select {
  padding: 0.5rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 0.875rem;
  min-width: 120px;
}

/* Stage Checkboxes */
.stage-checkboxes {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.stage-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: white;
  transition: all 0.2s ease;
}

.stage-checkbox:hover {
  border-color: #3b82f6;
  background: #f0f9ff;
}

.checkbox-input {
  width: 16px;
  height: 16px;
  accent-color: #3b82f6;
}

.checkbox-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
  user-select: none;
}

/* Timeline Container */
.timeline-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  position: relative;
}

/* Header */
.timeline-header {
  display: flex;
  background: #f1f5f9;
  border-bottom: 2px solid #e2e8f0;
  position: sticky;
  top: 0;
  z-index: 10;
}

.time-column-header {
  width: 80px;
  padding: 1rem 0.5rem;
  font-weight: 600;
  color: #374151;
  border-right: 2px solid #e2e8f0;
  background: #f8fafc;
  text-align: center;
}

.stage-header {
  flex: 1;
  min-width: 200px;
  padding: 0.75rem 0.5rem;
  text-align: center;
  border-right: 1px solid #e2e8f0;
}

.stage-title {
  font-weight: 700;
  color: #1f2937;
  font-size: 1rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stage-subtitle {
  color: #6b7280;
  font-size: 0.75rem;
  margin-top: 0.25rem;
}

/* Timeline Grid */
.timeline-grid {
  position: relative;
  display: flex;
  min-height: 1920px; /* 16 hours * 120px per hour (2px per minute) */
  background: linear-gradient(to bottom, #ffffff, #f8fafc);
}

/* Horizontal Grid Lines */
.horizontal-grid {
  position: absolute;
  left: 80px; /* Start after time column */
  right: 0;
  top: 0;
  bottom: 0;
  pointer-events: none;
}

.horizontal-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #f1f5f9;
  z-index: 0;
}

/* Time Grid Background */
.time-grid-background {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  width: 80px;
  background: #f8fafc;
  border-right: 2px solid #e2e8f0;
}

.hour-marker {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
}

.hour-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 1px;
  background: #d1d5db;
  z-index: 0;
}

.hour-label {
  position: absolute;
  left: 4px;
  top: -8px;
  font-size: 0.625rem;
  color: #6b7280;
  font-weight: 600;
  background: #f8fafc;
  padding: 2px 4px;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
  z-index: 10;
  white-space: nowrap;
}

/* Stage Columns */
.stage-column {
  flex: 1;
  min-width: 200px;
  border-right: 1px solid #f1f5f9;
  position: relative;
}

.stage-column:last-child {
  border-right: none;
}

.stage-content {
  position: relative;
  height: 100%;
}

/* Period Blocks - Positioned by actual time */
.period-block {
  position: absolute;
  left: 8px;
  right: 8px;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
  cursor: pointer;
  z-index: 1;
  min-height: 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border: 1px solid rgba(0, 0, 0, 0.1);
}

.period-block.editing {
  z-index: 10;
  box-shadow: 0 0 0 3px #3b82f6, 0 4px 20px rgba(59, 130, 246, 0.4);
  transform: scale(1.05);
}

/* Period Edit Mode */
.period-edit-mode {
  padding: 8px;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.edit-form {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.edit-input {
  padding: 4px 6px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.9);
  font-size: 0.65rem;
  font-weight: 500;
  color: #1f2937;
  outline: none;
  transition: all 0.2s ease;
}

.edit-input:focus {
  border-color: #3b82f6;
  background: rgba(255, 255, 255, 1);
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}

.edit-input::placeholder {
  color: #9ca3af;
  font-style: italic;
}

.edit-actions {
  display: flex;
  gap: 4px;
  justify-content: center;
  margin-top: 4px;
}

.edit-btn {
  width: 20px;
  height: 20px;
  border: none;
  border-radius: 50%;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.save-btn {
  background: #10b981;
  color: white;
}

.save-btn:hover {
  background: #059669;
  transform: scale(1.1);
}

.cancel-btn {
  background: #ef4444;
  color: white;
}

.cancel-btn:hover {
  background: #dc2626;
  transform: scale(1.1);
}

.period-block.has-content {
  color: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(0, 0, 0, 0.2);
}

.period-block.break-period {
  background: linear-gradient(135deg, #10b981, #059669) !important;
  color: white;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
  border: 1px solid rgba(16, 185, 129, 0.2);
}

.period-block.activity-period {
  background: linear-gradient(135deg, #f59e0b, #d97706) !important;
  color: white;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
  border: 1px solid rgba(245, 158, 11, 0.2);
}

.period-block.current-period {
  box-shadow: 0 0 0 3px #ef4444, 0 0 20px rgba(239, 68, 68, 0.5) !important;
  z-index: 5;
  animation: pulse 2s infinite;
}

.period-block.has-nafs {
  position: relative;
}

.period-block.has-nafs::after {
  content: '';
  position: absolute;
  top: 2px;
  right: 2px;
  width: 8px;
  height: 8px;
  background: rgba(255, 255, 255, 0.8);
  border-radius: 50%;
}

@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 3px #ef4444, 0 0 20px rgba(239, 68, 68, 0.5);
  }
  50% {
    box-shadow: 0 0 0 3px #ef4444, 0 0 30px rgba(239, 68, 68, 0.7);
  }
}

.period-block:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
  z-index: 3;
}

.period-content {
  padding: 6px;
  font-size: 0.7rem;
  line-height: 1.2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 100%;
  word-wrap: break-word;
  overflow: hidden;
  position: relative;
}

.period-title {
  font-weight: 600;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.65rem;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

.period-teacher {
  opacity: 0.9;
  font-size: 0.6rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

.nafs-indicator {
  position: absolute;
  top: 2px;
  right: 2px;
  background: rgba(255, 255, 255, 0.9);
  color: #333;
  padding: 1px 3px;
  border-radius: 3px;
  font-size: 0.5rem;
  font-weight: 700;
  text-transform: uppercase;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.edit-hint {
  position: absolute;
  bottom: 2px;
  right: 2px;
  background: rgba(59, 130, 246, 0.9);
  color: white;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.45rem;
  font-weight: 600;
  opacity: 0;
  transition: opacity 0.2s ease;
  pointer-events: none;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.period-block:hover .edit-hint {
  opacity: 1;
}

/* KaTeX Math Styles */
.period-content .katex {
  font-size: 0.9em;
}

.period-content .katex-display {
  margin: 2px 0;
  text-align: center;
}

.period-content .katex-error {
  color: #ef4444 !important;
  background: rgba(239, 68, 68, 0.1);
  padding: 1px 2px;
  border-radius: 2px;
  font-size: 0.8em;
}

/* Ensure math doesn't break layout */
.period-title .katex,
.period-teacher .katex {
  display: inline-block;
  vertical-align: middle;
  line-height: 1;
}

/* Adjust math size for small period blocks */
.period-block.has-content .katex {
  font-size: 0.8em;
}

/* Dark mode support for KaTeX */
@media (prefers-color-scheme: dark) {
  .period-content .katex {
    color: #f1f5f9;
  }
  
  .period-content .katex .frac-line {
    border-bottom-color: #94a3b8;
  }
  
  .period-content .katex .sqrt > .root {
    border-top-color: #94a3b8;
  }
  
  .period-content .katex .overline {
    border-top-color: #94a3b8;
  }
  
  .period-content .katex .underline {
    border-bottom-color: #94a3b8;
  }
}

/* Progress Indicator */
.progress-indicator {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: rgba(255, 255, 255, 0.3);
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #ef4444;
  transition: height 1s linear;
}

.progress-line {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  box-shadow: 0 0 4px rgba(239, 68, 68, 0.8);
}

.progress-line::before {
  content: '';
  position: absolute;
  right: 0;
  top: -2px;
  width: 6px;
  height: 6px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 3px rgba(239, 68, 68, 0.8);
}

/* Current Time Line */
.current-time-line {
  position: absolute;
  left: 0;
  right: 0;
  height: 2px;
  background: #ef4444;
  z-index: 20;
  pointer-events: none;
}

.current-time-line .time-label {
  position: absolute;
  left: -80px;
  top: -10px;
  background: #ef4444;
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.625rem;
  font-weight: 600;
  white-space: nowrap;
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.time-dot {
  position: absolute;
  left: -4px;
  top: -3px;
  width: 8px;
  height: 8px;
  background: #ef4444;
  border-radius: 50%;
  box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
}

/* Dark Mode */
@media (prefers-color-scheme: dark) {
  .timeline-view {
    background: #1e293b;
  }
  
  .timeline-container {
    background: #0f172a;
  }
  
  .timeline-header {
    background: #1e293b;
    border-bottom-color: #334155;
  }
  
  .time-column-header {
    background: #1e293b;
    color: #e2e8f0;
    border-right-color: #334155;
  }
  
  .stage-header {
    border-right-color: #334155;
  }
  
  .stage-title {
    color: #f1f5f9;
  }
  
  .stage-subtitle {
    color: #94a3b8;
  }
  
  .timeline-grid {
    background: linear-gradient(to bottom, #0f172a, #1e293b);
  }
  
  .time-grid-background {
    background: #1e293b;
    border-right-color: #334155;
  }
  
  .hour-line {
    background: #334155;
  }
  
  .hour-label {
    color: #94a3b8;
  }
  
  .stage-column {
    border-right-color: #334155;
  }
  
  .view-mode-select,
  .stage-select,
  .day-select {
    background: #1e293b;
    border-color: #475569;
    color: #e2e8f0;
  }
  
  .stage-checkbox {
    background: #1e293b;
    border-color: #475569;
  }
  
  .stage-checkbox:hover {
    border-color: #3b82f6;
    background: #1e3a8a;
  }
  
  .checkbox-label {
    color: #e2e8f0;
  }
  
  .control-label {
    color: #94a3b8;
  }
}

/* Event Controls */
.event-controls {
  display: flex;
  gap: 0.5rem;
  align-items: center;
}

.btn-add-event {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-event:hover {
  background: #2563eb;
  transform: translateY(-1px);
}

.btn-clear-events {
  background: #ef4444;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-clear-events:hover {
  background: #dc2626;
  transform: translateY(-1px);
}

/* User Event Blocks */
.user-event-block {
  position: absolute;
  left: 8px;
  right: 8px;
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.2s ease;
  cursor: pointer;
  z-index: 2;
  min-height: 20px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  border: 2px solid rgba(0, 0, 0, 0.2);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.user-event-block:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
  z-index: 4;
}

.user-event-content {
  padding: 6px;
  font-size: 0.7rem;
  line-height: 1.2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 100%;
  position: relative;
  color: white;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
}

.user-event-title {
  font-weight: 600;
  margin-bottom: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.65rem;
}

.user-event-time {
  opacity: 0.9;
  font-size: 0.6rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-weight: 500;
}

.btn-remove-event {
  position: absolute;
  top: 2px;
  right: 2px;
  background: rgba(255, 255, 255, 0.9);
  color: #333;
  border: none;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  font-size: 10px;
  font-weight: 700;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.user-event-block:hover .btn-remove-event {
  opacity: 1;
}

.btn-remove-event:hover {
  background: #ef4444;
  color: white;
}

/* Dialog Styles */
.dialog-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.dialog-content {
  background: white;
  border-radius: 12px;
  padding: 2rem;
  width: 90%;
  max-width: 400px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.dialog-content h3 {
  margin: 0 0 1.5rem 0;
  color: #1f2937;
  font-size: 1.25rem;
  font-weight: 700;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  color: #374151;
  font-weight: 600;
  font-size: 0.875rem;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.875rem;
  transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.color-picker {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.color-option {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  cursor: pointer;
  border: 2px solid transparent;
  transition: all 0.2s ease;
}

.color-option:hover {
  transform: scale(1.1);
}

.color-option.active {
  border-color: #1f2937;
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

.btn-cancel {
  background: #f3f4f6;
  color: #374151;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-cancel:hover {
  background: #e5e7eb;
}

.btn-add {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-add:hover {
  background: #2563eb;
}

/* LaTeX Help Button */
.btn-latex-help {
  background: #8b5cf6;
  color: white;
  border: none;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-latex-help:hover {
  background: #7c3aed;
  transform: translateY(-1px);
}

/* LaTeX Hint */
.latex-hint {
  display: block;
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #6b7280;
  font-style: italic;
}

/* LaTeX Help Dialog */
.latex-help-content {
  max-width: 600px;
  max-height: 80vh;
  overflow-y: auto;
}

.latex-examples {
  margin: 1.5rem 0;
}

.example-section {
  margin-bottom: 2rem;
}

.example-section h4 {
  margin: 0 0 1rem 0;
  color: #1f2937;
  font-size: 1rem;
  font-weight: 600;
  border-bottom: 2px solid #e5e7eb;
  padding-bottom: 0.5rem;
}

.example-item {
  margin-bottom: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
  border-left: 4px solid #3b82f6;
}

.example-item code {
  display: block;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  color: #1f2937;
  background: #f3f4f6;
  padding: 0.5rem;
  border-radius: 4px;
  margin-bottom: 0.5rem;
  white-space: pre-wrap;
  word-break: break-all;
}

.rendered-example {
  font-size: 1.1rem;
  color: #374151;
  padding: 0.5rem;
  background: white;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
  text-align: center;
}

.symbols-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 0.75rem;
}

.symbol-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: #f9fafb;
  border-radius: 4px;
  border: 1px solid #e5e7eb;
}

.symbol-item code {
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
  color: #1f2937;
}

.symbol-item span {
  font-size: 1rem;
  font-weight: 500;
}
@media (max-width: 768px) {
  .timeline-controls {
    flex-direction: column;
  }
  
  .timeline-container {
    overflow-x: auto;
  }
  
  .time-column-header {
    width: 60px;
    font-size: 0.75rem;
  }
  
  .time-grid-background {
    width: 60px;
  }
  
  .hour-label {
    font-size: 0.5rem;
    left: 2px;
  }
  
  .stage-header {
    min-width: 150px;
    padding: 0.5rem;
  }
  
  .stage-column {
    min-width: 150px;
  }
  
  .period-content {
    padding: 0.25rem;
    font-size: 0.625rem;
  }
  
  .period-time {
    font-size: 0.5rem;
  }
  
  .period-title {
    font-size: 0.5625rem;
  }
  
  .period-subject {
    font-size: 0.5rem;
  }
  
  .period-teacher {
    font-size: 0.4375rem;
  }
}
</style>
