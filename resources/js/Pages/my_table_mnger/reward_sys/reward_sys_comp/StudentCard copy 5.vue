
<template>
 

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
 

</style>
