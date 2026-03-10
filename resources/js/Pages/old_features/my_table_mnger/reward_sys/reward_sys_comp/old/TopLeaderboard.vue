<template>
  <div v-if="students.length" class="mb-6">
    <q-card class="shadow-2xl rounded-3xl overflow-hidden bg-gray-900 border-4 border-yellow-500 relative">
      
      <!-- Background Animation -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="stars"></div>
        <div class="twinkling"></div>
      </div>

      <!-- Epic Header -->
      <q-card-section class="relative z-10 text-center py-8 bg-gradient-to-b from-transparent to-black/50">
        <h1 class="text-5xl md:text-7xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 via-yellow-100 to-yellow-500 drop-shadow-lg animate-shine">
          🏆 CHAMPIONS 🏆
        </h1>
        <p class="text-xl text-yellow-200 mt-2 font-bold tracking-widest uppercase opacity-80">Top 3 Ranks</p>
      </q-card-section>

      <q-card-section class="p-4 md:p-8 relative z-10">
        <div v-if="topGroups.length === 0" class="text-center py-16">
          <p class="text-3xl text-yellow-400 font-bold animate-pulse">
            The arena is empty... Who will rise? ⚔️
          </p>
        </div>

        <div v-else class="flex flex-col md:flex-row justify-center items-end gap-4 md:gap-8 min-h-[500px]">
          
          <!-- Loop through Top 3 Groups -->
          <!-- Order: 2nd (Left), 1st (Center), 3rd (Right) for Podium Effect -->
          <template v-for="(group, index) in podiumOrder" :key="group.rank">
            <div 
              v-if="group"
              class="w-full md:w-1/3 flex flex-col transition-all duration-700"
              :class="[
                'podium-card',
                group.rank === 1 ? ' z-20  mb-40' : 
                group.rank === 2 ? 'order-2 md:order-1 z-10' : 
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
                  <div class="flex-1 overflow-y-auto custom-scrollbar max-h-40 space-y-2 mt-4">
                    <div 
                      v-for="student in group.students" 
                      :key="student.id"
                      class="flex items-center gap-3 bg-white/10 rounded-lg p-2 backdrop-blur-sm"
                    >
                      <q-avatar size="32px" class="shadow-sm border border-white/30">
                        <img :src="student.avatar || getPlaceholder(student.name)" />
                      </q-avatar>
                      <div class="text-left overflow-hidden min-w-0">
                        <div class="font-bold text-sm truncate text-white">{{ student.name }}</div>
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
    </q-card>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  students: { type: Array, default: () => [] },
  studentBehaviors: { type: Object, default: () => ({}) }
})

const topGroups = computed(() => {
  if (!props.students.length) return []
  
  // 1. Calculate points
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

  // 4. Return ONLY Top 3 Groups
  return groups.slice(0, 3)
})

// Computed property to arrange groups for podium (2, 1, 3)
const podiumOrder = computed(() => {
  const groups = topGroups.value
  const first = groups.find(g => g.rank === 1)
  const second = groups.find(g => g.rank === 2)
  const third = groups.find(g => g.rank === 3)
  
  // Return array with explicit order for v-for, filtering out undefined
  return [second, first, third].filter(Boolean)
})

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