<template>
  <AppLayout title="Documentation Portal">
    <div class="documentation-portal">
      <!-- Header Section -->
      <div class="portal-header q-pa-lg">
        <div class="row items-center justify-between">
          <div class="col-auto">
            <h1 class="text-h3 text-weight-bold q-ma-none">
              <q-icon name="library_books" class="q-mr-md" color="primary" />
              Documentation Portal
            </h1>
            <p class="text-subtitle1 text-grey-7 q-mt-sm">
              Comprehensive documentation system for developers and administrators
            </p>
          </div>
          <div class="col-auto">
            <q-btn
              color="primary"
              icon="add"
              label="Add New Doc"
              @click="$inertia.visit('/admin/documentation')"
              class="q-mr-sm"
            />
            <q-btn
              color="secondary"
              icon="refresh"
              label="Refresh"
              @click="refreshData"
              outline
            />
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="stats-section q-px-lg q-pb-lg">
        <div class="row q-gutter-md">
          <div class="col-12 col-md-2">
            <q-card class="stat-card">
              <q-card-section class="text-center">
                <q-icon name="description" size="2rem" color="primary" />
                <div class="text-h4 text-weight-bold q-mt-sm">{{ stats.total }}</div>
                <div class="text-caption text-grey-7">Total Documents</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-2">
            <q-card class="stat-card">
              <q-card-section class="text-center">
                <q-icon name="storage" size="2rem" color="green" />
                <div class="text-h4 text-weight-bold q-mt-sm">{{ stats.database }}</div>
                <div class="text-caption text-grey-7">Database Docs</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-2">
            <q-card class="stat-card">
              <q-card-section class="text-center">
                <q-icon name="folder" size="2rem" color="orange" />
                <div class="text-h4 text-weight-bold q-mt-sm">{{ stats.files }}</div>
                <div class="text-caption text-grey-7">File Docs</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-2">
            <q-card class="stat-card">
              <q-card-section class="text-center">
                <q-icon name="category" size="2rem" color="purple" />
                <div class="text-h4 text-weight-bold q-mt-sm">{{ stats.categories }}</div>
                <div class="text-caption text-grey-7">Categories</div>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-2">
            <q-card class="stat-card">
              <q-card-section class="text-center">
                <q-icon name="schedule" size="2rem" color="blue" />
                <div class="text-h4 text-weight-bold q-mt-sm">{{ stats.recent }}</div>
                <div class="text-caption text-grey-7">Recent (7 days)</div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="search-section q-px-lg q-pb-lg">
        <q-card class="search-card">
          <q-card-section>
            <div class="row q-gutter-md items-end">
              <div class="col-12 col-md-4">
                <q-input
                  v-model="searchQuery"
                  placeholder="Search documentation..."
                  outlined
                  dense
                  @input="performSearch"
                  debounce="300"
                >
                  <template v-slot:prepend>
                    <q-icon name="search" />
                  </template>
                  <template v-slot:append>
                    <q-btn
                      v-if="searchQuery"
                      icon="clear"
                      flat
                      round
                      dense
                      @click="clearSearch"
                    />
                  </template>
                </q-input>
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="selectedCategory"
                  :options="categoryOptions"
                  label="Category"
                  outlined
                  dense
                  clearable
                  @update:model-value="filterByCategory"
                />
              </div>
              <div class="col-12 col-md-3">
                <q-select
                  v-model="selectedType"
                  :options="typeOptions"
                  label="Type"
                  outlined
                  dense
                  clearable
                  @update:model-value="filterByType"
                />
              </div>
              <div class="col-12 col-md-2">
                <q-btn-group outline>
                  <q-btn
                    :color="viewMode === 'grid' ? 'primary' : 'grey'"
                    icon="grid_view"
                    @click="viewMode = 'grid'"
                  />
                  <q-btn
                    :color="viewMode === 'list' ? 'primary' : 'grey'"
                    icon="view_list"
                    @click="viewMode = 'list'"
                  />
                </q-btn-group>
              </div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Categories Section -->
      <div class="categories-section q-px-lg q-pb-lg" v-if="!searchQuery && !selectedCategory">
        <h2 class="text-h5 text-weight-bold q-mb-md">
          <q-icon name="category" class="q-mr-sm" />
          Browse by Category
        </h2>
        <div class="row q-gutter-md">
          <div
            v-for="category in categories"
            :key="category.name"
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <q-card
              class="category-card cursor-pointer"
              @click="selectCategory(category.name)"
            >
              <q-card-section class="text-center">
                <q-icon
                  :name="getCategoryIcon(category.name)"
                  size="2rem"
                  :color="getCategoryColor(category.name)"
                />
                <div class="text-h6 text-weight-bold q-mt-sm">{{ category.name }}</div>
                <div class="text-caption text-grey-7">
                  {{ category.count }} document{{ category.count !== 1 ? 's' : '' }}
                </div>
                <q-chip
                  :color="category.type === 'database' ? 'green' : 'orange'"
                  text-color="white"
                  size="sm"
                  class="q-mt-sm"
                >
                  {{ category.type }}
                </q-chip>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>

      <!-- Documents Grid/List -->
      <div class="documents-section q-px-lg q-pb-lg">
        <div class="row items-center justify-between q-mb-md">
          <h2 class="text-h5 text-weight-bold">
            <q-icon name="description" class="q-mr-sm" />
            {{ getDocumentsSectionTitle() }}
          </h2>
          <div class="text-caption text-grey-7">
            {{ filteredDocuments.length }} document{{ filteredDocuments.length !== 1 ? 's' : '' }} found
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center q-py-xl">
          <q-spinner-dots size="3rem" color="primary" />
          <div class="text-subtitle1 q-mt-md">Loading documentation...</div>
        </div>

        <!-- Grid View -->
        <div v-else-if="viewMode === 'grid'" class="row q-gutter-md">
          <div
            v-for="doc in filteredDocuments"
            :key="doc.id"
            class="col-12 col-sm-6 col-md-4 col-lg-3"
          >
            <DocumentationCard
              :document="doc"
              @view="viewDocument"
              @edit="editDocument"
            />
          </div>
        </div>

        <!-- List View -->
        <div v-else class="q-gutter-sm">
          <DocumentationListItem
            v-for="doc in filteredDocuments"
            :key="doc.id"
            :document="doc"
            @view="viewDocument"
            @edit="editDocument"
          />
        </div>

        <!-- Empty State -->
        <div v-if="!loading && filteredDocuments.length === 0" class="text-center q-py-xl">
          <q-icon name="description" size="4rem" color="grey-5" />
          <div class="text-h6 text-grey-7 q-mt-md">No documentation found</div>
          <div class="text-body2 text-grey-6 q-mt-sm">
            Try adjusting your search criteria or add new documentation
          </div>
          <q-btn
            color="primary"
            icon="add"
            label="Add Documentation"
            @click="$inertia.visit('/admin/documentation')"
            class="q-mt-md"
          />
        </div>
      </div>

      <!-- Document Viewer Modal -->
      <DocumentViewer
        v-model="showViewer"
        :document="selectedDocument"
        @close="closeViewer"
      />
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayoutDefault.vue';
import DocumentationCard from './Components/DocumentationCard.vue';
import DocumentationListItem from './Components/DocumentationListItem.vue';
import DocumentViewer from './Components/DocumentViewer.vue';

// Props
const props = defineProps({
  dbDocs: Array,
  fileDocs: Array,
  stats: Object,
  categories: Array,
  filters: Object,
});

// Reactive data
const loading = ref(false);
const searchQuery = ref(props.filters?.search || '');
const selectedCategory = ref(props.filters?.category || null);
const selectedType = ref(props.filters?.type || null);
const viewMode = ref('grid');
const showViewer = ref(false);
const selectedDocument = ref(null);

// Computed properties
const allDocuments = computed(() => {
  const combined = [];

  // Add database docs
  props.dbDocs?.forEach(doc => {
    combined.push({
      ...doc,
      source: 'database',
      category: doc.type || 'general',
    });
  });

  // Add file docs
  props.fileDocs?.forEach(doc => {
    combined.push({
      ...doc,
      source: 'file',
    });
  });

  return combined;
});

const filteredDocuments = computed(() => {
  let docs = allDocuments.value;

  if (searchQuery.value) {
    const search = searchQuery.value.toLowerCase();
    docs = docs.filter(doc =>
      doc.title?.toLowerCase().includes(search) ||
      doc.content?.toLowerCase().includes(search) ||
      doc.excerpt?.toLowerCase().includes(search)
    );
  }

  if (selectedCategory.value) {
    docs = docs.filter(doc =>
      doc.category?.toLowerCase() === selectedCategory.value.toLowerCase()
    );
  }

  if (selectedType.value) {
    docs = docs.filter(doc => doc.source === selectedType.value);
  }

  return docs;
});

const categoryOptions = computed(() => {
  return props.categories?.map(cat => ({
    label: `${cat.name} (${cat.count})`,
    value: cat.name
  })) || [];
});

const typeOptions = computed(() => [
  { label: 'Database Documents', value: 'database' },
  { label: 'File Documents', value: 'file' }
]);

// Methods
const refreshData = () => {
  router.reload({ only: ['dbDocs', 'fileDocs', 'stats', 'categories'] });
};

const performSearch = () => {
  // Debounced search is handled by the input component
  // This method can be used for additional search logic if needed
};

const clearSearch = () => {
  searchQuery.value = '';
  selectedCategory.value = null;
  selectedType.value = null;
};

const filterByCategory = () => {
  // Filter logic is handled by computed property
};

const filterByType = () => {
  // Filter logic is handled by computed property
};

const selectCategory = (categoryName) => {
  selectedCategory.value = categoryName;
};

const viewDocument = (document) => {
  selectedDocument.value = document;
  showViewer.value = true;
};

const editDocument = (document) => {
  if (document.source === 'database') {
    router.visit(`/admin/documentation/${document.id}/edit`);
  } else {
    // For file documents, we could open in a code editor or show read-only
    viewDocument(document);
  }
};

const closeViewer = () => {
  showViewer.value = false;
  selectedDocument.value = null;
};

const getDocumentsSectionTitle = () => {
  if (selectedCategory.value) {
    return `${selectedCategory.value} Documentation`;
  }
  if (searchQuery.value) {
    return `Search Results for "${searchQuery.value}"`;
  }
  return 'All Documentation';
};

const getCategoryIcon = (categoryName) => {
  const iconMap = {
    'Offline': 'cloud_off',
    'Code': 'code',
    'Tutorial': 'school',
    'Guide': 'map',
    'API': 'api',
    'Reference': 'book',
    'General': 'description',
    'Note': 'note',
    'Research': 'science',
    'Question': 'help',
    'Comment': 'comment',
    'Idea': 'lightbulb'
  };
  return iconMap[categoryName] || 'description';
};

const getCategoryColor = (categoryName) => {
  const colorMap = {
    'Offline': 'orange',
    'Code': 'green',
    'Tutorial': 'blue',
    'Guide': 'purple',
    'API': 'red',
    'Reference': 'indigo',
    'General': 'grey',
    'Note': 'yellow',
    'Research': 'teal',
    'Question': 'pink',
    'Comment': 'cyan',
    'Idea': 'amber'
  };
  return colorMap[categoryName] || 'primary';
};

onMounted(() => {
  // Any initialization logic
});
</script>

<style scoped>
.documentation-portal {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

.portal-header {
  background: white;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 0;
}

.stats-section {
  margin-top: -20px;
}

.stat-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.search-card {
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.category-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.category-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
  cursor: pointer;
}

/* Dark mode support */
.body--dark .documentation-portal {
  background: linear-gradient(135deg, #1a1a1a 0%, #2d3748 100%);
}

.body--dark .portal-header {
  background: #1e1e1e;
  border-bottom-color: #333;
}

.body--dark .stat-card,
.body--dark .search-card,
.body--dark .category-card {
  background: #2d2d2d;
  color: #f5f5f5;
}

/* Responsive adjustments */
@media (max-width: 600px) {
  .portal-header .row {
    flex-direction: column;
    gap: 1rem;
  }

  .portal-header .col-auto {
    width: 100%;
  }

  .stats-section .row {
    justify-content: center;
  }
}
</style>
