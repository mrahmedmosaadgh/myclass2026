<template>
  <AppLayout title="Weekly Plan Editor">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Weekly Plan Editor
          </h2>
          <p class="text-sm text-gray-600 mt-1" v-if="weeklyPlan">
            {{ weeklyPlan.classroom_subject_teacher?.classroom?.name }} - 
            {{ weeklyPlan.classroom_subject_teacher?.subject?.name }} - 
            Week {{ weeklyPlan.week_number }}
          </p>
        </div>
        <div class="flex space-x-2">
          <q-btn 
            outline 
            color="grey" 
            icon="arrow_back" 
            label="Back to Overview" 
            @click="goBack"
          />
          <q-btn 
            color="primary" 
            icon="save" 
            label="Save All Changes" 
            @click="saveAllChanges"
            :loading="saving"
          />
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Week Navigation -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
          <WeekNavigator 
            :current-week="currentWeek"
            :total-weeks="18"
            :week-status="weekStatus"
            @week-changed="changeWeek"
          />
        </div>

        <!-- Session Management -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">
              Week {{ currentWeek }} Sessions
            </h3>
            <q-btn 
              color="primary" 
              icon="add" 
              label="Add Session" 
              @click="addNewSession"
              size="sm"
            />
          </div>

          <!-- Sessions Grid -->
          <div class="space-y-4" v-if="sessions.length > 0">
            <draggable
              v-model="sessions"
              group="sessions"
              item-key="id"
              :animation="200"
              @change="handleSessionReorder"
              handle=".drag-handle"
            >
              <template #item="{ element, index }">
                <SessionCard
                  :session="element"
                  :index="index + 1"
                  @edit="editSession"
                  @delete="deleteSession"
                  @duplicate="duplicateSession"
                />
              </template>
            </draggable>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <div class="text-gray-400 mb-4">
              <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Sessions Planned</h3>
            <p class="text-gray-600 mb-4">Add your first session to get started with this week's plan.</p>
            <q-btn 
              color="primary" 
              icon="add" 
              label="Add First Session" 
              @click="addNewSession"
            />
          </div>
        </div>

        <!-- Full Screen Session Editor Dialog -->
        <q-dialog
          v-model="showSessionDrawer"
          maximized
          transition-show="slide-left"
          transition-hide="slide-right"
          class="session-dialog"
        >
          <q-card class="session-dialog-card">
            <!-- Dialog Header -->
            <q-card-section class="row items-center q-pb-none bg-grey-1">
              <div class="col">
                <div class="text-h6 text-grey-8">
                  {{ selectedSession?.id ? 'Edit Session' : 'New Session' }}
                </div>
                <div class="text-caption text-grey-6">
                  Week {{ currentWeek }} - {{ weeklyPlan?.classroom_subject_teacher?.classroom?.name }} - {{ weeklyPlan?.classroom_subject_teacher?.subject?.name }}
                </div>
              </div>
              <q-btn
                flat
                round
                dense
                icon="close"
                color="grey-7"
                @click="closeSessionDrawer"
              />
            </q-card-section>

            <!-- Dialog Content -->
            <q-card-section class="q-pa-none full-height">
              <div class="full-height overflow-auto">
                <SessionModal
                  :session="selectedSession"
                  :is-editing="isEditing"
                  :is-drawer="true"
                  @save="saveSession"
                  @cancel="cancelEdit"
                  @close="closeSessionDrawer"
                />
              </div>
            </q-card-section>
          </q-card>
        </q-dialog>

        <!-- Week Summary -->
        <div class="mt-6 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Week Summary</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ sessions.length }}</div>
              <div class="text-sm text-gray-600">Total Sessions</div>
            </div>
            
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ completedSessions }}</div>
              <div class="text-sm text-gray-600">Completed</div>
            </div>
            
            <div class="text-center">
              <div class="text-2xl font-bold text-yellow-600">{{ getSessionsByType('quiz').length }}</div>
              <div class="text-sm text-gray-600">Quizzes</div>
            </div>
            
            <div class="text-center">
              <div class="text-2xl font-bold text-red-600">{{ getSessionsByType('exam').length }}</div>
              <div class="text-sm text-gray-600">Exams</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import WeekNavigator from './components/WeekNavigator.vue'
import SessionCard from './components/SessionCard.vue'
import SessionModal from './components/SessionModal.vue'
import { ref, onMounted, computed, watch } from 'vue'
// import { QBtn, QDialog, QCard, QCardSection } from 'quasar'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import draggable from 'vuedraggable'

export default {
  components: {
    AppLayout,
    WeekNavigator,
    SessionCard,
    SessionModal,
    // QBtn,
    // QDialog,
    // QCard,
    // QCardSection,
    draggable
  },
  props: {
    weeklyPlanId: {
      type: [String, Number],
      required: true
    }
  },
  setup(props) {
    // Reactive data
    const loading = ref(false)
    const saving = ref(false)
    const weeklyPlan = ref(null)
    const sessions = ref([])
    const selectedSession = ref(null)
    const isEditing = ref(false)
    const currentWeek = ref(1)
    const weekStatus = ref({})
    const showSessionDrawer = ref(false)

    // Load data on mount
    onMounted(async () => {
      await loadWeeklyPlan()
    })

    // Watch for week changes
    watch(currentWeek, async (newWeek) => {
      await loadWeekSessions(newWeek)
    })

    // Load weekly plan data
    const loadWeeklyPlan = async () => {
      loading.value = true
      try {
        const response = await axios.get(`/api/weeklyplansystem/headers/${props.weeklyPlanId}`)
        weeklyPlan.value = response.data
        currentWeek.value = response.data.week_number
        await loadWeekSessions(currentWeek.value)
      } catch (error) {
        console.error('Error loading weekly plan:', error)
      } finally {
        loading.value = false
      }
    }

    // Load sessions for a specific week
    const loadWeekSessions = async (weekNumber) => {
      if (!weeklyPlan.value) return

      try {
        const response = await axios.get('/api/weeklyplansystem/sessions', {
          params: {
            weekly_plan_id: weeklyPlan.value.id
          }
        })
        sessions.value = response.data.sort((a, b) => a.session_index - b.session_index)
      } catch (error) {
        console.error('Error loading sessions:', error)
        sessions.value = []
      }
    }

    // Change week
    const changeWeek = async (weekNumber) => {
      // Save current changes before switching
      await saveAllChanges()
      
      // Navigate to the new week's plan
      try {
        const response = await axios.get('/api/weeklyplansystem/headers', {
          params: {
            cst_id: weeklyPlan.value.cst_id,
            academic_year_id: weeklyPlan.value.academic_year_id,
            semester_number: weeklyPlan.value.semester_number,
            week_number: weekNumber
          }
        })
        
        if (response.data.length > 0) {
          router.visit(`/weekly-plans/${response.data[0].id}/edit`)
        }
      } catch (error) {
        console.error('Error changing week:', error)
      }
    }

    // Add new session
    const addNewSession = () => {
      const newSession = {
        id: null,
        weekly_plan_id: weeklyPlan.value.id,
        session_index: sessions.value.length + 1,
        period_code: generatePeriodCode(),
        type: 'lesson',
        title: `Session ${sessions.value.length + 1}`,
        data: {}
      }
      
      selectedSession.value = newSession
      isEditing.value = true
      showSessionDrawer.value = true
    }

    // Edit session
    const editSession = (session) => {
      selectedSession.value = { ...session }
      isEditing.value = true
      showSessionDrawer.value = true
    }

    // Save session
    const saveSession = async (sessionData) => {
      try {
        if (sessionData.id) {
          // Update existing session
          const response = await axios.put(`/api/weeklyplansystem/sessions/${sessionData.id}`, sessionData)
          const index = sessions.value.findIndex(s => s.id === sessionData.id)
          if (index !== -1) {
            sessions.value[index] = response.data
          }
        } else {
          // Create new session
          const response = await axios.post('/api/weeklyplansystem/sessions', sessionData)
          sessions.value.push(response.data)
          sessions.value.sort((a, b) => a.session_index - b.session_index)
        }
        
        selectedSession.value = null
        isEditing.value = false
      } catch (error) {
        console.error('Error saving session:', error)
      }
    }

    // Delete session
    const deleteSession = async (session) => {
      if (confirm('Are you sure you want to delete this session?')) {
        try {
          await axios.delete(`/api/weeklyplansystem/sessions/${session.id}`)
          sessions.value = sessions.value.filter(s => s.id !== session.id)
          
          // Close details panel if this session was selected
          if (selectedSession.value?.id === session.id) {
            selectedSession.value = null
            isEditing.value = false
          }
        } catch (error) {
          console.error('Error deleting session:', error)
        }
      }
    }

    // Duplicate session
    const duplicateSession = (session) => {
      const duplicated = {
        ...session,
        id: null,
        session_index: sessions.value.length + 1,
        title: `${session.title} (Copy)`,
        period_code: generatePeriodCode()
      }
      
      selectedSession.value = duplicated
      isEditing.value = true
      showSessionDrawer.value = true
    }

    // Handle session reordering
    const handleSessionReorder = async () => {
      // Update session indices
      sessions.value.forEach((session, index) => {
        session.session_index = index + 1
      })

      // Send reorder request to API
      try {
        await axios.post('/api/weeklyplansystem/sessions/reorder', {
          weekly_plan_id: weeklyPlan.value.id,
          sessions: sessions.value.map(session => ({
            id: session.id,
            session_index: session.session_index
          }))
        })
      } catch (error) {
        console.error('Error reordering sessions:', error)
      }
    }

    // Cancel edit
    const cancelEdit = () => {
      selectedSession.value = null
      isEditing.value = false
    }

    // Close session details
    const closeSessionDetails = () => {
      selectedSession.value = null
      isEditing.value = false
    }

    // Close session drawer
    const closeSessionDrawer = () => {
      selectedSession.value = null
      isEditing.value = false
      showSessionDrawer.value = false
    }

    // Save all changes
    const saveAllChanges = async () => {
      saving.value = true
      try {
        // Save any pending session changes
        if (selectedSession.value && isEditing.value) {
          await saveSession(selectedSession.value)
        }
        
        console.log('All changes saved successfully')
      } catch (error) {
        console.error('Error saving changes:', error)
      } finally {
        saving.value = false
      }
    }

    // Go back to overview
    const goBack = () => {
      router.visit('/weekly-plans')
    }

    // Generate period code
    const generatePeriodCode = () => {
      const year = weeklyPlan.value.academic_year_id.toString().slice(-2)
      const semester = weeklyPlan.value.semester_number
      const week = weeklyPlan.value.week_number
      const session = sessions.value.length + 1
      return `${year}.${semester}.${week}.${session}`
    }

    // Get sessions by type
    const getSessionsByType = (type) => {
      return sessions.value.filter(session => session.type === type)
    }

    // Computed properties
    const completedSessions = computed(() => {
      return sessions.value.filter(session => session.data?.completed).length
    })

    return {
      loading,
      saving,
      weeklyPlan,
      sessions,
      selectedSession,
      isEditing,
      currentWeek,
      weekStatus,
      showSessionDrawer,
      changeWeek,
      addNewSession,
      editSession,
      saveSession,
      deleteSession,
      duplicateSession,
      handleSessionReorder,
      cancelEdit,
      closeSessionDetails,
      closeSessionDrawer,
      saveAllChanges,
      goBack,
      getSessionsByType,
      completedSessions
    }
  }
}
</script>

<style scoped>
.drag-handle {
  cursor: grab;
}

.drag-handle:active {
  cursor: grabbing;
}

/* Session Dialog Styles */
.session-dialog {
  z-index: 9999;
}

.session-dialog-card {
  display: flex;
  flex-direction: column;
  height: 100vh;
}

.session-dialog .q-card__section {
  flex-shrink: 0;
}

.session-dialog .full-height {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.session-dialog .overflow-auto {
  flex: 1;
  overflow-y: auto;
}
</style>