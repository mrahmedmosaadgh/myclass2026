<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  pendingJobs: Array,
  failedJobs: Array,
  stats: Object,
});

const retryJob = (id) => {
  if (confirm('Retry this job?')) {
    router.post(route('v2.super-system.jobs.retry', id));
  }
};

const retryAll = () => {
  if (confirm('Retry all failed jobs?')) {
    router.post(route('v2.super-system.jobs.retry-all'));
  }
};

const deleteJob = (id) => {
  if (confirm('Delete this failed job?')) {
    router.delete(route('v2.super-system.jobs.forget', id));
  }
};

const flushAll = () => {
  if (confirm('Delete ALL failed jobs? This cannot be undone.')) {
    router.delete(route('v2.super-system.jobs.flush'));
  }
};
</script>

<template>
  <AdminLayout>
    <Head title="Jobs Monitor" />

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
      
      <!-- Header -->
      <div class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Queue Jobs Monitor</h1>
          <p class="text-sm text-gray-500 mt-1">Monitor and manage background jobs</p>
        </div>
        <div class="flex space-x-3">
          <button @click="retryAll" v-if="stats.failed_count > 0" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Retry All Failed
          </button>
          <button @click="flushAll" v-if="stats.failed_count > 0" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Flush All Failed
          </button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 mb-8">
        <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-md bg-blue-100">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Jobs</dt>
                <dd class="text-lg font-bold text-gray-900">{{ stats.pending_count }}</dd>
              </dl>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm rounded-xl p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-md bg-red-100">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Failed Jobs</dt>
                <dd class="text-lg font-bold text-gray-900">{{ stats.failed_count }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Pending Jobs -->
      <div class="bg-white rounded-xl shadow-sm p-6 mb-8 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Pending Jobs</h2>
        <div v-if="pendingJobs.length === 0" class="text-center py-8 text-gray-500">
          No pending jobs
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attempts</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="job in pendingJobs" :key="job.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ job.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ job.name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.queue }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.attempts }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ job.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Failed Jobs -->
      <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Failed Jobs</h2>
        <div v-if="failedJobs.length === 0" class="text-center py-8 text-gray-500">
          No failed jobs
        </div>
        <div v-else class="space-y-4">
          <div v-for="job in failedJobs" :key="job.id" class="border border-gray-200 rounded-lg p-4">
            <div class="flex justify-between items-start mb-2">
              <div class="flex-1">
                <h3 class="text-sm font-medium text-gray-900">{{ job.name }}</h3>
                <p class="text-xs text-gray-500 mt-1">Queue: {{ job.queue }} | UUID: {{ job.uuid }}</p>
                <p class="text-xs text-gray-500">Failed at: {{ job.failed_at }}</p>
              </div>
              <div class="flex space-x-2">
                <button @click="retryJob(job.id)" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700">
                  Retry
                </button>
                <button @click="deleteJob(job.id)" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700">
                  Delete
                </button>
              </div>
            </div>
            <div class="mt-2 bg-red-50 border border-red-200 rounded p-2">
              <p class="text-xs font-mono text-red-800">{{ job.exception }}...</p>
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </AdminLayout>
</template>
