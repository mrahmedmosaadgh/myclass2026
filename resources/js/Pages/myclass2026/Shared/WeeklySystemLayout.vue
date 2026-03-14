<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-1">
    <q-header elevated class="bg-primary text-white">
      <q-toolbar>
        <q-btn flat round dense icon="menu" class="q-mr-sm" v-if="$q.screen.lt.md" />
        <q-toolbar-title>
          <div class="row items-center">
            <q-icon name="event_note" size="sm" class="q-mr-sm" />
            <span>Weekly System</span>
          </div>
        </q-toolbar-title>
        
        <q-space />
        
        <div class="row items-center q-gutter-sm">
          <q-chip outline color="white" text-color="white" icon="school">
             {{ $page.props.auth.user.school_name || 'My School' }}
          </q-chip>
          <q-btn flat round dense icon="notifications" />
          <q-btn flat round dense icon="account_circle" />
        </div>
      </q-toolbar>

      <!-- Role-Aware Navigation Tabs -->
      <q-tabs align="left" dense class="bg-primary-dark">
        <template v-if="isAdmin">
          <q-route-tab :to="route('weekly-system.index')" label="Home" icon="home" />
          <q-route-tab :to="route('weekly-system.curriculum-lessons.index')" label="Curriculum & Locks" icon="library_books" />
          <q-route-tab :to="route('weekly-system.weekly-plans-manager')" label="Weekly Plans" icon="calendar_today" />
        </template>
        
        <template v-if="isTeacher">
          <q-route-tab :to="route('weekly-system.teacher.curriculum-lessons.index')" label="My Lesson Plans" icon="book" />
          <q-route-tab :to="route('weekly-system.my-weekly-plans')" label="My Weekly Plans" icon="edit_calendar" />
        </template>
      </q-tabs>
    </q-header>

    <q-page-container>
      <div class="q-pa-md">
        <slot />
      </div>
    </q-page-container>
    
    <q-footer v-if="$q.screen.lt.md" class="bg-white text-primary" bordered>
      <q-tabs align="justify" dense class="text-primary" indicator-color="transparent" active-color="primary">
        <template v-if="isAdmin">
          <q-route-tab :to="route('weekly-system.index')" icon="home" label="Home" />
          <q-route-tab :to="route('weekly-system.curriculum-lessons.index')" icon="library_books" label="Books" />
          <q-route-tab :to="route('weekly-system.weekly-plans-manager')" icon="calendar_today" label="Plans" />
        </template>
        <template v-if="isTeacher">
          <q-route-tab :to="route('weekly-system.teacher.curriculum-lessons.index')" icon="book" label="Lessons" />
          <q-route-tab :to="route('weekly-system.my-weekly-plans')" icon="edit_calendar" label="Weekly" />
        </template>
      </q-tabs>
    </q-footer>
  </q-layout>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const page = usePage()

const isAdmin = computed(() => {
  const roles = page.props.auth.user.roles || []
  return roles.some(role => role.name === 'school-admin' || role.name === 'admin' || role.slug === 'school-admin')
})

const isTeacher = computed(() => {
  const roles = page.props.auth.user.roles || []
  return roles.some(role => role.name === 'teacher' || role.slug === 'teacher')
})
</script>

<style scoped>
.bg-primary-dark {
  background: rgba(0, 0, 0, 0.1);
}
</style>
