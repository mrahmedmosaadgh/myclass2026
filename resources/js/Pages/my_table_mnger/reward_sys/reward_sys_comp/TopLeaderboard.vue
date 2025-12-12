<template>
  <div v-if="students.length" class="mb-6">
    <q-card class="shadow-2xl rounded-3xl overflow-hidden bg-gray-900 border-4 border-yellow-500 relative">

      <!-- Background Animation -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="stars"></div>
        <div class="twinkling"></div>
      </div>

      <!-- Header with Filters -->
      <q-card-section class="relative z-10 text-center py-6 bg-gradient-to-b from-transparent to-black/50">
        <h1 class="text-4xl md:text-6xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-yellow-100 to-yellow-500 drop-shadow-lg animate-shine">
          🏆 CHAMPIONS 🏆
        </h1>
        <div class="mt-6 flex flex-col md:flex-row items-center justify-center gap-4 px-4">
          <q-select v-model="filterScope" :options="filterOptions" outlined dense dark label="Filter Scope" class="w-full md:w-48 bg-black/30" @update:model-value="applyFilters" />
          <q-select v-model="topCount" :options="[{ label: 'Top 5', value: 5 }, { label: 'Top 10', value: 10 }]" outlined dense dark label="Show Places" class="w-full md:w-32 bg-black/30" emit-value map-options @update:model-value="applyFilters" />
          <q-btn color="yellow-7" icon="refresh" label="Reset" @click="resetFilters" size="md" class="w-full md:w-auto" />
        </div>
        <p class="text-sm text-yellow-200 mt-3 font-bold tracking-widest uppercase opacity-80">
          {{ filterScope.label }} • Top {{ topCount }}
        </p>
      </q-card-section>

      <!-- Podium Section (Top 3) -->
      <q-card-section class="p-4 md:p-8 relative z-10">
        <div v-if="podiumGroups.length === 0" class="text-center py-16">
          <p class="text-3xl text-yellow-400 font-bold animate-pulse">The arena is empty... Who will rise? ⚔️</p>
        </div>
        <div v-else class="flex flex-col md:flex-row justify-center items-end gap-6 md:gap-10 min-h-[580px]">
          <template v-for="(group, index) in podiumOrder" :key="group?.rank || index">
            <div v-if="group" class="w-full md:w-1/3 flex flex-col transition-all duration-700 podium-card" :class="[group.rank === 1 ? 'order-2 md:mb-32 z-30' : group.rank === 2 ? 'order-1 md:mb-12 z-20' : 'order-3 z-20']">
              <div class="text-center mb-6">
                <div v-if="group.rank === 1" class="text-9xl animate-bounce-slow drop-shadow-2xl">👑</div>
                <div v-else class="text-8xl drop-shadow-2xl">{{ getMedalEmoji(group.rank) }}</div>
              </div>
              <div class="relative rounded-3xl overflow-hidden border-4 shadow-2xl backdrop-blur-xl flex flex-col mx-4 pb-6" :class="getPodiumStyle(group.rank)">
                <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/30 to-white/0 w-[200%] h-full animate-shine-move pointer-events-none"></div>
                <div class="text-center pt-8 pb-4 relative z-10">
                  <h2 class="text-4xl font-black italic tracking-wider text-white drop-shadow-2xl">{{ getOrdinal(group.rank) }}</h2>
                  <div class="h-1 w-32 mx-auto bg-white/40 rounded-full mt-3"></div>
                </div>
                <div class="px-4 pb-4 relative z-10">
                  <div class="space-y-4">
                    <div v-for="student in group.students" :key="student.id" class="bg-black/70 rounded-2xl p-4 backdrop-blur-md border border-white/20 shadow-xl hover:border-yellow-400/50 transition-all flex items-center gap-4">
                      <q-avatar size="80px" class="ring-4 ring-yellow-400/60 shadow-xl border-2 flex-shrink-0">
                        <img :src="student.avatar || getPlaceholder(student.name)" class="object-cover" />
                      </q-avatar>
                      <div class="flex-1 min-w-0">
                        <div class="font-black">
                          <span class="shiny-name text-3xl md:text-4xl leading-tight block truncate">{{ student.firstName }}</span>
                          <span class="shiny-name-small block text-lg mt-1 opacity-95 truncate">{{ student.lastName }}</span>
                        </div>
                        <div class="text-yellow-300 font-bold text-lg mt-1">{{ student.total }} pts</div>
                      </div>
                      <q-btn flat round color="white" icon="card_membership" @click="openCertificate(student)">
                        <q-tooltip>Print Certificate</q-tooltip>
                      </q-btn>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>
        </div>
      </q-card-section>

      <!-- Full Leaderboard List -->
      <q-card-section ref="leaderboardList" v-if="listGroups.length > 0" class="p-4 md:p-8 pt-0 relative z-10">
        <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-2xl p-6 border-2 border-gray-700">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-bold text-yellow-400 text-center flex-1">🏆 Complete Leaderboard 🏆</h3>
            <q-btn color="green" icon="screenshot" label="Share" @click="captureScreenshot" :loading="capturingScreenshot" class="shadow-lg">
              <q-tooltip>Capture & Share on WhatsApp</q-tooltip>
            </q-btn>
          </div>
          <div class="space-y-3">
            <div v-for="group in listGroups" :key="group.rank" class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-xl p-5 border border-gray-600 hover:border-yellow-500 hover:shadow-yellow-500/30 transition-all duration-300">
              <div class="flex items-center gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center border-4 border-yellow-300 shadow-lg">
                  <span class="text-white font-black text-2xl">{{ group.rank }}</span>
                </div>
                <div class="flex-1 flex flex-wrap gap-4">
                  <div v-for="student in group.students" :key="student.id" class="flex items-center gap-3 bg-black/50 rounded-lg px-4 py-3 backdrop-blur-sm">
                    <q-avatar size="48px" class="border-2 border-yellow-500/40">
                      <img :src="student.avatar || getPlaceholder(student.name)" />
                    </q-avatar>
                    <div>
                      <div class="font-bold text-lg">
                        <span class="shiny-name">{{ student.firstName }}</span>
                        <span class="shiny-name-small text-sm ml-1">{{ student.lastName }}</span>
                      </div>
                      <div class="text-yellow-300 text-sm font-semibold">{{ student.total }} pts</div>
                    </div>
                    <q-btn flat round dense color="yellow-500" icon="card_membership" @click="openCertificate(student)">
                      <q-tooltip>Print Certificate</q-tooltip>
                    </q-btn>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>
    <CertificateGenerator 
      v-model="showCertificateDialog" 
      :student="selectedStudentForCertificate"
      :default-date="date"
    />
    </q-card>
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

const filterScope = ref({ label: 'Current Session', value: 'session' })
const topCount = ref(5)
const filterOptions = [
  { label: 'Current Session', value: 'session' },
  { label: 'Today', value: 'today' },
  { label: 'This Week', value: 'week' },
  { label: 'All Time', value: 'all' }
]

// Preferences persistence
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

watch([filterScope, topCount], () => {
  localStorage.setItem('leaderboard_preferences', JSON.stringify({
    scope: filterScope.value.value,
    topCount: topCount.value
  }))
}, { deep: true })

// Ranking logic
const rankedGroups = computed(() => {
  if (!props.students.length) return []
  const studentsWithPoints = props.students.map(student => {
    const b = props.studentBehaviors[student.id] || {}
    const total = (b.points_plus || 0) - (b.points_minus || 0)
    return { ...student, total }
  })
  const positive = studentsWithPoints.filter(s => s.total > 0).sort((a, b) => b.total - a.total)
  if (!positive.length) return []
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

const podiumGroups = computed(() => rankedGroups.value.slice(0, 3))
const listGroups = computed(() => rankedGroups.value)
const podiumOrder = computed(() => {
  const g = podiumGroups.value
  const first = g.find(x => x.rank === 1)
  const second = g.find(x => x.rank === 2)
  const third = g.find(x => x.rank === 3)
  return [second, first, third].filter(Boolean)
})

function applyFilters() { console.log('Filters applied') }
function resetFilters() {
  filterScope.value = filterOptions[0]
  topCount.value = 5
}

function getMedalEmoji(rank) {
  if (rank === 1) return '🥇'
  if (rank === 2) return '🥈'
  if (rank === 3) return '🥉'
  return ''
}

function getOrdinal(n) {
  const s = ['th', 'st', 'nd', 'rd']
  const v = n % 100
  return n + (s[(v - 20) % 10] || s[v] || s[0])
}

function getPodiumStyle(rank) {
  if (rank === 1) return 'bg-gradient-to-b from-yellow-400 via-yellow-500 to-yellow-700 border-yellow-300 shadow-yellow-500/70 ring-8 ring-yellow-400/40'
  if (rank === 2) return 'bg-gradient-to-b from-gray-300 via-gray-400 to-gray-600 border-gray-200 shadow-gray-500/50'
  if (rank === 3) return 'bg-gradient-to-b from-orange-400 via-orange-500 to-orange-700 border-orange-300 shadow-orange-500/50'
  return ''
}

// Reactive refs for screenshot
const leaderboardList = ref(null)
const capturingScreenshot = ref(false)

function captureScreenshot() {
  capturingScreenshot.value = true
  nextTick(() => {
    const element = leaderboardList.value
    if (!element) {
      $q.notify({ message: 'Leaderboard element not found', color: 'negative' })
      capturingScreenshot.value = false
      return
    }
    // Check if element is still attached to the document
    if (!document.contains(element)) {
      $q.notify({ message: 'Element not available for screenshot', color: 'negative' })
      capturingScreenshot.value = false
      return
    }

    html2canvas(element).then(canvas => {
      const dataUrl = canvas.toDataURL('image/png')
      if (navigator.canShare && navigator.canShare({ files: [] })) {
        fetch(dataUrl)
          .then(res => res.blob())
          .then(blob => {
            const file = new File([blob], 'leaderboard.png', { type: 'image/png' })
            return navigator.share({ files: [file], title: 'Leaderboard' })
          })
          .then(() => { $q.notify({ message: 'Shared successfully!', color: 'positive' }) })
          .catch(() => { downloadCanvas(dataUrl) })
      } else {
        downloadCanvas(dataUrl)
      }
    }).catch(err => {
      console.error(err)
      $q.notify({ message: 'Screenshot failed', color: 'negative' })
    }).finally(() => { capturingScreenshot.value = false })
  })
}

function downloadCanvas(dataUrl) {
  const a = document.createElement('a')
  a.href = dataUrl
  a.download = 'leaderboard.png'
  a.click()
  $q.notify({ message: 'Screenshot downloaded – share on WhatsApp!', color: 'positive', icon: 'download' })
}

function getPlaceholder(name = 'S') {
  const initials = name.split(' ').map(s => s[0]).slice(0,2).join('').toUpperCase()
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140"><rect width="140" height="140" fill="#1e293b"/><text x="70" y="70" dy="12" text-anchor="middle" font-size="60" font-family="Arial" font-weight="bold" fill="#cbd5e1">${initials}</text></svg>`
  return `data:image/svg+xml,${encodeURIComponent(svg)}`
}

// End of script

const showCertificateDialog = ref(false)
const selectedStudentForCertificate = ref(null)

function openCertificate(student) {
  selectedStudentForCertificate.value = student
  showCertificateDialog.value = true
}
</script>

<style scoped>
.shiny-name { background: linear-gradient(135deg, #ffd700, #ffed4e, #ffd700, #ffed4e); background-size: 300% 300%; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; font-weight: 900; text-shadow: 0 0 20px rgba(255,215,0,0.8); animation: shimmer 4s ease-in-out infinite; }
.shiny-name-small { background: linear-gradient(135deg, #ffffff, #e0e0e0, #ffffff); background-size: 200% 200%; -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; animation: shimmer 3s ease-in-out infinite; }
@keyframes shimmer { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
@keyframes shine-move { 0% { transform: translateX(-100%) skewX(-15deg); } 100% { transform: translateX(100%) skewX(-15deg); } }
.animate-shine-move { animation: shine-move 4s infinite; }
@keyframes bounce-slow { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
.animate-bounce-slow { animation: bounce-slow 2.5s ease-in-out infinite; }
.stars { background: #000 url('http://www.script-tutorials.com/demos/360/images/stars.png') repeat top center; }
.twinkling { background: transparent url('http://www.script-tutorials.com/demos/360/images/twinkling.png') repeat top center; animation: move-twink-back 200s linear infinite; }
@keyframes move-twink-back { from { background-position: 0 0; } to { background-position: -10000px 5000px; } }
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,215,0,0.4); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,215,0,0.7); }
</style>
