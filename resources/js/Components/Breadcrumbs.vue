<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

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

// Auto-generate breadcrumbs based on the current URL if no items are provided
const breadcrumbs = computed(() => {
  if (props.items.length > 0) {
    return props.items;
  }
  
  if (!props.autoGenerate) {
    return [];
  }
  
  // Get current URL path and split into segments
  const path = window.location.pathname;
  const segments = path.split('/').filter(segment => segment);
  
  // Always start with home
  const items = [
    { 
      title: t('common.home'), 
      href: '/' 
    }
  ];
  
  // Build breadcrumb items from URL segments
  let currentPath = '';
  segments.forEach((segment, index) => {
    currentPath += `/${segment}`;
    
    // Format the segment title (capitalize, replace dashes with spaces)
    const title = segment
      .replace(/-/g, ' ')
      .replace(/\b\w/g, l => l.toUpperCase());
    
    items.push({
      title,
      href: currentPath,
      active: index === segments.length - 1
    });
  });
  
  return items;
});
</script>

<template>
  <nav v-if="breadcrumbs.length > 1" aria-label="Breadcrumb" class="breadcrumbs">
    <ol class="flex flex-wrap items-center space-x-2 text-sm">
      <li v-for="(item, index) in breadcrumbs" :key="index" class="flex items-center">
        <!-- Separator (except for first item) -->
        <span v-if="index > 0" class="mx-2 text-gray-400">/</span>
        
        <!-- Link or text depending on whether it's the current page -->
        <Link 
          v-if="!item.active && item.href" 
          :href="item.href"
          class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
        >
          {{ item.title }}
        </Link>
        <span 
          v-else 
          class="text-gray-500 dark:text-gray-400"
          aria-current="page"
        >
          {{ item.title }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
/* RTL support */
:global([dir="rtl"]) .breadcrumbs ol {
  flex-direction: row-reverse;
}
</style>
