<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  envVars: Array,
  maintenanceMode: Boolean,
  cacheStatus: Object,
});

const clearCache = (type) => {
  if (confirm(`Clear ${type} cache?`)) {
    router.post(route('v2.super-system.config.clear-cache'), { type });
  }
};

const cacheAll = () => {
  if (confirm('Cache all configuration?')) {
    router.post(route('v2.super-system.config.cache'));
  }
};

const toggleMaintenance = () => {
  const action = props.maintenanceMode ? 'disable' : 'enable';
  if (confirm(`Are you sure you want to ${action} maintenance mode?`)) {
    router.post(route('v2.super-system.config.maintenance'));
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="System Configuration" />

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
      
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">System Configuration</h1>
        <p class="text-sm text-gray-500 mt-1">Manage application configuration and cache</p>
      </div>

      <!-- Maintenance Mode -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-medium text-gray-900">Maintenance Mode</h2>
            <p class="text-sm text-gray-500 mt-1">
              {{ maintenanceMode ? 'Application is currently in maintenance mode' : 'Application is live' }}
            </p>
          </div>
          <button @click="toggleMaintenance" :class="maintenanceMode ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700'" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ maintenanceMode ? 'Disable' : 'Enable' }} Maintenance
          </button>
        </div>
      </div>

      <!-- Cache Management -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Cache Management</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-gray-900">Config Cache</h3>
              <span :class="cacheStatus.config ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-medium rounded">
                {{ cacheStatus.config ? 'Cached' : 'Not Cached' }}
              </span>
            </div>
            <button @click="clearCache('config')" class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Clear Config
            </button>
          </div>

          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-gray-900">Routes Cache</h3>
              <span :class="cacheStatus.routes ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-medium rounded">
                {{ cacheStatus.routes ? 'Cached' : 'Not Cached' }}
              </span>
            </div>
            <button @click="clearCache('routes')" class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Clear Routes
            </button>
          </div>

          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-gray-900">Views Cache</h3>
              <span :class="cacheStatus.views ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" class="px-2 py-1 text-xs font-medium rounded">
                {{ cacheStatus.views ? 'Cached' : 'Not Cached' }}
              </span>
            </div>
            <button @click="clearCache('views')" class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
              Clear Views
            </button>
          </div>

          <div class="border border-gray-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-gray-900">All Cache</h3>
            </div>
            <button @click="clearCache('all')" class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-red-300 shadow-sm text-xs font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
              Clear All
            </button>
            <button @click="cacheAll" class="mt-2 w-full inline-flex justify-center items-center px-3 py-2 border border-green-300 shadow-sm text-xs font-medium rounded-md text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
              Cache All
            </button>
          </div>
        </div>
      </div>

      <!-- Environment Variables -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Environment Variables</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Key</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(envVar, index) in envVars" :key="index">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ envVar.key }}
                  <span v-if="envVar.is_sensitive" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                    Sensitive
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                  {{ envVar.value }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
          <p class="text-sm text-yellow-800">
            <strong>Note:</strong> Sensitive values are masked. To edit environment variables, modify the <code class="bg-yellow-100 px-1 rounded">.env</code> file directly.
          </p>
        </div>
      </div>
      
    </div>
  </AdminLayout>
</template>
