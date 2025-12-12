<template>
  <div class="q-pa-md">

    <!-- schedule_new_data:{{ $page }} -->
    <!-- schedule_new_data:{{ $page?.props?.schedule_new_data  }} -->
    <!-- schedule_new_data:{{ $page?.schedule_new_data }} -->
    <!-- <schools v-if="userRole" class="w-96"
    :role="userRole"
    :teacher-id="teacherId"
    @selected_school="handleSchoolSelected"
  />initialSchedule:{{ initialSchedule }} -->


    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="primary"
      indicator-color="primary"
      align="left"
      narrow-indicator
    >
      <q-tab name="classroom" label="Classroom Subjects" />
      <q-tab name="ScheduleCopies_use" label="ScheduleCopies_use" />
      <q-tab name="Schedule_admin_new" label="Schedule_admin_new" />
      <q-tab name="TimingManager" label="TimingManager" />
      <q-tab name="TypingApp" label="TypingApp" />
    </q-tabs>

    <q-tab-panels v-model="tab" animated>
      <q-tab-panel name="classroom">
        <!-- <classroom_subject_teacher /> -->
        <load_classroom_subject_teacher
    v-if="school_id"
    :school-id="school_id"
    class="mt-4"
  />
      </q-tab-panel>

      <q-tab-panel name="ScheduleCopies_use">
        <ScheduleCopies_use v-if="school_id"
        :school_id="school_id"
        :schedule_data="schedule_data"
        />
      </q-tab-panel>

      <q-tab-panel name="Schedule_admin_new">

        <q-btn v-if="schedule_data" label="Maximized" color="primary" @click="dialog = true" />
    <q-dialog
      v-model="dialog"

      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
      >
      <q-card class="  ">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
              <q-tooltip v-if="maximizedToggle" class="bg-white text-primary">Minimize</q-tooltip>
            </q-btn>
            <!-- <q-btn dense flat icon="crop_square" @click="maximizedToggle = true" :disable="maximizedToggle">
                <q-tooltip v-if="!maximizedToggle" class="bg-white text-primary">Maximize</q-tooltip>
            </q-btn> -->
            <q-btn dense flat icon="close" v-close-popup>
                <q-tooltip class="bg-white text-primary">Close</q-tooltip>
            </q-btn>
        </q-bar>

        <Schedule_admin_new
        :school_id="school_id"
           />
      </q-card>

</q-dialog>


      </q-tab-panel>










      <q-tab-panel name="TimingManager">

        <q-btn  label="TimingManager" color="primary" @click="dialog = true" />
    <q-dialog
      v-model="dialog"

      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
      >
      <q-card class="  ">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
              <q-tooltip v-if="maximizedToggle" class="bg-white text-primary">Minimize</q-tooltip>
            </q-btn>
            <!-- <q-btn dense flat icon="crop_square" @click="maximizedToggle = true" :disable="maximizedToggle">
                <q-tooltip v-if="!maximizedToggle" class="bg-white text-primary">Maximize</q-tooltip>
            </q-btn> -->
            <q-btn dense flat icon="close" v-close-popup>
                <q-tooltip class="bg-white text-primary">Close</q-tooltip>
            </q-btn>
        </q-bar>

        <TimingManager
        />
        <!-- v-model="timingData"
        @update:modelValue="handleTimingUpdate" -->
</q-card>
</q-dialog>


      </q-tab-panel>


      <q-tab-panel name="TypingApp">

        <q-btn  label="TypingApp" color="primary" @click="dialog = true" />
    <q-dialog
      v-model="dialog"

      :maximized="maximizedToggle"
      transition-show="slide-up"
      transition-hide="slide-down"
      >
      <q-card class="  ">
        <q-bar>
          <q-space />
          <q-btn dense flat icon="minimize" @click="maximizedToggle = false" :disable="!maximizedToggle">
              <q-tooltip v-if="maximizedToggle" class="bg-white text-primary">Minimize</q-tooltip>
            </q-btn>
            <!-- <q-btn dense flat icon="crop_square" @click="maximizedToggle = true" :disable="maximizedToggle">
                <q-tooltip v-if="!maximizedToggle" class="bg-white text-primary">Maximize</q-tooltip>
            </q-btn> -->
            <q-btn dense flat icon="close" v-close-popup>
                <q-tooltip class="bg-white text-primary">Close</q-tooltip>
            </q-btn>
        </q-bar>

        <TypingApp
        />
        <!-- v-model="timingData"
        @update:modelValue="handleTimingUpdate" -->
</q-card>
</q-dialog>


      </q-tab-panel>

    </q-tab-panels>
  </div>
</template>

<script setup>
import { ref,computed } from 'vue'
import load_classroom_subject_teacher from './components/load_classroom_subject_teacher.vue'
import ScheduleCopies_use from './components/ScheduleCopies_use.vue'
import schools from './components/schools.vue'
import Schedule_admin_new from './Schedule_admin_new.vue'
import TimingManager from './components/TimingManager/TimingManager.vue'
import TypingApp from './components/TimingManager/typing/TypingApp.vue'

// resources\js\Pages\my_class\admin/Schedules/new/components/TimingManager/TimingManager.vue
import { usePage } from '@inertiajs/vue3'
const page = usePage()
const scheduleNewData = computed(() => page.props.schedule_new_data)
const schedule_data = ref( page.props.schedule_new_data)
// import Schedule_admin_new from './Schedule_admin_new.vue'
const dialog = ref(false)
const maximizedToggle = ref(true)
// import Schedule_use from './components/Schedule_use.vue'
// import schedule_copies_use from './components/schedule_copies_use.vue'// import Schedule_use from './components/Schedule_use.vue'
// resources\js\Pages\my_class\admin\Schedules\new\components\Schedule_use.vue
const tab = ref('TimingManager')

const userRole = ref(localStorage.getItem('selectedRole'))
const teacherId = ref(parseInt(localStorage.getItem('user_id')))
const school_id = ref(Number(localStorage.getItem('school_id')))
const initialSchedule = ref([])
const loading = ref(false)

// const handleSchoolSelected = (id) => {
//   school_id.value = id
//   localStorage.setItem('school_id', id)
// }


</script>
