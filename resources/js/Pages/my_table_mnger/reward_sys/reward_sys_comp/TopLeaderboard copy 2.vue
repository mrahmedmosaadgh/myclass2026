<template>
  <div v-if="students.length" class="mb-6">
 <q-card class="shadow-2xl rounded-3xl overflow-hidden bg-gray-900 border-4 border-yellow-500">
  <!-- Epic Header with Animation -->
  <q-card-section class="bg-gradient-to-r from-red-600 via-yellow-500 to-red-800 text-white py-8 relative overflow-hidden">
    <div class="absolute inset-0 opacity-30">
      <div class="stars"></div>
    </div>
    <div class="text-center animate-pulse">
      <h1 class="text-5xl md:text-6xl font-black tracking-wider drop-shadow-2xl">
        🏆 HALL OF CHAMPIONS 🏆
      </h1>
      <p class="text-2xl mt-2 font-bold animate-bounce">MAKE ACHIEVEMENT GREAT AGAIN!</p>
      <div class="flex justify-center gap-4 mt-4">
        <span class="text-4xl animate-spin">⭐</span>
        <span class="text-4xl animate-spin animation-delay-500">🔥</span>
        <span class="text-4xl animate-spin animation-delay-1000">💫</span>
      </div>
    </div>
  </q-card-section>

  <q-card-section class="bg-black bg-opacity-90 p-6">
    <div v-if="topStudents.length === 0" class="text-center py-16">
      <p class="text-3xl text-yellow-400 font-bold animate-pulse">
        No champions yet... Who will claim the throne? 👀
      </p>
    </div>

    <div v-else class="space-y-6">
      <!-- Top 3 Podium Style -->
      <div v-for="(student, index) in topStudents.slice(0, 3)" :key="student.id"
        class="relative transform transition-all duration-700 hover:scale-105"
        :class="[
          index === 0 ? 'order-2 mt-8' : index === 1 ? 'order-1 mt-16' : 'order-3 mt-24',
          'podium-item animate__animated',
          index === 0 ? 'animate__fadeInDown' : index === 1 ? 'animate__fadeInUp animation-delay-300' : 'animate__fadeInUp animation-delay-600'
        ]">
        
        <!-- GLOWING PODIUM CARD -->
        <div class="relative mx-auto max-w-md">
          <!-- Crown for 1st Place -->
          <div v-if="index === 0" class="absolute -top-16 left-1/2 transform -translate-x-1/2 text-8xl animate-bounce">
            👑
          </div>

          <div class="bg-gradient-to-br from-yellow-600 to-amber-700 rounded-3xl shadow-2xl overflow-hidden border-4"
            :class="index === 0 ? 'border-yellow-400 shadow-yellow-400/80' : 
                    index === 1 ? 'from-gray-500 to-gray-700 border-gray-300 shadow-gray-400/80' : 
                                 'from-orange-600 to-orange-800 border-orange-400 shadow-orange-400/80'">
            
            <!-- Medal Badge -->
            <div class="absolute -top-6 -right-6 text-9xl opacity-30">
              {{ index === 0 ? '🥇' : index === 1 ? '🥈' : '🥉' }}
            </div>

            <div class="p-8 text-center text-white relative z-10">
              <div class="text-6xl mb-4 animate-pulse">
                {{ index === 0 ? '🏆' : index === 1 ? '🌟' : '🔥' }}
              </div>
              <h2 class="text-4xl font-black tracking-wide drop-shadow-lg">
                {{ ['1ST PLACE', '2ND PLACE', '3RD PLACE'][index] }}
              </h2>
              <p class="text-3xl mt-3 font-bold">{{ student.name.toUpperCase() }}</p>
              
              <div class="mt-6">
                <p class="text-7xl font-black text-yellow-300 animate-pulse">
                  {{ student.total }}
                </p>
                <p class="text-2xl opacity-90">POINTS</p>
              </div>

              <div class="flex justify-center gap-6 mt-4 text-lg">
                <span class="text-green-400">+{{ student.points_plus }}</span>
                <span class="text-red-400">-{{ student.points_minus }}</span>
              </div>
            </div>

            <!-- Firework Effect -->
            <div class="absolute inset-0 pointer-events-none">
              <div class="fireworks"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Rest of the Top 5 (4th & 5th) -->
      <div v-for="(student, index) in topStudents.slice(3)" :key="student.id"
        class="bg-gradient-to-r from-purple-900 to-indigo-900 rounded-2xl p-5 flex items-center justify-between text-white shadow-xl hover:shadow-2xl transition-all">
        <div class="flex items-center gap-4">
          <span class="text-5xl font-bold">#{{ index + 4 }}</span>
          <div>
            <p class="text-2xl font-bold">{{ student.name }}</p>
            <p class="text-sm opacity-80">Legend in the Making</p>
          </div>
        </div>
        <div class="text-right">
          <p class="text-4xl font-black text-cyan-300">{{ student.total }}</p>
          <p class="text-lg">points</p>
        </div>
      </div>
    </div>
  </q-card-section>
</q-card>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  students: {
    type: Array,
    default: () => []
  },
  studentBehaviors: {
    type: Object,
    default: () => ({})
  }
})

const topStudents = computed(() => {
  if (!props.students.length) return []
  
  // Map students with their points
  const studentsWithPoints = props.students.map(student => {
    const behavior = props.studentBehaviors[student.id] || {}
    const pointsPlus = behavior.points_plus || 0
    const pointsMinus = behavior.points_minus || 0
    const total = pointsPlus - pointsMinus
    
    return {
      id: student.id,
      name: student.name,
      points_plus: pointsPlus,
      points_minus: pointsMinus,
      total: total
    }
  })
  
  // Sort by total points (descending) and take top 5
  return studentsWithPoints
    .filter(s => s.total > 0) // Only show students with positive points
    .sort((a, b) => b.total - a.total)
    .slice(0, 5)
})

function getMedalEmoji(index) {
  const medals = ['🥇', '🥈', '🥉']
  return medals[index] || `${index + 1}.`
}
</script>

<style scoped>
.stars {
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="10" cy="10" r="2" fill="white"/><circle cx="30" cy="20" r="1.5" fill="white"/><circle cx="80" cy="15" r="2" fill="white"/><circle cx="50" cy="70" r="1.8" fill="white"/></svg>') repeat;
  animation: twinkle 4s infinite;
}
@keyframes twinkle { 0%,100% {opacity:0.3} 50% {opacity:0.8} }

.podium-item { animation-duration: 1.5s; }

.fireworks {
  background: radial-gradient(circle at 30% 30%, rgba(255,215,0,0.6), transparent 50%),
              radial-gradient(circle at 70% 70%, rgba(255,0,150,0.6), transparent 50%),
              radial-gradient(circle at 50% 10%, rgba(0,255,255,0.6), transparent 50%);
  animation: fireworks 3s infinite alternate;
}
@keyframes fireworks { 0% {opacity:0.4; transform:scale(0.8)} 100% {opacity:0.8; transform:scale(1.2)} }

.animation-delay-300 { animation-delay: 0.3s; }
.animation-delay-500 { animation-delay: 0.5s; }
.animation-delay-600 { animation-delay: 0.6s; }
.animation-delay-1000 { animation-delay: 1s; }
</style>