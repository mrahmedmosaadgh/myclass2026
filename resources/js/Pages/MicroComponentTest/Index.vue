<template>
  <Head title="Micro Component Test" />

  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Micro Component Test</h1>
    <p class="text-gray-600 mb-6">Use this page to experiment with small components (dropdown + button) for testing and versioning.</p>

    <div class="bg-white rounded-lg shadow p-6">
      <label class="block text-sm font-medium text-gray-700 mb-2">Choose an option</label>
      <MicroDropdown v-model="selected" :options="options" />

      <div class="mt-4 flex items-center gap-3">
        <button @click="showSelected"
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Show Selected</button>

        <div class="text-sm text-gray-700">Current: <strong class="ml-2">{{ selected || 'None' }}</strong></div>
      </div>
    </div>

    <div class="mt-6 text-xs text-gray-500">This page and components are intentionally minimal so you can iterate and copy them elsewhere when ready.</div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayoutDefault from '@/Layouts/AppLayoutDefault.vue';
import MicroDropdown from './MicroDropdown.vue';

defineOptions({
  layout: AppLayoutDefault
});

const selected = ref('');
const options = [
  { value: '', label: 'Select an option' },
  { value: 'alpha', label: 'Alpha' },
  { value: 'beta', label: 'Beta' },
  { value: 'gamma', label: 'Gamma' }
];

function showSelected() {
  // simple demonstration; you can replace with more advanced test logic
  alert(`Selected: ${selected.value || 'None'}`);
}
</script>

<style scoped>
/* small styling if needed */
</style>
