<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Parent Dashboard</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Quick Stats -->
            <div class="bg-purple-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-purple-700 mb-2">Children</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 3) + 1 }}</div>
                <div class="text-sm text-purple-600 mt-1">Enrolled students</div>
            </div>
            
            <div class="bg-teal-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-teal-700 mb-2">Upcoming Events</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 5) + 2 }}</div>
                <div class="text-sm text-teal-600 mt-1">School events this month</div>
            </div>
            
            <div class="bg-pink-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-pink-700 mb-2">Messages</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 10) }}</div>
                <div class="text-sm text-pink-600 mt-1">Unread messages</div>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">Children's Performance</h4>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Child</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Attendance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Standing</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Emma Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap">10th Grade</td>
                            <td class="px-6 py-4 whitespace-nowrap">98%</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Excellent
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Michael Johnson</td>
                            <td class="px-6 py-4 whitespace-nowrap">7th Grade</td>
                            <td class="px-6 py-4 whitespace-nowrap">92%</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Good
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">Upcoming School Events</h4>
            <div class="space-y-4">
                <div class="border rounded-lg p-4">
                    <div class="font-medium">Parent-Teacher Conference</div>
                    <div class="text-sm text-gray-500">May 15, 2023 • 4:00 PM - 7:00 PM</div>
                    <div class="mt-2 text-sm">Discuss your child's academic progress with their teachers.</div>
                </div>
                <div class="border rounded-lg p-4">
                    <div class="font-medium">School Science Fair</div>
                    <div class="text-sm text-gray-500">May 22, 2023 • 1:00 PM - 4:00 PM</div>
                    <div class="mt-2 text-sm">Students will present their science projects to judges and visitors.</div>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-right">
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Events →
            </button>
        </div>
    </div>
</template>
