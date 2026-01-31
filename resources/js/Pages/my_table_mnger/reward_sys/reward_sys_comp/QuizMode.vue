<template>
  <q-dialog 
    :model-value="modelValue" 
    @update:model-value="$emit('update:modelValue', $event)" 
    maximized 
    transition-show="slide-up" 
    transition-hide="slide-down"
  >
    <q-card class="bg-gray-50    h-full">
      
      <!-- Header -->
      <q-card-section class="bg-primary text-white  p-3 sm:p-4 shadow-md z-10 hidden md:block">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4">
      
          <!-- Title & Controls -->
          <div class="flex flex-wrap items-center gap-2 sm:gap-4 flex-grow">
            <q-btn flat round dense icon="arrow_back" @click="$emit('update:modelValue', false)" />
            <div class="text-base sm:text-h6 font-bold flex items-center gap-2 whitespace-nowrap">
              <q-icon name="quiz" /> <span class="hidden sm:inline">Quiz Mode</span>
            </div>
 
            <!-- Quiz Settings -->
            <div class="flex items-center gap-2 sm:gap-3 bg-blue-800/30 p-1.5 sm:p-2 rounded-lg border border-blue-400/30 overflow-x-auto max-w-[calc(100vw-120px)] sm:max-w-none  ">
               <q-input 
                 v-model="quizTitle" 
                 dense 
                 dark 
                 borderless
                 placeholder="Quiz Title"
                 class="font-bold min-w-[120px] sm:min-w-[200px]"
                 input-class="text-white placeholder-blue-200"
                 @update:model-value="saveState"
               >
                 <template v-slot:prepend><q-icon name="edit" size="xs" color="blue-200" /></template>
               </q-input>

               <div class="h-6 w-px bg-blue-400/30"></div>

               <!-- Behavior Selector (New) -->
               <q-select
                  v-model="selectedBehaviorId"
                  :options="behaviorOptions"
                  emit-value
                  map-options
                  dense
                  dark
                  borderless
                  options-dense
                  class="min-w-[120px] sm:min-w-[150px]"
                  style="min-width: 120px"
                  label-color="blue-200"
                  popup-content-class="text-xs"
                  @update:model-value="saveState"
               >
                 <template v-slot:selected>
                    <div class="text-white font-bold text-xs truncate max-w-[100px] sm:max-w-[120px]">
                       {{ getBehaviorName(selectedBehaviorId) || 'Select Category' }}
                    </div>
                 </template>
               </q-select>

               <div class="h-6 w-px bg-blue-400/30"></div>

               <div class="flex items-center gap-1 sm:gap-2">
                 <span class="text-blue-200 text-xs font-bold uppercase hidden sm:inline">Max:</span>
                 <q-input 
                   v-model.number="finalMark" 
                   type="number" 
                   dense 
                   dark 
                   borderless
                   placeholder="10"
                   class="font-bold w-10 sm:w-12 text-center"
                   input-class="text-center text-white"
                   @update:model-value="saveState"
                 />
               </div>
            </div>
          </div>

             <!-- Timer -->
          <div class="flex items-center gap-2 bg-gray-900/50 p-1.5 sm:p-2 rounded-lg border border-gray-700 ml-auto sm:ml-0">
             
             <!-- Refresh Button -->
             <q-btn 
               round 
               dense 
               flat 
               icon="sync" 
               color="blue-200" 
               size="sm" 
               @click="$emit('refresh-data')"
             >
               <q-tooltip>Refresh Student Data</q-tooltip>
             </q-btn>

             <q-separator vertical color="grey-7" class="mx-1" />

             <q-btn-dropdown
                flat
                dense
                content-class="bg-white"
                :label="formatTime(timerSeconds)"
                class="text-lg sm:text-h5 font-mono font-bold text-white tracking-widest"
                no-caps
             >
                <q-list class="min-w-[200px]">
                   <q-item-label header>Select Duration</q-item-label>
                   
                   <q-item clickable v-close-popup @click="setQuickTimer(5)">
                      <q-item-section avatar><q-icon name="timer" color="grey-7" /></q-item-section>
                      <q-item-section>5 Minutes</q-item-section>
                   </q-item>

                   <q-item clickable v-close-popup @click="setQuickTimer(10)">
                      <q-item-section avatar><q-icon name="timer" color="grey-7" /></q-item-section>
                      <q-item-section>10 Minutes</q-item-section>
                   </q-item>

                   <q-item clickable v-close-popup @click="setQuickTimer(15)">
                      <q-item-section avatar><q-icon name="timer" color="grey-7" /></q-item-section>
                      <q-item-section>15 Minutes</q-item-section>
                   </q-item>
                   
                   <q-item clickable v-close-popup @click="setQuickTimer(30)">
                      <q-item-section avatar><q-icon name="timer" color="grey-7" /></q-item-section>
                      <q-item-section>30 Minutes</q-item-section>
                   </q-item>

                   <q-separator />
                   
                   <q-item-label header>Manual Input</q-item-label>
                   <q-item class="gap-2">
                       <q-input v-model.number="timerInputMinutes" label="Min" type="number" outlined dense class="col" @click.stop />
                       <q-input v-model.number="timerInputSeconds" label="Sec" type="number" outlined dense class="col" @click.stop />
                   </q-item>
                   <q-item>
                      <q-btn label="Set Time" color="primary" class="full-width" size="sm" v-close-popup @click="setTimer" />
                   </q-item>
                </q-list>
             </q-btn-dropdown>

             <div class="flex gap-1 border-l border-gray-600 pl-2">
               <q-btn round dense :icon="timerRunning ? 'pause' : 'play_arrow'" :color="timerRunning ? 'amber' : 'green'" size="sm" @click.stop="toggleTimer" />
               <q-btn round dense icon="refresh" flat color="grey-4" size="sm" @click.stop="resetTimer" />
             </div>
          </div>

        </div>
      </q-card-section>

      <!-- Mobile Header (Compact) -->
      <div class="bg-primary text-white p-2 shadow-md z-10 fixed top-0 w-full md:hidden flex items-center justify-between gap-2">
         <q-btn flat round dense icon="arrow_back" @click="$emit('update:modelValue', false)" size="sm" />
         <div class="text-sm font-bold truncate flex-grow text-center">{{ quizTitle }}</div>
         
         <div class="flex items-center gap-1 bg-gray-900/50 px-1 py-0.5 rounded border border-gray-700">
             <!-- Mobile Refresh -->
             <q-btn 
               round 
               dense 
               flat 
               icon="sync" 
               color="blue-200" 
               size="xs" 
               @click="$emit('refresh-data')"
             />
             
            <q-btn-dropdown
                flat
                dense
                content-class="bg-white"
                :label="formatTime(timerSeconds)"
                class="text-xs font-mono font-bold text-white tracking-widest min-w-[50px]"
                no-caps
                size="sm"
             >
                <q-list class="min-w-[200px]">
                   <q-item-label header>Select Duration</q-item-label>
                   
                   <q-item clickable v-close-popup @click="setQuickTimer(5)">
                      <q-item-section>5 Minutes</q-item-section>
                   </q-item>
                   <q-item clickable v-close-popup @click="setQuickTimer(10)">
                      <q-item-section>10 Minutes</q-item-section>
                   </q-item>
                   <q-item clickable v-close-popup @click="setQuickTimer(15)">
                      <q-item-section>15 Minutes</q-item-section>
                   </q-item>
                   
                   <q-separator />
                   <q-item-label header>Manual</q-item-label>
                   <q-item class="gap-2">
                       <q-input v-model.number="timerInputMinutes" label="Min" type="number" outlined dense class="col" @click.stop />
                       <q-input v-model.number="timerInputSeconds" label="Sec" type="number" outlined dense class="col" @click.stop />
                   </q-item>
                   <q-item>
                      <q-btn label="Set" color="primary" class="full-width" size="sm" v-close-popup @click="setTimer" />
                   </q-item>
                </q-list>
             </q-btn-dropdown>
            
            <div class="h-4 w-px bg-gray-600"></div>
            <q-btn round dense :icon="timerRunning ? 'pause' : 'play_arrow'" :color="timerRunning ? 'amber' : 'green'" size="xs" @click.stop="toggleTimer" />
         </div>
      </div>
 
      <!-- Name Filter Bar -->
      <div class=" bg-white w-full  border-b border-gray-200 p-2 overflow-x-auto  mt-12  ">
         <div class="flex gap-1 min-w-max px-2">
            <!-- Clear Filter Button -->
            <q-btn 
              icon="filter_alt_off"
              :color="activeNameFilter ? 'negative' : 'grey-3'"
              :text-color="activeNameFilter ? 'white' : 'grey-6'"
              round
              dense
              unelevated
              size="sm"
              class="font-bold shadow-sm mr-2"
              style="width: 28px; height: 28px"
              @click="activeNameFilter = null"
              :disable="!activeNameFilter"
            >
               <q-tooltip>Clear Filter</q-tooltip>
            </q-btn>
            
            <q-separator vertical class="mr-2" />

            <q-btn 
              v-for="char in filterChars" 
              :key="char"
              :label="char"
              @click="activeNameFilter = activeNameFilter === char ? null : char"
              :color="activeNameFilter === char ? 'primary' : 'grey-2'"
              :text-color="activeNameFilter === char ? 'white' : 'grey-8'"
              round
              dense
              unelevated
              size="sm"
              class="font-bold shadow-sm"
              style="width: 28px; height: 28px"
            />
         </div>
      </div>
<div class="flex flex-col flex-grow overflow-hidden"> 
</div>
      <!-- Main Content Split -->
      <div class="flex-grow flex flex-col md:flex-row overflow-hidden relative bg-white">
         
         <!-- Pending Students -->
         <div class="flex-grow flex flex-col p-4 overflow-hidden">
            <div class="flex items-center justify-between mb-2">
               <h3 class="text-lg font-bold text-gray-700 flex items-center gap-2">
                 <q-icon name="pending" color="orange" />
                 Pending
                 <q-badge color="orange" rounded class="px-2">
                    {{ filteredPendingStudents.length }} 
                    <span v-if="activeNameFilter" class="ml-1 text-xs opacity-75">
                        (of {{ students.length - completedStudentIds.length }} total pending)
                    </span>
                 </q-badge>
                 <span class="text-xs text-gray-400 font-normal ml-2">
                    Total Progress: {{ completedStudentIds.length }} / {{ students.length }}
                 </span>
               </h3>
               <q-btn 
                 dense 
                 flat 
                 icon="shuffle" 
                 color="primary" 
                 label="Pick Random" 
                 @click="pickRandomStudent"
                 :disable="filteredPendingStudents.length === 0"
               />
            </div>
             
            <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar relative">
               <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                  <div 
                    v-for="student in filteredPendingStudents" 
                    :key="student.id"
                    class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 hover:shadow-md transition-all flex flex-col items-center group relative overflow-hidden"
                    :class="{'ring-2 ring-primary ring-offset-1': focusedStudentId === student.id}"
                    @click="focusStudent(student.id)"
                  >
                     <!-- Avatar -->
                     <q-avatar size="56px" class="shadow-sm mb-2">
                        <img :src="getAvatarUrl(student)" />
                     </q-avatar>
                     
                     <!-- Name -->
                     <div class="text-sm font-bold text-center text-gray-800 leading-tight h-8 overflow-hidden line-clamp-2 w-full">
                        {{ student.name }}
                     </div>
                     
                     <!-- Mark Input -->
                     <div class="mt-3 w-full relative">
                        <q-input 
                          v-model.number="studentMarks[student.id]" 
                          type="number" 
                          outlined 
                          dense 
                          placeholder="Mark" 
                          class="input-centered-text"
                          bg-color="blue-50"
                          :max="finalMark"
                          readonly
                          @click="focusStudent(student.id)"
                          @keydown.enter="submitMark(student)"
                        >
                           <template v-slot:append>
                              <q-btn round dense flat icon="send" size="sm" color="primary" @click.stop="submitMark(student)" :disable="!studentMarks[student.id] && studentMarks[student.id] !== 0">
                                <q-tooltip>Submit</q-tooltip>
                              </q-btn>
                           </template>
                        </q-input>
                     </div>
                  </div>
               </div>
                 
               <div v-if="filteredPendingStudents.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400">
                  <q-icon name="done_all" size="4em" class="mb-2 opacity-50" />
                  <div>No pending students found</div>
               </div>
            </div>
         </div>

         <!-- Completed Students -->
         <div class="h-1/3 min-h-[150px] flex flex-col overflow-hidden bg-green-50 border-t border-green-100 p-4">
             <div class="flex items-center justify-between mb-2">
               <h3 class="text-lg font-bold text-green-800 flex items-center gap-2">
                 <q-icon name="check_circle" color="green" />
                 Completed
                 <q-badge color="green" rounded>{{ completedStudents.length }}</q-badge>
               </h3>
               <q-btn flat dense icon="delete_sweep" color="green" label="Reset List" size="sm" @click="resetCompletedList" />
            </div>

            <div class="flex-grow overflow-y-auto pr-2 custom-scrollbar">
               <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                  <div 
                    v-for="item in completedStudents" 
                    :key="item.student.id"
                    class="bg-white rounded-xl shadow-sm border border-green-100 p-2 flex items-center gap-3 opacity-90"
                  >
                     <q-avatar size="32px">
                        <img :src="getAvatarUrl(item.student)" />
                     </q-avatar>
                     <div class="flex-grow overflow-hidden">
                        <div class="text-xs font-bold truncate">{{ item.student.name }}</div>
                        <div class="text-xs font-mono text-green-700 font-bold bg-green-50 px-1 rounded inline-block">
                           {{ item.mark }} / {{ finalMark }}
                        </div>
                     </div>
                     <q-btn round flat dense icon="undo" size="xs" color="grey-5" @click="undoCompletion(item.student.id)">
                        <q-tooltip>Undo</q-tooltip>
                     </q-btn>
                  </div>
               </div>
            </div>
         </div>

      </div>
     
      <!-- Numpad Sheet (Overlay) -->
      <transition enter-active-class="animated slideInUp" leave-active-class="animated slideOutDown">
        <div v-if="focusedStudentId" class="fixed-bottom z-50 flex justify-center pointer-events-none">
           <!-- Overlay background to close on click outside (optional) -->
           
           <div class="w-full max-w-md pointer-events-auto bg-white rounded-t-xl shadow-2xl overflow-hidden">
             <!-- Header of Numpad (Student Info) -->
             <div class="bg-blue-600 text-white p-2 flex items-center justify-between shadow-2 relative z-10">
                <div class="flex items-center gap-2">
                   <q-avatar size="28px" class="border border-white/50">
                      <img :src="getAvatarUrl(focusedStudent)" />
                   </q-avatar>
                   <div class="text-subtitle2 font-bold">{{ focusedStudent?.name }}</div>
                </div>
                <q-btn flat round dense icon="close" size="sm" @click="closeNumpad" />
             </div>

             <!-- The Numpad Component -->
             <QuizNumpad 
                :model-value="studentMarks[focusedStudentId]"
                :max="finalMark"
                @update:model-value="updateMarkFromNumpad"
                @submit="submitMark(focusedStudent)"
             />
           </div>
        </div>
      </transition>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useQuasar } from 'quasar'
import QuizNumpad from './QuizNumpad.vue'

const props = defineProps({
  modelValue: Boolean,
  students: {
    type: Array,
    default: () => []
  },
  behaviors: {
    type: Array,
    default: () => []
  },
  newSession: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'update-points', 'refresh-data'])
const $q = useQuasar()

// === State ===
const quizTitle = ref('Unit 1 Quiz')
const finalMark = ref(10)
const selectedBehaviorId = ref(null) // New
const studentMarks = ref({})
const completedStudentIds = ref([])
const activeNameFilter = ref(null)
const focusedStudentId = ref(null)

const focusedStudent = computed(() => {
    return props.students.find(s => s.id === focusedStudentId.value)
})

const behaviorOptions = computed(() => {
   return props.behaviors.map(b => ({
      label: b.name,
      value: b.id,
      icon: b.icon
   }))
})

const getBehaviorName = (id) => {
   const b = props.behaviors.find(beh => beh.id === id)
   return b ? b.name : null
}

// === Persistence Logic ===
const STORAGE_KEY = 'quiz_mode_state'

const saveState = () => {
    const state = {
        quizTitle: quizTitle.value,
        finalMark: finalMark.value,
        selectedBehaviorId: selectedBehaviorId.value,
        studentMarks: studentMarks.value,
        completedStudentIds: completedStudentIds.value
    }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state))
}

const initSession = () => {
    // Check if new session triggered
    if (props.newSession) {
        console.log('Initializing New Session', props.newSession)
        quizTitle.value = props.newSession.title || 'Unknown Quiz'
        finalMark.value = props.newSession.maxMark || 10
        selectedBehaviorId.value = props.newSession.behaviorId || (props.behaviors.length > 0 ? props.behaviors[0].id : null)
        
        // Reset Progress
        studentMarks.value = {}
        completedStudentIds.value = []
        
        // Save
        saveState()
        return
    }

    const saved = localStorage.getItem(STORAGE_KEY)
    if (saved) {
        try {
            const parsed = JSON.parse(saved)
            quizTitle.value = parsed.quizTitle || 'Unit 1 Quiz'
            finalMark.value = parsed.finalMark || 10
            selectedBehaviorId.value = parsed.selectedBehaviorId || null
            studentMarks.value = parsed.studentMarks || {}
            completedStudentIds.value = parsed.completedStudentIds || []
            
            // Auto-select first behavior if none selected
            if (!selectedBehaviorId.value && props.behaviors.length > 0) {
                 selectedBehaviorId.value = props.behaviors[0].id
            }
        } catch (e) {
            console.error('Failed to restore quiz state', e)
        }
    } else {
        // Init default behavior
        if (props.behaviors.length > 0) {
             selectedBehaviorId.value = props.behaviors[0].id
        }
    }
}

watch(() => props.modelValue, (val) => {
    if (val) {
        initSession()
    }
})

// Also watch newSession in case it changes while open (unlikely but safe)
watch(() => props.newSession, (val) => {
    if (val && props.modelValue) {
        initSession()
    }
})

onMounted(() => {
    if (props.modelValue) {
        initSession()
    }
})

// === Numpad Logic (Delegated to Component) ===
const updateMarkFromNumpad = (val) => {
    if (!focusedStudentId.value) return
    studentMarks.value[focusedStudentId.value] = val
    saveState()
}

const closeNumpad = () => {
    focusedStudentId.value = null
}

// Persist Completed List Logic (Optional, but good for refresh safety)
// For now, simpler to rely on in-memory, but valid request "every student took his feedback should go down to a now list"
// We track completion locally.

// === Filter Logic ===
const filterChars = computed(() => {
  const chars = new Set()
  // Always include # if non-alpha
  
  props.students.forEach(s => {
      const char = s.name.trim().charAt(0).toUpperCase()
      if (/[A-Z]/.test(char)) {
          chars.add(char)
      } else {
          chars.add('#')
      }
  })
  
  return Array.from(chars).sort()
})

const filteredStudents = computed(() => {
   if (!activeNameFilter.value) return props.students
   
   return props.students.filter(s => {
      const firstChar = s.name.trim().charAt(0).toUpperCase()
      if (activeNameFilter.value === '#') {
         return !/[A-Z]/.test(firstChar)
      }
      return firstChar === activeNameFilter.value
   })
})

const filteredPendingStudents = computed(() => {
   return filteredStudents.value.filter(s => !completedStudentIds.value.includes(s.id))
})

const completedStudents = computed(() => {
   // Return objects with mark
   return completedStudentIds.value.map(id => {
      const student = props.students.find(s => s.id === id)
      if (!student) return null
      return {
         student,
         mark: studentMarks.value[id]
      }
   }).filter(Boolean).reverse() // Newest first
})

// === Timer Logic ===
const timerSeconds = ref(0)
const timerRunning = ref(false)
const timerInterval = ref(null)
const timerInputMinutes = ref(0)
const timerInputSeconds = ref(0)

import { TimerAudio } from './TimerAudio.js'

const toggleTimer = () => {
   if (timerRunning.value) {
      clearInterval(timerInterval.value)
      timerRunning.value = false
      TimerAudio.pauseTicking()
   } else {
      if (timerSeconds.value <= 0) return // Don't start if 0
      
      timerRunning.value = true
      
      // Decide initial audio based on current time
      if (timerSeconds.value > 10) {
          TimerAudio.playTicking()
      } else {
          TimerAudio.playAlarm()
      }
      
      timerInterval.value = setInterval(() => {
         if (timerSeconds.value > 0) {
            timerSeconds.value--
            
            // Switch to alarm at 10s
            if (timerSeconds.value === 10) {
                TimerAudio.pauseTicking()
                TimerAudio.playAlarm()
            }
         } else {
            clearInterval(timerInterval.value)
            timerRunning.value = false
            TimerAudio.pauseTicking() 
            // We allow alarm to finish playing naturally
         }
      }, 1000)
   }
}

const resetTimer = () => {
   clearInterval(timerInterval.value)
   timerRunning.value = false
   TimerAudio.stopAll()
   timerSeconds.value = (timerInputMinutes.value * 60) + timerInputSeconds.value
}

const setTimer = () => {
   // Stop previous sounds
   TimerAudio.stopAll()
   
   timerSeconds.value = (timerInputMinutes.value * 60) + timerInputSeconds.value
   resetTimer()
}

const setQuickTimer = (min) => {
   // Stop previous sounds
   TimerAudio.stopAll()
   
   timerInputMinutes.value = min
   timerInputSeconds.value = 0
   timerSeconds.value = min * 60
   resetTimer()
}

const formatTime = (totalSeconds) => {
    const m = Math.floor(totalSeconds / 60)
    const s = totalSeconds % 60
    return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`
}

// === Action Logic ===

const pickRandomStudent = () => {
   const pending = filteredPendingStudents.value
   if (pending.length === 0) return
   
   const randomIndex = Math.floor(Math.random() * pending.length)
   const randomStudent = pending[randomIndex]
   
   focusStudent(randomStudent.id)
   
   // Optional: Play a sound?
   // Util.playSound('select') 
}

const focusStudent = (id) => {
   focusedStudentId.value = id
}

const submitMark = (student) => {
   const mark = studentMarks.value[student.id]
   if (mark === undefined || mark === null || mark === '') return
   
   // Validation
   if (mark < 0 || mark > finalMark.value) {
       $q.notify({ message: `Invalid mark. Must be between 0 and ${finalMark.value}`, color: 'negative' })
       return
   }
   
   // 1. Give Points
   // Logic: "marks as points".
   // Assuming Mark 10 = 10 Points.
   emit('update-points', {
      studentId: student.id,
      points: Number(mark),
      behaviorName: `${quizTitle.value}: ${mark}/${finalMark.value}`,
      type: 'quiz',
      behaviorId: selectedBehaviorId.value // Pass selected ID
   })
   
   // 2. TTS
   speakPoints(student.name, Number(mark))
   
   // 3. Move to Completed
   completedStudentIds.value.push(student.id)
   saveState()
   
   // 4. Clear focus or move to next?
   focusedStudentId.value = null
   
   // Helper: Auto focus next?
   // Maybe user clicks next.
}

const undoCompletion = (id) => {
   const idx = completedStudentIds.value.indexOf(id)
   if (idx > -1) {
       completedStudentIds.value.splice(idx, 1)
       saveState()
   }
   
   // We don't undo points for now unless requested, as that's complex (need to know exact trans ID).
   // User just said "go down to a now list".
}

const resetCompletedList = () => {
   completedStudentIds.value = []
   studentMarks.value = {}
   saveState()
}

const speakPoints = (name, points) => {
   if (!('speechSynthesis' in window)) return
   
   const synth = window.speechSynthesis
   
   // Extract first two names
   const nameParts = name.trim().split(/\s+/)
   const shortName = nameParts.slice(0, 2).join(' ')
   
   // Added pause (comma/ellipsis) as requested
   const text = `Number of points... ${points}... for ${shortName}`
   
   const utterance = new SpeechSynthesisUtterance(text)
   // Try to find English voice if name is English, or Auto.
   // Just default is fine usually.
   synth.speak(utterance)
}

const getAvatarUrl = (student) => {
  if (!student) return '';
  if (student.avatar) return student.avatar.startsWith('/') ? student.avatar : `/${student.avatar}`;
  return '/images/avatars/default-avatar.svg';
}

onUnmounted(() => {
   clearInterval(timerInterval.value)
   TimerAudio.stopAll()
})

</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #f1f1f1; 
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #c1c1c1; 
  border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8; 
}
.input-centered-text :deep(input) {
   text-align: center;
   font-weight: bold;
   font-size: 1.1em;
}
</style>
