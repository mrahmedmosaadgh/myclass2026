<template>
  <q-card flat bordered class="q-mb-md bg-white">
    <div class="row items-center q-px-md">
      <div class="text-h6 q-mr-lg text-primary text-weight-bold">Weekly Plan</div>
      <q-separator vertical inset class="q-mr-md" />
      
      <q-tabs
        v-model="currentTab"
        dense
        class="text-grey-7"
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
        no-caps
        inline-label
      >
        <template v-for="item in menuItems" :key="item.route">
           <Link
             :href="route(item.route)"
             custom
             v-slot="slotProps"
           >
             <q-tab
               :name="item.route"
               :label="item.label"
               :icon="item.icon"
               @click="slotProps?.navigate"
               :class="{ 'text-primary': slotProps?.isActive }"
             />
           </Link>
        </template>
      </q-tabs>
      
      <q-space />
      
      <q-btn
        flat
        round
        dense
        icon="info"
        color="primary"
        @click="showGuide = true"
      >
        <q-tooltip>Weekly System Guide</q-tooltip>
      </q-btn>
    </div>
    
    <WeeklySystemGuide v-model="showGuide" />
  </q-card>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import WeeklySystemGuide from './components/WeeklySystemGuide.vue';

const showGuide = ref(false);

const menuItems = [
  // Admin Routes
  { label: 'Manager', route: 'weekly-system.weekly-plans-manager', icon: 'dashboard' },
  { label: 'Schedule Copies', route: 'weekly-system.schedule-copies.index', icon: 'content_copy' },
  { label: 'Timetable', route: 'weekly-system.timetable-editor', icon: 'edit_calendar' },
  
  // Teacher Routes
  { label: 'My Schedule', route: 'weekly-system.my-schedule', icon: 'schedule' },
  { label: 'My Plans', route: 'weekly-system.my-weekly-plans', icon: 'event_note' },
];

const currentTab = computed(() => {
  // Finds the first matching route from the menu items
  // This allows the tab to be highlighted even if the current route is a child or related
  // But for simple top-level nav, exact match on route name is usually fine.
  // Using route().current() which supports wildcards if needed: route().current('admin.posts.*')
  
  for (const item of menuItems) {
    if (route().current(item.route)) {
        return item.route;
    }
  }
  return null;
});
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>
