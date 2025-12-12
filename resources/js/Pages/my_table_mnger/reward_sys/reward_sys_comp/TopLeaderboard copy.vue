<template>
  <div v-if="students.length" class="mb-6">
    <q-card class="shadow-lg rounded-2xl overflow-hidden">
      <q-card-section class="bg-gradient-to-r from-amber-500 to-yellow-600 text-white p-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-2xl">🏆</span>
            <h3 class="text-xl font-bold">Top 5 Students</h3>
          </div>
          <div class="text-sm opacity-90">Current Session</div>
        </div>
      </q-card-section>
      <q-card-section class="p-4">
        <div v-if="topStudents.length === 0" class="text-center py-4 text-gray-500">
          No points awarded yet. Start rewarding students!
        </div>
        <div v-else class="space-y-2">
          <div
            v-for="(student, index) in topStudents"
            :key="student.id"
            class="flex items-center justify-between p-1 rounded-lg transition-all hover:bg-gray-50"
            :class="[
              index === 0 ? 'bg-gradient-to-r from-yellow-50 to-amber-50 border-2 border-amber-300' :
              index === 1 ? 'bg-gradient-to-r from-gray-50 to-slate-50 border-2 border-gray-300' :
              index === 2 ? 'bg-gradient-to-r from-orange-50 to-amber-50 border-2 border-orange-300' :
              'bg-white border border-gray-200'
            ]"
          >
            <div class="flex items-center gap-3">
              <span class="text-3xl">{{ getMedalEmoji(index) }}</span>
              <div>
                <p class="font-bold text-lg">{{ student.name }}</p>
                <div class="flex gap-2 text-xs">
                  <span class="text-green-600">+{{ student.points_plus }}</span>
                  <span class="text-red-600">-{{ student.points_minus }}</span>
                </div>
              </div>
            </div>
            <div class="text-right">
              <p class="text-2xl font-bold text-blue-600">{{ student.total }}</p>
              <p class="text-xs text-gray-500">points</p>
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
