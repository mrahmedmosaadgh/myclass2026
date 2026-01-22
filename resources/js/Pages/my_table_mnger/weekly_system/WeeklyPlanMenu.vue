<template>
  <!-- Main School Data Component - should be at the top of the weekly plan system -->
  <MainSchoolData />

  <q-card flat bordered class="q-mb-md bg-white">
    <div class="row items-center q-px-md"> 
      <div class="text-h6 q-mr-lg text-primary text-weight-bold">{{ t('weeklyPlans.weeklyLearningPlan') }}</div>
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
               :label="t(item.labelKey)"
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
        <q-tooltip>{{ t('weeklySystem.weeklyPlansManager.title') }}</q-tooltip>
      </q-btn>
    </div>
    
    <WeeklySystemGuide v-model="showGuide" />
  </q-card>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import WeeklySystemGuide from './components/WeeklySystemGuide.vue';
import MainSchoolData from './components/MainSchoolData.vue'; // Import the component from the shared components directory
// According to the specification, this component is shared across the weekly system
// but currently located in the admin section

const { t } = useI18n();
const showGuide = ref(false);

const menuItems = [
  // Admin Routes
  { labelKey: 'weeklySystem.menu.schoolBrowser', route: 'weekly-system.school-browser', icon: 'school' },
  { labelKey: 'weeklySystem.menu.manager', route: 'weekly-system.weekly-plans-manager', icon: 'dashboard' },
  { labelKey: 'weeklySystem.menu.timetable', route: 'admin.schedules.dashboard', icon: 'edit_calendar' },
  
  // Teacher Routes
  { labelKey: 'weeklySystem.menu.mySchedule', route: 'weekly-system.my-schedule', icon: 'schedule' },
  { labelKey: 'weeklySystem.menu.myPlans', route: 'weekly-system.my-weekly-plans', icon: 'event_note' },
];

const currentTab = computed({
  get: () => {
    // Finds the first matching route from the menu items
    for (const item of menuItems) {
      if (route().current(item.route)) {
          return item.route;
      }
    }
    return null;
  },
  set: (val) => {
    // No-op setter to avoid "computed value is readonly" warning
    // Navigation is handled by the Link component or click handler
  }
});
</script>

<style scoped>
/* Add any custom styles here if needed */
</style>