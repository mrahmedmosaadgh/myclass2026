<script setup>
import { Link } from '@inertiajs/vue3';
import { useRole } from '../composables/useRole';

const props = defineProps({
  title: String,
});

const { currentRole } = useRole();
</script>

<template>
  <div class="min-h-screen bg-gray-100 flex flex-col">
    <!-- Top Navigation -->
    <nav class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center shrink-0">
              <span class="text-xl font-bold text-indigo-600">MyClass 2026</span>
              <span class="ml-2 px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 capitalize">{{ currentRole }}</span>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <!-- Notifications placeholder -->
            <button class="text-gray-400 hover:text-gray-500">
               <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
               </svg>
            </button>
          </div>
        </div>
      </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-1 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-center justify-between" v-if="title || $slots.header">
          <h1 v-if="title" class="text-2xl font-bold text-gray-900">{{ title }}</h1>
          <slot name="header"></slot>
        </div>
        <slot></slot>
      </div>
    </main>
  </div>
</template>
