<template>
<div class="p-0 space-y-4">

  <schedule_copies
    v-if="school_id"
    :school-id="school_id"
    class="mt-4"
    @scheduleCopyId="scheduleCopyId=$event"
  />

  <schedule_entries_creator
    v-if="school_id"
    :school-id="school_id"
    :scheduleCopyId="scheduleCopyId"
    class="mt-4"
  />
  <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
    >
      <q-tab name="Schedule_display" label="Schedule_display" />
      <q-tab name="Schedule_display_grid" label="Schedule_display_grid" />
     <!-- <q-tab name="DragDropScheduleTable" label="DragDropScheduleTable" /> -->
    </q-tabs>

    <q-tab-panels v-model="tab" animated>
      <q-tab-panel name="Schedule_display">
        <Schedule_display
    v-if="scheduleCopyId"
    :school-id="school_id"
    :scheduleCopyId="scheduleCopyId"
    class="mt-4"
  />
      </q-tab-panel>

      <q-tab-panel name="Schedule_display_grid">
        <Schedule_display_grid
    v-if="scheduleCopyId"
    :school-id="school_id"
    :scheduleCopyId="scheduleCopyId"
    :schedule_data="schedule_data"
    class="mt-4"
  />
      </q-tab-panel>



    </q-tab-panels>

</div>
</template>

<script setup>
// import schools from './schools.vue'
import schedule_entries_creator from './ScheduleEntriesCreator.vue'
import { ref } from 'vue'
import schedule_copies from './ScheduleCopies_use_comp/ScheduleCopies.vue'
import Schedule_display from './ScheduleCopies_use_comp/Schedule_display.vue'
import Schedule_display_grid from './ScheduleCopies_use_comp/Schedule_display_grid.vue'
// import DragDropScheduleTable from './ScheduleCopies_use_comp/DragDropScheduleTable.vue'

const props = defineProps({
    school_id: {
    type: Number,
    required: true
  },
  schedule_data: {
    type: Array,
    required: false
  },

})
const scheduleCopyId = ref(null)
const tab = ref('Schedule_display_grid')
const teachers = ref([])
const handleSchoolSelected = (id) => {
  school_id.value = id
  localStorage.setItem('selected_school', id)
}

</script>
