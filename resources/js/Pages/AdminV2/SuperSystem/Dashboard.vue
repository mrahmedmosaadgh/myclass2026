<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref } from 'vue';

const props = defineProps({
  metrics: Object,
  recentLogs: Array,
});

const quickStats = [
  { name: 'System Status', value: props.metrics.system_status, icon: 'shield-check', color: 'text-green-600', bg: 'bg-green-100' },
  { name: 'Avg Response Time', value: '124ms', icon: 'zap', color: 'text-yellow-600', bg: 'bg-yellow-100' },
  { name: 'Active Jobs', value: props.metrics.active_jobs, icon: 'server', color: 'text-blue-600', bg: 'bg-blue-100' },
  { name: 'Error Rate', value: props.metrics.error_rate, icon: 'alert-triangle', color: 'text-red-600', bg: 'bg-red-100' },
];

const systemResources = [
    { name: 'CPU Usage', value: props.metrics.cpu_usage, color: 'bg-blue-500' },
    { name: 'Memory Usage', value: props.metrics.memory_usage, color: 'bg-purple-500' },
    { name: 'Disk Usage', value: props.metrics.disk_usage, color: 'bg-indigo-500' },
];
</script>

<template>
  <AdminLayout>
    <Head title="SuperSystem Dashboard" />

    <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
      
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">SuperSystem Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Global System Monitoring & Control Center</p>
            </div>
            <div class="flex space-x-3">
                <Link :href="route('v2.super-system.config')" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                    Configuration
                </Link>
                <Link :href="route('v2.super-system.logs')" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    View Logs
                </Link>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div v-for="stat in quickStats" :key="stat.name" class="bg-white overflow-hidden shadow-sm rounded-xl hover:shadow-md transition-shadow duration-300 pb-2">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div :class="`p-3 rounded-md ${stat.bg}`">
                                <!-- Simple SVG Icons based on name -->
                                <svg v-if="stat.icon === 'shield-check'" class="w-6 h-6" :class="stat.color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg v-else-if="stat.icon === 'zap'" class="w-6 h-6" :class="stat.color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <svg v-else-if="stat.icon === 'server'" class="w-6 h-6" :class="stat.color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path></svg>
                                <svg v-else-if="stat.icon === 'alert-triangle'" class="w-6 h-6" :class="stat.color" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ stat.name }}</dt>
                                <dd>
                                    <div class="text-lg font-bold text-gray-900">{{ stat.value }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- System Resources -->
            <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-1 border border-gray-100">
                <h2 class="text-lg font-medium text-gray-900 mb-6">System Resources</h2>
                <div class="space-y-6">
                    <div v-for="resource in systemResources" :key="resource.name">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-600">{{ resource.name }}</span>
                            <span class="text-sm font-bold text-gray-900">{{ resource.value }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div :class="`h-2.5 rounded-full ${resource.color}`" :style="`width: ${resource.value}`"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h3 class="text-sm font-medium text-gray-900 mb-3">Database Health</h3>
                    <div class="flex items-center space-x-2 text-sm text-green-600">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span>Connected (Latency: 2ms)</span>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-2 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-medium text-gray-900">Recent System Logs</h2>
                    <Link :href="route('v2.super-system.logs')" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View all</Link>
                </div>
                
                <div class="flow-root">
                    <ul role="list" class="-my-5 divide-y divide-gray-100">
                        <li v-for="log in recentLogs" :key="log.id" class="py-4">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <span v-if="log.level === 'info'" class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-4 ring-white">
                                        <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                    <span v-else-if="log.level === 'warning'" class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center ring-4 ring-white">
                                        <svg class="h-4 w-4 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    </span>
                                    <span v-else-if="log.level === 'error'" class="h-8 w-8 rounded-full bg-red-100 flex items-center justify-center ring-4 ring-white">
                                        <svg class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    </span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ log.message }}</p>
                                    <p class="text-xs text-gray-500">System Module</p>
                                </div>
                                <div class="inline-flex items-center text-xs font-semibold text-gray-500">
                                    {{ log.created_at }}
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
        
    </div>
  </AdminLayout>
</template>
