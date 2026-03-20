<template>
  <q-card class="fg-table-view no-shadow bordered">
    
    <!-- Table Toolbar -->
    <q-toolbar class="bg-grey-1 text-dark q-py-sm">
      <q-input dense outlined v-model="uiStore.filters.search" placeholder="Search tasks..." class="col-grow q-mr-md" clearable>
        <template v-slot:append>
          <q-icon name="search" />
        </template>
      </q-input>
      
      <q-select 
        dense outlined 
        v-model="uiStore.filters.status" 
        :options="['inbox', 'active', 'done']" 
        label="Status"
        class="q-mr-sm"
        style="width: 120px"
      />
      
      <q-select 
        dense outlined 
        v-model="uiStore.filters.domainId" 
        :options="domainOptions" 
        label="Domain"
        clearable
        style="width: 150px"
      />
    </q-toolbar>

    <!-- Data Table -->
    <q-table
      flat
      :rows="filteredTasks"
      :columns="columns"
      row-key="id"
      :filter="uiStore.filters.search"
      :pagination="initialPagination"
    >
      <!-- Title Column Slot -->
      <template v-slot:body-cell-title="props">
        <q-td :props="props">
          <div class="text-subtitle2" :class="{'text-strike text-grey': props.row.status === 'done'}">
             {{ props.row.title }}
          </div>
          <div class="text-caption text-grey-6">{{ props.row.notes }}</div>
        </q-td>
      </template>

      <!-- Actions Column Slot -->
      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="text-right">
          <q-btn flat round dense color="primary" icon="play_arrow" @click="makeActive(props.row)" v-if="props.row.status === 'inbox'">
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">Set as active focus — moves to Now view.</q-tooltip>
          </q-btn>
          <q-btn flat round dense color="positive" icon="check" @click="markDone(props.row)" v-if="props.row.status !== 'done'">
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">Mark this task as Done.</q-tooltip>
          </q-btn>
          <q-btn flat round dense color="negative" icon="delete" @click="deleteTask(props.row)">
            <q-tooltip anchor="top middle" self="bottom middle" :delay="400">Delete this task permanently.</q-tooltip>
          </q-btn>
        </q-td>
      </template>
    </q-table>
  </q-card>
</template>

<script setup>
import { computed } from 'vue'
import { useFgTasksStore } from '../stores/fg-tasks.store'
import { useFgDomainsStore } from '../stores/fg-domains.store'
import { useFgUiStore } from '../stores/fg-ui.store'
import { useFgPriority } from '../composables/fg-use-priority'

const tasksStore = useFgTasksStore()
const domainsStore = useFgDomainsStore()
const uiStore = useFgUiStore()

const domainOptions = computed(() => {
  return domainsStore.activeDomains.map(d => ({ label: `${d.emoji || ''} ${d.name}`, value: d.id }))
})

// Columns definition
const columns = [
  { name: 'title', align: 'left', label: 'Task', field: 'title', sortable: true },
  { name: 'importance', align: 'center', label: 'Imp.', field: 'importance', sortable: true },
  { name: 'is_today', align: 'center', label: 'Today', field: 'is_today', format: val => val ? '⭐' : '', sortable: true },
  { name: 'actions', align: 'right', label: 'Actions' }
]

const initialPagination = { rowsPerPage: 15 }

// Use priority composable sorting
const sourceList = computed(() => {
  return tasksStore.tasks.filter(t => {
     let match = true
     if (uiStore.filters.status && t.status !== uiStore.filters.status) match = false
     if (uiStore.filters.domainId && t.domain_id !== uiStore.filters.domainId.value) match = false
     return match
  })
})

const { sortedTasks: filteredTasks } = useFgPriority(sourceList)

const makeActive = async (task) => {
  await tasksStore.updateTask(task.id, { status: 'active', is_today: true })
}

const markDone = async (task) => {
  await tasksStore.updateTask(task.id, { status: 'done' })
}

const deleteTask = async (task) => {
  await tasksStore.deleteTask(task.id)
}
</script>
