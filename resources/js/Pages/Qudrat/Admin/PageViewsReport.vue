<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    totalViews: Number,
    uniquePages: Number,
    viewsByPage: Array,
    viewsByDate: Array,
    recentViews: Array,
});

// Format date for display
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
};
</script>

<template>
    <Head title="Page Views Report" />

    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="py-6 px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-bold leading-tight text-gray-900">Page Views Report</h1>
        </div>

        <!-- Stats Overview -->
        <div class="mx-4 sm:mx-6 md:mx-8 mb-6">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <!-- Total Views Card -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500">Total Views</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">{{ totalViews }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unique Pages Card -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500">Unique Pages</dt>
                                    <dd class="text-2xl font-semibold text-gray-900">{{ uniquePages }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Export Button Card -->
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 rounded-md bg-purple-500 p-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="truncate text-sm font-medium text-gray-500">Export Data</dt>
                                    <dd class="text-sm font-semibold text-gray-900">
                                        <a :href="route('admin.page-views.export')" 
                                           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                            Download CSV
                                        </a>
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Tables Section -->
        <div class="mx-4 mb-6 grid grid-cols-1 gap-6 sm:mx-6 md:mx-8 lg:grid-flow-col-dense lg:grid-cols-3">
            <!-- Views by Page Table -->
            <div class="lg:col-span-1">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Views by Page</h3>
                    </div>
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-for="page in viewsByPage" :key="page.page_name" class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-indigo-600 truncate">{{ page.page_name }}</div>
                                <div class="ml-2">
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ page.count }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Recent Views Table -->
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-lg bg-white shadow">
                    <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Recent Views</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="view in recentViews" :key="view.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ view.page_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ view.ip_address }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(view.created_at) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Views by Date Chart -->
        <div class="mx-4 mb-6 rounded-lg bg-white shadow sm:mx-6 md:mx-8">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Views Over Time (Last 30 Days)</h3>
            </div>
            <div class="p-4 sm:p-6">
                <div v-if="viewsByDate.length > 0" class="space-y-4">
                    <div v-for="day in viewsByDate.slice().reverse()" :key="day.date" class="flex items-center">
                        <div class="w-32 text-sm text-gray-600">{{ formatDate(day.date) }}</div>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div 
                                        class="bg-indigo-600 h-2.5 rounded-full" 
                                        :style="{ width: `${Math.min(100, (day.count / viewsByDate.reduce((max, item) => Math.max(max, item.count), 1)) * 100)}%` }"
                                    ></div>
                                </div>
                                <div class="ml-4 text-sm text-gray-600 w-10">{{ day.count }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8">
                    <p class="text-gray-500">No data available yet</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Additional styles if needed */
</style>