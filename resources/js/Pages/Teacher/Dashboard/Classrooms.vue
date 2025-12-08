<template>
	<div class="p-6">
		<h1 class="text-2xl font-semibold mb-4">My Classrooms</h1>
		<div v-if="isLoading" class="text-slate-500">Loading...</div>
		<div v-else>
			<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
				<div
					v-for="item in assignments"
					:key="item.id"
					class="rounded-lg shadow border hover:shadow-md transition cursor-pointer"
					:style="cardStyle(item)"
					@click="openMenu(item)"
				>
					<div class="p-4">
						<div class="flex items-center justify-between">
							<div class="text-sm uppercase tracking-wide opacity-80">{{ item.subject?.name }}</div>
							<button class="text-xs px-2 py-1 rounded bg-white/20 hover:bg-white/30" @click.stop="openMenu(item)">Options</button>
						</div>
						<h2 class="text-xl font-bold mt-1">{{ item.classroom?.name }}</h2>
						<div class="mt-3 text-sm opacity-90">Classes / week: <span class="font-semibold">{{ item.classes_per_week ?? '-' }}</span></div>
					</div>
				</div>
			</div>
		</div>

		<!-- Options Drawer -->
		<transition name="fade">
			<div v-if="active" class="fixed inset-0 z-40">
				<div class="absolute inset-0 bg-black/30" @click="active = null"></div>
				<div class="absolute right-0 top-0 h-full w-full sm:w-96 bg-white shadow-xl z-50 flex flex-col">
					<div class="p-4 border-b flex items-center justify-between">
						<div>
							<div class="text-xs text-slate-500">{{ active?.subject?.name }}</div>
							<div class="text-lg font-semibold">{{ active?.classroom?.name }}</div>
						</div>
						<button class="text-slate-600 hover:text-slate-900" @click="active = null">✕</button>
					</div>
					<div class="p-4 space-y-3 overflow-y-auto">
						<button class="w-full text-left px-3 py-2 rounded border hover:bg-slate-50" @click="loadStudents">Students</button>
						<button class="w-full text-left px-3 py-2 rounded border hover:bg-slate-50" @click="loadTeachers">Teachers</button>
						<button class="w-full text-left px-3 py-2 rounded border hover:bg-slate-50" @click="placeholder('Assignments')">Assignments</button>
						<button class="w-full text-left px-3 py-2 rounded border hover:bg-slate-50" @click="placeholder('Attendance')">Attendance</button>

						<div v-if="loadingExtra" class="text-slate-500 text-sm">Loading...</div>
						<div v-if="students.length" class="space-y-1">
							<div class="text-xs font-medium text-slate-500">Students</div>
							<ul class="list-disc list-inside text-sm">
								<li v-for="s in students" :key="s.id">{{ s.name }}</li>
							</ul>
						</div>
						<div v-if="teachers.length" class="space-y-1">
							<div class="text-xs font-medium text-slate-500">Teachers</div>
							<ul class="list-disc list-inside text-sm">
								<li v-for="t in teachers" :key="t.id">{{ t.name }}</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</transition>
	</div>
</template>

<script setup>
	import { ref, computed, onMounted } from 'vue'
	import { usePage } from '@inertiajs/vue3'
	import axios from 'axios'

	const page = usePage()
	const props = defineProps({
		assignments: { type: Array, default: () => [] }
	})

	const assignments = ref(props.assignments)
	const isLoading = ref(false)
	const active = ref(null)
	const students = ref([])
	const teachers = ref([])
	const loadingExtra = ref(false)

	const openMenu = (item) => {
		active.value = item
		students.value = []
		teachers.value = []
	}

	const cardStyle = (item) => {
		const bg = item?.colors?.bg || '#f8fafc'
		const text = item?.colors?.text || '#0f172a'
		return {
			backgroundColor: bg,
			color: text
		}
	}

	const loadStudents = async () => {
		if (!active.value) return
		loadingExtra.value = true
		try {
			// Example endpoint. Replace with your actual endpoint when ready.
			const { data } = await axios.get(`/api/classrooms/${active.value.classroom.id}/students`)
			students.value = data?.data || []
		} finally {
			loadingExtra.value = false
		}
	}

	const loadTeachers = async () => {
		if (!active.value) return
		loadingExtra.value = true
		try {
			// Example endpoint. Replace with your actual endpoint when ready.
			const { data } = await axios.get(`/api/classrooms/${active.value.classroom.id}/teachers`)
			teachers.value = data?.data || []
		} finally {
			loadingExtra.value = false
		}
	}

	const placeholder = (name) => {
		window.alert(`${name} coming soon`)
	}

	onMounted(async () => {
		if (!assignments.value?.length) {
			isLoading.value = true
			try {
				const { data } = await axios.get('/api/teacher/dashboard/classrooms')
				assignments.value = data?.data || []
			} finally {
				isLoading.value = false
			}
		}
	})
</script>

<style scoped>
	.fade-enter-active,.fade-leave-active{transition:opacity .2s ease}
	.fade-enter-from,.fade-leave-to{opacity:0}
</style> 