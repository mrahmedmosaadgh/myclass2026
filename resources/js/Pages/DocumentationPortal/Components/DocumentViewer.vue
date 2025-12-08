<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="$emit('update:modelValue', $event)"
    maximized
    transition-show="slide-up"
    transition-hide="slide-down"
  >
    <q-card class="document-viewer">
      <!-- Header -->
      <q-card-section class="viewer-header row items-center">
        <div class="col">
          <div class="row items-center">
            <q-icon
              :name="getDocumentIcon()"
              :color="getDocumentColor()"
              size="2rem"
              class="q-mr-md"
            />
            <div>
              <div class="text-h5 text-weight-bold">{{ document?.title }}</div>
              <div class="text-subtitle2 text-grey-7">
                {{ document?.category }} • {{ document?.source }}
                <span v-if="document?.author"> • by {{ document.author.name }}</span>
              </div>
            </div>
          </div>
        </div>
        <div class="col-auto">
          <q-btn-group outline>
            <q-btn
              :color="viewMode === 'rendered' ? 'primary' : 'grey'"
              icon="visibility"
              label="Rendered"
              @click="viewMode = 'rendered'"
              :disable="loading"
            />
            <q-btn
              :color="viewMode === 'source' ? 'primary' : 'grey'"
              icon="code"
              label="Source"
              @click="viewMode = 'source'"
              :disable="loading"
            />
          </q-btn-group>
          <q-btn
            flat
            round
            icon="close"
            @click="$emit('close')"
            class="q-ml-md"
          />
        </div>
      </q-card-section>

      <!-- Toolbar -->
      <q-card-section class="viewer-toolbar row items-center q-py-sm">
        <q-btn
          flat
          icon="print"
          label="Print"
          @click="printDocument"
          size="sm"
          class="q-mr-sm"
        />
        <q-btn
          flat
          icon="download"
          label="Export"
          @click="exportDocument"
          size="sm"
          class="q-mr-sm"
        />
        <q-btn
          v-if="document?.source === 'database'"
          flat
          icon="edit"
          label="Edit"
          @click="editDocument"
          size="sm"
          class="q-mr-sm"
        />
        <q-space />
        <q-btn
          flat
          icon="fullscreen"
          @click="toggleFullscreen"
          size="sm"
        />
      </q-card-section>

      <!-- Content -->
      <q-card-section class="viewer-content">
        <!-- Loading State -->
        <div v-if="loading" class="text-center q-py-xl">
          <q-spinner-dots size="3rem" color="primary" />
          <div class="text-subtitle1 q-mt-md">Loading content...</div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="text-center q-py-xl">
          <q-icon name="error" size="3rem" color="negative" />
          <div class="text-h6 text-negative q-mt-md">Error loading content</div>
          <div class="text-body2 text-grey-7 q-mt-sm">{{ error }}</div>
          <q-btn
            color="primary"
            icon="refresh"
            label="Retry"
            @click="loadContent"
            class="q-mt-md"
          />
        </div>

        <!-- Content Display -->
        <div v-else class="content-display">
          <!-- Rendered View -->
          <div v-if="viewMode === 'rendered'" class="rendered-content">
            <!-- Markdown Content -->
            <div
              v-if="document?.source === 'file'"
              class="markdown-content"
              v-html="renderedMarkdown"
            ></div>

            <!-- Database Content -->
            <div v-else class="database-content">
              <div
                v-if="Array.isArray(document?.content)"
                class="rich-content"
              >
                <div
                  v-for="(block, index) in document.content"
                  :key="index"
                  class="content-block q-mb-md"
                >
                  <div v-if="block.type === 'paragraph'" v-html="block.content"></div>
                  <h1 v-else-if="block.type === 'heading1'" v-html="block.content"></h1>
                  <h2 v-else-if="block.type === 'heading2'" v-html="block.content"></h2>
                  <h3 v-else-if="block.type === 'heading3'" v-html="block.content"></h3>
                  <pre v-else-if="block.type === 'code'" class="code-block"><code v-html="block.content"></code></pre>
                  <ul v-else-if="block.type === 'list'">
                    <li v-for="(item, i) in block.items" :key="i" v-html="item"></li>
                  </ul>
                  <div v-else v-html="block.content"></div>
                </div>
              </div>
              <div v-else class="simple-content" v-html="document?.content"></div>
            </div>
          </div>

          <!-- Source View -->
          <div v-else class="source-content">
            <pre class="source-code"><code>{{ sourceContent }}</code></pre>
          </div>
        </div>
      </q-card-section>

      <!-- Table of Contents (if available) -->
      <q-drawer
        v-if="tableOfContents.length > 0"
        v-model="showToc"
        side="right"
        overlay
        bordered
        :width="300"
        class="toc-drawer"
      >
        <q-list>
          <q-item-label header>Table of Contents</q-item-label>
          <q-item
            v-for="(item, index) in tableOfContents"
            :key="index"
            clickable
            @click="scrollToHeading(item.id)"
            :class="`toc-level-${item.level}`"
          >
            <q-item-section>
              <q-item-label>{{ item.text }}</q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-drawer>

      <!-- TOC Toggle Button -->
      <q-btn
        v-if="tableOfContents.length > 0"
        fab
        icon="list"
        color="primary"
        @click="showToc = !showToc"
        class="toc-toggle"
      />
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import { useQuasar } from 'quasar';
import axios from 'axios';

const $q = useQuasar();

// Props
const props = defineProps({
  modelValue: Boolean,
  document: Object
});

// Emits
const emit = defineEmits(['update:modelValue', 'close']);

// Reactive data
const loading = ref(false);
const error = ref(null);
const viewMode = ref('rendered');
const showToc = ref(false);
const fullContent = ref('');
const renderedMarkdown = ref('');
const tableOfContents = ref([]);

// Computed
const sourceContent = computed(() => {
  if (props.document?.source === 'file') {
    return fullContent.value;
  }
  if (props.document?.content) {
    return typeof props.document.content === 'string'
      ? props.document.content
      : JSON.stringify(props.document.content, null, 2);
  }
  return '';
});

// Watch for document changes
watch(() => props.document, (newDoc) => {
  if (newDoc && props.modelValue) {
    loadContent();
  }
}, { immediate: true });

watch(() => props.modelValue, (show) => {
  if (show && props.document) {
    loadContent();
  }
});

// Methods
const loadContent = async () => {
  if (!props.document) return;

  loading.value = true;
  error.value = null;

  try {
    if (props.document.source === 'file') {
      const response = await axios.get('/admin/documentation-portal/file-content', {
        params: { path: props.document.path }
      });

      fullContent.value = response.data.content;
      renderedMarkdown.value = await renderMarkdown(response.data.content);
      generateTableOfContents(response.data.content);
    } else {
      // Database content is already loaded
      fullContent.value = sourceContent.value;
      if (Array.isArray(props.document.content)) {
        generateTableOfContentsFromBlocks(props.document.content);
      }
    }
  } catch (err) {
    error.value = err.response?.data?.error || 'Failed to load content';
  } finally {
    loading.value = false;
  }
};

const renderMarkdown = async (content) => {
  // Simple markdown rendering - you might want to use a proper markdown library
  return content
    .replace(/^# (.+)$/gm, '<h1>$1</h1>')
    .replace(/^## (.+)$/gm, '<h2>$1</h2>')
    .replace(/^### (.+)$/gm, '<h3>$1</h3>')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/`(.+?)`/g, '<code>$1</code>')
    .replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>')
    .replace(/\n/g, '<br>');
};

const generateTableOfContents = (content) => {
  const headings = content.match(/^#{1,6}\s+.+$/gm) || [];
  tableOfContents.value = headings.map((heading, index) => {
    const level = heading.match(/^#+/)[0].length;
    const text = heading.replace(/^#+\s+/, '');
    return {
      id: `heading-${index}`,
      level,
      text
    };
  });
};

const generateTableOfContentsFromBlocks = (blocks) => {
  tableOfContents.value = blocks
    .filter(block => ['heading1', 'heading2', 'heading3'].includes(block.type))
    .map((block, index) => ({
      id: `heading-${index}`,
      level: parseInt(block.type.replace('heading', '')),
      text: block.content.replace(/<[^>]*>/g, '') // Strip HTML tags
    }));
};

const getDocumentIcon = () => {
  const typeIcons = {
    'code': 'code',
    'tutorial': 'school',
    'guide': 'map',
    'api': 'api',
    'reference': 'book',
    'note': 'note',
    'research': 'science',
    'question': 'help',
    'comment': 'comment',
    'idea': 'lightbulb'
  };

  return typeIcons[props.document?.type?.toLowerCase()] || 'description';
};

const getDocumentColor = () => {
  const colorMap = {
    'code': 'green',
    'tutorial': 'blue',
    'guide': 'purple',
    'api': 'red',
    'reference': 'indigo',
    'note': 'yellow',
    'research': 'teal',
    'question': 'pink',
    'comment': 'cyan',
    'idea': 'amber'
  };

  return colorMap[props.document?.type?.toLowerCase()] || 'primary';
};

const printDocument = () => {
  window.print();
};

const exportDocument = () => {
  $q.notify({
    type: 'info',
    message: 'Export functionality coming soon',
    position: 'top'
  });
};

const editDocument = () => {
  if (props.document?.source === 'database') {
    window.open(`/admin/documentation/${props.document.id}/edit`, '_blank');
  }
};

const toggleFullscreen = () => {
  if (document.fullscreenElement) {
    document.exitFullscreen();
  } else {
    document.documentElement.requestFullscreen();
  }
};

const scrollToHeading = (headingId) => {
  const element = document.getElementById(headingId);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth' });
  }
};
</script>

<style scoped>
.document-viewer {
  height: 100vh;
  display: flex;
  flex-direction: column;
}

.viewer-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e0e0e0;
  padding: 16px 24px;
}

.viewer-toolbar {
  background: #f8f9fa;
  border-bottom: 1px solid #e0e0e0;
  padding: 8px 24px;
}

.viewer-content {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
}

.content-display {
  max-width: 800px;
  margin: 0 auto;
}

.rendered-content {
  line-height: 1.6;
}

.markdown-content,
.database-content {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.markdown-content h1,
.markdown-content h2,
.markdown-content h3 {
  margin-top: 2rem;
  margin-bottom: 1rem;
  font-weight: 600;
}

.markdown-content h1 {
  font-size: 2rem;
  border-bottom: 2px solid #e0e0e0;
  padding-bottom: 0.5rem;
}

.markdown-content h2 {
  font-size: 1.5rem;
}

.markdown-content h3 {
  font-size: 1.25rem;
}

.markdown-content code {
  background: #f5f5f5;
  padding: 2px 6px;
  border-radius: 4px;
  font-family: 'Monaco', 'Consolas', monospace;
}

.markdown-content pre {
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  overflow-x: auto;
  margin: 16px 0;
}

.markdown-content pre code {
  background: none;
  padding: 0;
}

.source-content {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 16px;
}

.source-code {
  font-family: 'Monaco', 'Consolas', monospace;
  font-size: 14px;
  line-height: 1.5;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.code-block {
  background: #f8f9fa;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  overflow-x: auto;
  font-family: 'Monaco', 'Consolas', monospace;
}

.toc-drawer {
  background: white;
}

.toc-level-1 {
  padding-left: 16px;
}

.toc-level-2 {
  padding-left: 32px;
}

.toc-level-3 {
  padding-left: 48px;
}

.toc-toggle {
  position: fixed;
  bottom: 24px;
  right: 24px;
  z-index: 1000;
}

/* Dark mode support */
.body--dark .viewer-header {
  background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
  border-bottom-color: #444;
  color: #f5f5f5;
}

.body--dark .viewer-toolbar {
  background: #2d2d2d;
  border-bottom-color: #444;
}

.body--dark .markdown-content h1 {
  border-bottom-color: #444;
}

.body--dark .markdown-content code,
.body--dark .markdown-content pre,
.body--dark .source-content,
.body--dark .code-block {
  background: #1e1e1e;
  border-color: #444;
  color: #f5f5f5;
}

.body--dark .toc-drawer {
  background: #2d2d2d;
  color: #f5f5f5;
}

/* Print styles */
@media print {
  .viewer-header,
  .viewer-toolbar,
  .toc-toggle {
    display: none !important;
  }

  .viewer-content {
    padding: 0;
  }

  .content-display {
    max-width: none;
  }
}

/* Responsive */
@media (max-width: 768px) {
  .viewer-header .row {
    flex-direction: column;
    gap: 16px;
  }

  .viewer-toolbar .row {
    flex-wrap: wrap;
    gap: 8px;
  }

  .viewer-content {
    padding: 16px;
  }

  .toc-toggle {
    bottom: 16px;
    right: 16px;
  }
}
</style>
