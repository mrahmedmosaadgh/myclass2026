<template>
  <div 
    class="relative flex flex-col items-center group cursor-pointer transition-transform duration-200 hover:-translate-y-1"
    :class="[
      disableBehavior ? 'opacity-60 grayscale' : '',
      isAbsent ? 'opacity-50 grayscale' : ''
    ]"
    @click="handleCardClick"
    @mouseenter="$emit('preview', student)"
    @mouseleave="$emit('leave')"
  >
    
    <!-- Avatar Container -->
    <div class="relative mb-2">
      <!-- Main Avatar Circle -->
      <div 
        class="w-24 h-24 rounded-full bg-gray-100 flex items-center justify-center relative overflow-visible border-4 transition-all duration-300 shadow-sm"
        :class="[
          selected ? 'border-blue-500 shadow-md scale-105' : 'border-white',
          disableBehavior ? 'border-gray-200' : ''
        ]"
      >
        <AvatarManager 
          :student="student" 
          size="5.5rem"
          :edit-enabled="avatarEditEnabled"
          @update:avatar="(url) => student.avatar = url"
        />
      </div>

      <!-- Bottom Left: Overall Points (Rectangle) -->
      <div 
        class="absolute -bottom-1 -left-3 bg-white border border-gray-200 shadow-md rounded-lg px-2 py-0.5 min-w-[2.5rem] text-center z-20 flex items-center justify-center transform scale-90"
      >
        <span class="text-sm font-bold text-gray-700">{{ overallPoints }}</span>
      </div>

      <!-- Bottom Right: Scoped Points (Circle) -->
      <div 
        v-if="studentSummary"
        class="absolute -bottom-1 -right-3 w-8 h-8 rounded-full border-2 flex items-center justify-center z-20 shadow-md transition-all duration-300 transform scale-90"
         :class="{
           'bg-green-100 border-green-500 text-green-800': studentSummary.total > 0,
           'bg-red-100 border-red-500 text-red-800': studentSummary.total < 0,
           'bg-gray-100 border-gray-300 text-gray-600': studentSummary.total === 0
        }"
      >
        <span class="text-sm font-bold">{{ studentSummary.total }}</span>
      </div>

      <!-- Selection Indicator -->
      <div 
        v-if="selected"
        class="absolute -top-1 -right-1 z-30 bg-green-500 rounded-full w-4 h-4 flex items-center justify-center shadow-sm"
      >
        <q-icon name="check" color="white" size="10px" />
      </div>

    </div>

    <!-- Name Below -->
    <div class="text-center leading-tight max-w-[140px] mt-2 px-1 transition-all duration-300"
         :class="{'drop-shadow-md scale-105': selected}">
      <div class="truncate transition-colors duration-300">
        <span class="font-extrabold text-base" 
              :class="selected ? 'text-blue-900' : 'text-gray-800'">
          {{ localizedName.firstName }}
        </span>
        <span class="font-bold text-sm ml-0.5" 
              :class="selected ? 'text-blue-800' : 'text-gray-600'"
              v-if="localizedName.secondName || localizedName.lastName">
          <span v-if="localizedName.secondName">, {{ localizedName.secondName }}</span>
          <span v-if="localizedName.lastName">{{ localizedName.secondName ? '' : ',' }} {{ localizedName.lastName }}</span>
        </span>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import AvatarManager from './AvatarManager.vue'

const { locale } = useI18n()

// ... existing props ...
const props = defineProps({
	student: { type: Object, required: true },
	selected: { type: Boolean, default: false },
	selectedId: { type: [String, Number], default: null },
	cardClass: { type: [String, Object, Array], default: '' },
	studentSummary: { type: Object, default: () => ({ positive: 0, negative: 0, total: 0 }) },
    overallPoints: { type: Number, default: 0 },
	avatarEditEnabled: { type: Boolean, default: false },
	showAvatarButtons: { type: Boolean, default: false },
	disableBehavior: { type: Boolean, default: false },
  allowDisabledClick: { type: Boolean, default: false },
  isAbsent: { type: Boolean, default: false }
})

const emit = defineEmits(['select', 'open-camera', 'open-behavior', 'preview', 'leave'])

function handleCardClick() {
  emit('preview', props.student) // Also toggle preview on click
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
</script>

<style scoped>
/* No extra styles needed, using Tailwind */
</style>
