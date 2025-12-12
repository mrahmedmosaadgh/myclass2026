<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const user = computed(() => usePage().props.auth.user);
</script>

<template>
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Welcome to MyClass</h3>
        
        <div class="bg-blue-50 p-4 rounded-lg mb-6">
            <p class="text-blue-700">
                Hello {{ user.name }}, welcome to the MyClass platform. Your account is currently being set up.
                Please contact an administrator if you need assistance.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-2">Getting Started</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Complete your profile information
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Set up notification preferences
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Explore the platform features
                    </li>
                </ul>
            </div>
            
            <div class="border rounded-lg p-4">
                <h4 class="font-semibold text-gray-700 mb-2">Help & Support</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <a href="#" class="text-blue-600 hover:underline">View FAQs</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <a href="#" class="text-blue-600 hover:underline">Contact Support</a>
                    </li>
                    <li class="flex items-center">
                        <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <a href="#" class="text-blue-600 hover:underline">User Guide</a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="mt-8 bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-700 mb-2">System Announcements</h4>
            <div class="space-y-4">
                <div class="border-b pb-3">
                    <div class="font-medium">Platform Maintenance</div>
                    <div class="text-sm text-gray-500">April 30, 2023</div>
                    <div class="mt-1 text-sm">Scheduled maintenance will occur this weekend. The system may be unavailable for short periods.</div>
                </div>
                <div>
                    <div class="font-medium">New Features Released</div>
                    <div class="text-sm text-gray-500">April 25, 2023</div>
                    <div class="mt-1 text-sm">We've added new features to improve your experience. Check out the What's New section.</div>
                </div>
            </div>
        </div>
    </div>
</template>
