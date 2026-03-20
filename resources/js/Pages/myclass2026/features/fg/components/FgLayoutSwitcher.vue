<template>
  <div class="fg-layout-switcher row q-gutter-xs items-center">
    <span class="text-caption text-grey-6 q-mr-xs">Layout</span>
    <q-btn-toggle
      :model-value="uiStore.layout"
      @update:model-value="uiStore.setLayout"
      dense
      unelevated
      toggle-color="primary"
      color="grey-2"
      text-color="grey-8"
      :options="[
        { label: 'V1', value: 'v1' },
        { label: 'V2', value: 'v2' },
        { label: 'V3', value: 'v3' }
      ]"
    />
  </div>
</template>

<script setup>
import { useFgUiStore } from '../stores/fg-ui.store'

const uiStore = useFgUiStore()
</script>
