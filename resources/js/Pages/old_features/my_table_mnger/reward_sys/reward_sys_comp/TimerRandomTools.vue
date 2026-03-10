<template>
  <div class="q-pa-md">
    <div class="row q-col-gutter-md">
      <!-- Tool Selection (Left Column on Desktop) -->
      <div class="col-12 col-md-3">
        <q-list bordered class="rounded-borders bg-white shadow-sm">
          <q-item-label header class="text-weight-bold text-uppercase text-grey-7 bg-grey-2 q-py-md">
            {{ $t('rewardSys.tools', 'Tools') }}
          </q-item-label>

          <!-- Random Student Generators -->
          <q-item clickable v-ripple :active="activeTool === 'random1'" @click="activeTool = 'random1'" active-class="bg-blue-1 text-primary">
            <q-item-section avatar>
              <q-icon name="shuffle" />
            </q-item-section>
            <q-item-section>Random List 1</q-item-section>
          </q-item>

          <q-item clickable v-ripple :active="activeTool === 'random2'" @click="activeTool = 'random2'" active-class="bg-purple-1 text-purple">
            <q-item-section avatar>
              <q-icon name="casino" />
            </q-item-section>
            <q-item-section>Random List 2</q-item-section>
          </q-item>

          <q-separator />

          <!-- Timers (Counts Up) -->
          <q-item clickable v-ripple :active="activeTool === 'timer1'" @click="activeTool = 'timer1'" active-class="bg-green-1 text-green">
            <q-item-section avatar>
              <q-icon name="timer" />
            </q-item-section>
            <q-item-section>Timer 1</q-item-section>
          </q-item>

          <q-item clickable v-ripple :active="activeTool === 'timer2'" @click="activeTool = 'timer2'" active-class="bg-teal-1 text-teal">
            <q-item-section avatar>
              <q-icon name="timelapse" />
            </q-item-section>
            <q-item-section>Timer 2</q-item-section>
          </q-item>

          <q-separator />

          <!-- Countdowns (Counts Down) -->
          <q-item clickable v-ripple :active="activeTool === 'countdown1'" @click="activeTool = 'countdown1'" active-class="bg-orange-1 text-orange">
            <q-item-section avatar>
              <q-icon name="hourglass_empty" />
            </q-item-section>
            <q-item-section>Countdown 1</q-item-section>
          </q-item>

          <q-item clickable v-ripple :active="activeTool === 'countdown2'" @click="activeTool = 'countdown2'" active-class="bg-red-1 text-red">
            <q-item-section avatar>
              <q-icon name="hourglass_bottom" />
            </q-item-section>
            <q-item-section>Countdown 2</q-item-section>
          </q-item>
        </q-list>
      </div>

      <!-- Main Content Area -->
      <div class="col-12 col-md-9">
        <q-card class="shadow-sm border border-gray-200" style="min-height: 400px">
          <q-card-section>
            
            <!-- RANDOM LIST 1 & 2 -->
            <div v-if="activeTool.startsWith('random')">
              <div class="flex items-center justify-between mb-4">
                 <h2 class="text-h5 q-my-none text-primary flex items-center gap-2">
                    <q-icon :name="activeTool === 'random1' ? 'shuffle' : 'casino'" />
                    {{ activeTool === 'random1' ? 'Random List 1' : 'Random List 2' }}
                 </h2>
                 <div class="flex gap-2">
                     <q-chip 
                        :color="getRemainingCount(activeTool) === 0 ? 'positive' : 'grey-3'" 
                        :text-color="getRemainingCount(activeTool) === 0 ? 'white' : 'black'"
                     >
                        {{ randomState[activeTool].pickedIds.length }} / {{ students.length }} Picked
                     </q-chip>
                     <q-btn 
                        v-if="getRemainingCount(activeTool) > 0"
                        label="Pick Random" 
                        color="primary" 
                        icon="autorenew" 
                        @click="pickRandom(activeTool)" 
                     />
                     <q-btn 
                        v-else
                        label="Start New Round"
                        color="positive"
                        icon="refresh"
                        @click="resetRound(activeTool)"
                        class="animate-pulse"
                     />
                 </div>
              </div>

              <!-- Result Display -->
              <div v-if="randomState[activeTool].current" class="text-center p-12 bg-blue-50 rounded-xl border border-blue-100 mb-6 transition-all duration-500 transform scale-100 relative overflow-hidden">
                  <div class="absolute-top-right p-2">
                       <q-badge color="blue" floating>Round Progress: {{ Math.round((randomState[activeTool].pickedIds.length / students.length) * 100) }}%</q-badge>
                  </div>
                  <q-avatar size="100px" class="shadow-lg mb-4">
                     <img :src="getAvatarUrl(randomState[activeTool].current)" />
                  </q-avatar>
                  <div class="text-h3 font-bold text-blue-900">{{ randomState[activeTool].current.name }}</div>
                  <div class="text-subtitle1 text-blue-600 mt-2">Congratulations!</div>
              </div>
              <div v-else-if="getRemainingCount(activeTool) === 0 && students.length > 0" class="text-center p-12 bg-green-50 rounded-xl border border-green-200 mb-6">
                 <q-icon name="check_circle" size="4em" color="positive" />
                 <div class="text-h4 text-green-900 mt-2">All Students Picked!</div>
                 <div class="text-subtitle1 text-green-700">Turn has ended. Ready for a new round?</div>
                 <q-btn label="Reset Round" color="positive" class="mt-4" @click="resetRound(activeTool)" />
              </div>
              <div v-else class="text-center p-12 bg-gray-50 rounded-xl border border-dashed border-gray-300 mb-6">
                 <q-icon name="help_outline" size="4em" color="grey-4" />
                 <div class="text-grey-5 mt-2">Click "Pick Random" to select a student</div>
              </div>

              <!-- History List (Persisted) -->
              <div v-if="randomState[activeTool].history.length > 0">
                 <div class="flex justify-between items-center mb-2">
                     <div class="text-subtitle2 text-grey-7">Session History</div>
                     <q-btn flat dense label="Clear History" size="sm" color="negative" @click="clearRandomHistory(activeTool)" />
                 </div>
                 <div class="flex flex-wrap gap-2">
                    <q-chip v-for="(student, idx) in randomState[activeTool].history" :key="idx" removable @remove="removeFromHistory(activeTool, idx)">
                        <q-avatar>
                           <img :src="getAvatarUrl(student)" />
                        </q-avatar>
                        {{ student.name }}
                    </q-chip>
                 </div>
              </div>
            </div>

            <!-- TIMERS 1 & 2 -->
            <div v-if="activeTool.startsWith('timer')">
               <div class="flex items-center justify-between mb-4">
                 <h2 class="text-h5 q-my-none text-green-8 flex items-center gap-2">
                    <q-icon :name="activeTool === 'timer1' ? 'timer' : 'timelapse'" />
                    {{ activeTool === 'timer1' ? 'Timer 1' : 'Timer 2' }}
                 </h2>
              </div>

              <div class="flex flex-col items-center justify-center p-8">
                 <!-- Timer Display -->
                 <div class="text-9xl font-mono font-bold text-gray-800 tracking-wider mb-8">
                    {{ formatTime(timers[activeTool].seconds) }}
                 </div>
                 
                 <!-- Controls -->
                 <div class="flex gap-4">
                    <q-btn 
                        :color="timers[activeTool].running ? 'warning' : 'positive'" 
                        :icon="timers[activeTool].running ? 'pause' : 'play_arrow'" 
                        :label="timers[activeTool].running ? 'Pause' : 'Start'" 
                        size="lg"
                        class="px-8"
                        @click="toggleTimer(activeTool)"
                    />
                    <q-btn 
                        color="grey" 
                        icon="restart_alt" 
                        label="Reset" 
                        size="lg" 
                        outline
                        @click="resetTimer(activeTool)"
                    />
                 </div>
              </div>
            </div>

            <!-- COUNTDOWNS 1 & 2 -->
             <div v-if="activeTool.startsWith('countdown')">
               <div class="flex items-center justify-between mb-4">
                 <h2 class="text-h5 q-my-none text-orange-9 flex items-center gap-2">
                    <q-icon :name="activeTool === 'countdown1' ? 'hourglass_empty' : 'hourglass_bottom'" />
                    {{ activeTool === 'countdown1' ? 'Countdown 1' : 'Countdown 2' }}
                 </h2>
                 <q-btn icon="settings" flat round color="grey" @click="openCountdownSettings(activeTool)">
                    <q-tooltip>Set Duration</q-tooltip>
                 </q-btn>
              </div>

              <div class="flex flex-col items-center justify-center p-8 relative">
                 <!-- Progress Ring -->
                 <q-circular-progress
                    show-value
                    class="text-orange-8 q-ma-md"
                    :value="getCountdownProgress(activeTool)"
                    size="300px"
                    :thickness="0.1"
                    color="orange"
                    track-color="orange-1"
                    center-color="white"
                 >
                    <div class="text-6xl font-mono font-bold text-gray-800">
                        {{ formatTime(countdowns[activeTool].remaining) }}
                    </div>
                 </q-circular-progress>
                 
                 <!-- Controls -->
                 <div class="flex gap-4 mt-6">
                    <q-btn 
                        :color="countdowns[activeTool].running ? 'warning' : 'positive'" 
                        :icon="countdowns[activeTool].running ? 'pause' : 'play_arrow'" 
                        :label="countdowns[activeTool].running ? 'Pause' : 'Start'" 
                        size="lg"
                        class="px-8"
                        @click="toggleCountdown(activeTool)"
                    />
                    <q-btn 
                        color="grey" 
                        icon="restart_alt" 
                        label="Reset" 
                        size="lg" 
                        outline
                        @click="resetCountdown(activeTool)"
                    />
                 </div>
              </div>

              <!-- Settings Dialog -->
              <q-dialog v-model="showCountdownSettings">
                  <q-card style="min-width: 300px">
                      <q-card-section>
                          <div class="text-h6">Set Duration</div>
                      </q-card-section>
                      <q-card-section>
                          <q-input 
                              v-model.number="countdownSettings.minutes" 
                              type="number" 
                              label="Minutes" 
                              outlined 
                              autofocus
                          />
                          <q-input 
                              v-model.number="countdownSettings.seconds" 
                              type="number" 
                              label="Seconds" 
                              outlined 
                              class="mt-2"
                          />
                      </q-card-section>
                      <q-card-actions align="right">
                          <q-btn flat label="Cancel" color="primary" v-close-popup />
                          <q-btn flat label="Set" color="primary" @click="saveCountdownSettings" />
                      </q-card-actions>
                  </q-card>
              </q-dialog>
            </div>

          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted, onUnmounted } from 'vue'

const props = defineProps({
    students: {
        type: Array,
        default: () => []
    }
})

// Active Tool State (Persisted)
const activeTool = ref(localStorage.getItem('timer_random_active_tool') || 'random1')

watch(activeTool, (val) => {
    localStorage.setItem('timer_random_active_tool', val)
})

// === RANDOM LIST LOGIC ===
const randomState = reactive({
    random1: { current: null, history: [], pickedIds: [] },
    random2: { current: null, history: [], pickedIds: [] }
})

// Load Random State
onMounted(() => {
    try {
        const loadState = (key) => {
             const savedHistory = JSON.parse(localStorage.getItem(`${key}_history`))
             if (savedHistory) randomState[key].history = savedHistory
             
             const savedPicked = JSON.parse(localStorage.getItem(`${key}_picked_ids`))
             if (savedPicked) randomState[key].pickedIds = savedPicked
        }
        loadState('random1')
        loadState('random2')
    } catch (e) { console.error('Error loading random history', e) }
})

const getAvatarUrl = (student) => {
  if (!student) return '';
  if (student.avatar) return student.avatar.startsWith('/') ? student.avatar : `/${student.avatar}`;
  return '/images/avatars/default-avatar.svg';
}

const getRemainingCount = (key) => {
    return props.students.length - randomState[key].pickedIds.length
}

const pickRandom = (key) => {
    if (props.students.length === 0) return
    
    // Filter out already picked students
    const available = props.students.filter(s => !randomState[key].pickedIds.includes(s.id))
    
    if (available.length === 0) {
        // Should be handled by UI (Reset button), but just in case
        return
    }

    const randomIndex = Math.floor(Math.random() * available.length)
    const picked = available[randomIndex]
    
    randomState[key].current = picked
    randomState[key].history.unshift(picked)
    randomState[key].pickedIds.push(picked.id)
    
    // Persist
    localStorage.setItem(`${key}_history`, JSON.stringify(randomState[key].history))
    localStorage.setItem(`${key}_picked_ids`, JSON.stringify(randomState[key].pickedIds))
}

const resetRound = (key) => {
     randomState[key].pickedIds = []
     randomState[key].current = null
     // Optional: Clear history on new round? User said "reset from new", usually implies starting fresh selection.
     // But history might be useful to see previous round. Let's keep history for now, just reset tracking.
     localStorage.setItem(`${key}_picked_ids`, JSON.stringify([]))
}

const clearRandomHistory = (key) => {
    randomState[key].history = []
    randomState[key].current = null // Only clear current display, not progress
    localStorage.removeItem(`${key}_history`)
}

const removeFromHistory = (key, idx) => {
    // If we remove from history, do we un-pick them? 
    // Usually history is just a log. Let's keep logic simple: History removal doesn't affect "picked" status for the round.
    // Unless the user wants to "undo". 
    // For now, just remove from view.
    randomState[key].history.splice(idx, 1)
    localStorage.setItem(`${key}_history`, JSON.stringify(randomState[key].history))
}

// === TIMER LOGIC (Counts UP) ===
const timers = reactive({
    timer1: { seconds: 0, running: false, intervalId: null },
    timer2: { seconds: 0, running: false, intervalId: null }
})

// Load Timer State
onMounted(() => {
   // Ideally we restore total accumulated time + calculate elapsed since last 'active' timestamp if it was running.
   // For simplicity, we just restore the accumulated seconds.
   timers.timer1.seconds = parseInt(localStorage.getItem('timer1_seconds') || 0)
   timers.timer2.seconds = parseInt(localStorage.getItem('timer2_seconds') || 0)
})

const toggleTimer = (key) => {
    if (timers[key].running) {
        // Pause
        clearInterval(timers[key].intervalId)
        timers[key].running = false
    } else {
        // Start
        timers[key].running = true
        timers[key].intervalId = setInterval(() => {
            timers[key].seconds++
            localStorage.setItem(`${key}_seconds`, timers[key].seconds)
        }, 1000)
    }
}

const resetTimer = (key) => {
    if (timers[key].running) toggleTimer(key)
    timers[key].seconds = 0
    localStorage.setItem(`${key}_seconds`, 0)
}

// === COUNTDOWN LOGIC (Counts DOWN) ===
const countdowns = reactive({
    countdown1: { remaining: 300, total: 300, running: false, intervalId: null },
    countdown2: { remaining: 60, total: 60, running: false, intervalId: null }
})

// Load Countdown State
onMounted(() => {
    const loadCD = (key) => {
        const saved = localStorage.getItem(`${key}_config`)
        if (saved) {
            const parsed = JSON.parse(saved)
            countdowns[key].remaining = parsed.remaining
            countdowns[key].total = parsed.total
        }
    }
    loadCD('countdown1')
    loadCD('countdown2')
})

const getCountdownProgress = (key) => {
    if (countdowns[key].total === 0) return 0
    return ((countdowns[key].remaining) / countdowns[key].total) * 100
}

const toggleCountdown = (key) => {
    if (countdowns[key].running) {
        clearInterval(countdowns[key].intervalId)
        countdowns[key].running = false
    } else {
        if (countdowns[key].remaining <= 0) return
        
        countdowns[key].running = true
        countdowns[key].intervalId = setInterval(() => {
            if (countdowns[key].remaining > 0) {
                countdowns[key].remaining--
                // Save state
                localStorage.setItem(`${key}_config`, JSON.stringify({
                    remaining: countdowns[key].remaining,
                    total: countdowns[key].total
                }))
            } else {
                // Done
                clearInterval(countdowns[key].intervalId)
                countdowns[key].running = false
                // Play sound?
                playSound('alarm')
            }
        }, 1000)
    }
}

const resetCountdown = (key) => {
    if (countdowns[key].running) {
        clearInterval(countdowns[key].intervalId)
        countdowns[key].running = false
    }
    countdowns[key].remaining = countdowns[key].total
    localStorage.setItem(`${key}_config`, JSON.stringify({
        remaining: countdowns[key].remaining,
        total: countdowns[key].total
    }))
}

// Settings Dialog
const showCountdownSettings = ref(false)
const countdownSettings = reactive({ key: null, minutes: 0, seconds: 0 })

const openCountdownSettings = (key) => {
    countdownSettings.key = key
    const totalSec = countdowns[key].total
    countdownSettings.minutes = Math.floor(totalSec / 60)
    countdownSettings.seconds = totalSec % 60
    showCountdownSettings.value = true
}

const saveCountdownSettings = () => {
    const key = countdownSettings.key
    const total = (countdownSettings.minutes * 60) + countdownSettings.seconds
    countdowns[key].total = total
    countdowns[key].remaining = total
    
    if (countdowns[key].running) {
        clearInterval(countdowns[key].intervalId)
        countdowns[key].running = false
    }
    
    // Save
    localStorage.setItem(`${key}_config`, JSON.stringify({ remaining: total, total }))
    showCountdownSettings.value = false
}

// Helper: Format Time MM:SS
const formatTime = (totalSeconds) => {
    const m = Math.floor(totalSeconds / 60)
    const s = totalSeconds % 60
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

const playSound = (type) => {
    // Basic beep or leverage main app sound player
    const audio = new Audio('/audio/notification.mp3') // Placeholder
    audio.play().catch(e => {})
}

// Cleanup
onUnmounted(() => {
    Object.keys(timers).forEach(k => clearInterval(timers[k].intervalId))
    Object.keys(countdowns).forEach(k => clearInterval(countdowns[k].intervalId))
})
</script>

<style scoped>
/* Add any specific styles */
</style>
