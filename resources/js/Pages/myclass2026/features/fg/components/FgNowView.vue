<template>
  <div class="fg-now-view row q-col-gutter-lg justify-center">
    <!-- Main Focus Area -->
    <div class="col-12 col-md-8 col-lg-6 text-center q-py-xl">
      <div v-if="activeTask">
         <q-badge color="accent" rounded class="q-py-xs q-px-sm q-mb-md">CURRENT FOCUS</q-badge>
         <div class="text-h3 text-weight-bold q-mb-sm">{{ activeTask.title }}</div>
         <div class="text-subtitle1 text-grey-8 q-mb-xl" v-if="activeTask.notes">{{ activeTask.notes }}</div>
         
         <div class="row justify-center q-gutter-md q-mb-xl">
            <q-btn outline color="primary" label="Complete Task" icon="check" @click="completeTask(activeTask.id)" />
            <q-btn flat color="grey-7" label="Pause / Send to Inbox" icon="pause" @click="pauseTask(activeTask.id)" />
         </div>
         
         <!-- Session Panel Included Here -->
         <FgSessionPanel :taskId="activeTask.id" />
      </div>
      
      <div v-else class="q-pa-xl text-grey-6 text-center">
         <q-icon name="self_improvement" size="6rem" class="q-mb-md opacity-50" />
         <div class="text-h5">No active task.</div>
         <p class="q-mt-sm">Go to planning or your inbox to select a focus.</p>
         <q-btn unelevated color="primary" label="Plan My Day" class="q-mt-md" @click="uiStore.toggleMode('planning')" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import { useFgUiStore } from '../stores/fg-ui.store'
import FgSessionPanel from './FgSessionPanel.vue'

const tasksStore = useFgTasksStore()
const uiStore = useFgUiStore()

const activeTask = computed(() => {
  // Now View technically supports ONE true focus. We take the first active task.
  const active = tasksStore.activeTasks
  return active.length > 0 ? active[0] : null
})

const completeTask = async (id) => {
  await tasksStore.updateTask(id, { status: 'done' })
}

const pauseTask = async (id) => {
  await tasksStore.updateTask(id, { status: 'inbox' })
}
</script>
