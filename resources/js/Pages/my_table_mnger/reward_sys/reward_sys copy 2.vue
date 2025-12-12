<template>
  <div class="p-6 space-y-6 bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen">



















    <!-- Header Card -->
    <q-card class="shadow-lg rounded-2xl overflow-hidden">
      <q-card-section class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white">
        <h1 class="text-3xl font-bold">🏆 Reward System</h1>
        <p class="text-blue-100">Manage student behaviors and track achievements</p>
      </q-card-section>

      <!-- Control Panel -->
      <q-card-section class="p-6 space-y-4">
        <!-- Session Summary & Setup Button -->
        <div class="flex items-center justify-between bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
          <div class="flex items-center gap-4">
            <div class="bg-blue-100 p-3 rounded-full text-blue-600">
              <q-icon name="event_note" size="md" />
            </div>
            <div>
              <div class="text-sm text-gray-500 font-medium">Current Session</div>
              <div class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span v-if="selectedClassroomId">
                  {{ classrooms.find(c => c.classroom_id === selectedClassroomId)?.classroom_name || 'Unknown Class' }}
                </span>
                <span v-else class="text-gray-400 italic">No classroom selected</span>
                
                <span class="text-gray-300">|</span>
                
                <span class="text-blue-600 font-mono bg-blue-50 px-2 py-0.5 rounded text-base">
                  {{ periodCode }}
                </span>
              </div>
              <div class="text-xs text-gray-500 mt-1">
                {{ new Date(selectedDate).toLocaleDateString() }} • Period {{ selectedPeriodNumber }}
              </div>
            </div>
          </div>
          
          <q-btn
            color="primary"
            icon="settings"
            label="Setup Session"
            @click="showSetupDialog = true"
            size="lg"
            class="shadow-md"
          />
        </div>
      </q-card-section>
    </q-card>

    <!-- Session Setup Dialog -->
    <q-dialog v-model="showSetupDialog" full-width full-height>
      <q-card class="flex flex-col bg-gray-50">
        <q-toolbar class="bg-white border-b border-gray-200 p-4">
          <q-toolbar-title class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <q-icon name="settings_suggest" color="primary" />
            Session Setup
          </q-toolbar-title>
          <q-btn flat round dense icon="close" v-close-popup />
        </q-toolbar>

        <q-card-section class="flex-1 overflow-auto p-6">
          <div class="max-w-7xl mx-auto space-y-8">
            <!-- Period Selection -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                <q-icon name="schedule" class="text-blue-500" />
                Time & Period
              </h3>
              <PeriodSelectionRefactored
                :date="selectedDate"
                :semester="selectedSemester"
                :week="selectedWeek"
                :day="selectedDay"
                :period-number="selectedPeriodNumber"
                @update:date="selectedDate = $event"
                @update:semester="selectedSemester = $event"
                @update:week="selectedWeek = $event"
                @update:day="selectedDay = $event"
                @update:periodNumber="selectedPeriodNumber = $event"
                @change="handlePeriodChange"
                :persist="true"
                persistKey="reward-system-period-selection"
              />
              <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100 flex justify-center">
                <p class="text-sm text-gray-700">
                  Active Period Code: <span class="font-bold text-blue-600 font-mono text-lg">{{ periodCode }}</span>
                </p>
              </div>
            </div>

            <!-- Classroom Selection -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
              <h3 class="text-lg font-bold text-gray-700 mb-4 flex items-center gap-2">
                <q-icon name="school" class="text-blue-500" />
                Classroom
              </h3>
              <ClassroomSelection
                v-model="selectedClassroomId"
                :classrooms="classrooms"
                :loading="loadingData"
                :init-status="initStatus"
                v-model:avatar-edit-enabled="avatarEditEnabled"
                @change="handleClassroomChange"
                @init="initClassroomSession"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>



































<!-- Selection Controls -->
<div v-if="students.length" class="mb-6">
  <q-card class="shadow-lg rounded-2xl">
    <q-card-section class="p-4">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-bold">Quick Actions</h3>
        <div class="text-sm text-gray-600">
          Selected: <strong class="text-blue-600">{{ selectedIds.length }}</strong> students
        </div>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <!-- Positive Behavior -->
        <div class="flex gap-2">
          <q-select
            v-model="selectedPositiveBehaviorId"
            :options="positiveBehaviors"
            option-value="id"
            option-label="name"
            outlined
            dense
            placeholder="+ Behavior"
            emit-value
            map-options
            class="flex-1"
          />
          <q-btn
            color="positive"
            icon="add_circle"
            @click="applyPositiveBehavior"
            :disable="!selectedIds.length || !selectedPositiveBehaviorId"
            :loading="applyingBehavior"
          />
        </div>

        <!-- Negative Behavior -->
        <div class="flex gap-2">
          <q-select
            v-model="selectedNegativeBehaviorId"
            :options="negativeBehaviors"
            option-value="id"
            option-label="name"
            outlined
            dense
            placeholder="- Behavior"
            emit-value
            map-options
            class="flex-1"
          />
          <q-btn
            color="negative"
            icon="remove_circle"
            @click="applyNegativeBehavior"
            :disable="!selectedIds.length || !selectedNegativeBehaviorId"
            :loading="applyingBehavior"
          />
        </div>

        <!-- Clear Selection -->
        <q-btn
          color="grey"
          icon="clear"
          label="Clear Selection"
          outline
          @click="clearSelection"
          :disable="!selectedIds.length"
        />
      </div>
    </q-card-section>
  </q-card>
</div>










----------------------------------------------
<!-- Student Cards -->
<div class="dojo-container">
    <StudentCard
      v-for="student in students"
      :key="student.id"
      :student="student"
      :selected="selectedIds.includes(student.id)"
      :selected-id="selectedIds.includes(student.id) ? student.id : null"
      :disable-behavior="!studentAttendance[student.id]"
      :avatar-edit-enabled="avatarEditEnabled"
      :student-summary="{
        positive: studentBehaviors[student.id]?.points_plus || 0,
        negative: studentBehaviors[student.id]?.points_minus || 0,
        total: (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0)
      }"
      @select="toggleSelected(student.id)"
    />
  </div>



<!-- 

card2
   <card2
        v-for="student in students"
        :key="student.id"
        :student="student"
        @update-points="handleUpdatePoints"
      /> -->






    <!-- Main Tabs -->
    <q-card class="shadow-lg rounded-2xl" v-if="students.length">
      <q-tabs
        v-model="activeTab"
        dense
        class="text-grey-7"
        active-color="primary"
        indicator-color="primary"
        align="left"
      >
        <q-tab name="attendance" icon="how_to_reg" label="Attendance" />
        <q-tab name="positive" icon="add_circle" label="+ Points" />
        <q-tab name="negative" icon="remove_circle" label="- Points" />
        <q-tab name="history" icon="cancel" label="Cancel" />
        <q-tab name="champions" icon="emoji_events" label="Champions" @click="showLeaderboard = true" />
      </q-tabs>

      <q-separator />

      <q-tab-panels v-model="activeTab" animated>
        <!-- ATTENDANCE TAB -->
        <q-tab-panel name="attendance">
          <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">Manage Attendance</h3>
              <div class="flex gap-2">
                <q-btn
                  color="positive"
                  label="Mark All Present"
                  @click="markAllPresent"
                  :loading="bulkMarking"
                />
                <q-btn
                  color="warning"
                  label="Mark All Absent"
                  @click="markAllAbsent"
                  :loading="bulkMarking"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="student in students"
                :key="student.id"
                class="p-4 border-2 rounded-lg transition cursor-pointer"
                :class="getAttendanceClass(student.id)"
                @click="toggleAttendance(student.id)"
              >
                <div class="flex items-center justify-between mb-3">
                  <div class="flex-1">
                    <p class="font-semibold text-lg">{{ student.name }}</p>
                    <p class="text-xs text-gray-600">ID: {{ student.id }}</p>
                  </div>
                  <q-toggle
                    :model-value="studentAttendance[student.id]"
                    @update:model-value="(val) => toggleAttendance(student.id, val)"
                    :color="studentAttendance[student.id] ? 'positive' : 'negative'"
                    :disable="studentAttendanceSaving[student.id]"
                    size="lg"
                    @click.stop
                  />
                </div>
                <p class="text-center mb-3 font-semibold">
                  {{ studentAttendance[student.id] ? '✅ Present' : '❌ Absent' }}
                </p>
                
                <!-- Points Display -->
                <div class="space-y-2">
                  <div class="p-2 bg-green-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-green-800">Positive</span>
                    <span class="text-sm font-bold text-green-900">+{{ studentBehaviors[student.id]?.points_plus || 0 }} ⭐</span>
                  </div>
                  <div class="p-2 bg-red-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-red-800">Negative</span>
                    <span class="text-sm font-bold text-red-900">-{{ studentBehaviors[student.id]?.points_minus || 0 }} ⚠️</span>
                  </div>
                  <div class="p-2 bg-blue-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-blue-800">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- POSITIVE POINTS TAB -->
        <q-tab-panel name="positive">
          <div class="space-y-4">
            <div class="mb-4">
              <h3 class="text-xl font-bold mb-2">Add Positive Points</h3>
              <p class="text-sm text-gray-600">Select students and choose a positive behavior</p>
            </div>

            <!-- Behavior Selection -->
            <div class="p-4 bg-green-50 rounded-lg border border-green-200">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <q-select
                  v-model="selectedPositiveBehaviorId"
                  :options="positiveBehaviors"
                  option-value="id"
                  option-label="name"
                  outlined
                  dense
                  placeholder="Select positive behavior"
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.name }}</q-item-label>
                        <q-item-label caption>+{{ scope.opt.value || scope.opt.points || 0 }} points</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                <q-btn
                  color="positive"
                  icon="add_circle"
                  label="Apply to Selected"
                  @click="applyPositiveBehavior"
                  :disable="!selectedIds.length || !selectedPositiveBehaviorId"
                  :loading="applyingBehavior"
                />
              </div>
              <div class="mt-2 text-sm">Selected: <strong>{{ selectedIds.length }}</strong> students</div>
            </div>

            <!-- Student Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="student in students"
                :key="student.id"
                class="p-4 border-2 rounded-lg transition"
                :class="[
                  !studentAttendance[student.id] 
                    ? 'bg-gray-100 border-gray-300 opacity-50 cursor-not-allowed'
                    : selectedIds.includes(student.id)
                    ? 'bg-green-100 border-green-500 cursor-pointer'
                    : 'bg-white border-gray-200 cursor-pointer'
                ]"
                @click="studentAttendance[student.id] && toggleSelected(student.id)"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <p class="font-semibold text-lg">{{ student.name }}</p>
                    <p class="text-xs text-gray-600">ID: {{ student.id }}</p>
                    <p v-if="!studentAttendance[student.id]" class="text-xs text-red-600 mt-1">❌ Absent</p>
                  </div>
                  <q-checkbox
                    :model-value="selectedIds.includes(student.id)"
                    @update:model-value="toggleSelected(student.id)"
                    :disable="!studentAttendance[student.id]"
                    color="positive"
                    size="lg"
                  />
                </div>
                
                <!-- Points Display -->
                <div class="space-y-2">
                  <div class="p-2 bg-green-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-green-800">Positive</span>
                    <span class="text-sm font-bold text-green-900">+{{ studentBehaviors[student.id]?.points_plus || 0 }} ⭐</span>
                  </div>
                  <div class="p-2 bg-red-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-red-800">Negative</span>
                    <span class="text-sm font-bold text-red-900">-{{ studentBehaviors[student.id]?.points_minus || 0 }} ⚠️</span>
                  </div>
                  <div class="p-2 bg-blue-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-blue-800">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- NEGATIVE POINTS TAB -->
        <q-tab-panel name="negative">
          <div class="space-y-4">
            <div class="mb-4">
              <h3 class="text-xl font-bold mb-2">Deduct Points</h3>
              <p class="text-sm text-gray-600">Select students and choose a negative behavior</p>
            </div>

            <!-- Behavior Selection -->
            <div class="p-4 bg-red-50 rounded-lg border border-red-200">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <q-select
                  v-model="selectedNegativeBehaviorId"
                  :options="negativeBehaviors"
                  option-value="id"
                  option-label="name"
                  outlined
                  dense
                  placeholder="Select negative behavior"
                  emit-value
                  map-options
                >
                  <template v-slot:option="scope">
                    <q-item v-bind="scope.itemProps">
                      <q-item-section>
                        <q-item-label>{{ scope.opt.name }}</q-item-label>
                        <q-item-label caption>{{ scope.opt.value || scope.opt.points || 0 }} points</q-item-label>
                      </q-item-section>
                    </q-item>
                  </template>
                </q-select>
                <q-btn
                  color="negative"
                  icon="remove_circle"
                  label="Apply to Selected"
                  @click="applyNegativeBehavior"
                  :disable="!selectedIds.length || !selectedNegativeBehaviorId"
                  :loading="applyingBehavior"
                />
              </div>
              <div class="mt-2 text-sm">Selected: <strong>{{ selectedIds.length }}</strong> students</div>
            </div>

            <!-- Student Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              <div
                v-for="student in students"
                :key="student.id"
                class="p-4 border-2 rounded-lg transition"
                :class="[
                  !studentAttendance[student.id] 
                    ? 'bg-gray-100 border-gray-300 opacity-50 cursor-not-allowed'
                    : selectedIds.includes(student.id)
                    ? 'bg-red-100 border-red-500 cursor-pointer'
                    : 'bg-white border-gray-200 cursor-pointer'
                ]"
                @click="studentAttendance[student.id] && toggleSelected(student.id)"
              >
                <div class="flex items-start justify-between mb-3">
                  <div class="flex-1">
                    <p class="font-semibold text-lg">{{ student.name }}</p>
                    <p class="text-xs text-gray-600">ID: {{ student.id }}</p>
                    <p v-if="!studentAttendance[student.id]" class="text-xs text-red-600 mt-1">❌ Absent</p>
                  </div>
                  <q-checkbox
                    :model-value="selectedIds.includes(student.id)"
                    @update:model-value="toggleSelected(student.id)"
                    :disable="!studentAttendance[student.id]"
                    color="negative"
                    size="lg"
                  />
                </div>
                
                <!-- Points Display -->
                <div class="space-y-2">
                  <div class="p-2 bg-green-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-green-800">Positive</span>
                    <span class="text-sm font-bold text-green-900">+{{ studentBehaviors[student.id]?.points_plus || 0 }} ⭐</span>
                  </div>
                  <div class="p-2 bg-red-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-red-800">Negative</span>
                    <span class="text-sm font-bold text-red-900">-{{ studentBehaviors[student.id]?.points_minus || 0 }} ⚠️</span>
                  </div>
                  <div class="p-2 bg-blue-100 rounded flex justify-between items-center">
                    <span class="text-xs font-semibold text-blue-800">Total</span>
                    <span class="text-lg font-bold text-blue-600">{{ (studentBehaviors[student.id]?.points_plus || 0) - (studentBehaviors[student.id]?.points_minus || 0) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </q-tab-panel>

        <!-- HISTORY TAB -->
        <q-tab-panel name="history">
          <div class="space-y-4">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-xl font-bold">Recent Actions</h3>
              <q-btn
                color="primary"
                icon="refresh"
                label="Refresh"
                @click="loadHistory"
                :loading="loadingHistory"
              />
            </div>

            <div v-if="!recentActions.length" class="text-center py-8">
              <p class="text-gray-500 text-lg">No recent actions</p>
            </div>

            <div v-for="action in recentActions" :key="action.id" class="p-4 bg-white rounded-lg border-l-4"
              :class="[
                action.canceled ? 'border-gray-400 opacity-60' : 
                action.value > 0 ? 'border-green-500' : 'border-red-500'
              ]"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-lg">{{ action.student_behavior?.student?.name || 'Unknown' }}</span>
                    <span class="text-2xl">{{ action.value > 0 ? '⭐' : '⚠️' }}</span>
                  </div>
                  <p class="text-sm text-gray-700">
                    <strong>{{ action.behavior?.name || 'Unknown Behavior' }}</strong>
                    <span :class="action.value > 0 ? 'text-green-600' : 'text-red-600'">
                      ({{ action.value > 0 ? '+' : '' }}{{ action.value }} points)
                    </span>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatDateTime(action.created_at) }} by {{ action.created_by?.name || 'Unknown' }}
                  </p>
                  <p v-if="action.note" class="text-xs text-gray-600 mt-1 italic">Note: {{ action.note }}</p>
                  <p v-if="action.canceled" class="text-xs text-red-600 mt-1">
                    ❌ Canceled: {{ action.cancel_reason }} ({{ formatDateTime(action.canceled_at) }})
                  </p>
                </div>
                <q-btn
                  v-if="!action.canceled"
                  color="warning"
                  icon="undo"
                  label="Undo"
                  size="sm"
                  @click="undoAction(action.id)"
                  :loading="undoingAction === action.id"
                />
              </div>
            </div>
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </q-card>

    <!-- Empty State -->
    <q-card v-if="!students.length" class="shadow-lg rounded-2xl">
      <q-card-section class="text-center py-12">
        <p class="text-2xl font-semibold text-gray-600">📚 Select a classroom and click "Init Session" to get started</p>
      </q-card-section>
    </q-card>

    <!-- Leaderboard Dialog -->
    <q-dialog v-model="showLeaderboard" maximized>
      <q-card>
        <q-card-section class="p-0">
          <TopLeaderboard :students="students" :student-behaviors="studentBehaviors" />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Close" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import rewardPointService from './reward_sys_comp/reward_sys_point_action.js'
import PeriodSelectionRefactored from './reward_sys_comp/PeriodSelectionRefactored.vue'
import ClassroomSelection from './reward_sys_comp/ClassroomSelection.vue'
import TopLeaderboard from './reward_sys_comp/TopLeaderboard.vue'
import card2 from './final/card2.vue'; // Adjust path as needed
import card3 from './final/card3.vue'; // Adjust path as needed
import StudentCard from './reward_sys_comp/StudentCard.vue'
import noise from './final/noise.vue'; // Adjust path as needed

import pdf_main from './final/pdf_main.vue'
import PDFAnnotatorMain from './final/PDFAnnotatorMain.vue'
import video_player from './final/video_player.vue'
import video_player2 from './final/video_player2.vue'
import draw from './final/draw.vue'
import draw2 from './final/draw2.vue'
import draw3 from './final/draw3.vue'

const pdfUrl = ref('https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf')
// or local file: '/pdfs/sample.pdf'
// or base64: 'data:application/pdf;base64,JVBERi0x...'

const handleLoaded = () => {
  console.log('PDF loaded successfully!')
}

const handleError = (err ) => {
  console.error('Failed to load PDF:', err)
}

const $q = useQuasar()

// ============ REACTIVE STATE ============
const activeTab = ref('attendance')

// Watch for tab changes and clear selection
watch(activeTab, (newTab, oldTab) => {
  if (newTab !== oldTab) {
    selectedIds.value = []
  }
})
const classrooms = ref([])
const students = ref([])
const behaviors = ref([])
const selectedClassroomId = ref(null)
const selectedDate = ref(new Date().toISOString().split('T')[0])
const selectedSemester = ref(1)
const selectedWeek = ref(1)
const selectedDay = ref(1)
const selectedPeriodNumber = ref(1)
const selectedPositiveBehaviorId = ref(null)
const selectedNegativeBehaviorId = ref(null)
const selectedIds = ref([])
const studentBehaviors = ref({})
const studentAttendance = ref({})
const studentAttendanceSaving = ref({})
const recentActions = ref([])
const leaderboard = ref([])
const showLeaderboard = ref(false)
const loadingData = ref(false)
const applyingBehavior = ref(false)
const bulkMarking = ref(false)
const loadingHistory = ref(false)
const undoingAction = ref(null)
const studentBehaviorsMainId = ref(null)
const initStatus = ref({ message: '', created: 0, skipped: 0 })
const showSetupDialog = ref(false)
const avatarEditEnabled = ref(false)

// Computed period code generator
const periodCode = computed(() => {
  return `${selectedSemester.value}.${selectedWeek.value}.${selectedDay.value}.${selectedPeriodNumber.value}`
})

// Computed behavior lists
const positiveBehaviors = computed(() => {
  console.log('🔍 All behaviors:', behaviors.value)
  const positive = behaviors.value.filter(b => {
    // Check type field first, then value
    if (b.type) {
      return b.type === 'positive' || b.type === 'reward'
    }
    const value = b.value || b.points || 0
    return value > 0
  })
  console.log('✅ Positive behaviors:', positive)
  return positive
})

const negativeBehaviors = computed(() => {
  const negative = behaviors.value.filter(b => {
    // Check type field first, then value
    if (b.type) {
      return b.type === 'negative' || b.type === 'penalty'
    }
    const value = b.value || b.points || 0
    return value < 0
  })
  console.log('⚠️ Negative behaviors:', negative)
  return negative
})

// Top 5 students by total points
// ============ METHODS ============

function handlePeriodChange(data) {
  selectedDate.value = data.date
  selectedSemester.value = data.semester
  selectedWeek.value = data.week
  selectedDay.value = data.day
  selectedPeriodNumber.value = data.periodNumber
  console.log('📅 Period changed:', { periodCode: periodCode.value, ...data })
}

function toggleSelected(studentId) {
  // Don't allow selecting absent students
  if (!studentAttendance.value[studentId]) {
    $q.notify({
      message: 'Cannot select absent students',
      color: 'warning',
      position: 'top',
      timeout: 1000
    })
    return
  }
  
  const idx = selectedIds.value.indexOf(studentId)
  if (idx === -1) {
    selectedIds.value.push(studentId)
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

function clearSelection() {
  selectedIds.value = []
}

async function handleClassroomChange(classroomId) {
  if (!classroomId) {
    students.value = []
    selectedIds.value = []
    return
  }

  try {
    loadingData.value = true
    students.value = []
    selectedIds.value = []
    studentBehaviorsMainId.value = null
    initStatus.value = { message: '', created: 0, skipped: 0 }
  } catch (error) {
    console.error('Failed to load classroom:', error)
    $q.notify({
      message: 'Failed to load classroom: ' + error.message,
      color: 'negative',
      position: 'top'
    })
  } finally {
    loadingData.value = false
  }
}

async function initClassroomSession() {
  if (!selectedClassroomId.value) return
  loadingData.value = true
  initStatus.value = { message: 'Initializing...', created: 0, skipped: 0 }

  try {
    const payload = {
      classroom_id: selectedClassroomId.value,
      date: selectedDate.value,
      period_code: periodCode.value,
    }

    const res = await axios.post('/api/student-behaviors/init-classroom', payload)
    if (res && res.data) {
      const d = res.data
      studentBehaviorsMainId.value = d.student_behaviors_mains_id
      initStatus.value = { 
        message: `Session initialized (created ${d.created}, skipped ${d.skipped})`, 
        created: d.created, 
        skipped: d.skipped 
      }
      
      const items = d.student_behaviors || []
      const mapped = items.map(b => ({
        id: b.student.id,
        name: b.student.name || `Student ${b.student_id}`,
        avatar: b.student.avatar,
        behaviorRecordId: b.id,
      }))

      students.value = mapped
      selectedIds.value = []
      
      const newStudentBehaviors = {}
      for (const b of items) {
        studentAttendance.value[b.student_id] = b.attend === undefined ? true : b.attend
        newStudentBehaviors[b.student_id] = {
          attend: b.attend === undefined ? true : b.attend,
          points_plus: b.points_plus || 0,
          points_minus: b.points_minus || 0,
        }
      }
      studentBehaviors.value = newStudentBehaviors

      $q.notify({ message: 'Session initialized', color: 'positive', position: 'top' })
      
      // Load history after init
      await loadHistory()
      showSetupDialog.value = false
    }
  } catch (err) {
    console.error('Failed to init classroom session:', err)
    initStatus.value = { message: 'Initialization failed', created: 0, skipped: 0 }
    $q.notify({ 
      message: 'Failed to init session: ' + (err.message || 'error'), 
      color: 'negative', 
      position: 'top' 
    })
  } finally {
    loadingData.value = false
  }
}

async function applyPositiveBehavior() {
  await applyBehaviorToStudents(selectedPositiveBehaviorId.value)
  selectedPositiveBehaviorId.value = null
}

async function applyNegativeBehavior() {
  await applyBehaviorToStudents(selectedNegativeBehaviorId.value)
  selectedNegativeBehaviorId.value = null
}

async function applyBehaviorToStudents(behaviorId) {
  if (!selectedIds.value.length || !behaviorId) return

  try {
    applyingBehavior.value = true

    const result = await rewardPointService.applyBehaviorToStudents(
      selectedIds.value,
      behaviorId,
      {
        date: selectedDate.value,
        periodCode: periodCode.value,
        classroomId: selectedClassroomId.value,
      }
    )

    if (result.success) {
      $q.notify({
        message: `Applied behavior to ${selectedIds.value.length} students`,
        color: 'positive',
        position: 'top'
      })
      await initClassroomSession()
      selectedIds.value = []
    } else {
      $q.notify({
        message: result.error || 'Failed to apply behavior',
        color: 'negative',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error applying behavior:', error)
    $q.notify({
      message: error.message || 'Error applying behavior',
      color: 'negative',
      position: 'top'
    })
  } finally {
    applyingBehavior.value = false
  }
}

async function toggleAttendance(studentId, newValue) {
  const prev = studentAttendance.value[studentId] === undefined ? true : studentAttendance.value[studentId]
  const next = typeof newValue === 'boolean' ? newValue : !prev

  // If marking as absent, check if student has points for this session
  if (next === false) {
    const studentBehavior = studentBehaviors.value[studentId]
    const hasPoints = studentBehavior && (studentBehavior.points_plus > 0 || studentBehavior.points_minus > 0)
    
    if (hasPoints) {
      // Show warning dialog
      $q.dialog({
        title: 'Warning',
        message: `This student has ${studentBehavior.points_plus} positive and ${studentBehavior.points_minus} negative points for this session. Marking them absent will remove all their points for this session. Continue?`,
        cancel: true,
        persistent: true,
        ok: {
          label: 'Yes, Mark Absent',
          color: 'negative'
        },
        cancel: {
          label: 'Cancel',
          color: 'grey'
        }
      }).onOk(async () => {
        await performAttendanceUpdate(studentId, next, prev)
      })
      return
    }
  }

  await performAttendanceUpdate(studentId, next, prev)
}

async function performAttendanceUpdate(studentId, next, prev) {
  studentAttendance.value[studentId] = next
  studentAttendanceSaving.value[studentId] = true

  try {
    const res = await rewardPointService.updateAttendance(studentId, next, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })

    if (res.success) {
      $q.notify({ message: res.message || 'Attendance updated', color: 'positive', position: 'top' })
      
      // If marked absent and had points, refresh to show updated points
      if (next === false) {
        await initClassroomSession()
      }
    } else {
      throw new Error(res.error || 'Failed to update attendance')
    }
  } catch (err) {
    console.error('Failed to persist attendance for', studentId, err)
    studentAttendance.value[studentId] = prev
    $q.notify({ message: 'Failed to update attendance. Reverted.', color: 'negative', position: 'top' })
  } finally {
    studentAttendanceSaving.value[studentId] = false
  }
}

async function markAllPresent() {
  bulkMarking.value = true
  const attendancePayload = {}
  
  for (const student of students.value) {
    attendancePayload[student.id] = true
    studentAttendance.value[student.id] = true
  }

  try {
    const res = await rewardPointService.batchUpdateAttendance(attendancePayload, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })
    
    if (res.success) {
      $q.notify({ message: 'All students marked present', color: 'positive', position: 'top' })
    }
  } catch (err) {
    console.error('Failed to mark all present:', err)
    $q.notify({ message: 'Failed to mark all present', color: 'negative', position: 'top' })
  } finally {
    bulkMarking.value = false
  }
}

async function markAllAbsent() {
  bulkMarking.value = true
  const attendancePayload = {}
  
  for (const student of students.value) {
    attendancePayload[student.id] = false
    studentAttendance.value[student.id] = false
  }

  try {
    const res = await rewardPointService.batchUpdateAttendance(attendancePayload, {
      date: selectedDate.value,
      periodCode: periodCode.value,
      classroomId: selectedClassroomId.value,
    })
    
    if (res.success) {
      $q.notify({ message: 'All students marked absent', color: 'warning', position: 'top' })
    }
  } catch (err) {
    console.error('Failed to mark all absent:', err)
    $q.notify({ message: 'Failed to mark all absent', color: 'negative', position: 'top' })
  } finally {
    bulkMarking.value = false
  }
}

function getAttendanceClass(studentId) {
  const isPresent = studentAttendance.value[studentId]
  return isPresent 
    ? 'bg-green-50 border-green-300' 
    : 'bg-red-50 border-red-300 opacity-60'
}

async function loadHistory() {
  loadingHistory.value = true
  try {
    const result = await rewardPointService.getRecentActions({
      classroomId: selectedClassroomId.value,
      date: selectedDate.value,
      limit: 10
    })

    if (result.success) {
      recentActions.value = result.data
    } else {
      console.error('Failed to load history:', result.error)
    }
  } catch (error) {
    console.error('Error loading history:', error)
  } finally {
    loadingHistory.value = false
  }
}

async function undoAction(actionId) {
  undoingAction.value = actionId
  try {
    const result = await rewardPointService.undoAction(actionId, 'Undone by teacher')

    if (result.success) {
      $q.notify({
        message: 'Action undone successfully',
        color: 'positive',
        position: 'top'
      })
      await loadHistory()
      await initClassroomSession()
    } else {
      $q.notify({
        message: result.error || 'Failed to undo action',
        color: 'negative',
        position: 'top'
      })
    }
  } catch (error) {
    console.error('Error undoing action:', error)
    $q.notify({
      message: 'Error undoing action',
      color: 'negative',
      position: 'top'
    })
  } finally {
    undoingAction.value = null
  }
}

function formatDateTime(dateTime) {
  if (!dateTime) return ''
  const date = new Date(dateTime)
  return date.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function getMedalEmoji(index) {
  const medals = ['🥇', '🥈', '🥉']
  return medals[index] || `${index + 1}.`
}

function handleStudentClick(student) {
  console.log('Student clicked:', student)
  // You can add behavior manager or other actions here
  // For now, just log the click
}

 

// ============ LIFECYCLE ============
onMounted(async () => {
  try {
    console.log('🚀 Initializing reward system...')

    // Load classrooms
    const classRes = await axios.get('/my_classes_with_students')
    classrooms.value = classRes.data
    console.log(`✅ Loaded ${classrooms.value.length} classrooms`)

    // Load behaviors
    const behaviorRes = await rewardPointService.fetchBehaviors()
    if (behaviorRes.success) {
      behaviors.value = behaviorRes.data
      console.log(`✅ Loaded ${behaviors.value.length} behaviors`)
      console.log('📋 Behaviors data:', behaviors.value)
      
      // Normalize behaviors to ensure they have a 'value' field
      behaviors.value = behaviors.value.map(b => ({
        ...b,
        value: b.value || b.points || 0
      }))
      
      console.log('📋 Normalized behaviors:', behaviors.value)
    } else {
      console.error('❌ Failed to load behaviors:', behaviorRes.error)
      $q.notify({
        message: 'Failed to load behaviors: ' + behaviorRes.error,
        color: 'negative',
        position: 'top'
      })
    }

    console.log('✅ Reward system initialized')
  } catch (error) {
    console.error('❌ Failed to initialize reward system:', error)
    $q.notify({
      message: 'Failed to initialize reward system: ' + error.message,
      color: 'negative',
      position: 'top'
    })
  }
})
</script>

<style scoped>
.space-y-6 > * + * {
  margin-top: 1.5rem;
}

.space-y-4 > * + * {
  margin-top: 1rem;
}

.gap-3 {
  gap: 0.75rem;
}

.gap-4 {
  gap: 1rem;
}
</style>
<style scoped>
.dojo-container {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  padding: 24px;
  background: #f5f7fa;
  justify-content: center;
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
}
</style>
