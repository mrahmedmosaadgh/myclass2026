<template>
  <WeeklySystemLayout>
    <div class="row q-col-gutter-lg justify-center items-stretch q-pt-lg">
      <!-- Title Section -->
      <div class="col-12 text-center q-mb-md">
        <h1 class="text-h4 text-weight-bold text-primary q-ma-none">Admin Dashboard</h1>
        <p class="text-subtitle1 text-grey-7">Manage curricula, set lock dates, and track teacher progress.</p>
      </div>

      <!-- Navigation Cards -->
      <div class="col-12 col-sm-6 col-md-4" v-for="menu in menus" :key="menu.title">
        <q-card 
          class="menu-card full-height cursor-pointer q-hoverable" 
          v-ripple
          @click="navigateTo(menu.route)"
        >
          <div class="card-content q-pa-xl flex flex-center column">
            <div class="icon-wrapper q-mb-lg flex flex-center" :class="`bg-${menu.color}-1 text-${menu.color}`">
              <q-icon :name="menu.icon" size="3rem" />
            </div>
            
            <div class="text-h6 text-weight-bold text-center q-mb-sm">{{ menu.title }}</div>
            <div class="text-body2 text-grey-7 text-center">{{ menu.description }}</div>
            
            <q-btn 
              flat 
              rounded 
              label="Open Section" 
              :color="menu.color" 
              class="q-mt-lg no-pointer-events" 
            />
          </div>
        </q-card>
      </div>
    </div>
  </WeeklySystemLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import WeeklySystemLayout from '../../../Shared/WeeklySystemLayout.vue'

const menus = [
  {
    title: 'Curriculum & Locks',
    description: 'Create books, manage lessons, and define edit lock deadlines.',
    icon: 'library_books',
    route: 'weekly-system.curriculum-lessons.index',
    color: 'primary'
  },
  {
    title: 'Weekly Plans Manager',
    description: 'Overview of all weekly plans, distributions, and teacher availability.',
    icon: 'calendar_today',
    route: 'weekly-system.weekly-plans-manager',
    color: 'secondary'
  },
  {
    title: 'System Settings',
    description: 'Configure general weekly plan parameters and AI generation rules.',
    icon: 'settings',
    route: 'weekly-system.index', // Fallback for now
    color: 'orange'
  }
]

const navigateTo = (routeName) => {
  router.visit(route(routeName))
}
</script>

<style scoped>
.menu-card {
  transition: all 0.3s ease;
  border-radius: 20px;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.menu-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important;
}

.icon-wrapper {
  width: 100px;
  height: 100px;
  border-radius: 24px;
}

.card-content {
  height: 100%;
}
</style>