<template>
  <div class="fg-planning-v2 q-pa-md column q-gutter-lg">

    <!-- Dump / Vent -->
    <FgVentingArea />

    <!-- Inbox Tasks -->
    <div>
      <div class="row items-center justify-between q-mb-md">
        <div class="text-subtitle1 text-weight-bold text-dark">
          <q-icon name="inbox" color="orange-8" class="q-mr-xs" />
          Inbox
          <q-badge v-if="inboxTasks.length" rounded color="orange-8" class="q-ml-sm">
            {{ inboxTasks.length }}
          </q-badge>
        </div>
        <q-btn
          flat
          dense
          icon="filter_list"
          color="grey-7"
          @click="showAll = !showAll"
          :label="showAll ? 'Inbox only' : 'Show all'"
        />
      </div>

      <!-- Empty inbox -->
      <div v-if="displayTasks.length === 0" class="text-center q-py-xl text-grey-5">
        <q-icon name="done_all" size="3rem" class="q-mb-sm" />
        <div class="text-body1">All clear! Nothing in inbox.</div>
      </div>

      <!-- Task Cards -->
      <div class="column q-gutter-sm">
        <q-card
          v-for="task in displayTasks"
          :key="task.id"
          flat
          bordered
          class="fg-v2-task-card"
          style="border-radius: 16px"
        >
          <q-card-section class="q-py-md q-px-lg">
            <div class="row items-start no-wrap q-gutter-sm">
              <!-- Status / Start -->
              <q-btn
                v-if="task.status === 'inbox'"
                unelevated
                round
                color="primary"
                icon="play_arrow"
                size="sm"
                style="min-width: 44px; min-height: 44px"
                @click="makeActive(task)"
              >
                <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
                  Set this as your <strong>active focus</strong> and move it to the Now view.
                </q-tooltip>
              </q-btn>
              <q-btn
                v-else-if="task.status === 'active'"
                unelevated
                round
                color="teal"
                icon="radio_button_checked"
                size="sm"
                style="min-width: 44px; min-height: 44px"
                @click="$emit('switch-tab', 0)"
              >
                <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
                  This is your current focus. Tap to go to the <strong>Now</strong> view.
                </q-tooltip>
              </q-btn>
              <q-btn
                v-else
                unelevated
                round
                color="positive"
                icon="check"
                size="sm"
                style="min-width: 44px; min-height: 44px"
                disable
              >
                <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
                  Task completed. Nice work! 🎉
                </q-tooltip>
              </q-btn>

              <!-- Task Content -->
              <div class="col">
                <div
                  class="text-body1 text-weight-medium"
                  :class="{ 'text-strike text-grey-5': task.status === 'done' }"
                >
                  {{ task.title }}
                </div>
                <div class="text-caption text-grey-6 q-mt-xs" v-if="task.notes">
                  {{ task.notes }}
                </div>
                <div class="row q-gutter-xs q-mt-sm items-center">
                  <q-badge v-if="task.status === 'active'" color="teal-1" text-color="teal-8" rounded class="q-px-sm">
                    Active
                  </q-badge>
                  <q-badge v-else-if="task.status === 'done'" color="green-1" text-color="green-8" rounded class="q-px-sm">
                    Done
                  </q-badge>
                  <q-badge color="grey-2" text-color="grey-7" rounded class="q-px-sm" v-if="task.is_today">
                    ⭐ Today
                  </q-badge>
                </div>
              </div>

              <!-- Delete -->
              <q-btn
                flat
                round
                dense
                color="red-3"
                icon="delete_outline"
                style="min-width: 44px; min-height: 44px"
                @click="deleteTask(task)"
              >
                <q-tooltip anchor="top middle" self="bottom middle" :delay="400">
                  Delete this task permanently.
                </q-tooltip>
              </q-btn>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import FgVentingArea from './FgVentingArea.vue'

defineEmits(['switch-tab'])

const tasksStore = useFgTasksStore()
const showAll = ref(false)

const inboxTasks = computed(() => tasksStore.tasks.filter(t => t.status === 'inbox'))

const displayTasks = computed(() =>
  showAll.value
    ? tasksStore.tasks.filter(t => t.status !== 'done' || t.is_today)
    : inboxTasks.value
)

const makeActive = async (task) => {
  await tasksStore.updateTask(task.id, { status: 'active', is_today: true })
}

const deleteTask = async (task) => {
  await tasksStore.deleteTask(task.id)
}
</script>

<style scoped>
.fg-v2-task-card {
  transition: box-shadow 0.2s ease;
}
.fg-v2-task-card:active {
  box-shadow: 0 0 0 2px var(--q-primary);
}
</style>
