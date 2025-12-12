<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Admin Dashboard</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Quick Stats -->
            <div class="bg-blue-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-blue-700 mb-2">Users</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 1000) }}</div>
                <div class="text-sm text-blue-600 mt-1">Total registered users</div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-green-700 mb-2">Schools</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 50) }}</div>
                <div class="text-sm text-green-600 mt-1">Active schools</div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-purple-700 mb-2">Classes</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 200) }}</div>
                <div class="text-sm text-purple-600 mt-1">Total classes</div>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">Recent Activity</h4>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">John Doe</td>
                            <td class="px-6 py-4 whitespace-nowrap">Created new class</td>
                            <td class="px-6 py-4 whitespace-nowrap">2 hours ago</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Jane Smith</td>
                            <td class="px-6 py-4 whitespace-nowrap">Updated school information</td>
                            <td class="px-6 py-4 whitespace-nowrap">5 hours ago</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Mike Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap">Added new teacher</td>
                            <td class="px-6 py-4 whitespace-nowrap">Yesterday</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-right">
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Activity →
            </button>
        </div>
    </div>
</template>
