<template>
  <div v-if="school_id && scheduleCopyId" class="mt-4">
    <div class="text-h6 q-mb-md">Schedule Grid</div>
    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
    >
      <q-tab name="grid" label="grid" />
      <q-tab name="drag" label="drag" />
      <!-- <q-tab name="Schedule_use" label="Schedule_use" /> -->
    </q-tabs>
    <div class="p-4" v-if="tab== 'grid'">


  <q-select
    v-model="selectedTeacher"
    :options="uniqueTeachers"
    option-label="name"
    option-value="id"
    label="Filter by Teacher"
    outlined
    dense
    clearable
    class="q-mb-md"
  />

  <q-table
      v-if="selectedTeacher"
      :rows="teacherSchedule"
      :columns="teacherColumns"
      row-key="id"
      flat
      bordered
      :loading="loading"
      :pagination="{ rowsPerPage: 0 }"
      hide-pagination
    >
      <template v-slot:body-cell-period_number="props">
        <q-td :props="props">
          Period {{ props.row.period_number }}
        </q-td>
      </template>

      <template v-slot:body-cell-day="props">
        <q-td :props="props">
          {{ getDayName(props.row.day) }}
        </q-td>
      </template>

      <template v-slot:body-cell-subject="props">
        <q-td :props="props">
          {{ props.row.cst?.subject?.name || '-' }}
        </q-td>
      </template>

      <template v-slot:body-cell-classroom="props">
        <q-td :props="props">
          {{ props.row.cst?.classroom?.name || '-' }}
        </q-td>
      </template>
    </q-table>


  <div class="row q-col-gutter-md">
      <div
        v-for="day in 7"
        :key="day"
        class="col-12 col-md-4"
      >
        <q-card class="q-pa-md">
          <q-card-section>
            <div class="text-h6">Day {{ day }}</div>

            <div
              v-for="period in 8"
              :key="period"
              class="q-mt-sm"
            >
              <div class="text-subtitle2">Period {{ period }}</div>
              <div v-if="getScheduleForPeriod(day, period).length">
                <q-list bordered>
                  <q-item
                    v-for="item in getScheduleForPeriod(day, period)"
                    :key="item.id"
                  >
                    <q-item-section>
                      <q-item-label>{{ item.cst?.subject?.name }}</q-item-label>
                      <q-item-label caption>
                        {{ item.cst?.teacher?.name }} - {{ item.cst?.classroom?.name }}
                      </q-item-label>
                    </q-item-section>
                    <q-item-section side>
                      <q-icon
                        :name="item.active ? 'check_circle' : 'cancel'"
                        :color="item.active ? 'positive' : 'negative'"
                      />
                    </q-item-section>
                  </q-item>
                </q-list>
              </div>
              <div v-else class="text-grey q-pa-sm">
                No schedule
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

    </div>
    </div>





    <div class="p-4" v-if="tab== 'drag'">
        <DragDropScheduleTable
      v-if="schoolId"
      :school-id="schoolId"
      :scheduleCopyId="scheduleCopyId"
      :teachers="uniqueTeachers"
      :days="['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']"
      :periods="8"
      :initialSchedule="scheduleData"
      @schedule-change="handleScheduleChange"
    />
  </div>



</div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import axios from 'axios'
import DragDropScheduleTable from './DragDropScheduleTable.vue'
const tab = ref('drag')

const props = defineProps({
  schoolId: {
    type: Number,
    required: true
  },
  scheduleCopyId: {
    type: Number,
    required: true
  },
  schedule_data: {
    type: Array,
    required: false
  },
})

const school_id = ref(props.schoolId)
const scheduleData = ref(props.schedule_data)
const loading = ref(false)
const selectedTeacher = ref(null)

const getScheduleForPeriod = (day, period) => {
  return scheduleData.value.filter(item =>
    item.day === day && item.period_number === period
  )
}
const getScheduleForTeacher = (selectedTeacher) => {
  return scheduleData.value.filter(item =>
    item.cst?.teacher?.id === selectedTeacher
  )
}
const teacherColumns = [
  {
    name: 'period_number',
    label: 'Period',
    field: 'period_number',
    align: 'left'
  },
  {
    name: 'day',
    label: 'Day',
    field: 'day',
    align: 'left'
  },
  {
    name: 'subject',
    label: 'Subject',
    field: row => row.cst?.subject?.name,
    align: 'left'
  },
  {
    name: 'classroom',
    label: 'Classroom',
    field: row => row.cst?.classroom?.name,
    align: 'left'
  }
]

const teacherSchedule = computed(() => {
  if (!selectedTeacher.value) return []
  return scheduleData.value.filter(
    item => item.cst?.teacher?.id === selectedTeacher.value.id
  )
})
const getDayName = (day) => {
  const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
  return days[day - 1] || `Day ${day}`
}

// Add this computed property to your script setup section
const uniqueTeachers = computed(() => {
  if (!scheduleData.value.length) return []

  const teachers = scheduleData.value
    .map(item => item.cst?.teacher)
    .filter(teacher => teacher) // Remove undefined/null
    .reduce((unique, teacher) => {
      if (!unique.some(t => t.id === teacher.id)) {
        unique.push(teacher)
      }
      return unique
    }, [])

  return teachers.sort((a, b) => a.name.localeCompare(b.name))
})
const fetchScheduleData = async () => {
  try {
    loading.value = true
    const response = await axios.get(`/admin/schedules/${props.schoolId}/${props.scheduleCopyId}`)
    scheduleData.value = response.data
  } catch (error) {
    console.error('Error fetching schedule data:', error)
  } finally {
    loading.value = false
  }
}
const handleScheduleChange = (updatedSchedule) => {
  // Handle saving the schedule changes
  console.log('Schedule updated:', updatedSchedule)
}
watch(() => props.scheduleCopyId, fetchScheduleData, { immediate: true })
</script>
