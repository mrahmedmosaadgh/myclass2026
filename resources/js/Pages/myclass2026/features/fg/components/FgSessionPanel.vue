<template>
  <q-card
    bordered
    class="fg-session-panel q-mt-md"
    :class="isRunning ? 'bg-primary text-white' : 'bg-grey-1'"
  >
    <q-card-section>
      <!-- Panel Label -->
      <div class="text-caption q-mb-xs" :class="isRunning ? 'text-blue-2' : 'text-grey-6'">
        <q-icon name="info" size="14px" class="q-mr-xs" />
        {{ isRunning ? 'Session in progress — stay focused!' : 'Start a timed focus session to track your deep work.' }}
      </div>

      <div class="row items-center justify-between">
        <!-- Timer Display -->
        <div>
          <div class="text-h6">
            <q-icon :name="isRunning ? 'timer' : 'timer_off'" class="q-mr-sm" />
            {{ formattedTime }}
          </div>
          <div class="text-caption" :class="isRunning ? 'text-blue-1' : 'text-grey-6'">Current Session</div>
        </div>

        <!-- Idle State Controls -->
        <div v-if="!isRunning" class="row q-gutter-sm items-center flex-wrap">
          <q-input
            v-model="intention"
            outlined dense
            bg-color="white"
            label="What's your intention?"
            style="min-width: 180px"
          >
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
              Set a clear goal for this session (e.g. "Fix the login bug")
            </q-tooltip>
          </q-input>

          <q-select
            v-model="energy"
            :options="['high', 'medium', 'low']"
            outlined dense
            bg-color="white"
            label="Energy level"
            style="width: 110px"
          >
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
              How energised are you right now? Helps track patterns over time.
            </q-tooltip>
          </q-select>

          <q-btn
            unelevated
            color="primary"
            label="Start Focus"
            icon="play_arrow"
            @click="startFocus"
          >
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
              Begin a timed session. The clock starts now!
            </q-tooltip>
          </q-btn>
        </div>

        <!-- Running State Controls -->
        <div v-else class="row q-gutter-sm">
          <q-btn-dropdown
            split
            unelevated
            color="green-7"
            text-color="white"
            label="Stop & Save"
            icon="stop"
            @click="endFocus('on_track')"
            content-class="bg-white text-dark shadow-10"
            content-style="border-radius: 12px; min-width: 250px"
          >
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
              End this focus session and save it as completed.
            </q-tooltip>
            
            <q-list separator class="q-py-sm">
              <!-- Stop & Complete Task -->
              <q-item clickable v-close-popup @click="stopAndCompleteTask">
                <q-item-section avatar>
                  <q-avatar color="green-1" text-color="positive" icon="done_all" size="md" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold">Stop & Complete Task</q-item-label>
                  <q-item-label caption>Save session and mark task done</q-item-label>
                </q-item-section>
              </q-item>

              <!-- Stop & Pause -->
              <q-item clickable v-close-popup @click="stopAndPauseTask">
                <q-item-section avatar>
                  <q-avatar color="orange-1" text-color="orange-8" icon="inbox" size="md" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold">Stop & Send to Inbox</q-item-label>
                  <q-item-label caption>Save session and pause task</q-item-label>
                </q-item-section>
              </q-item>

              <!-- Drifted -->
              <q-item clickable v-close-popup @click="endFocus('drifted')">
                <q-item-section avatar>
                  <q-avatar color="grey-2" text-color="grey-8" icon="directions_walk" size="md" />
                </q-item-section>
                <q-item-section>
                  <q-item-label class="text-weight-bold">Mark as Drifted</q-item-label>
                  <q-item-label caption>Session was distracted/off-task</q-item-label>
                </q-item-section>
              </q-item>
            </q-list>
          </q-btn-dropdown>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
import { useFgSession } from '../composables/fg-use-session'
import { useFgTasksStore } from '../stores/fg-tasks.store'

const props = defineProps({
  taskId: { type: String, required: true }
})

const intention = ref('')
const energy = ref(null)
const tasksStore = useFgTasksStore()
const { isRunning, formattedTime, startSession, completeSession } = useFgSession(props.taskId)

const startFocus = async () => { await startSession(intention.value, energy.value) }
const endFocus = async (status) => { await completeSession(status) }

const stopAndCompleteTask = async () => {
  await endFocus('on_track')
  await tasksStore.updateTask(props.taskId, { status: 'done' })
}

const stopAndPauseTask = async () => {
  await endFocus('on_track')
  await tasksStore.updateTask(props.taskId, { status: 'inbox' })
}
</script>

<style scoped>
.fg-session-panel {
  transition: background-color 0.5s ease;
}
</style>
