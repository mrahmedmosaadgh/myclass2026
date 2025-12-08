<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { debounce } from 'lodash';

const { t } = useI18n();
const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const isOpen = ref(false);
const searchInput = ref(null);

// Categories for search results
const categories = [
  { key: 'pages', label: t('common.pages') },
  { key: 'users', label: t('common.users') },
  { key: 'documents', label: t('common.documents') }
];

// Mock search function - replace with actual API call
const performSearch = debounce(async (query) => {
  if (!query.trim()) {
    searchResults.value = [];
    return;
  }
  
  isSearching.value = true;
  
  try {
    // This would be replaced with an actual API call
    // const response = await axios.get('/api/search', { params: { query } });
    // searchResults.value = response.data;
    
    // Mock results for demonstration
    searchResults.value = [
      {
        category: 'pages',
        items: [
          { title: 'Dashboard', url: '/dashboard' },
          { title: 'Settings', url: '/settings' }
        ]
      },
      {
        category: 'users',
        items: [
          { title: 'John Doe', url: '/users/1' },
          { title: 'Jane Smith', url: '/users/2' }
        ]
      }
    ].filter(category => 
      category.items.some(item => 
        item.title.toLowerCase().includes(query.toLowerCase())
      )
    );
  } catch (error) {
    console.error('Search error:', error);
  } finally {
    isSearching.value = false;
  }
}, 300);

// Watch for changes in search query
watch(searchQuery, (newQuery) => {
  performSearch(newQuery);
});

// Navigate to a search result
const navigateToResult = (url) => {
  closeSearch();
  router.visit(url);
};

// Open search dialog
const openSearch = () => {
  isOpen.value = true;
  setTimeout(() => {
    searchInput.value?.focus();
  }, 100);
};

// Close search dialog
const closeSearch = () => {
  isOpen.value = false;
  searchQuery.value = '';
  searchResults.value = [];
};

// Handle keyboard shortcuts
const handleKeyDown = (event) => {
  // Ctrl+K or Cmd+K to open search
  if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
    event.preventDefault();
    if (isOpen.value) {
      closeSearch();
    } else {
      openSearch();
    }
  }
  
  // Escape to close search
  if (event.key === 'Escape' && isOpen.value) {
    closeSearch();
  }
};

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown);
});

defineExpose({ openSearch });
</script>

<template>
  <div>
    <!-- Search Trigger Button -->
    <button 
      @click="openSearch" 
      class="flex items-center text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
      :title="t('common.search') + ' (Ctrl+K)'"
    >
      <q-icon name="search" size="sm" class="q-mr-xs" />
      <span class="hidden md:inline">{{ t('common.search') }}</span>
    </button>
    
    <!-- Search Dialog -->
    <q-dialog v-model="isOpen" position="top">
      <q-card class="search-dialog">
        <q-card-section class="q-pa-none">
          <!-- Search Input -->
          <div class="search-input-container">
            <q-input 
              ref="searchInput"
              v-model="searchQuery"
              :placeholder="t('common.searchPlaceholder')"
              outlined
              dense
              class="search-input"
              @keyup.esc="closeSearch"
            >
              <template v-slot:prepend>
                <q-icon name="search" />
              </template>
              <template v-slot:append>
                <q-chip dense color="primary" text-color="white">
                  ESC
                </q-chip>
              </template>
            </q-input>
          </div>
          
          <!-- Search Results -->
          <div v-if="searchQuery && !isSearching" class="search-results">
            <div v-if="searchResults.length === 0" class="no-results">
              {{ t('common.noSearchResults') }}
            </div>
            
            <div v-else>
              <div v-for="(category, index) in searchResults" :key="index" class="result-category">
                <div class="category-title">{{ categories.find(c => c.key === category.category)?.label || category.category }}</div>
                
                <div v-for="(item, itemIndex) in category.items" :key="itemIndex" 
                  class="result-item"
                  @click="navigateToResult(item.url)"
                >
                  <div class="item-title">{{ item.title }}</div>
                  <q-icon name="arrow_forward" size="xs" />
                </div>
              </div>
            </div>
          </div>
          
          <!-- Loading Indicator -->
          <div v-if="isSearching" class="loading-indicator">
            <q-spinner color="primary" size="2em" />
            <span>{{ t('common.searching') }}</span>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<style scoped>
.search-dialog {
  width: 600px;
  max-width: 90vw;
}

.search-input-container {
  padding: 1rem;
}

.search-results {
  max-height: 400px;
  overflow-y: auto;
  padding: 0 1rem 1rem;
}

.no-results {
  padding: 1rem;
  text-align: center;
  color: var(--q-negative);
}

.result-category {
  margin-bottom: 1rem;
}

.category-title {
  font-weight: bold;
  margin-bottom: 0.5rem;
  color: var(--q-primary);
}

.result-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
}

.result-item:hover {
  background-color: rgba(0, 0, 0, 0.05);
}

.loading-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 0.5rem;
}
</style>
