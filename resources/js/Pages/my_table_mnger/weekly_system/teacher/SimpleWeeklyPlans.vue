<template>
  <Head title="My Weekly Plans" />
  <div class="q-pa-md">
    <!-- Page Header -->
    <div class="row items-center q-mb-lg">
      <div class="col">
        <h4 class="q-ma-none text-weight-bold">
          <q-icon name="edit_note" class="q-mr-sm" color="primary" />
          My Weekly Plans
        </h4>
        <p class="text-grey-7 q-mb-none">
          Fill in your classwork and homework for each class
        </p>
      </div>
      <!-- Edit Mode Toggle -->
      <div class="col-auto">
        <q-toggle
          v-model="editMode"
          icon="edit"
          label="Edit Mode"
          color="primary"
        />
      </div>
    </div>

    <!-- Controls -->
    <q-card flat bordered class="q-pa-md q-mb-lg">
      <div class="row q-gutter-md items-center">
        <!-- Week Navigation -->
        <div class="col-auto">
          <div class="row items-center">
            <q-btn
              icon="chevron_left"
              color="primary"
              round
              flat
              @click="prevWeek"
              :disable="weekNumber <= 1"
            />
            <WeekSelector
              v-model="weekNumber"
              :weeks="weeks"
              @week-selected="loadWeeklyPlans"
              class="q-mx-md"
            />
            <q-btn
              icon="chevron_right"
              color="primary"
              round
              flat
              @click="nextWeek"
              :disable="weekNumber >= maxWeeks"
            />
          </div>
        </div>

        <!-- Edit Mode Switch -->
        <div class="col-12 col-sm-3 col-md-2">
          <q-toggle
            v-model="editMode"
            label="Edit Mode"
            color="primary"
          />
        </div>

        <!-- Progress Summary -->
        <div class="col-auto q-ml-auto">
          <q-linear-progress
            :value="completionPercentage / 100"
            size="25px"
            :color="progressColor"
            track-color="grey-3"
            rounded
            style="width: 150px"
          />
        </div>
        <div class="col-auto">
          <q-chip :color="progressColor" text-color="white" :label="`${completionPercentage}%`" />
        </div>
      </div>
    </q-card>

    <!-- Weekly Plans Grid -->
    <div class="weekly-plans-grid">
      <div v-for="plan in sortedPlansByDay" :key="plan.dayNumber" class="day-section q-mb-xl">
        <h5 class="q-mb-md text-weight-bold">
          {{ plan.dayName }}
          <q-chip dense :color="progressColor" text-color="white" :label="plan.plans.length" />
        </h5>

        <div class="plans-grid">
          <q-card
            v-for="item in plan.plans"
            :key="item.id"
            flat
            bordered
            class="plan-card q-mr-sm q-mb-sm"
            :class="{ 'copied': copyingPlanId === item.id }"
          >
            <!-- Plan Header -->
            <div class="plan-header row items-center q-px-md q-py-sm">
              <div class="col">
                <div class="text-subtitle2 text-weight-bold">
                  <q-icon name="meeting_room" size="sm" color="primary" />
                  <!-- Add null check for schedule and cst -->
                  {{ item.data?.schedule?.cst?.classroom_name || 'No classroom assigned' }}
                </div>
                <div class="text-caption">
                  <q-icon name="school" size="xs" color="primary" />
                  {{ item.data?.schedule?.cst?.subject_name || 'No subject assigned' }}
                </div>
              </div>

              <div class="col-auto">
                <StatusBadge :status="item.data?.status || 'empty'" />
              </div>
            </div>

            <!-- Plan Content -->
            <div class="plan-content q-px-md q-py-sm">
              <!-- Classwork -->
              <div class="plan-section q-mb-sm">
                <div class="plan-label">Classwork (CW)</div>
                <div class="plan-content-text" :class="{ 'empty': !item.data?.cw }">
                  <q-icon name="school" size="xs" color="primary" />
                  <span v-if="item.data?.cw">{{ item.data.cw }}</span>
                  <span v-else class="text-grey-5">No classwork added yet</span>
                </div>
              </div>

              <!-- Homework -->
              <div class="plan-section q-mb-sm">
                <div class="plan-label">Homework (HW)</div>
                <div class="plan-content-text" :class="{ 'empty': !item.data?.hw }">
                  <q-icon name="home_work" size="xs" color="amber" />
                  <span v-if="item.data?.hw">{{ item.data.hw }}</span>
                  <span v-else class="text-grey-5">No homework added yet</span>
                </div>
              </div>

              <!-- Notes -->
              <div class="plan-section">
                <div class="plan-label">Notes</div>
                <div class="plan-content-text" :class="{ 'empty': !item.data?.notes }">
                  <q-icon name="note" size="xs" color="secondary" />
                  <span v-if="item.data?.notes">{{ item.data.notes }}</span>
                  <span v-else class="text-grey-5">No notes added yet</span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="plan-actions row justify-end q-px-md q-py-sm">
              <q-btn
                v-if="!editMode && (!copiedData || copyingPlanId !== item.id)"
                icon="content_copy"
                size="sm"
                flat
                color="primary"
                @click="copyPlan(item, $event)"
                class="q-mr-sm"
              >
                <q-tooltip>Copied to clipboard</q-tooltip>
              </q-btn>

              <q-btn
                v-if="!editMode && copiedData && copyingPlanId !== item.id"
                icon="content_paste"
                size="sm"
                flat
                color="primary"
                @click="pastePlan(item, $event)"
                class="q-mr-sm"
              >
                <q-tooltip>Paste copied data</q-tooltip>
              </q-btn>

              <q-btn
                v-if="!editMode"
                icon="edit"
                size="sm"
                flat
                color="primary"
                @click="editPlan(item)"
              >
                <q-tooltip>Edit plan</q-tooltip>
              </q-btn>

              <!-- Save button shown in edit mode -->
              <q-btn
                v-if="editMode && item.isEditing"
                icon="save"
                size="sm"
                flat
                color="green"
                :loading="item.saving"
                @click="savePlanField(item, 'all')"
              >
                <q-tooltip>Save changes</q-tooltip>
              </q-btn>

              <!-- Cancel button shown in edit mode -->
              <q-btn
                v-if="editMode && item.isEditing"
                icon="cancel"
                size="sm"
                flat
                color="red"
                @click="revertPlan(item)"
              >
                <q-tooltip>Cancel</q-tooltip>
              </q-btn>
            </div>
          </q-card>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!weeklyPlans.length" class="text-center q-pa-xl">
        <q-icon name="edit_note" size="64px" color="grey-5" />
        <p class="text-h6 text-grey-7 q-mt-md">No weekly plans found</p>
        <p class="text-grey-6">Please select a week and semester to view your weekly plans</p>
      </div>
    </div>

    <!-- Edit Dialog -->
    <q-dialog v-model="showEditor" persistent>
      <WeeklyPlanEditor
        v-model="showEditor"
        :plan="selectedPlan"
        :saving="saving"
        @submit="handleSave"
        @close="showEditor = false"
      />
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import WeekSelector from '@/Components/WeekSelector.vue';
import WeeklyPlanEditor from '@/Pages/my_table_mnger/weekly_system/teacher/WeeklyPlanEditor.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

// Define reactive properties
const editMode = ref(false);
const weekNumber = ref(1);
const maxWeeks = ref(10);
const currentWeek = ref(1);
const weeklyPlans = ref([]);
const copyingPlanId = ref(null);
const copiedData = ref(null);
const saving = ref(false);
const selectedPlan = ref(null);
const showEditor = ref(false);

// Create weeks array for WeekSelector
const weeks = ref([
  { id: 1, name: 'Week 1' },
  { id: 2, name: 'Week 2' },
  { id: 3, name: 'Week 3' },
  { id: 4, name: 'Week 4' },
  { id: 5, name: 'Week 5' },
  { id: 6, name: 'Week 6' },
  { id: 7, name: 'Week 7' },
  { id: 8, name: 'Week 8' },
  { id: 9, name: 'Week 9' },
  { id: 10, name: 'Week 10' }
]);

// Function to load weekly plans from the backend
async function loadWeeklyPlans(week) {
  try {
    // Show loading state if needed
    console.log(`Loading plans for week ${week}`);
    
    // In a real implementation, you would fetch data from an API like:
    // const response = await fetch(`/api/weekly-plans?week=${week}`);
    // weeklyPlans.value = await response.json();
    
    // For now, we'll use mock data until we have the actual API endpoint
    weeklyPlans.value = [
      {
        dayNumber: 1,
        dayName: 'Saturday',
        plans: [
          {
            id: 1,
            data: {
              cw: 'Introduction to Algebra',
              hw: 'Exercises 1-10',
              notes: 'Review basic concepts',
              schedule: {
                cst: {
                  classroom_name: 'Room 101',
                  subject_name: 'Math'
                }
              },
              status: 'complete'
            },
            isEditing: false,
            saving: false
          }
        ]
      },
      {
        dayNumber: 2,
        dayName: 'Sunday',
        plans: [
          {
            id: 2,
            data: {
              cw: 'Geometry Basics',
              hw: 'Exercises 11-20',
              notes: 'Focus on triangles',
              schedule: {
                cst: {
                  classroom_name: 'Room 102',
                  subject_name: 'Math'
                }
              },
              status: 'partial'
            },
            isEditing: false,
            saving: false
          }
        ]
      }
    ];
  } catch (error) {
    console.error('Error loading weekly plans:', error);
    // Handle error appropriately
  }
}

// Navigate to previous week
function prevWeek() {
  if (weekNumber.value > 1) {
    weekNumber.value--;
    loadWeeklyPlans(weekNumber.value);
  }
}

// Navigate to next week
function nextWeek() {
  if (weekNumber.value < maxWeeks.value) {
    weekNumber.value++;
    loadWeeklyPlans(weekNumber.value);
  }
}

// Watch for weekNumber changes and load plans when week is selected
watch(
  () => weekNumber.value,
  (newWeek) => {
    if (newWeek) {
      loadWeeklyPlans(newWeek);
    }
  }
);

// Computed properties
const sortedPlansByDay = computed(() => {
  // This is a placeholder - implement actual sorting logic
  return weeklyPlans.value;
});

const completionPercentage = computed(() => {
  // Calculate based on actual data
  if (weeklyPlans.value.length === 0) return 0;
  
  const totalPlans = weeklyPlans.value.reduce((total, day) => total + day.plans.length, 0);
  if (totalPlans === 0) return 0;
  
  const completedPlans = weeklyPlans.value.reduce((count, day) => {
    const dayCompleted = day.plans.filter(plan => plan.data?.status === 'complete').length;
    return count + dayCompleted;
  }, 0);
  
  return Math.round((completedPlans / totalPlans) * 100);
});

const progressColor = computed(() => {
  // Determine color based on completion percentage
  const percentage = completionPercentage.value;
  if (percentage < 30) return 'negative';
  if (percentage < 70) return 'warning';
  return 'positive';
});
</script>

<style scoped>
.weekly-plans-grid {
  max-width: 1200px;
}

.day-section {
  border-left: 4px solid #1976d2;
  padding-left: 16px;
}

.plan-card {
  transition: all 0.2s ease-in-out;
  min-width: 300px;
  max-width: 300px;
  height: 100%;
}

.plan-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.copied {
  border-left: 4px solid #1976d2;
}

.plan-header {
  border-bottom: 1px solid #f1f1f1;
}

.plan-content {
  min-height: 150px;
}

.plan-section {
  min-height: 60px;
}

.plan-label {
  font-size: 0.8em;
  font-weight: 500;
  color: #666;
  margin-bottom: 4px;
}

.plan-content-text {
  font-size: 0.9em;
  line-height: 1.5;
}

.plan-content-text.empty {
  color: #999;
}

.plan-actions {
  border-top: 1px solid #f1f1f1;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .plan-card {
    max-width: 100%;
  }
}

.editable-input {
  width: 100%;
  margin-top: 4px;
  margin-bottom: 4px;
}
</style>