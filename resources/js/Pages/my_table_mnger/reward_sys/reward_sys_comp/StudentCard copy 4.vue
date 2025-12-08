
<template>
 <div class="p-0 relative w-32 h-40 mt-12">
  <div
    :class="[cardClass, { 'selected-card': selected || selectedId === student.id }]"
    @click="!disableBehavior ? $emit('select', student.id) : null"
    class="student-card absolute top-10 left-0"
  >
    <!-- Avatar -->
    <div class="relative w-full flex justify-center">
      <img
        :src="student.avatar || placeholderAvatar(student.name)"
        class="student-avatar"
      />

      <!-- Avatar Overlay -->
      <transition name="fade">
        <div
          v-if="avatarEditEnabled"
          class="avatar-overlay"
        >
          <button
            @click.stop.prevent="$emit('open-camera', student)"
            class="avatar-btn"
            :class="showAvatarButtons ? '' : 'hidden'"
          >
            📷
          </button>
        </div>
      </transition>
    </div>

    <!-- Name Section -->
    <div class="text-left overflow-hidden p-2 mt-10">
      <div
        v-if="student.firstName"
        class="text-sm text-gray-700 font-semibold"
        :title="(student.secondName || '') + ' ' + (student.lastName || '')"
      ></div>

      <div v-if="student.firstName" class="text-xs text-gray-500">
        <div class="overflow-hidden hover:overflow-visible text-xl">
          <div class="rounded-xl px-2 w-full text-blue-600 text-xl bg-yellow-300">
            {{ student.firstName }}
          </div>
          {{ student.secondName }}
          <div class="text-blue-600">{{ student.lastName }}</div>
        </div>
      </div>
    </div>

    <!-- Checkbox -->
    <div class="absolute top-2 right-2 z-50">
      <input
        type="checkbox"
        :checked="selected || (selectedId === student.id)"
        @change="$emit('select', student.id)"
        class="student-checkbox"
      />
    </div>
  </div>
</div>

</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	student: { type: Object, required: true },
	selected: { type: Boolean, default: false },
	selectedId: { type: [String, Number], default: null },
	cardClass: { type: [String, Object, Array], default: '' },
	studentSummary: { type: Object, default: () => ({ positive: 0, negative: 0, total: 0 }) },
	avatarEditEnabled: { type: Boolean, default: false },
	showAvatarButtons: { type: Boolean, default: false },
	disableBehavior: { type: Boolean, default: false }
})

const emit = defineEmits(['select', 'open-camera', 'open-behavior'])

function parseName(fullName) {
	if (!fullName || typeof fullName !== 'string') return { firstName: '', secondName: '', lastName: '' }
	const parts = fullName.trim().split(/\s+/).filter(p => p.length > 0)
	const firstName = parts[0] || ''
	const lastName = parts.length > 1 ? parts[parts.length - 1] : ''
	const secondName = parts.length > 2 ? parts.slice(1, -1).join(' ') : ''
	return { firstName, secondName, lastName }
}

function placeholderAvatar(name) {
	try {
		if (name) {
			const initials = name.split(' ').map(s => s[0] || '').slice(0, 2).join('').toUpperCase()
			const bgColor = '#e2e8f0'
			const textColor = '#475569'
			const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 96 96"><rect width="96" height="96" fill="${bgColor}" rx="48" ry="48"/><text x="48" y="48" dy="8" text-anchor="middle" font-family="Arial" font-size="32" font-weight="bold" fill="${textColor}">${initials}</text></svg>`
			return `data:image/svg+xml,${encodeURIComponent(svg)}`
		}
	} catch (e) {
		console.warn('Failed to generate avatar SVG:', e)
	}
	return '/images/avatars/default-avatar.svg'
}
</script>

<style scoped>
.student-card {
  width: 8rem;
  min-height: 10rem;
  background: linear-gradient(to bottom right, white, #eff6ff, #dbeafe);
  border: 1px solid #bfdbfe;
  border-radius: 1rem;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  transition: all 0.3s ease;
  cursor: pointer;
}

.student-card:hover {
  transform: scale(1.05);
  z-index: 50;
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.student-card.selected-card {
  border-color: #2563eb;
  box-shadow: 0 0 12px rgba(37, 99, 235, 0.4);
}

/* Avatar */
.student-avatar {
  width: 8rem;
  height: 8rem;
  border-radius: 50%;
  position: absolute;
  top: -6rem;
  left: -1.2rem;
  object-fit: cover;
  box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Avatar overlay */
.avatar-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  background: rgba(0,0,0,0.3);
  border-radius: 50%;
  transition: all 0.3s ease;
}

.avatar-btn {
  background: white;
  color: black;
  width: 2.2rem;
  height: 2.2rem;
  border-radius: 50%;
  font-size: 1.3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 8px rgba(0,0,0,0.25);
  border: none;
  cursor: pointer;
}

.avatar-btn:hover {
  transform: scale(1.1);
}

/* Checkbox */
.student-checkbox {
  width: 1rem;
  height: 1rem;
  cursor: pointer;
}

</style>
