<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  logs: Array,
  filters: Object,
  stats: Object,
});

const levelFilter = ref(props.filters.level);
const searchQuery = ref(props.filters.search);

const applyFilters = () => {
  router.get(route('v2.super-system.logs'), {
    level: levelFilter.value,
    search: searchQuery.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  });
};

const clearLogs = () => {
  if (confirm('Clear all logs? This cannot be undone.')) {
    router.post(route('v2.super-system.logs.clear'));
  }
};

const downloadLogs = () => {
  window.location.href = route('v2.super-system.logs.download');
};

const getLevelColor = (level) => {
  const colors = {
    'ERROR': 'bg-red-100 text-red-800 border-red-200',
    'WARNING': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'INFO': 'bg-blue-100 text-blue-800 border-blue-200',
    'DEBUG': 'bg-gray-100 text-gray-800 border-gray-200',
  };
  return colors[level] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const formatFileSize = (bytes) => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
};
</script>

<template>
  <AdminLayout>
    <Head title="System Logs" />

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
      
      <!-- Header -->
      <div class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">System Logs</h1>
          <p class="text-sm text-gray-500 mt-1">View and manage application logs ({{ formatFileSize(stats.file_size) }})</p>
        </div>
        <div class="flex space-x-3">
          <button @click="downloadLogs" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
            Download
          </button>
          <button @click="clearLogs" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Clear Logs
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-6 border border-gray-100">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
            <select v-model="levelFilter" @change="applyFilters" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <option value="all">All Levels</option>
              <option value="error">Error</option>
              <option value="warning">Warning</option>
              <option value="info">Info</option>
              <option value="debug">Debug</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <div class="flex space-x-2">
              <input v-model="searchQuery" @keyup.enter="applyFilters" type="text" placeholder="Search logs..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
              <button @click="applyFilters" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Search
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Logs -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Log Entries ({{ stats.total }})</h2>
        <div v-if="logs.length === 0" class="text-center py-8 text-gray-500">
          No logs found
        </div>
        <div v-else class="space-y-3">
          <div v-for="(log, index) in logs" :key="index" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-2">
              <div class="flex items-center space-x-3">
                <span :class="`px-2 py-1 text-xs font-medium rounded border ${getLevelColor(log.level)}`">
                  {{ log.level }}
                </span>
                <span class="text-xs text-gray-500">{{ log.timestamp }}</span>
              </div>
            </div>
            <p class="text-sm text-gray-900 mb-2">{{ log.message }}</p>
            <div v-if="log.context && log.context.trim()" class="mt-2 bg-gray-50 border border-gray-200 rounded p-3">
              <pre class="text-xs font-mono text-gray-700 whitespace-pre-wrap overflow-x-auto">{{ log.context }}</pre>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </AdminLayout>
</template>
