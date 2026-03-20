<template>
  <div class="column q-gutter-y-lg q-pa-md">
    
    <!-- Planning Header -->
    <div class="row items-center justify-between">
      <div class="text-h5 text-primary text-weight-bold">
        <q-icon name="lightbulb" size="sm" class="q-mr-sm" />
        Morning Setup
      </div>
      <q-btn flat icon="close" @click="uiStore.toggleMode('planning')" label="Back to Focus" />
    </div>
    
    <q-separator />

    <!-- Table View mapping to State -->
    <div class="col">
      <FgTableView />
    </div>

  </div>
</template>

<script setup>
import { useFgUiStore } from './stores/fg-ui.store'
import FgTableView from './components/FgTableView.vue'

const uiStore = useFgUiStore()
</script>
