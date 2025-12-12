<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Super Admin Dashboard</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Quick Stats -->
            <div class="bg-blue-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-blue-700 mb-2">Users</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 2000) }}</div>
                <div class="text-sm text-blue-600 mt-1">Total users</div>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-green-700 mb-2">Schools</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 100) }}</div>
                <div class="text-sm text-green-600 mt-1">Active schools</div>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-purple-700 mb-2">System Health</h4>
                <div class="text-3xl font-bold">100%</div>
                <div class="text-sm text-purple-600 mt-1">All systems operational</div>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-yellow-700 mb-2">Storage</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 50) + 30 }}%</div>
                <div class="text-sm text-yellow-600 mt-1">Storage utilization</div>
            </div>
        </div>
        
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="font-semibold text-gray-700 mb-4">System Logs</h4>
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">10:45 AM</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        INFO
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">User login successful</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">10:30 AM</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        WARN
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Failed login attempt</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">10:15 AM</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        ERROR
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Database connection timeout</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold text-gray-700 mb-4">Recent Admin Actions</h4>
                <div class="border rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">John Admin</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Created new school</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">1 hour ago</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Sarah Admin</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Modified user permissions</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">3 hours ago</td>
                            </tr>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Mike Super</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">System backup</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">Yesterday</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">System Maintenance</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="font-medium">Next Scheduled Backup</div>
                        <div class="text-sm text-gray-500">Tomorrow, 2:00 AM</div>
                    </div>
                    <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                        Run Manual Backup
                    </button>
                </div>
                
                <div class="mt-4 pt-4 border-t">
                    <div class="font-medium mb-2">System Updates</div>
                    <div class="flex items-center justify-between">
                        <div class="text-sm">
                            New system update available (v2.5.3)
                        </div>
                        <button class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md text-sm">
                            Install Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
