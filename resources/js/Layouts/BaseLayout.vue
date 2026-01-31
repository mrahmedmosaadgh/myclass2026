<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ChatbotWidget from '@/Components/Chatbot/ChatbotWidget.vue';

const drawer = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role || 'guest');

// Common logout functionality
const logout = () => {
  // Implement logout logic
};
</script>

<template>
  <q-layout view="hHh LpR fFf" class="bg-gray-100">
    <!-- Top Navigation Bar -->
    <q-header elevated class="bg-white text-black border-b border-gray-100">
      <q-toolbar class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16">
        <!-- Drawer Toggle for Mobile -->
        <q-btn
          flat
          dense
          round
          icon="menu"
          aria-label="Menu"
          @click="drawer = !drawer"
          class="sm:hidden q-mr-sm"
        />

        <!-- Logo and Brand -->
        <div class="flex items-center shrink-0">
          <Link :href="route('dashboard')">
            <img class="block h-9 w-auto" src="/logo.svg" alt="MyClass LMS" />
          </Link>
        </div>

        <!-- Navigation Links (Desktop) -->
        <div class="hidden sm:flex sm:items-center sm:ml-6">
          <slot name="navigation"></slot>
        </div>

        <q-toolbar-title></q-toolbar-title>

        <!-- Settings Dropdown -->
        <div class="flex items-center sm:ml-6">
          <div class="ml-3 relative">
            <div class="flex items-center">
              <!-- User Profile -->
              <button class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                <img v-if="user?.profile_photo_url" :src="user.profile_photo_url" class="h-8 w-8 rounded-full object-cover" />
                <span v-else class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-500">
                  <span class="text-sm font-medium leading-none text-white">
                    {{ user?.name?.[0] || 'U' }}
                  </span>
                </span>
              </button>
              
              <!-- Role Badge -->
              <span class="ml-2 hidden sm:inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                {{ role }}
              </span>
            </div>
          </div>
        </div>
      </q-toolbar>
    </q-header>

    <!-- Sidebar / Drawer -->
    <q-drawer
      v-model="drawer"
      show-if-above
      bordered
      :width="260"
      :breakpoint="640"
      class="bg-white"
    >
      <q-scroll-area class="fit">
        <slot name="sidebar"></slot>
      </q-scroll-area>
    </q-drawer>

    <!-- Main Content -->
    <q-page-container>
      <q-page class="q-pa-lg">
        <div class="max-w-7xl mx-auto">
          <slot></slot>
        </div>
      </q-page>
    </q-page-container>
    
    <ChatbotWidget />
  </q-layout>
</template>

