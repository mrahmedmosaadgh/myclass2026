<template>
  <Head title="My Weekly Plans" />
  <div class="q-pa-lg">
    <!-- Title Section -->
    <div class="text-center q-mb-xl">
      <h1 class="text-h4 text-weight-bold text-primary q-ma-none">
        My Weekly Plans
      </h1>
      <p class="text-subtitle1 text-grey-7 q-mt-sm">
        Plan your lessons, manage assignments, and track progress.
      </p>
    </div>

    <!-- Stats Overview -->
    <q-card class="q-mb-xl">
      <q-card-section>
        <div class="row items-center q-gutter-md">
          <q-icon name="person" size="3rem" color="primary" />
          <div>
            <div class="text-h6 text-weight-bold">Welcome, {{ teacherName }}</div>
            <div class="text-body2 text-grey-7">
              You have {{ assignedClassesCount }} assigned {{ assignedClassesCount === 1 ? 'class' : 'classes' }}
            </div>
          </div>
        </div>
      </q-card-section>
    </q-card>

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
  teacherName: String,
  assignedClassesCount: Number,
  canViewCurriculum: Boolean,
  canEditWeeklyPlans: Boolean,
})

const menus = [
  {
    title: 'My Weekly Plans',
    description: 'Edit and manage your weekly lesson plans.',
    icon: 'edit_note',
    route: 'weekly-system-v1.my-weekly-plans',
    color: 'primary',
    actionLabel: 'Edit Plans'
  },
  // Temporarily disabled - Timetable editor not yet implemented
  // {
  //   title: 'My Schedule',
  //   description: 'View your weekly teaching schedule.',
  //   icon: 'calendar_today',
  //   route: 'weekly-system-v1.timetable-editor',
  //   color: 'secondary',
  //   actionLabel: 'View Schedule'
  // },
  {
    title: 'Curriculum Access',
    description: 'View curricula for your assigned classes.',
    icon: 'school',
    route: 'weekly-system-v1.curriculum-lessons.index',
    color: 'accent',
    actionLabel: 'View Curricula'
  }
]

const navigateTo = (routeName) => {
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
