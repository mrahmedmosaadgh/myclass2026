<template>
  <div class="schedule-tree">
    <div v-for="classroom in classrooms" :key="classroom.id" class="tree-node">
      <div class="classroom-node" @click="toggleClassroom(classroom.id)">
        <LucideIcon
          :name="expandedClassrooms.has(classroom.id) ? 'chevron-down' : 'chevron-right'"
          size="16"
          class="mr-2"
        />
        <span class="font-medium">{{ classroom.name }}</span>
      </div>
      
      <div v-if="expandedClassrooms.has(classroom.id)" class="ml-6">
        <div v-for="day in days" :key="day.number" class="day-node">
          <div @click="toggleDay(classroom.id, day.number)" class="flex items-center py-1">
            <LucideIcon
              :name="expandedDays.has(`${classroom.id}-${day.number}`) ? 'chevron-down' : 'chevron-right'"
              size="16"
              class="mr-2"
            />
            <span>{{ day.name }}</span>
          </div>

          <div v-if="expandedDays.has(`${classroom.id}-${day.number}`)" class="ml-6">
            <div v-for="period in periods" :key="period" class="period-node">
              <div class="flex items-center py-1">
                <span class="text-gray-600 min-w-[100px]">Period {{ period }}:</span>
                <template v-if="getScheduleInfo(classroom.id, day.number, period)">
                  <div class="ml-2 p-2 bg-gray-50 rounded flex-1">
                    <div class="flex justify-between">
                      <div>
                        <span class="font-medium">{{ getScheduleInfo(classroom.id, day.number, period).subject }}</span>
                        <span class="text-gray-500 ml-2">
                          ({{ getScheduleInfo(classroom.id, day.number, period).teacher }})
                        </span>
                      </div>
                      <div class="text-gray-400 text-sm">
                        {{ periodTimes[period] }}
                      </div>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="ml-2 p-2 bg-gray-50 rounded flex-1 text-gray-400">
                    No class scheduled
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import LucideIcon from '@/Components/Common/LucideIcon.vue';

const props = defineProps({
  schedules: {
    type: Array,
    required: true
  }
});

const days = [
  { name: 'Sunday', number: 1 },
  { name: 'Monday', number: 2 },
  { name: 'Tuesday', number: 3 },
  { name: 'Wednesday', number: 4 },
  { name: 'Thursday', number: 5 }
];

const periods = Array.from({ length: 8 }, (_, i) => i + 1);

const periodTimes = {
  1: '07:30 - 08:15',
  2: '08:15 - 09:00',
  3: '09:00 - 09:45',
  4: '10:00 - 10:45',
  5: '10:45 - 11:30',
  6: '11:30 - 12:15',
  7: '12:45 - 13:30',
  8: '13:30 - 14:15'
};

const expandedClassrooms = ref(new Set());
const expandedDays = ref(new Set());

// Group schedules by classroom
const classrooms = computed(() => {
  const classroomMap = new Map();

  props.schedules.forEach(schedule => {
    if (!schedule.cst?.classroom) return;

    const classroom = schedule.cst.classroom;
    if (!classroomMap.has(classroom.id)) {
      classroomMap.set(classroom.id, {
        id: classroom.id,
        name: classroom.name,
        grade: classroom.grade
      });
    }
  });

  return Array.from(classroomMap.values());
});

// Get schedule information for a specific slot
const getScheduleInfo = (classroomId, day, period) => {
  const schedule = props.schedules.find(s =>
    s.cst?.classroom?.id === classroomId &&
    s.day === day &&
    s.period_number === period
  );

  if (!schedule) return null;

  return {
    subject: schedule.cst.subject.name,
    teacher: schedule.cst.teacher.name
  };
};

const toggleClassroom = (classroomId) => {
  if (expandedClassrooms.value.has(classroomId)) {
    expandedClassrooms.value.delete(classroomId);
    // Close all days for this classroom
    days.forEach(day => {
      expandedDays.value.delete(`${classroomId}-${day.number}`);
    });
  } else {
    expandedClassrooms.value.add(classroomId);
  }
};

const toggleDay = (classroomId, dayNumber) => {
  const key = `${classroomId}-${dayNumber}`;
  if (expandedDays.value.has(key)) {
    expandedDays.value.delete(key);
  } else {
    expandedDays.value.add(key);
  }
};
</script>

<style scoped>
.schedule-tree {
  @apply space-y-2;
}

.tree-node {
  @apply rounded-lg border border-gray-200 mb-4;
}

.classroom-node {
  @apply flex items-center p-2 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors;
}

.day-node {
  @apply py-1;
}

.period-node {
  @apply border-l-2 border-gray-200 pl-2;
}
</style>
