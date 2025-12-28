<template>
  <q-dialog v-model="isOpen" transition-show="scale" transition-hide="scale">
    <q-card style="width: 700px; max-width: 90vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6 text-primary">Weekly System Guide</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-tabs
          v-model="tab"
          dense
          class="text-grey"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
        >
          <q-tab name="admin" label="Admin Guide" icon="admin_panel_settings" />
          <q-tab name="teacher" label="Teacher Guide" icon="school" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <!-- Admin Panel -->
          <q-tab-panel name="admin">
            <div class="text-subtitle1 q-mb-md text-weight-bold">How to manage the Weekly System:</div>
            
            <q-list bordered class="rounded-borders">
              <q-expansion-item
                group="admin-steps"
                icon="content_copy"
                label="1. Create Schedule Copy"
                caption="Set up the academic period"
                header-class="text-primary"
                default-opened
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Go to <strong>Schedule Copies</strong>. Create a new copy for the current academic year and semester. Mark it as "Active" to start using it.
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <q-separator />

              <q-expansion-item
                group="admin-steps"
                icon="grid_view"
                label="2. Input Timetable"
                caption="Assign subjects to classrooms"
                header-class="text-primary"
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Go to <strong>Timetable</strong>. Select your active schedule copy and a classroom. Click on the grid cells to assign subjects and teachers to each period.
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <q-separator />

              <q-expansion-item
                group="admin-steps"
                icon="auto_fix_high"
                label="3. Generate Weekly Plans"
                caption="Prepare plans for teachers"
                header-class="text-primary"
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Go to <strong>Manager</strong>. Select the active schedule and the current week. Click <strong>Generate Plans</strong>. This creates the empty plan records that teachers will fill out.
                  </q-card-section>
                </q-card>
              </q-expansion-item>
            </q-list>
          </q-tab-panel>

          <!-- Teacher Panel -->
          <q-tab-panel name="teacher">
            <div class="text-subtitle1 q-mb-md text-weight-bold">How to use your Weekly Plan:</div>

            <q-list bordered class="rounded-borders">
              <q-expansion-item
                group="teacher-steps"
                icon="calendar_month"
                label="1. Check Your Schedule"
                caption="View your assigned classes"
                header-class="text-secondary"
                default-opened
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Go to <strong>My Schedule</strong> to see your weekly timetable. This shows you when and where you are teaching.
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <q-separator />

              <q-expansion-item
                group="teacher-steps"
                icon="edit_note"
                label="2. Fill Weekly Plans"
                caption="Enter Classwork & Homework"
                header-class="text-secondary"
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Go to <strong>My Plans</strong>. Select the current week. Click on any class slot to open the editor.
                  </q-card-section>
                </q-card>
              </q-expansion-item>

              <q-separator />

              <q-expansion-item
                group="teacher-steps"
                icon="save"
                label="3. Save & Update"
                caption="Keep your plans up to date"
                header-class="text-secondary"
              >
                <q-card>
                  <q-card-section class="text-grey-8">
                    Enter the <strong>Classwork (CW)</strong> and <strong>Homework (HW)</strong> for the lesson. Click Save. Your progress is automatically tracked for the administration.
                  </q-card-section>
                </q-card>
              </q-expansion-item>
            </q-list>
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: Boolean
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const tab = ref('admin')

watch(() => props.modelValue, (val) => {
  isOpen.value = val
})

watch(isOpen, (val) => {
  emit('update:modelValue', val)
})
</script>
