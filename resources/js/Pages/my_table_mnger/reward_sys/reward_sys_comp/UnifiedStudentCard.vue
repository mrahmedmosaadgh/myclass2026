<template>
  <div class="relative w-36 h-48 mt-14 flex flex-col items-center">
    
    <!-- Avatar Card (Clickable) -->
    <div
      :class="[
        'student-card',
        selected || selectedId === student.id ? 'selected' : '',
        cardMode === 'attendance' && !isPresent ? 'disabled-card' : '',
        cardMode === 'selection' && disableBehavior ? 'disabled-card' : ''
      ]"
      @click="handleCardClick"
      :title="getCardTitle"
    >

      <!-- Name Layer Above Avatar -->
      <div class="absolute -top-10 text-center w-full z-20 name-container">
        <div 
          class="first-name"
          :class="[
            selected || selectedId === student.id ? 'selected-name' : '', 
            (cardMode === 'attendance' && !isPresent) || (cardMode === 'selection' && disableBehavior) ? 'disabled-first-name' : ''
          ]"
        >{{ student.firstName }}</div>
        <div class="last-name">{{ student.lastName }}</div>
      </div>

      <!-- Avatar Manager Component -->
      <div class="student-avatar-wrapper z-10 relative">
        <AvatarManager 
          :student="student" 
          size="7.5rem"
          :edit-enabled="avatarEditEnabled"
          @update:avatar="(url) => student.avatar = url"
        />
        
        <!-- Points Badge (only show in selection mode or when specified) -->
        <div 
          v-if="showPointsBadge && studentSummary" 
          class="points-badge" 
          :class="pointsBadgeClass"
        >
          {{ studentSummary.total }}
          <q-tooltip class="bg-white text-black shadow-lg border border-gray-200">
             <div class="flex flex-col gap-1 p-1">
               <div class="text-green-600 font-bold">Positive: +{{ studentSummary.positive }}</div>
               <div class="text-red-600 font-bold">Negative: -{{ studentSummary.negative }}</div>
               <div class="border-t pt-1 font-bold">Total: {{ studentSummary.total }}</div>
             </div>
          </q-tooltip>
        </div>

        <!-- Attendance Status Indicator (only show in attendance mode) -->
        <div 
          v-if="cardMode === 'attendance'" 
          class="attendance-indicator"
          :class="isPresent ? 'present-indicator' : 'absent-indicator'"
        >
          <q-icon 
            :name="isPresent ? 'check_circle' : 'cancel'" 
            :color="isPresent ? 'positive' : 'negative'"
            size="sm"
          />
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import AvatarManager from './AvatarManager.vue'

const props = defineProps({
  student: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  selectedId: { type: [String, Number], default: null },
  cardClass: { type: [String, Object, Array], default: '' },
  studentSummary: { type: Object, default: () => ({ positive: 0, negative: 0, total: 0 }) },
  avatarEditEnabled: { type: Boolean, default: false },
  showAvatarButtons: { type: Boolean, default: false },
  disableBehavior: { type: Boolean, default: false },
  allowDisabledClick: { type: Boolean, default: false },
  
  // New props for unified functionality
  cardMode: { 
    type: String, 
    default: 'selection', 
    validator: (value) => ['attendance', 'selection'].includes(value)
  },
  isPresent: { type: Boolean, default: true },
  showPointsBadge: { type: Boolean, default: true }
})

const emit = defineEmits(['select', 'toggle-attendance', 'open-camera', 'open-behavior'])

const getCardTitle = computed(() => {
  const name = `${props.student.firstName} ${props.student.lastName}`.trim()
  
  if (props.cardMode === 'attendance') {
    return `${name} - Click to ${props.isPresent ? 'mark absent' : 'mark present'}`
  } else {
    if (props.disableBehavior) {
      return `${name} - Absent (cannot select)`
    }
    return `${name} - Click to ${props.selected ? 'deselect' : 'select'}`
  }
})

function handleCardClick() {
  if (props.cardMode === 'attendance') {
    // In attendance mode, always allow clicking to toggle attendance
    emit('toggle-attendance', props.student.id, !props.isPresent)
  } else {
    // In selection mode, check if disabled and allowDisabledClick
    if (props.disableBehavior && !props.allowDisabledClick) return
    emit('select', props.student.id)
  }
}

const pointsBadgeClass = computed(() => {
  const total = props.studentSummary?.total || 0
  if (total > 10) return 'badge-excellent'
  if (total > 0) return 'badge-good'
  if (total < 0) return 'badge-warning'
  return 'badge-neutral'
})
</script>

<style scoped>
/* Name Above Avatar */
.name-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: -0.3rem;
}

.first-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e3a8a;
  background: #fde047;
  padding: 0.1rem 0.6rem;
  border-radius: 0.7rem;
  box-shadow: 0 1px 5px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}

.first-name.selected-name {
  background: linear-gradient(135deg, #2427fb, #510bf5);
  color: white;
  transform: scale(1.2);
  box-shadow: 0 4px 12px rgba(251, 191, 36, 0.5);
}

.disabled-first-name {
  font-size: 1.1rem;
  color: #ffffff;
  background: #424242;
  padding: 0.1rem 0.6rem;
  border-radius: 0.7rem;
  box-shadow: 0 1px 5px rgba(0,0,0,0.1);
}

.last-name {
  font-size: 0.75rem;
  margin-top: -0.2rem;
  color: #475569;
}

/* Main Card Shell */
.student-card {
  width: 8rem;
  height: 8rem;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;

  /* Unselected State (Default) */
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  border: 4px solid #e2e8f0; /* Light gray border */
  
  transition: all 0.3s ease;
  cursor: pointer;
  position: relative;
  overflow: visible;
}

/* Avatar */
.student-avatar {
  width: 7.5rem;
  height: 7.5rem;
  border-radius: 50%;
  object-fit: cover;
  z-index: 5;
}

/* Hover Interaction */
.student-card:hover {
  transform: scale(1.05);
  box-shadow: 0 10px 15px rgba(0,0,0,0.1);
  border-color: #93c5fd; /* Light blue on hover */
  z-index: 20;
}

/* Selected State */
.student-card.selected {
  background: linear-gradient(135deg, #3b82f6, #8b5cf6, #ec4899);
  box-shadow: 0 0 0 5px #3b82f6, /* Blue ring */
              0 0 30px rgba(59, 130, 246, 0.8), /* Blue glow */
              0 0 50px rgba(139, 92, 246, 0.5); /* Purple outer glow */
  border-color: #ffffff; /* White border for contrast */
  transform: scale(1.15);
  z-index: 30;
  animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 0 5px #3b82f6,
                0 0 30px rgba(59, 130, 246, 0.8),
                0 0 50px rgba(139, 92, 246, 0.5);
  }
  50% {
    box-shadow: 0 0 0 5px #8b5cf6,
                0 0 40px rgba(139, 92, 246, 0.9),
                0 0 60px rgba(236, 72, 153, 0.6);
  }
}

/* Disabled/Absent State */
.disabled-card {
  opacity: 0.6;
  filter: grayscale(100%); /* Black and white */
  border-color: #94a3b8; /* Gray border */
  background-image: repeating-linear-gradient(
    45deg,
    transparent,
    transparent 10px,
    rgba(0, 0, 0, 0.05) 10px,
    rgba(0, 0, 0, 0.05) 20px
  );
}

/* Points Badge */
.points-badge {
  position: absolute;
  top: 0;
  right: 0;
  width: 32px;
  height: 32px;
  background: #2ecc71;
  color: white;
  font-weight: bold;
  font-size: 14px;
  line-height: 32px;
  border-radius: 50%;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
  z-index: 20;
  text-align: center;
  border: 2px solid white;
}

.points-badge.badge-excellent { background: linear-gradient(135deg, #2ecc71, #27ae60); }
.points-badge.badge-good { background: linear-gradient(135deg, #3498db, #2980b9); }
.points-badge.badge-neutral { background: linear-gradient(135deg, #95a5a6, #7f8c8d); }
.points-badge.badge-warning { background: linear-gradient(135deg, #e74c3c, #c0392b); }

/* Attendance Status Indicator */
.attendance-indicator {
  position: absolute;
  bottom: -5px;
  left: 50%;
  transform: translateX(-50%);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 20;
  border: 2px solid white;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.present-indicator {
  background: linear-gradient(135deg, #10b981, #059669);
}

.absent-indicator {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}
</style>