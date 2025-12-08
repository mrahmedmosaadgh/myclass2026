<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
const page = usePage();

const user = computed(() => page.props.auth?.user);
const role = computed(() => user.value?.role || 'guest');

// Common logout functionality
const logout = () => {
  // Implement logout logic
};
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Top Navigation Bar -->
    <nav class="bg-white border-b border-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <!-- Logo and Brand -->
          <div class="flex">
            <div class="shrink-0 flex items-center">
              <Link :href="route('dashboard')">
                <img class="block h-9 w-auto" src="/logo.svg" alt="MyClass LMS" />
              </Link>
            </div>
          </div>

          <!-- Navigation Links - Will be provided by child layouts -->
          <div class="hidden sm:flex sm:items-center sm:ml-6">
            <slot name="navigation"></slot>
          </div>

          <!-- Settings Dropdown -->
          <div class="hidden sm:flex sm:items-center sm:ml-6">
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
                <span class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                  {{ role }}
                </span>
              </div>
            </div>
          </div>

          <!-- Hamburger -->
          <div class="-mr-2 flex items-center sm:hidden">
            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Responsive Navigation Menu -->
      <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="sm:hidden">
        <slot name="responsive-navigation"></slot>
      </div>
    </nav>

    <!-- Sidebar and Main Content -->
    <div class="flex">
      <!-- Sidebar -->
      <aside class="w-64 bg-white shadow-sm h-screen fixed left-0 overflow-y-auto">
        <slot name="sidebar"></slot>
      </aside>

      <!-- Main Content -->
      <main class="flex-1 ml-64 p-8">
        <slot></slot>
      </main>
    </div>
  </div>
</template>
