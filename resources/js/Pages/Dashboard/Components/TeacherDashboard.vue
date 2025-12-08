<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Teacher Dashboard</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Quick Stats -->
            <div class="bg-blue-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-blue-700 mb-2">Classes</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 10) }}</div>
                <div class="text-sm text-blue-600 mt-1">Active classes</div>
            </div>
            
            <div class="bg-yellow-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-yellow-700 mb-2">Students</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 200) }}</div>
                <div class="text-sm text-yellow-600 mt-1">Total students</div>
            </div>
            
            <div class="bg-red-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-red-700 mb-2">Assignments</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 30) }}</div>
                <div class="text-sm text-red-600 mt-1">Pending review</div>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">Upcoming Classes</h4>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Students</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Mathematics 101</td>
                            <td class="px-6 py-4 whitespace-nowrap">9:00 AM - 10:30 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap">Room 203</td>
                            <td class="px-6 py-4 whitespace-nowrap">32</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Physics 202</td>
                            <td class="px-6 py-4 whitespace-nowrap">11:00 AM - 12:30 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap">Lab 105</td>
                            <td class="px-6 py-4 whitespace-nowrap">24</td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Computer Science 301</td>
                            <td class="px-6 py-4 whitespace-nowrap">2:00 PM - 3:30 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap">Computer Lab</td>
                            <td class="px-6 py-4 whitespace-nowrap">18</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-right">
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View Full Schedule →
            </button>
        </div>
    </div>
</template>
