<template>
  <Head title="Weekly System - Admin Dashboard" />
  <div class="q-pa-lg">
    <!-- Title Section -->
    <div class="text-center q-mb-xl">
      <h1 class="text-h4 text-weight-bold text-primary q-ma-none">
        Weekly System - Admin Dashboard
      </h1>
      <p class="text-subtitle1 text-grey-7 q-mt-sm">
        Manage curricula, set lock dates, and track teacher progress.
      </p>
    </div>

    <!-- Navigation Cards - All in one row -->
    <div class="row q-col-gutter-lg">
      <div class="col-12 col-md-4" v-for="menu in menus" :key="menu.title">
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
            <div class="text-body2 text-grey-7 text-center q-mb-md">{{ menu.description }}</div>
            
            <q-btn 
              flat 
              rounded 
              :label="menu.actionLabel" 
              :color="menu.color" 
              class="no-pointer-events" 
            />
          </div>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  schoolName: String,
  canManageCurriculum: Boolean,
  canManageWeeklyPlans: Boolean,
  canManageTimetable: Boolean,
})

const menus = [
  {
    title: 'Curriculum & Locks',
    description: 'Create books, manage lessons, and define edit lock deadlines.',
    icon: 'library_books',
    route: 'weekly-system-v1.curriculum-lessons.index',
    color: 'primary',
    actionLabel: 'Open Section'
  },
  {
    title: 'Weekly Plans Manager',
    description: 'Overview of all weekly plans, distributions, and teacher availability.',
    icon: 'calendar_today',
    route: 'weekly-system-v1.weekly-plans-manager',
    color: 'secondary',
    actionLabel: 'View All Plans'
  },
  // Temporarily disabled - Timetable editor not yet implemented
  // {
  //   title: 'Timetable Editor',
  //   description: 'Manage schedule copies and edit the weekly timetable.',
  //   icon: 'edit_calendar',
  //   route: 'weekly-system-v1.timetable-editor',
  //   color: 'accent',
  //   actionLabel: 'Edit Schedule'
  // }
]

const navigateTo = (routeName) => {
  // TODO: Check if route exists before navigating
  router.visit(route(routeName))
}
</script>

<style scoped>
.menu-card {
  transition: all 0.3s ease;
  border-radius: 16px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  height: 100%;
  min-height: 280px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.menu-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 16px 32px rgba(0, 0, 0, 0.12) !important;
  border-color: transparent;
}

.icon-wrapper {
  width: 80px;
  height: 80px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card-content {
  height: 100%;
  padding: 32px 24px;
}

.text-h6 {
  font-size: 1.125rem;
  line-height: 1.4;
}

.text-body2 {
  font-size: 0.875rem;
  line-height: 1.5;
}
</style>
