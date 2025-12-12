<template>
  <div class="w-fit">
  <div class="w-full max-w-4xl mx-auto p-1">
    <table class="w-full text-center bg-white rounded-2xl shadow-sm overflow-hidden">
      <thead>
        <!-- Header / Filters Row -->
        <tr class="bg-gray-50 border-b border-gray-200">
          <td colspan="4" class="p-3">
            <div class="flex items-center justify-between px-2">
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                  <span class="font-bold text-gray-700 text-lg">Current Session</span>
                  <span class="text-gray-300">|</span>
                  <span class="font-bold text-indigo-600">Leaderboard</span>
                </div>

                <div class="h-6 w-px bg-gray-300 mx-2"></div>

                <!-- Filters -->
                <div class="flex items-center gap-2">
                   <q-select 
                      v-model="filterScope" 
                      :options="filterOptions" 
                      dense 
                      borderless 
                      options-dense 
                      class="text-sm font-medium min-w-[120px]" 
                      @update:model-value="savePrefs"
                    >
                      <template v-slot:selected>
                        <span class="text-xs font-bold text-gray-600 uppercase tracking-wide">{{ filterScope.label }}</span>
                      </template>
                   </q-select>

                   <span class="text-gray-300">•</span>

                   <q-select 
                      v-model="topCount" 
                      :options="topCountOptions" 
                      dense 
                      borderless 
                      class="text-sm font-medium" 
                      emit-value 
                      map-options 
                      @update:model-value="savePrefs"
                   >
                     <template v-slot:selected>
                        <span class="text-xs font-bold text-gray-600">Top {{ topCount }}</span>
                     </template>
                   </q-select>
                </div>
              </div>

              <div class="flex items-center gap-2">
                 <q-btn flat round dense icon="refresh" color="grey-7" size="sm" @click="resetFilters">
                   <q-tooltip>Reset Filters</q-tooltip>
                 </q-btn>
                 <q-btn flat round dense icon="screenshot" color="green-7" size="sm" @click="captureScreenshot" :loading="capturingScreenshot">
                   <q-tooltip>Share</q-tooltip>
                 </q-btn>
              </div>
            </div>
          </td>
        </tr>

        <!-- Column Headers -->
        <tr class="text-xs uppercase tracking-wider font-bold text-gray-500 border-b border-gray-100">
          <th class="py-3 bg-yellow-50/50 text-yellow-800 w-16">Rank</th>
          <th class="py-3 bg-gray-50 text-left pl-4 text-gray-600">Student</th>
          <th class="py-3 bg-indigo-50/50 text-indigo-800 w-24">Points</th>
          <th class="py-3 bg-purple-50/50 text-purple-800 w-20">Cert</th>
        </tr>
      </thead>
      
      <tbody ref="leaderboardTableBody">
        <tr v-if="flatList.length === 0">
           <td colspan="4" class="py-12 text-center">
              <div class="flex flex-col items-center justify-center text-gray-400">
                  <q-icon name="emoji_events" size="3rem" class="mb-2 opacity-50" />
                  <span class="font-bold text-sm">No champions yet</span>
              </div>
           </td>
        </tr>
        <template v-else>
            <tr v-for="item in flatList" :key="item.student.id" class="border-b border-gray-50 hover:bg-gray-50 transition-colors group">
              <!-- Rank Column -->
              <td class="py-2">
                 <div class="flex items-center justify-center">
                    <div v-if="item.rank <= 3" class="text-2xl drop-shadow-sm transform group-hover:scale-110 transition-transform duration-300">
                        {{ getMedalEmoji(item.rank) }}
                    </div>
                    <div v-else class="w-8 h-8 rounded-full bg-gray-100/80 text-gray-500 font-bold flex items-center justify-center text-xs shadow-inner">
                        {{ item.rank }}
                    </div>
                 </div>
              </td>

              <!-- Student Column -->
              <td class="py-2 pl-4 text-left">
                  <div class="flex items-center gap-3">
                      <q-avatar size="32px" class="shadow-sm border border-gray-100">
                          <img :src="item.student.avatar || getPlaceholder(item.student.name)" class="object-cover" />
                      </q-avatar>
                      <div>
                          <div class="font-bold text-gray-700 text-sm leading-tight">{{ getFullName(item.student) }}</div>
                          <div v-if="item.rank === 1" class="text-[9px] font-black text-yellow-600 uppercase tracking-wider mt-0.5">Current Champion</div>
                      </div>
                  </div>
              </td>

              <!-- Points Column -->
              <td class="py-2">
                  <span class="font-black text-indigo-600 text-base">{{ item.total }}</span>
              </td>

              <!-- Actions Column -->
              <td class="py-2">
                  <q-btn flat round dense color="purple-6" icon="card_membership" size="sm" @click="openCertificate(item.student)" class="opacity-0 group-hover:opacity-100 transition-opacity">
                      <q-tooltip>Print Certificate</q-tooltip>
                  </q-btn>
              </td>
            </tr>
        </template>
      </tbody>
    </table>
  </div>

  <CertificateGenerator 
      v-model="showCertificateDialog" 
      :student="selectedStudentForCertificate"
      :default-date="date"
  />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import html2canvas from 'html2canvas'
import { useQuasar } from 'quasar'
import CertificateGenerator from './CertificateGenerator.vue'

const $q = useQuasar()

const props = defineProps({
  students: { type: Array, default: () => [] },
  studentBehaviors: { type: Object, default: () => ({}) },
  periodCode: { type: String, default: '' },
  date: { type: String, default: '' }
})

// Filters
const filterScope = ref({ label: 'Current Session', value: 'session' })
const topCount = ref(5)
const filterOptions = [
  { label: 'Current Session', value: 'session' },
  { label: 'Today', value: 'today' },
  { label: 'This Week', value: 'week' },
  { label: 'All Time', value: 'all' }
]
const topCountOptions = [
    { label: 'Top 5', value: 5 },
    { label: 'Top 10', value: 10 },
    { label: 'Top 20', value: 20 }
]

// Preferences
onMounted(() => {
  const saved = localStorage.getItem('leaderboard_preferences')
  if (saved) {
    try {
      const prefs = JSON.parse(saved)
      const found = filterOptions.find(o => o.value === prefs.scope)
      if (found) filterScope.value = found
      if (prefs.topCount) topCount.value = prefs.topCount
    } catch (e) { console.error(e) }
  }
})

const savePrefs = () => {
  localStorage.setItem('leaderboard_preferences', JSON.stringify({
    scope: filterScope.value.value,
    topCount: topCount.value
  }))
  // In a real app, you might emit an event here to fetch new data based on scope
  console.log('Filters applied:', filterScope.value.label, topCount.value)
}

const resetFilters = () => {
  filterScope.value = filterOptions[0]
  topCount.value = 5
  savePrefs()
}

// Logic
const rankedGroups = computed(() => {
  if (!props.students.length) return []
  
  // Calculate totals
  const studentsWithPoints = props.students.map(student => {
    const b = props.studentBehaviors[student.id] || {}
    // Simplified point calculation logic based on current session/view
    // Note: In a real implementation with scopes (Today/Week/All), 
    // the 'studentBehaviors' prop would likely need to change or be filtered beforehand.
    // Here we use what is passed.
    const total = (b.points_plus || 0) - (b.points_minus || 0)
    return { ...student, total }
  })

  // Filter positive and sort
  const positive = studentsWithPoints.filter(s => s.total > 0).sort((a, b) => b.total - a.total)
  
  if (!positive.length) return []

  // Group by rank
  const groups = []
  let current = { rank: 1, total: positive[0].total, students: [positive[0]] }
  
  for (let i = 1; i < positive.length; i++) {
    if (positive[i].total === current.total) {
      current.students.push(positive[i])
    } else {
      groups.push(current)
      current = { rank: groups.length + 1, total: positive[i].total, students: [positive[i]] }
    }
  }
  groups.push(current)
  
  return groups.slice(0, topCount.value)
})

const flatList = computed(() => {
    return rankedGroups.value.flatMap(group => 
        group.students.map(s => ({
            rank: group.rank,
            total: group.total,
            student: s
        }))
    )
})

// Helpers
const getFullName = (student) => {
  if (student.firstName && student.lastName) return `${student.firstName} ${student.lastName}`
  if (student.name) return student.name
  return 'Unknown Student'
}

const getPlaceholder = (name = 'S') => {
  const initials = name.split(' ').map(s => s[0]).slice(0,2).join('').toUpperCase()
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140"><rect width="140" height="140" fill="#f1f5f9"/><text x="70" y="70" dy="12" text-anchor="middle" font-size="60" font-family="ui-sans-serif, system-ui, sans-serif" font-weight="bold" fill="#94a3b8">${initials}</text></svg>`
  return `data:image/svg+xml,${encodeURIComponent(svg)}`
}

const getMedalEmoji = (rank) => {
  if (rank === 1) return '🥇'
  if (rank === 2) return '🥈'
  if (rank === 3) return '🥉'
  return ''
}

// Certificate
const showCertificateDialog = ref(false)
const selectedStudentForCertificate = ref(null)

const openCertificate = (student) => {
  selectedStudentForCertificate.value = student
  showCertificateDialog.value = true
}

// Screenshot
const leaderboardTableBody = ref(null)
const capturingScreenshot = ref(false)

const captureScreenshot = () => {
    capturingScreenshot.value = true
    // Logic to capture the table parent div preferably
    const tableElement = leaderboardTableBody.value?.closest('table')
    
    if (!tableElement) {
        capturingScreenshot.value = false
        return
    }

    html2canvas(tableElement, { scale: 2 }).then(canvas => {
      const dataUrl = canvas.toDataURL('image/png')
       if (navigator.canShare && navigator.canShare({ files: [] })) {
        fetch(dataUrl)
          .then(res => res.blob())
          .then(blob => {
            const file = new File([blob], 'leaderboard.png', { type: 'image/png' })
            return navigator.share({ files: [file], title: 'Leaderboard' })
          })
          .catch(() => downloadCanvas(dataUrl))
      } else {
        downloadCanvas(dataUrl)
      }
    }).finally(() => {
        capturingScreenshot.value = false
    })
}

const downloadCanvas = (dataUrl) => {
  const a = document.createElement('a')
  a.href = dataUrl
  a.download = 'leaderboard.png'
  a.click()
  $q.notify({ message: 'Screenshot saved', color: 'positive', icon: 'download' })
}
</script>

<style scoped>
/* Custom Scrollbar for global usage if needed, though this component doesn't scroll internally much */
</style>
