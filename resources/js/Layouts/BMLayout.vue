<template>
  <q-layout view="hHh Lpr lff" class="bm-layout bg-grey-1">
    
    <!-- Floating Menu Button (Always on screen) -->
    <q-page-sticky position="top-left" :offset="[18, 18]" style="z-index: 2000;">
      <q-btn round color="primary" icon="menu" size="lg" class="shadow-4" @click="drawerOpen = !drawerOpen">
        <q-tooltip anchor="center right" self="center left" :offset="[10, 10]">
          Open Navigation Menu
        </q-tooltip>
      </q-btn>
    </q-page-sticky>

    <!-- Navigation Drawer -->
    <q-drawer v-model="drawerOpen" show-if-above :width="280" :breakpoint="500" bordered class="bg-white scroll">
      <div class="q-pa-md row items-center justify-between text-primary">
        <div class="text-h6 text-weight-bolder">Basic Math</div>
        <q-btn flat round icon="close" @click="drawerOpen = false" v-if="$q.screen.lt.md" />
      </div>
      
      <q-separator />

      <q-list padding class="text-dark">
        <!-- STUDENT PORTAL -->
        <q-item-label header class="text-weight-bold text-uppercase text-grey-8">🎓 Student Portal</q-item-label>
        
        <q-item clickable v-ripple @click="navigateTo(route('bm.assessment.launch'))" :active="route().current('bm.assessment.launch')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="rocket_launch" /></q-item-section>
          <q-item-section>Main Assessment</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple @click="navigateTo(route('bm.student.history'))" :active="route().current('bm.student.history')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="emoji_events" /></q-item-section>
          <q-item-section>History & Badges</q-item-section>
        </q-item>
        
        <q-item clickable v-ripple @click="navigateTo(route('bm.learning-path'))" :active="route().current('bm.learning-path')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="map" /></q-item-section>
          <q-item-section>Learning Path</q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="navigateTo(route('bm.practice'))" :active="route().current('bm.practice')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="fitness_center" /></q-item-section>
          <q-item-section>Daily Practice</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />

        <!-- TEACHER PORTAL -->
        <q-item-label header class="text-weight-bold text-uppercase text-grey-8">🍎 Teacher Portal</q-item-label>
        
        <q-item clickable v-ripple @click="navigateTo(route('bm.teacher.dashboard'))" :active="route().current('bm.teacher.dashboard')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="dashboard" /></q-item-section>
          <q-item-section>Overview Dashboard</q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="navigateTo(route('bm.teacher.class-scores'))" :active="route().current('bm.teacher.class-scores')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="table_chart" /></q-item-section>
          <q-item-section>Class Scores Table</q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="navigateTo(route('bm.teacher.gap-analysis'))" :active="route().current('bm.teacher.gap-analysis')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="warning" /></q-item-section>
          <q-item-section>Gap Analysis Alerts</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />

        <!-- PARENT PORTAL -->
        <q-item-label header class="text-weight-bold text-uppercase text-grey-8">👨‍👩‍👦 Parent Portal</q-item-label>
        
        <q-item clickable v-ripple @click="navigateTo(route('bm.parent.dashboard'))" :active="route().current('bm.parent.dashboard')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="family_restroom" /></q-item-section>
          <q-item-section>Child Progress</q-item-section>
        </q-item>

        <q-item clickable v-ripple @click="navigateTo(route('bm.parent.recommendations'))" :active="route().current('bm.parent.recommendations')" active-class="bg-blue-1 text-primary text-weight-bold">
          <q-item-section avatar><q-icon name="lightbulb" /></q-item-section>
          <q-item-section>Home Recommendations</q-item-section>
        </q-item>

        <q-separator class="q-my-sm" />
        
        <!-- GLOBAL -->
        <q-item clickable v-ripple @click="navigateTo('/')">
          <q-item-section avatar><q-icon name="arrow_back" color="negative" /></q-item-section>
          <q-item-section class="text-negative text-weight-bold">Exit Basic Math</q-item-section>
        </q-item>

      </q-list>
    </q-drawer>

    <q-page-container>
      <q-page>
        <slot />
      </q-page>
    </q-page-container>

  </q-layout>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const drawerOpen = ref(false); // Default entirely closed to avoid disrupting the assessment by default

const navigateTo = (url) => {
  // Check if we need to close the drawer on mobile automatically
  if ($q.screen.lt.md) {
    drawerOpen.value = false;
  }
  router.visit(url);
};
</script>

<style scoped>
.bm-layout {
  min-height: 100vh;
}
</style>
