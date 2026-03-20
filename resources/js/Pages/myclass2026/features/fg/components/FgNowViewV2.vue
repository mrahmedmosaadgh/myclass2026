<template>
  <div class="fg-now-v2 column items-center justify-center full-height q-pa-lg">

    <!-- Active Task -->
    <template v-if="activeTask">
      <q-badge
        rounded
        color="teal"
        class="q-py-sm q-px-lg q-mb-lg"
        style="font-size: 13px; letter-spacing: 2px"
      >
        CURRENT FOCUS
      </q-badge>

      <div
        class="text-center q-mb-xl"
        style="max-width: 560px"
      >
        <div class="text-h3 text-weight-bold text-dark q-mb-sm" style="line-height: 1.2">
          {{ activeTask.title }}
        </div>
        <div class="text-body1 text-grey-7" v-if="activeTask.notes">
          {{ activeTask.notes }}
        </div>
      </div>

      <!-- Session Panel -->
      <FgSessionPanel :taskId="activeTask.id" />

      <!-- Actions Dropdown -->
      <div class="q-mt-xl">
        <q-btn-dropdown
          unelevated
          color="white"
          text-color="black"
          icon="tune"
          label="Task Actions"
          size="lg"
          style="border-radius: 18px; min-height: 56px; font-weight: 600; min-width: 220px"
          content-class="bg-white text-dark shadow-10"
          content-style="border-radius: 16px; min-width: 260px"
        >
          <q-list separator class="q-py-sm">
            <!-- Complete -->
            <q-item clickable v-close-popup @click="completeTask(activeTask.id)" class="q-py-md">
              <q-item-section avatar>
                <q-avatar color="green-1" text-color="positive" icon="check" size="md" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Complete Task</q-item-label>
                <q-item-label caption>Mark done and remove from focus</q-item-label>
              </q-item-section>
              <q-tooltip anchor="center left" self="center right" :delay="400">Mark task as Done</q-tooltip>
            </q-item>

            <!-- Send to Inbox -->
            <q-item clickable v-close-popup @click="pauseTask(activeTask.id)" class="q-py-md">
              <q-item-section avatar>
                <q-avatar color="orange-1" text-color="orange-8" icon="inbox" size="md" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Send to Inbox</q-item-label>
                <q-item-label caption>Pause and focus on something else</q-item-label>
              </q-item-section>
              <q-tooltip anchor="center left" self="center right" :delay="400">Move task back to Inbox</q-tooltip>
            </q-item>

            <!-- Delete -->
            <q-item clickable v-close-popup @click="deleteTask(activeTask.id)" class="q-py-md">
              <q-item-section avatar>
                <q-avatar color="red-1" text-color="negative" icon="delete_outline" size="md" />
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold text-negative">Delete Task</q-item-label>
                <q-item-label caption>Permanently remove</q-item-label>
              </q-item-section>
              <q-tooltip anchor="center left" self="center right" :delay="400">Delete task permanently</q-tooltip>
            </q-item>
          </q-list>
        </q-btn-dropdown>
      </div>
    </template>


    <!-- Empty State -->
    <template v-else>
      <q-icon name="self_improvement" size="100px" color="grey-4" class="q-mb-lg" />
      <div class="text-h5 text-grey-7 text-weight-bold q-mb-sm text-center">Nothing in focus.</div>
      <p class="text-body1 text-grey-5 text-center q-mb-xl">Tap <strong>Plan</strong> to pick a task from your inbox and start crushing it.</p>
      <q-btn
        unelevated
        color="primary"
        label="Go to Plan"
        icon="event_note"
        size="lg"
        style="min-height: 56px; border-radius: 18px; font-size: 16px; min-width: 200px"
        @click="$emit('switch-tab', 1)"
      >
        <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
          Open your Inbox to choose a task to focus on.
        </q-tooltip>
      </q-btn>
    </template>

  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import FgSessionPanel from './FgSessionPanel.vue'

defineEmits(['switch-tab'])

const tasksStore = useFgTasksStore()

const activeTask = computed(() => {
  const active = tasksStore.activeTasks
  return active.length > 0 ? active[0] : null
})

const completeTask = async (id) => {
  await tasksStore.updateTask(id, { status: 'done' })
}

const pauseTask = async (id) => {
  await tasksStore.updateTask(id, { status: 'inbox' })
}

const deleteTask = async (id) => {
  await tasksStore.deleteTask(id)
}
</script>

<style scoped>
.fg-now-v2 {
  min-height: 60vh;
}
</style>
