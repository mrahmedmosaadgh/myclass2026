
<template>
	<div class="p-0 relative w-32 h-40 mt-12">
		<q-card
			:class="[cardClass, { 'selected-card': selectedId === student.id } ]"
			@click="!disableBehavior ? $emit('select', student.id) : null"
			class="absolute top-10 left-0 rounded-2xl shadow-lg transform hover:scale-105 transition-all duration-300 bg-gradient-to-br from-white via-blue-50 to-blue-100 border border-blue-100 cursor-pointer hover:z-50 hover:shadow-2xl hover:w-96"
			flat
		>
			<div class="relative w-full justify-center">
				<q-img
					:src="student.avatar || placeholderAvatar(student.name)"
					ratio="1"
					class="w-32 h-32 absolute rounded-full -top-24 -left-5"
				/>

				<div class="relative">
					<transition name="fade">
						<div v-if="avatarEditEnabled"
							class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30 rounded-full transition-all duration-300"
						>
							<q-btn
								round
								dense
								color="white"
								text-color="dark"
								icon="photo_camera"
								@click.stop.prevent="$emit('open-camera', student)"
								class="shadow-lg"
								:class="showAvatarButtons ? '' : 'hidden'"
							>
								<q-tooltip class="bg-primary text-white text-sm rounded-lg px-2 py-1">📷 Take a photo!</q-tooltip>
							</q-btn>
						</div>
					</transition>
				</div>
			</div>

			<q-card-section class="text-left overflow-hidden">
				<div class="text-sm -mt-4 text-gray-700 font-semibold" v-if="parseName(student.name).firstName"></div>
				<div class="text-xs text-gray-500" v-if="parseName(student.name).firstName">
					<div class="p-0 opacity-5 hover:opacity-100 overflow-hidden hover:overflow-visible text-xl">
						<div class="rounded-xl px-2 w-full text-blue-600 text-xl bg-yellow-300">{{ parseName(student.name).firstName }}</div>
						{{ parseName(student.name).secondName }}
						<div class="p-0 text-blue-600">{{ parseName(student.name).lastName }}</div>
					</div>
				</div>
			</q-card-section>

			<q-card-actions class="flex justify-between items-center px-5 py-3 bg-gradient-to-r from-blue-100 to-blue-50 rounded-b-2xl">
				<q-btn
					round
					dense
					:color="disableBehavior ? 'grey-5' : 'primary'"
					icon="star"
					glossy
					@click.stop.prevent="!disableBehavior ? $emit('open-behavior', student.id, student.name) : null"
					:disable="disableBehavior"
					class="shadow-sm"
				>
					<q-tooltip :class="disableBehavior ? 'bg-grey-8 text-white' : 'bg-primary text-white'" class="text-sm rounded-lg px-2 py-1">
						🌟 {{ disableBehavior ? 'Not available today' : 'Give a star!' }}
					</q-tooltip>
				</q-btn>

				<div class="flex flex-col items-center text-sm leading-tight">
					<div class="text-lg font-bold text-primary">🎯 {{ studentSummary?.total || 0 }}</div>
					<transition name="fade">
						<div v-if="studentSummary?.positive > 0" class="text-green-600">🟢 +{{ studentSummary?.positive }}</div>
					</transition>
					<transition name="fade">
						<div v-if="studentSummary?.negative < 0" class="text-red-500">🔴 {{ studentSummary?.negative }}</div>
					</transition>
				</div>
			</q-card-actions>
		</q-card>

		<div class="absolute -top-20 -left-4 text-white rounded-full px-2 text-xl bg-blue-700 overflow-visible">
			{{ parseName(student.name).firstName }}
			<div class="text-xs absolute -top-4 -left-4 text-blue-600">{{ parseName(student.name).lastName }}</div>
		</div>
	</div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
	student: { type: Object, required: true },
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
.selected-card {
	border-color: #1976d2 !important;
}
</style>
