<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const currentPath = ref(window.location.pathname);

const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  autoGenerate: {
    type: Boolean,
    default: true
  }
});

// Function to update breadcrumbs on navigation
const updateBreadcrumbs = () => {
  currentPath.value = window.location.pathname;
};

// Listen for Inertia navigation events
onMounted(() => {
  // Update currentPath when navigation is complete
  router.on('navigate', updateBreadcrumbs);
});

// Clean up event listeners when component is unmounted
onBeforeUnmount(() => {
  router.off('navigate', updateBreadcrumbs);
});

// Auto-generate breadcrumbs based on the current URL if no items are provided
const breadcrumbs = computed(() => {
  if (props.items.length > 0) {
    return props.items;
  }

  if (!props.autoGenerate) {
    return [];
  }

  // Get current URL path and split into segments
  const path = currentPath.value;
  const segments = path.split('/').filter(segment => segment);

  // Always start with home
  const items = [
    {
      label: t('common.home'),
      icon: 'home',
      to: '/'
    }
  ];

  // Build breadcrumb items from URL segments
  let pathSoFar = '';
  segments.forEach((segment, index) => {
    pathSoFar += `/${segment}`;

    // Format the segment title (capitalize, replace dashes with spaces)
    const label = segment
      .replace(/-/g, ' ')
      .replace(/\b\w/g, l => l.toUpperCase());

    items.push({
      label,
      to: pathSoFar,
      active: index === segments.length - 1
    });
  });

  return items;
});
</script>

<template>
  <q-breadcrumbs v-if="breadcrumbs.length > 1" class="q-px-md q-py-sm text-grey-7">
    <q-breadcrumbs-el
      v-for="(item, index) in breadcrumbs"
      :key="index"
      :label="item.label"
      :icon="item.icon"
      :to="!item.active ? item.to : null"
      :class="{ 'text-primary': !item.active, 'text-grey-7': item.active }"
    />
  </q-breadcrumbs>
</template>

<style scoped>
/* RTL support */
:global([dir="rtl"]) .q-breadcrumbs {
  flex-direction: row-reverse;
}
</style>
