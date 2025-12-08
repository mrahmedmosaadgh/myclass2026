<template>
  <AppLayout title="Weekly Plans">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Weekly Plans
      </h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Subject & Class Selection -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">My Teaching Assignments</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div 
              v-for="cst in classSubjectTeachers" 
              :key="cst.id"
              class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 transition-colors"
              :class="{ 'border-blue-500 bg-blue-50': selectedCst?.id === cst.id }"
              @click="selectCst(cst)"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="font-medium text-gray-900">{{ cst.classroom?.name || 'Unknown Class' }}</h4>
                  <p class="text-sm text-gray-600">{{ cst.subject?.name || 'Unknown Subject' }}</p>
                  <p class="text-xs text-gray-500">{{ cst.classes_per_week }} sessions/week</p>
                </div>
                <div class="text-right">
                  <div class="text-sm font-medium text-gray-900">
                    {{ getWeeklyPlanStatus(cst.id) }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ getCurrentAcademicYear()?.name }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Academic Year & Semester Selection -->
        <div v-if="selectedCst" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Academic Year</label>
              <select 
                v-model="selectedAcademicYear" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @change="loadWeeklyPlans"
              >
                <option v-for="year in academicYears" :key="year.id" :value="year.id">
                  {{ year.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Semester</label>
              <select 
                v-model="selectedSemester" 
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                @change="loadWeeklyPlans"
              >
                <option value="1">First Semester</option>
                <option value="2">Second Semester</option>
              </select>
            </div>
            
            <div class="flex items-end">
              <q-btn 
                color="primary" 
                icon="auto_fix_high" 
                label="Generate Semester Plans" 
                @click="generateSemesterPlans"
                :loading="generating"
                class="w-full"
              />
            </div>
          </div>
        </div>

        <!-- Weekly Plans Grid -->
        <div v-if="selectedCst && weeklyPlans.length > 0" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">
              Weekly Plans - {{ selectedCst.classroom?.name }} - {{ selectedCst.subject?.name }}
            </h3>
            <div class="text-sm text-gray-500">
              Semester {{ selectedSemester }} - {{ getCurrentAcademicYear()?.name }}
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div 
              v-for="plan in weeklyPlans" 
              :key="plan.id"
              class="border rounded-lg p-4 cursor-pointer hover:shadow-md transition-shadow"
              @click="openWeeklyPlan(plan)"
            >
              <div class="flex justify-between items-start mb-3">
                <h4 class="font-medium text-gray-900">Week {{ plan.week_number }}</h4>
                <span 
                  class="px-2 py-1 text-xs rounded-full"
                  :class="getWeekStatusClass(plan)"
                >
                  {{ getWeekStatus(plan) }}
                </span>
              </div>
              
              <div class="space-y-2">
                <div class="text-sm text-gray-600">
                  {{ plan.sessions?.length || 0 }} sessions planned
                </div>
                
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: getWeekProgress(plan) + '%' }"
                  ></div>
                </div>
                
                <div class="text-xs text-gray-500">
                  {{ getWeekProgress(plan) }}% complete
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else-if="selectedCst && !loading" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12 text-center">
          <div class="text-gray-400 mb-4">
            <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mb-2">No Weekly Plans Found</h3>
          <p class="text-gray-600 mb-4">Generate your semester plans to get started with weekly planning.</p>
          <q-btn 
            color="primary" 
            icon="auto_fix_high" 
            label="Generate Semester Plans" 
            @click="generateSemesterPlans"
            :loading="generating"
          />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-12 text-center">
          <q-spinner-dots size="50px" color="primary" />
          <p class="mt-4 text-gray-600">Loading weekly plans...</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
// import { QBtn, QSpinnerDots } from 'quasar'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export default {
  components: {
    AppLayout,
 
  },
  setup() {
    // Reactive data
    const loading = ref(false)
    const generating = ref(false)
    const academicYears = ref([])
    const classSubjectTeachers = ref([])
    const weeklyPlans = ref([])
    const selectedCst = ref(null)
    const selectedAcademicYear = ref(null)
    const selectedSemester = ref(1)

    // Load initial data
    onMounted(async () => {
      await loadInitialData()
    })

    // Load academic years and teacher assignments
    const loadInitialData = async () => {
      loading.value = true
      try {
        // Load academic years (you may need to adjust this endpoint)
        const academicYearsResponse = await axios.get('/api/academic-years')
        academicYears.value = academicYearsResponse.data

        // Load teacher's classroom subject assignments
        const cstResponse = await axios.get('/api/classroom-subject-teachers/my-assignments')
        classSubjectTeachers.value = cstResponse.data

        // Set default academic year to current
        if (academicYears.value.length > 0) {
          selectedAcademicYear.value = academicYears.value[0].id
        }
      } catch (error) {
        console.error('Error loading initial data:', error)
        // Handle error - maybe show a notification
      } finally {
        loading.value = false
      }
    }

    // Select a classroom subject teacher assignment
    const selectCst = (cst) => {
      selectedCst.value = cst
      loadWeeklyPlans()
    }

    // Load weekly plans for selected CST, academic year, and semester
    const loadWeeklyPlans = async () => {
      if (!selectedCst.value || !selectedAcademicYear.value) return

      loading.value = true
      try {
        const response = await axios.get('/api/weeklyplansystem/headers', {
          params: {
            cst_id: selectedCst.value.id,
            academic_year_id: selectedAcademicYear.value,
            semester_number: selectedSemester.value
          }
        })
        weeklyPlans.value = response.data
      } catch (error) {
        console.error('Error loading weekly plans:', error)
        weeklyPlans.value = []
      } finally {
        loading.value = false
      }
    }

    // Generate semester plans
    const generateSemesterPlans = async () => {
      if (!selectedCst.value || !selectedAcademicYear.value) return

      generating.value = true
      try {
        const response = await axios.post('/api/weeklyplansystem/headers/generate-semester', {
          cst_id: selectedCst.value.id,
          academic_year_id: selectedAcademicYear.value,
          semester_number: selectedSemester.value,
          total_weeks: 18 // Default to 18 weeks
        })
        weeklyPlans.value = response.data
        
        // Show success notification
        console.log('Semester plans generated successfully')
      } catch (error) {
        console.error('Error generating semester plans:', error)
        // Handle error - maybe show a notification
      } finally {
        generating.value = false
      }
    }

    // Open weekly plan editor
    const openWeeklyPlan = (plan) => {
      router.visit(`/weekly-plans/${plan.id}/edit`)
    }

    // Get current academic year
    const getCurrentAcademicYear = () => {
      return academicYears.value.find(year => year.id === selectedAcademicYear.value)
    }

    // Get weekly plan status for a CST
    const getWeeklyPlanStatus = (cstId) => {
      // This would typically check if plans exist for this CST
      return 'Ready'
    }

    // Get week status
    const getWeekStatus = (plan) => {
      const sessionCount = plan.sessions?.length || 0
      if (sessionCount === 0) return 'Empty'
      
      // Calculate completion based on session types
      const completedSessions = plan.sessions?.filter(session => 
        session.type === 'lesson' && session.data?.completed
      ).length || 0
      
      if (completedSessions === 0) return 'Planned'
      if (completedSessions === sessionCount) return 'Complete'
      return 'In Progress'
    }

    // Get week status CSS class
    const getWeekStatusClass = (plan) => {
      const status = getWeekStatus(plan)
      switch (status) {
        case 'Empty':
          return 'bg-gray-100 text-gray-800'
        case 'Planned':
          return 'bg-blue-100 text-blue-800'
        case 'In Progress':
          return 'bg-yellow-100 text-yellow-800'
        case 'Complete':
          return 'bg-green-100 text-green-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    // Get week progress percentage
    const getWeekProgress = (plan) => {
      const sessionCount = plan.sessions?.length || 0
      if (sessionCount === 0) return 0
      
      const completedSessions = plan.sessions?.filter(session => 
        session.data?.completed
      ).length || 0
      
      return Math.round((completedSessions / sessionCount) * 100)
    }

    return {
      loading,
      generating,
      academicYears,
      classSubjectTeachers,
      weeklyPlans,
      selectedCst,
      selectedAcademicYear,
      selectedSemester,
      selectCst,
      loadWeeklyPlans,
      generateSemesterPlans,
      openWeeklyPlan,
      getCurrentAcademicYear,
      getWeeklyPlanStatus,
      getWeekStatus,
      getWeekStatusClass,
      getWeekProgress
    }
  }
}
</script>
