 <template>
  <div class="w-fit">
  <div class="w-full max-w-3xl mx-auto p-1">
    <table class="w-full text-center bg-white rounded-2xl shadow-sm overflow-hidden">
      <thead>
        <!-- Session Info Row -->
        <tr class="bg-gray-50 border-b border-gray-200">
          <td colspan="3" class="p-3">
            <div class="flex items-center justify-between px-2">
              <div class="flex items-center gap-1 text-sm">
                <div class="flex items-center gap-1">
                  <span class="font-bold text-gray-700">Current Session</span>
                  <span class="text-gray-300">|</span>
                  <span class="font-bold text-indigo-600">{{ className || 'Unknown Class' }}</span>
                </div>
                
                <span class="text-gray-300">|</span>
                
                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-xs font-mono font-bold">
                  {{ periodCode }}
                </span>

                <span class="text-gray-300">|</span>

                <div class="text-gray-500 text-xs">
                  {{ sessionDate ? new Date(sessionDate).toLocaleDateString() : 'No Date' }} • Period {{ periodNumber }}
                </div>
              </div>

              <q-btn flat round dense icon="settings" color="grey-7" size="sm" @click="$emit('setup')">
                <q-tooltip>Setup Session</q-tooltip>
              </q-btn>
            </div>
          </td>
        </tr>
        
        <tr class="text-sm uppercase tracking-wider">
          <th class="py-3 bg-emerald-100 text-emerald-800 w-1/3">Present</th>
          <th class="py-3 bg-red-100 text-red-800 w-1/3">Absent</th>
          <th class="py-3 bg-purple-100 text-purple-800 w-1/3">Total</th>
        </tr>
      </thead>
      <tbody>
        <!-- Big Numbers -->
        <tr class="divide-x divide-gray-100">
          <td class="py-1 bg-emerald-50">
            <div class="text-5xl font-black text-emerald-600 drop-shadow-sm">{{ presentStudents.length }}</div>
          </td>
          <td class="py-1 bg-red-50">
            <div class="text-5xl font-black text-red-600 drop-shadow-sm">{{ absentStudents.length }}</div>
          </td>
          <td class="py-4 bg-purple-50">
            <div class="text-5xl font-black text-purple-600 drop-shadow-sm">{{ students.length }}</div>
          </td>
        </tr>

        <!-- Percentages -->
        <tr class="divide-x divide-gray-100">
          <td class="py-1 bg-emerald-50 text-emerald-900 font-bold text-lg">
            {{ attendancePercentage }}%
          </td>
          <td class="py-1 bg-red-50 text-red-900 font-bold text-lg">
            {{ students.length ? 100 - attendancePercentage : 0 }}%
          </td>
          <td class="py-1 bg-purple-50 text-purple-900 font-bold text-lg">
            100%
          </td>
        </tr>

        <!-- Action Buttons -->
        <tr class="divide-x divide-gray-100 border-t border-gray-100">
          <td class="py-2 bg-emerald-50/50 space-x-1">
             <q-btn round flat dense size="md" color="emerald-7" icon="content_copy" @click="copyList('present')">
               <q-tooltip>Copy Present</q-tooltip>
             </q-btn>
             <q-btn round flat dense size="md" color="emerald-7" icon="visibility" @click="showPresentDialog = true">
               <q-tooltip>Show Present</q-tooltip>
             </q-btn>
          </td>
          <td class="py-2 bg-red-50/50 space-x-1">
             <q-btn round flat dense size="md" color="red-7" icon="content_copy" @click="copyList('absent')">
               <q-tooltip>Copy Absent</q-tooltip>
             </q-btn>
             <q-btn round flat dense size="md" color="red-7" icon="visibility" @click="showAbsentDialog = true">
               <q-tooltip>Show Absent</q-tooltip>
             </q-btn>
          </td>
          <td class="py-2 bg-purple-50/50 space-x-1">
             <q-btn round flat dense size="md" color="purple-7" icon="content_copy" @click="copyList('total')">
               <q-tooltip>Copy Total</q-tooltip>
             </q-btn>
             <q-btn round flat dense size="md" color="purple-7" icon="visibility" @click="showTotalDialog = true">
                <q-tooltip>Show Total</q-tooltip>
             </q-btn>
          </td>
        </tr>
      </tbody>
    </table>
  </div>


    <!-- TOTAL Dialog -->
    <q-dialog v-model="showTotalDialog">
      <q-card class="min-w-[400px] max-w-[600px] rounded-2xl shadow-xl">
        <q-card-section class="bg-gradient-to-r from-pink-400 via-purple-400 to-indigo-400 text-white p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <q-icon name="diversity_3" size="md" />
              <div>
                <div class="text-h6 font-black uppercase tracking-wide text-sm">All My Friends</div>
                <div class="text-xs font-bold opacity-90">{{ students.length }} Registered 🌟</div>
              </div>
            </div>
            <q-btn flat round dense icon="close" v-close-popup class="hover:bg-white/20" />
          </div>
        </q-card-section>
        
        <q-card-section class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
          <div v-for="(student, index) in students" :key="student.id" class="flex items-center gap-3 p-3 border-b border-gray-100 hover:bg-purple-50 transition-colors">
            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center font-black text-xs shadow-sm">
              {{ index + 1 }}
            </div>
            <div class="font-bold text-gray-700 text-sm">{{ getFullName(student) }}</div>
          </div>
        </q-card-section>
        
        <q-card-actions align="right" class="p-3 bg-gray-50 border-t border-gray-100">
          <q-btn flat label="Close" color="grey-7" v-close-popup class="font-bold" size="sm" />
          <q-btn unelevated color="purple" icon="content_copy" label="Copy Names" @click="copyList('total')" size="sm" class="font-bold" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- PRESENT Dialog -->
    <q-dialog v-model="showPresentDialog">
      <q-card class="min-w-[400px] max-w-[600px] rounded-2xl shadow-xl">
        <q-card-section class="bg-gradient-to-r from-emerald-400 to-teal-500 text-white p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <q-icon name="emoji_emotions" size="md" />
              <div>
                <div class="text-h6 font-black uppercase tracking-wide text-sm">Here Today!</div>
                <div class="text-xs font-bold opacity-90">{{ presentStudents.length }} Present ✅</div>
              </div>
            </div>
            <q-btn flat round dense icon="close" v-close-popup class="hover:bg-white/20" />
          </div>
        </q-card-section>

        <q-card-section class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
          <div v-if="presentStudents.length === 0" class="text-center py-8 text-gray-400 font-bold">
            No students yet...
          </div>
          <div v-else v-for="(student, index) in presentStudents" :key="student.id" class="flex items-center gap-3 p-3 border-b border-gray-100 hover:bg-emerald-50 transition-colors">
            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-black text-xs shadow-sm">
              {{ index + 1 }}
            </div>
            <div class="font-bold text-gray-700 text-sm">{{ getFullName(student) }}</div>
            <div class="ml-auto">
              <span class="text-[10px] font-black px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 shadow-sm border border-emerald-200">
                {{ getStudentPoints(student.id) }} pts
              </span>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="p-3 bg-gray-50 border-t border-gray-100">
          <q-btn flat label="Close" color="grey-7" v-close-popup class="font-bold" size="sm" />
          <q-btn unelevated color="emerald" icon="content_copy" label="Names" @click="copyList('present')" size="sm" class="font-bold" />
          <q-btn unelevated color="teal" icon="emoji_events" label="w/ Points" @click="copyList('present_points')" size="sm" class="font-bold" />
        </q-card-actions>
      </q-card>
    </q-dialog>

    <!-- ABSENT Dialog -->
    <q-dialog v-model="showAbsentDialog">
      <q-card class="min-w-[400px] max-w-[600px] rounded-2xl shadow-xl border-4 border-orange-200">
        <q-card-section class="bg-gradient-to-r from-orange-400 to-red-500 text-white p-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <q-icon name="sentiment_dissatisfied" size="md" />
              <div>
                <div class="text-h6 font-black uppercase tracking-wide text-sm">Who is Missing?</div>
                <div class="text-xs font-bold opacity-90">{{ absentStudents.length }} Absent 😢</div>
              </div>
            </div>
            <q-btn flat round dense icon="close" v-close-popup class="hover:bg-white/20" />
          </div>
        </q-card-section>

        <q-card-section class="p-0 max-h-[60vh] overflow-y-auto custom-scrollbar">
          <div v-if="absentStudents.length === 0" class="text-center py-10">
            <q-icon name="auto_awesome" size="4rem" class="mb-3 text-purple-400 animate-pulse" />
            <p class="text-lg font-black text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-500">Everyone is Here!</p>
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">Perfect Attendance 🌈</p>
          </div>
          <div v-else>
            <div v-for="(student, index) in absentStudents" :key="student.id" class="flex items-center gap-3 p-3 border-b border-gray-100 hover:bg-red-50 transition-colors">
              <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center font-black text-xs shadow-sm">
                {{ index + 1 }}
              </div>
              <div class="font-bold text-gray-700 text-sm">{{ getFullName(student) }}</div>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="p-3 bg-gray-50 border-t border-gray-100">
          <q-btn flat label="Close" color="grey-7" v-close-popup class="font-bold" size="sm" />
          <q-btn v-if="absentStudents.length > 0" unelevated color="deep-orange" icon="content_copy" label="Copy List" @click="copyList('absent')" size="sm" class="font-bold" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<style scoped>
.animate-spin-slow {
  animation: spin 8s linear infinite;
}
.border-rainbow {
  background: linear-gradient(45deg, red, orange, yellow, green, blue, purple, pink);
  background-size: 400% 400%;
  animation: rainbow 15s ease infinite;
}
@keyframes rainbow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>
<style scoped>
.delayed {
  animation-delay: 0.5s;
}
</style>
<script setup>
import { ref, computed } from 'vue'
import { useQuasar, copyToClipboard } from 'quasar'

const props = defineProps({
  students: {
    type: Array,
    default: () => []
  },
  studentAttendance: {
    type: Object,
    default: () => ({})
  },
  studentBehaviors: {
    type: Object,
    default: () => ({})
  },
  // Session Info Props
  className: {
    type: String,
    default: ''
  },
  periodCode: {
    type: String,
    default: ''
  },
  sessionDate: {
    type: [String, Date],
    default: ''
  },
  periodNumber: {
    type: [Number, String],
    default: ''
  }
})

const emit = defineEmits(['setup'])

const $q = useQuasar()

const showTotalDialog = ref(false)
const showPresentDialog = ref(false)
const showAbsentDialog = ref(false)

// Computed Properties
const presentStudents = computed(() => {
  return props.students.filter(s => props.studentAttendance[s.id])
})

const absentStudents = computed(() => {
  return props.students.filter(s => !props.studentAttendance[s.id])
})

const attendancePercentage = computed(() => {
  if (props.students.length === 0) return 0
  return Math.round((presentStudents.value.length / props.students.length) * 100)
})

// Helpers
const getFullName = (student) => {
  return [student.firstName, student.secondName, student.lastName].filter(Boolean).join(' ')
}

const getStudentPoints = (studentId) => {
    const b = props.studentBehaviors[studentId]
    if (!b) return 0
    return (b.points_plus || 0) - (b.points_minus || 0)
}

// Text Generation
const getListText = (type) => {
  let list = []
  let header = ''

  if (type === 'total') {
    list = props.students
    header = `Total Class List (${list.length})`
  } else if (type === 'present') {
    list = presentStudents.value
    header = `Present Students (${list.length})`
  } else if (type === 'absent') {
    list = absentStudents.value
    header = `Absent Students (${list.length})`
  } else if (type === 'present_points') {
    list = presentStudents.value
    header = `Present Students with Points (${list.length})`
  }

  if (list.length === 0) return `No ${type} students`

  const content = list.map((s, index) => {
    const name = getFullName(s)
    if (type === 'present_points') {
      return `${index + 1}. ${name} - ${getStudentPoints(s.id)} points`
    }
    return `${index + 1}. ${name}`
  }).join('\n')

  return `${header}:\n\n${content}`
}


// Copy Action
const copyList = (type) => {
  const text = getListText(type)
  const count = type === 'total' ? props.students.length : 
                type.includes('present') ? presentStudents.value.length : 
                absentStudents.value.length

  if (count === 0 && type !== 'total') {
     $q.notify({
        message: 'List is empty',
        color: 'warning',
        position: 'top',
        timeout: 1500
      })
      return
  }

  copyToClipboard(text)
    .then(() => {
      $q.notify({
        message: `Copied ${count} names to clipboard`,
        color: 'positive',
        icon: 'content_copy',
        position: 'top',
        timeout: 2000
      })
    })
    .catch((err) => {
      console.error('Copy failed:', err)
      $q.notify({
        message: 'Failed to copy',
        color: 'negative',
        position: 'top'
      })
    })
}

</script>

<style scoped>
.glossy {
  background-image: linear-gradient(to bottom, rgba(255,255,255,0.3), rgba(255,255,255,0));
}
</style>
