<template>
  <div v-if="students.length" class="mb-6">
    <q-card class="shadow-2xl rounded-3xl overflow-hidden bg-gray-900 border-4 border-yellow-500 relative">
      
      <!-- Background Animation -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="stars"></div>
        <div class="twinkling"></div>
      </div>

      <!-- Epic Header with Filters -->
      <q-card-section class="relative z-10 text-center py-6 bg-gradient-to-b from-transparent to-black/50">
        <h1 class="text-4xl md:text-6xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-yellow-100 to-yellow-500 drop-shadow-lg animate-shine">
          🏆 CHAMPIONS 🏆
        </h1>
        
        <!-- Filter Controls -->
        <div class="mt-6 flex flex-col md:flex-row items-center justify-center gap-4 px-4">
          <q-select
            v-model="filterScope"
            :options="filterOptions"
            outlined
            dense
            dark
            label="Filter Scope"
            class="w-full md:w-48 bg-black/30"
            @update:model-value="applyFilters"
          />
          
          <q-select
            v-model="topCount"
            :options="[
              { label: 'Top 5', value: 5 },
              { label: 'Top 10', value: 10 }
            ]"
            outlined
            dense
            dark
            label="Show Places"
            class="w-full md:w-32 bg-black/30"
            emit-value
            map-options
            @update:model-value="applyFilters"
          />
          
          <q-btn
            color="yellow-7"
            icon="refresh"
            label="Reset"
            @click="resetFilters"
            size="md"
            class="w-full md:w-auto"
          />
        </div>
        
        <p class="text-sm text-yellow-200 mt-3 font-bold tracking-widest uppercase opacity-80">
          {{ filterScope.label }} • Top {{ topCount }}
        </p>
      </q-card-section>

      <!-- Podium Section (Top 3) -->
      <q-card-section class="p-4 md:p-8 relative z-10">
        <div v-if="podiumGroups.length === 0" class="text-center py-16">
          <p class="text-3xl text-yellow-400 font-bold animate-pulse">
            The arena is empty... Who will rise? ⚔️
          </p>
        </div>

        <div v-else class="flex flex-col md:flex-row justify-center items-end gap-4 md:gap-8 min-h-[400px]">
          
          <!-- Loop through Top 3 Groups -->
          <!-- Order: 2nd (Left), 1st (Center), 3rd (Right) for Podium Effect -->
          <template v-for="(group, index) in podiumOrder" :key="group.rank">
            <div 
              v-if="group"
              class="w-full md:w-1/3 flex flex-col transition-all duration-700"
              :class="[
                'podium-card',
                group.rank === 1 ? 'order-2 md:order-2 z-20 md:mb-20' : 
                group.rank === 2 ? 'order-1 md:order-1 z-10 md:mb-10' : 
                'order-3 md:order-3 z-10'
              ]"
            >
              <!-- Rank Badge (Crown/Medal) -->
              <div class="text-center mb-4 relative">
                <div v-if="group.rank === 1" class="text-8xl animate-bounce-slow drop-shadow-2xl filter brightness-125">👑</div>
                <div v-else class="text-7xl drop-shadow-xl opacity-90">{{ getMedalEmoji(group.rank) }}</div>
              </div>

              <!-- The Shiny Card -->
              <div 
                class="relative rounded-2xl overflow-hidden border-4 shadow-2xl backdrop-blur-md flex-1 flex flex-col"
                :class="getPodiumStyle(group.rank)"
              >
                <!-- Shine Overlay -->
                <div class="absolute inset-0 bg-gradient-to-tr from-white/0 via-white/20 to-white/0 w-[200%] h-full animate-shine-move pointer-events-none"></div>

                <!-- Card Content -->
                <div class="p-6 text-center text-white relative z-10 flex-1 flex flex-col justify-between">
                  
                  <div>
                    <h2 class="text-3xl font-black italic tracking-wider mb-1 drop-shadow-md">
                      {{ getOrdinal(group.rank) }}
                    </h2>
                    <div class="h-1 w-16 mx-auto bg-white/50 rounded-full mb-4"></div>
                  </div>

                  <!-- Student List -->
                  <div class="flex-1 overflow-visible  custom-scrollbar max-h-40 space-y-2 mt-4">
                    <div 
                      v-for="student in group.students" 
                      :key="student.id"
                      class="flex relative overflow-visible  flex-row justify-center items-center gap-3 bg-black rounded-lg p-2 backdrop-blur-sm"
                    >
                      <q-avatar size="120px" class="shadow-sm absolute z-50 left-0 -top-24 border border-white/30">
                        <img :src="student.avatar || getPlaceholder(student.name)" />
                      </q-avatar>
                      <div class="text-left overflow-visible   min-w-0">
                        <div class="font-bold   overflow-visible  " >
                          <div class="shiny-name  text-6xl">{{ student.firstName }}</div>
                          <div class="shiny-name-small text-xl ml-1">{{ student.lastName }}</div>
                        </div>
                        <div class="text-xs opacity-80 text-yellow-200">{{ student.total }} pts</div>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </template>
        </div>
      </q-card-section>

      <!-- List View for Places 4-10 -->
      <q-card-section v-if="listGroups.length > 0" class="p-4 md:p-8 pt-0 relative z-10">
        <div class="bg-gradient-to-b from-gray-800 to-gray-900 rounded-2xl p-6 border-2 border-gray-700">
          <h3 class="text-2xl font-bold text-yellow-400 mb-4 text-center">
            🌟 Rising Stars 🌟
          </h3>
          
          <div class="space-y-2 max-h-96 overflow-y-auto custom-scrollbar">
            <div
              v-for="group in listGroups"
              :key="group.rank"
              class="bg-gradient-to-r from-gray-700 to-gray-800 rounded-xl p-4 border border-gray-600 hover:border-yellow-500 transition-all hover:scale-105"
            >
              <div class="flex items-center gap-4">
                <!-- Rank Number -->
                <div class="flex-shrink-0 w-12 h-12 rounded-full bg-gradient-to-br from-yellow-600 to-orange-600 flex items-center justify-center border-2 border-yellow-400">
                  <span class="text-white font-black text-lg">{{ group.rank }}</span>
                </div>
                
                <!-- Students in this rank -->
                <div class="flex-1 flex flex-wrap gap-3">
                  <div
                    v-for="student in group.students"
                    :key="student.id"
                    class="flex items-center gap-2 bg-gray-900/50 rounded-lg p-2 pr-4"
                  >
                    <q-avatar size="36px" class="shadow-sm border-2 border-yellow-500/30">
                      <img :src="student.avatar || getPlaceholder(student.name)" />
                    </q-avatar>
                    <div>
                      <div class="font-bold">
                        <span class="shiny-name text-xl">{{ student.firstName }}</span>
                        <span class="shiny-name-small text-xs ml-1">{{ student.lastName }}</span>
                      </div>
                      <div class="text-xs text-yellow-300">{{ student.total }} pts</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'

const props = defineProps({
  students: { type: Array, default: () => [] },
  studentBehaviors: { type: Object, default: () => ({}) },
  periodCode: { type: String, default: '' },
  date: { type: String, default: '' }
})

// Filter state
const filterScope = ref({ label: 'Current Session', value: 'session' })
const topCount = ref(5)

const filterOptions = [
  { label: 'Current Session', value: 'session' },
  { label: 'Today', value: 'today' },
  { label: 'This Week', value: 'week' },
  { label: 'All Time', value: 'all' }
]

// Load saved preferences
onMounted(() => {
  const saved = localStorage.getItem('leaderboard_preferences')
  if (saved) {
    try {
      const prefs = JSON.parse(saved)
      const foundScope = filterOptions.find(opt => opt.value === prefs.scope)
      if (foundScope) filterScope.value = foundScope
      if (prefs.topCount) topCount.value = prefs.topCount
    } catch (e) {
      console.error('Failed to load leaderboard preferences:', e)
    }
  }
})

// Save preferences when they change
watch([filterScope, topCount], () => {
  localStorage.setItem('leaderboard_preferences', JSON.stringify({
    scope: filterScope.value.value,
    topCount: topCount.value
  }))
}, { deep: true })

// Filtered and ranked students
const rankedGroups = computed(() => {
  if (!props.students.length) return []
  
  // 1. Calculate points (for now, using current session data)
  // TODO: Implement different filtering logic based on filterScope
  const studentsWithPoints = props.students.map(student => {
    const behavior = props.studentBehaviors[student.id] || {}
    const total = (behavior.points_plus || 0) - (behavior.points_minus || 0)
    return { ...student, total }
  })
  
  // 2. Filter > 0 and Sort Descending
  const sorted = studentsWithPoints
    .filter(s => s.total > 0)
    .sort((a, b) => b.total - a.total)

  // 3. Group by Score
  const groups = []
  if (sorted.length === 0) return []

  let currentGroup = { rank: 1, total: sorted[0].total, students: [sorted[0]] }

  for (let i = 1; i < sorted.length; i++) {
    const student = sorted[i]
    if (student.total === currentGroup.total) {
      currentGroup.students.push(student)
    } else {
      groups.push(currentGroup)
      currentGroup = {
        rank: groups.length + 1,
        total: student.total,
        students: [student]
      }
    }
  }
  groups.push(currentGroup)

  // 4. Return top N groups based on topCount
  return groups.slice(0, topCount.value)
})

// Top 3 for podium
const podiumGroups = computed(() => {
  return rankedGroups.value.slice(0, 3)
})

// Places 4-10 for list view
const listGroups = computed(() => {
  return rankedGroups.value.slice(3)
})

// Computed property to arrange groups for podium (2, 1, 3)
const podiumOrder = computed(() => {
  const groups = podiumGroups.value
  const first = groups.find(g => g.rank === 1)
  const second = groups.find(g => g.rank === 2)
  const third = groups.find(g => g.rank === 3)
  
  // Return array with explicit order for v-for, filtering out undefined
  return [second, first, third].filter(Boolean)
})

function applyFilters() {
  // Filters are reactive, so this is just for explicit user action feedback
  console.log('Filters applied:', { scope: filterScope.value, topCount: topCount.value })
}

function resetFilters() {
  filterScope.value = { label: 'Current Session', value: 'session' }
  topCount.value = 5
}

function getPodiumStyle(rank) {
  if (rank === 1) return 'bg-gradient-to-b from-yellow-400 via-yellow-500 to-yellow-700 border-yellow-300 shadow-yellow-500/50'
  if (rank === 2) return 'bg-gradient-to-b from-gray-300 via-gray-400 to-gray-600 border-gray-200 shadow-gray-500/50'
  if (rank === 3) return 'bg-gradient-to-b from-orange-400 via-orange-500 to-orange-700 border-orange-300 shadow-orange-500/50'
  return ''
}

function getMedalEmoji(rank) {
  if (rank === 1) return '🥇'
  if (rank === 2) return '🥈'
  if (rank === 3) return '🥉'
  return ''
}

function getOrdinal(n) {
  const s = ['TH', 'ST', 'ND', 'RD']
  const v = n % 100
  return n + (s[(v - 20) % 10] || s[v] || s[0])
}

function getPlaceholder(name) {
  const initials = (name || 'S').split(' ').map(s => s[0]).slice(0, 2).join('').toUpperCase()
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="#e2e8f0"/><text x="20" y="20" dy="6" text-anchor="middle" font-family="Arial" font-weight="bold" fill="#64748b">${initials}</text></svg>`
  return `data:image/svg+xml,${encodeURIComponent(svg)}`
}
</script>

<style scoped>
/* Shiny Name Effects for Grade 4 Students */
.shiny-name {
  background: linear-gradient(135deg, #ffd700, #ffed4e, #ffd700, #ffed4e);
  background-size: 300% 300%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 900;
  text-shadow: 
    0 0 10px rgba(255, 215, 0, 0.8),
    0 0 20px rgba(255, 215, 0, 0.6),
    0 0 30px rgba(255, 215, 0, 0.4);
  animation: shimmer 3s ease-in-out infinite, glow-pulse 2s ease-in-out infinite;
  filter: drop-shadow(0 2px 4px rgba(255, 215, 0, 0.5));
}
.shiny-name2 {
  background: linear-gradient(135deg, #0033ff, #4e57ff, #2600ff, #8c4eff);
  /* background-size: 300% 300%; */
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  /* font-weight: 900; */
  /* text-shadow: 
    0 0 10px rgb(255, 217, 0),
    0 0 20px rgba(255, 217, 0, 0.6),
    0 0 30px rgba(255, 217, 0, 0.4); */
  /* animation: shimmer  3s ease-in-out infinite, glow-pulse 2s ease-in-out infinite; */
  /* filter: drop-shadow(0 2px 4px rgba(255, 215, 0, 0.5)); */
}
.shiny-name-small {
  background: linear-gradient(135deg, #ffffff, #f0f0f0, #ffffff);
  background-size: 200% 200%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  font-weight: 700;
  text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
  animation: shimmer 3s ease-in-out infinite;
  opacity: 0.9;
}

@keyframes shimmer {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
@keyframes shimmer2 {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes glow-pulse {
  0%, 100% { 
    filter: drop-shadow(0 2px 4px rgba(183, 255, 0, 0.5));
  }
  50% { 
    filter: drop-shadow(0 4px 12px rgba(0, 195, 255, 0.9)) drop-shadow(0 0 20px rgba(255, 215, 0, 0.6));
  }
}

/* Shiny Text Animation */
@keyframes shine {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-shine {
  background-size: 200% auto;
  animation: shine 3s linear infinite;
}

/* Moving Shine Overlay */
@keyframes shine-move {
  0% { transform: translateX(-100%) skewX(-15deg); }
  100% { transform: translateX(100%) skewX(-15deg); }
}
.animate-shine-move {
  animation: shine-move 3s infinite;
}

/* Bouncing Crown */
@keyframes bounce-slow {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}
.animate-bounce-slow {
  animation: bounce-slow 2s ease-in-out infinite;
}

/* Stars Background */
.stars {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background: #000 url('http://www.script-tutorials.com/demos/360/images/stars.png') repeat top center;
  z-index: 0;
}
.twinkling {
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background: transparent url('http://www.script-tutorials.com/demos/360/images/twinkling.png') repeat top center;
  z-index: 1;
  animation: move-twink-back 200s linear infinite;
}
@keyframes move-twink-back {
  from {background-position:0 0;}
  to {background-position:-10000px 5000px;}
}

/* Scrollbar for student lists */
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(255,255,255,0.1);
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(255,255,255,0.5);
}
</style>