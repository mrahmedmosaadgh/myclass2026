<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Student Dashboard</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Quick Stats -->
            <div class="bg-green-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-green-700 mb-2">Courses</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 8) }}</div>
                <div class="text-sm text-green-600 mt-1">Enrolled courses</div>
            </div>
            
            <div class="bg-orange-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-orange-700 mb-2">Assignments</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 15) }}</div>
                <div class="text-sm text-orange-600 mt-1">Due this week</div>
            </div>
            
            <div class="bg-indigo-50 p-4 rounded-lg shadow">
                <h4 class="font-semibold text-indigo-700 mb-2">Average Grade</h4>
                <div class="text-3xl font-bold">{{ Math.floor(Math.random() * 30) + 70 }}%</div>
                <div class="text-sm text-indigo-600 mt-1">Current semester</div>
            </div>
        </div>
        
        <div class="mt-8">
            <h4 class="font-semibold text-gray-700 mb-4">Upcoming Assignments</h4>
            <div class="border rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assignment</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Research Paper</td>
                            <td class="px-6 py-4 whitespace-nowrap">English Literature</td>
                            <td class="px-6 py-4 whitespace-nowrap">Tomorrow, 11:59 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Not Started
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Problem Set 5</td>
                            <td class="px-6 py-4 whitespace-nowrap">Calculus II</td>
                            <td class="px-6 py-4 whitespace-nowrap">Friday, 5:00 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    In Progress
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">Lab Report</td>
                            <td class="px-6 py-4 whitespace-nowrap">Chemistry</td>
                            <td class="px-6 py-4 whitespace-nowrap">Next Monday, 9:00 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Completed
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6 text-right">
            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                View All Assignments →
            </button>
        </div>
    </div>
</template>
