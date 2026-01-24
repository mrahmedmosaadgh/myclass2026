<template>
 <div class="relative w-36 flex flex-col items-center">
  
   <!-- Name at Top of Card -->
   <div class="text-center w-full mb-2 name-container">
     <!-- <div 
       class="first-name"
       :class="[
         selected || selectedId === student.id ? 'selected-name' : '', 
         disableBehavior ? 'disabled-first-name' : ''
       ]"
     >
       {{ localizedName.firstName }}

     </div> -->
     
     <div class="last-name mt-1 h-16" :class="{ 'selected-last-name': selected || selectedId === student.id , 'disabled-last-name': disableBehavior}">
           <div 
       class="first-name"
       :class="[
         selected || selectedId === student.id ? 'selected-name' : '', 
         disableBehavior ? 'disabled-first-name' : ''
       ]"
     >
       {{ localizedName.firstName }}

     </div>
      
      <span v-if="localizedName.secondName" class="block text-xs font-normal opacity-90 mb-0.5">
         {{ localizedName.secondName }}
       </span>


       {{ localizedName.lastName }}
     </div>
   </div>
  
  <!-- Avatar Card (Clickable) -->
  <div
    :class="[
      'student-card',
      selected || selectedId === student.id ? 'selected' : '',
      disableBehavior ? 'disabled-card' : '',
      isAbsent ? 'absent-card' : ''
    ]"
    @click="handleCardClick"
  >
    <q-tooltip anchor="top middle" self="bottom middle" :offset="[0, 45]" class="bg-gray-900 text-white shadow-xl border border-gray-700">
      <div class="text-center p-1">
        <div class="font-bold text-base">{{ student.name }}</div>
        <div v-if="student.name_ar" class="text-sm text-yellow-300 font-arabic mt-1">{{ student.name_ar }}</div>
      </div>
    </q-tooltip>



    <!-- Avatar Manager Component -->
    <div class="student-avatar-wrapper z-10 relative">
      <AvatarManager 
        :student="student" 
        size="7.5rem"
        :edit-enabled="avatarEditEnabled"
        @update:avatar="(url) => student.avatar = url"
      />
      
      <!-- Points Badge -->
      <div class="points-badge" :class="pointsBadgeClass" v-if="studentSummary">
        {{ studentSummary.total }}
        <q-tooltip class="bg-white text-black shadow-lg border border-gray-200">
           <div class="flex flex-col gap-1 p-1">
             <div class="text-green-600 font-bold">{{ $t('rewardSys.points.positive') }}: +{{ studentSummary.positive }}</div>
             <div class="text-red-600 font-bold">{{ $t('rewardSys.points.negative') }}: -{{ studentSummary.negative }}</div>
             <div class="border-t pt-1 font-bold">{{ $t('rewardSys.points.total') }}: {{ studentSummary.total }}</div>
           </div>
        </q-tooltip>
      </div>
    </div>
  </div>



</div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AvatarManager from './AvatarManager.vue'

const { locale } = useI18n()

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
  isAbsent: { type: Boolean, default: false }
})

const emit = defineEmits(['select', 'open-camera', 'open-behavior'])

function handleCardClick() {
  if (props.disableBehavior && !props.allowDisabledClick) return
  emit('select', props.student.id)
}

function parseName(fullName) {
	if (!fullName || typeof fullName !== 'string') return { firstName: '', secondName: '', lastName: '' }
	const parts = fullName.trim().split(/\s+/).filter(p => p.length > 0)
	const firstName = parts[0] || ''
	const lastName = parts.length > 1 ? parts[parts.length - 1] : ''
	const secondName = parts.length > 2 ? parts.slice(1, -1).join(' ') : ''
	return { firstName, secondName, lastName }
}

const localizedName = computed(() => {
  if (locale.value === 'ar' && props.student.name_ar) {
    const parsed = parseName(props.student.name_ar)
    return { ...parsed, fullName: props.student.name_ar }
  }
  // Fallback to existing props or parse English name
  const parsed = parseName(props.student.name)
  return { 
    firstName: props.student.firstName || parsed.firstName,
    lastName: props.student.lastName || parsed.lastName,
    secondName: parsed.secondName,
    fullName: props.student.name
  }
})

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
  margin-top: -0.5rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15));
}

.first-name {
  font-size: 1.15rem;
  /* font-weight: 800; */
  color: #ffffff;
  /* background: linear-gradient(135deg, #120a5e 0%, #540379 50%, #02357d 100%); */
   background: linear-gradient(135deg, #3e3a3a, #424242);
  /* background: linear-gradient(135deg, #ff6b6b 0%, #ff8e53 50%, #ffd93d 100%); */
  padding: 0.25rem 0.8rem;
  border-radius: 1rem;
  /* box-shadow: 
    0 4px 8px rgba(255, 107, 107, 0.3),
    0 2px 4px rgba(0, 0, 0, 0.1),
    inset 0 -2px 4px rgba(0, 0, 0, 0.1); */
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
  transform: perspective(500px) rotateX(5deg);
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  letter-spacing: 0.5px;
  /* Removed infinite float animation for better performance */
}

@keyframes float {
  0%, 100% { transform: perspective(500px) rotateX(5deg) translateY(0px); }
  50% { transform: perspective(500px) rotateX(5deg) translateY(-3px); }
}

.first-name.selected-name {
  background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); /* Cool Blue Gradient */
  color: white;
  font-weight: 800;
  box-shadow: 
    0 4px 12px rgba(0, 114, 255, 0.4),
    0 2px 4px rgba(0, 0, 0, 0.1);
  transform: translateY(-2px); /* Subtle lift */
}

@keyframes rainbow-pulse {
  0%, 100% {
    filter: hue-rotate(0deg) brightness(1.1);
  }
  50% {
    filter: hue-rotate(20deg) brightness(1.2);
  }
}

.disabled-first-name {
  font-size: 1.1rem;
  color: #060606;
  background: transparent;
  padding: 0.25rem 0.7rem;
  border-radius: 1rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.2);
  opacity: 0.7;
}

.last-name {
  font-size: 0.85rem;
  margin-top: 0.1rem;
  font-weight: 600;
  color: #4a5568;
  background: transparent;
  /* background: linear-gradient(135deg, #f7fafc, #edf2f7); */
  padding: 0.15rem 0.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8);
  transition: all 0.3s ease;
}
 .disabled-last-name  { 
  color: #060606;
  background: transparent;
}

.last-name.selected-last-name {
  background: #0072ff;
  color: white;
  text-shadow: none;
  box-shadow: 0 2px 6px rgba(0, 114, 255, 0.3);
}

.full-name-badge-bottom {
  margin-top: 0.5rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #333;
  background: white;
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e5e7eb;
  z-index: 20;
  white-space: nowrap;
  max-width: 150%;
  overflow: hidden;
  text-overflow: ellipsis;
  animation: slide-up-fade 0.3s ease-out;
}

@keyframes slide-up-fade {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Main Card Shell */
.student-card {
  width: 8.5rem;
  height: 8.5rem;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  
  /* Nice Shadow */
  /* box-shadow: 
    0 4px 6px -1px rgba(0, 0, 0, 0.1), 
    0 2px 4px -1px rgba(0, 0, 0, 0.06); */
  border: none;
  background-clip: padding-box;
  position: relative;
  
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  overflow: visible;
  transform: perspective(1000px) rotateY(0deg);
}

/* Removed colorful border gradient */
.student-card::before {
  display: none;
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
  transform: perspective(1000px) rotateY(5deg) scale(1.08) translateY(-5px);
  box-shadow: 
    0 12px 24px rgba(0, 0, 0, 0.18),
    0 6px 12px rgba(59, 130, 246, 0.15),
    0 0 30px rgba(96, 165, 250, 0.3),
    inset 0 -4px 8px rgba(0, 0, 0, 0.08),
    inset 0 4px 12px rgba(255, 255, 255, 0.6);
  z-index: 20;
}

.student-card:hover::before {
  opacity: 1;
  /* Removed infinite rotate-gradient animation for better performance */
}

@keyframes rotate-gradient {
  0% { filter: hue-rotate(0deg); }
  100% { filter: hue-rotate(360deg); }
}

/* Selected State */
.student-card.selected {
  background: #f0f9ff; /* Very light blue tint */
  border: 3px solid #00c6ff; /* Cool Cyan Border */
  box-shadow: 
    0 0 15px rgba(0, 198, 255, 0.4), /* Outer Glow */
    inset 0 0 10px rgba(0, 114, 255, 0.1); /* Inner Glow */
  z-index: 30;
  transform: scale(1.02); /* Very subtle scale to indicators selection without disrupting layout too much, or keep 1 if strict */
}

/* Cleaned up selected state animations */

/* Disabled/Absent State */
.disabled-card {
  opacity: 0.5;
  filter: grayscale(100%) brightness(0.9);
  border-color: #94a3b8;
  background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
  transform: perspective(1000px) scale(0.95);
}

.disabled-card::before {
  background: linear-gradient(135deg, #94a3b8, #64748b);
  opacity: 0.4;
}

/* Points Badge */
.points-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  min-width: 38px;
  height: 38px;
  padding: 0 6px;
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
  font-weight: 900;
  font-size: 15px;
  line-height: 38px;
  border-radius: 50%;
  box-shadow: 
    0 4px 12px rgba(16, 185, 129, 0.4),
    0 2px 6px rgba(0, 0, 0, 0.2),
    inset 0 -2px 4px rgba(0, 0, 0, 0.2),
    inset 0 2px 4px rgba(255, 255, 255, 0.3);
  z-index: 25;
  text-align: center;
  border: 3px solid white;
  transition: all 0.3s ease;
  animation: badge-pop 0.5s ease-out;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
}

@keyframes badge-pop {
  0% { transform: scale(0); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

.points-badge:hover {
  transform: scale(1.15) rotate(5deg);
}

.points-badge.badge-excellent { 
  background: linear-gradient(135deg, #10b981 0%, #059669 50%, #047857 100%);
  box-shadow: 
    0 4px 12px rgba(16, 185, 129, 0.5),
    0 0 20px rgba(16, 185, 129, 0.3);
  animation: badge-pop 0.5s ease-out; /* Removed infinite excellent-shine for better performance */
}

@keyframes excellent-shine {
  0%, 100% { filter: brightness(1); }
  50% { filter: brightness(1.2); }
}

.points-badge.badge-good { 
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
  box-shadow: 
    0 4px 12px rgba(59, 130, 246, 0.5),
    0 0 20px rgba(59, 130, 246, 0.3);
}

.points-badge.badge-neutral { 
  background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
  box-shadow: 
    0 4px 12px rgba(139, 92, 246, 0.5),
    0 0 20px rgba(139, 92, 246, 0.3);
}

.points-badge.badge-warning { 
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
  box-shadow: 
    0 4px 12px rgba(239, 68, 68, 0.5),
    0 0 20px rgba(239, 68, 68, 0.3);
  animation: badge-pop 0.5s ease-out; /* Removed infinite warning-pulse for better performance */
}

@keyframes warning-pulse {
  0%, 100% { 
    box-shadow: 
      0 4px 12px rgba(239, 68, 68, 0.5),
      0 0 20px rgba(239, 68, 68, 0.3);
  }
  50% { 
    box-shadow: 
      0 6px 16px rgba(239, 68, 68, 0.7),
      0 0 30px rgba(239, 68, 68, 0.5);
  }
}

/* Absent Card Styles */
.absent-card {
  transform: scale(0.85) !important;
  filter: grayscale(100%) opacity(0.6);
  animation: none !important;
  transition: none !important;
  cursor: not-allowed !important;
  pointer-events: none;
}

.absent-card:hover {
  transform: scale(0.85) !important;
  filter: grayscale(100%) opacity(0.6);
  box-shadow: 
    0 8px 16px rgba(0, 0, 0, 0.12),
    0 4px 8px rgba(59, 130, 246, 0.08),
    inset 0 -4px 8px rgba(0, 0, 0, 0.05),
    inset 0 4px 8px rgba(255, 255, 255, 0.5) !important;
}

.absent-card::before {
  animation: none !important;
  opacity: 0.3 !important;
}

.absent-card .name-container .first-name {
  animation: none !important;
  background: linear-gradient(135deg, #9ca3af, #6b7280) !important;
  opacity: 0.7;
}

.absent-card .name-container .last-name,
.absent-card .points-badge,
.absent-card .student-card {
  animation: none !important;
}

.absent-card .points-badge {
  display: none;
}

</style>
