<template>
    <div class="schedule-filters flex items-center gap-4">
      <!-- Teacher Filter -->
      <q-select
        v-model="selectedTeachers"
        multiple
        :options="teacherOptions"
        use-chips
        clearable
        filled
        dense
        option-label="teacher_cute"
        option-value="id"
        label="Filter Teachers"
        style="min-width: 200px"
        @update:model-value="emitFiltered"
      />
{{ selectedTeachers }}
      <!-- Day Filter -->
      <q-tabs
        v-model="selectedDay"
        dense
        class="ml-4"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
        @update:model-value="emitFiltered"
      >
        <q-tab v-for="day in dayOptions" :key="day" :name="day" :label="day" />
      </q-tabs>
    </div>
  </template>

  <script setup>
  import { ref, computed, watch } from 'vue';
 
  const props = defineProps({
    scheduleData: {
      type: Array,
      required: true,
      default: () => [] // Add default empty array
    },
    days: {
      type: Array,
      required: true
    }
  });

  const emit = defineEmits(['filter-changed']);

  // Filter states
  const selectedTeachers = ref([]);
  const selectedDay = ref('All');

  // Computed properties
  const dayOptions = computed(() => ['All', ...props.days]);

  const teacherOptions = computed(() => {
    if (!props.scheduleData) return [];

    return props.scheduleData
      .filter(item => item.teacher && item.teacher.id)
      .map(item => ({
        id: item.teacher.id,
        teacher_cute: item.teacher.teacher_cute || item.teacher.name,
        name: item.teacher.name
      }));
  });

  const filteredData = computed(() => {
    let filtered = [...props.scheduleData];

    // Apply teacher filter
    if (selectedTeachers.value?.length > 0) {
      filtered = filtered.filter(item =>
        selectedTeachers.value.includes(item.teacher?.id)
      );
    }

    return {
      scheduleData: filtered,
      filteredDays: selectedDay.value === 'All'
        ? props.days.map((day, index) => ({ name: day, originalIndex: index }))
        : [{
            name: selectedDay.value,
            originalIndex: props.days.findIndex(day => day === selectedDay.value)
          }]
    };
  });

  // Emit filtered data
  const emitFiltered = () => {
    emit('filter-changed', filteredData.value);
  };

  // Watch for props changes
  watch(() => props.scheduleData, emitFiltered, { deep: true });
  </script>
