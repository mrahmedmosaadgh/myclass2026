<template>
  <div class="quiz-navigation q-mb-md">
    <q-tabs
      v-model="activeTab"
      class="text-primary bg-blue-1 rounded-borders"
      active-color="primary"
      indicator-color="primary"
      align="justify"
      narrow-indicator
      dense
    >
      <q-route-tab
        v-for="link in links"
        :key="link.name"
        :to="link.route"
        :label="link.label"
        :icon="link.icon"
        exact
        class="quiz-navigation__tab"
      />
    </q-tabs>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
  role: {
    type: String,
    default: 'teacher', // 'admin', 'teacher', 'student'
    validator: (value) => ['admin', 'teacher', 'student'].includes(value)
  }
});

const activeTab = ref('');

const allLinks = [
  {
    name: 'dashboard',
    label: 'Dashboard',
    icon: 'dashboard',
    route: '/quiz-dashboard',
    roles: ['admin', 'teacher']
  },
  {
    name: 'create',
    label: 'Create Quiz',
    icon: 'add_circle',
    route: '/quizzes/create', // Adjust route as needed
    roles: ['admin', 'teacher']
  },
  {
    name: 'question-bank',
    label: 'Question Bank',
    icon: 'library_books',
    route: '/question-bank', // Adjust route as needed
    roles: ['admin', 'teacher']
  },
  {
    name: 'reports',
    label: 'Reports',
    icon: 'analytics',
    route: '/quiz-reports', // Adjust route as needed
    roles: ['admin', 'teacher']
  },
  {
    name: 'my-quizzes',
    label: 'My Quizzes',
    icon: 'quiz',
    route: '/my-quizzes', // Adjust route as needed
    roles: ['student']
  },
  {
    name: 'settings',
    label: 'Settings',
    icon: 'settings',
    route: '/quiz-settings', // Adjust route as needed
    roles: ['admin']
  }
];

const links = computed(() => {
  return allLinks.filter(link => link.roles.includes(props.role));
});
</script>

<style scoped lang="scss">
.quiz-navigation {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);

  &__tab {
    font-weight: 600;
    text-transform: none;
    font-size: 1rem;
    min-height: 48px;
    transition: all 0.3s ease;

    &:hover {
      background-color: rgba(25, 118, 210, 0.05);
    }
  }
}
</style>
