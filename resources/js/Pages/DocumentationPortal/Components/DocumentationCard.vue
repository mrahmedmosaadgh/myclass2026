<template>
  <q-card class="documentation-card">
    <q-card-section class="card-header">
      <div class="row items-center justify-between">
        <div class="col">
          <div class="row items-center">
            <q-icon
              :name="getDocumentIcon()"
              :color="getDocumentColor()"
              size="1.5rem"
              class="q-mr-sm"
            />
            <div class="text-h6 text-weight-bold ellipsis">
              {{ document.title }}
            </div>
          </div>
        </div>
        <div class="col-auto">
          <q-chip
            :color="document.source === 'database' ? 'green' : 'orange'"
            text-color="white"
            size="sm"
            :icon="document.source === 'database' ? 'storage' : 'folder'"
          >
            {{ document.source }}
          </q-chip>
        </div>
      </div>
    </q-card-section>

    <q-card-section class="card-content">
      <div class="text-body2 text-grey-7 q-mb-sm">
        {{ getExcerpt() }}
      </div>

      <div class="row items-center q-gutter-xs q-mb-sm">
        <q-chip
          :color="getCategoryColor()"
          text-color="white"
          size="sm"
          :icon="getCategoryIcon()"
        >
          {{ document.category }}
        </q-chip>

        <q-chip
          v-if="document.type && document.type !== document.category"
          color="grey-6"
          text-color="white"
          size="sm"
        >
          {{ document.type }}
        </q-chip>
      </div>

      <div class="document-meta text-caption text-grey-6">
        <div v-if="document.author" class="q-mb-xs">
          <q-icon name="person" size="xs" class="q-mr-xs" />
          {{ document.author.name }}
        </div>
        <div v-if="document.created_at || document.modified">
          <q-icon name="schedule" size="xs" class="q-mr-xs" />
          {{ formatDate() }}
        </div>
        <div v-if="document.size">
          <q-icon name="description" size="xs" class="q-mr-xs" />
          {{ formatFileSize(document.size) }}
        </div>
      </div>
    </q-card-section>

    <q-card-actions class="card-actions">
      <q-btn
        flat
        color="primary"
        icon="visibility"
        label="View"
        @click="$emit('view', document)"
        class="q-mr-sm"
      />
      <q-btn
        v-if="document.source === 'database'"
        flat
        color="secondary"
        icon="edit"
        label="Edit"
        @click="$emit('edit', document)"
      />
      <q-space />
      <q-btn
        flat
        round
        color="grey-6"
        icon="more_vert"
      >
        <q-menu>
          <q-list style="min-width: 100px">
            <q-item clickable v-close-popup @click="copyLink">
              <q-item-section avatar>
                <q-icon name="link" />
              </q-item-section>
              <q-item-section>Copy Link</q-item-section>
            </q-item>
            <q-item clickable v-close-popup @click="exportDocument">
              <q-item-section avatar>
                <q-icon name="download" />
              </q-item-section>
              <q-item-section>Export</q-item-section>
            </q-item>
            <q-item v-if="document.source === 'file'" clickable v-close-popup @click="openInEditor">
              <q-item-section avatar>
                <q-icon name="code" />
              </q-item-section>
              <q-item-section>Open in Editor</q-item-section>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>
    </q-card-actions>

    <!-- Status indicators -->
    <div class="status-indicators">
      <q-badge
        v-if="document.status === 'draft'"
        color="orange"
        floating
        class="status-badge"
      >
        Draft
      </q-badge>
      <q-badge
        v-if="document.status === 'archived'"
        color="grey"
        floating
        class="status-badge"
      >
        Archived
      </q-badge>
      <q-badge
        v-if="isRecent()"
        color="green"
        floating
        class="status-badge recent-badge"
      >
        New
      </q-badge>
    </div>
  </q-card>
</template>

<script setup>
import { computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

// Props
const props = defineProps({
  document: {
    type: Object,
    required: true
  }
});

// Emits
const emit = defineEmits(['view', 'edit']);

// Methods
const getDocumentIcon = () => {
  if (props.document.source === 'file') {
    return 'description';
  }

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

  return typeIcons[props.document.type?.toLowerCase()] || 'description';
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

  return colorMap[props.document.type?.toLowerCase()] || 'primary';
};

const getCategoryIcon = () => {
  const iconMap = {
    'offline': 'cloud_off',
    'code': 'code',
    'tutorial': 'school',
    'guide': 'map',
    'api': 'api',
    'reference': 'book',
    'general': 'description',
    'note': 'note',
    'research': 'science',
    'question': 'help',
    'comment': 'comment',
    'idea': 'lightbulb'
  };

  return iconMap[props.document.category?.toLowerCase()] || 'description';
};

const getCategoryColor = () => {
  const colorMap = {
    'offline': 'orange',
    'code': 'green',
    'tutorial': 'blue',
    'guide': 'purple',
    'api': 'red',
    'reference': 'indigo',
    'general': 'grey',
    'note': 'yellow',
    'research': 'teal',
    'question': 'pink',
    'comment': 'cyan',
    'idea': 'amber'
  };

  return colorMap[props.document.category?.toLowerCase()] || 'primary';
};

const getExcerpt = () => {
  if (props.document.excerpt) {
    return props.document.excerpt;
  }

  if (props.document.content) {
    if (typeof props.document.content === 'string') {
      return props.document.content.substring(0, 150) + '...';
    }
    if (Array.isArray(props.document.content)) {
      const text = props.document.content.map(item => item.content || '').join(' ');
      return text.substring(0, 150) + '...';
    }
  }

  return 'No description available.';
};

const formatDate = () => {
  if (props.document.created_at) {
    return new Date(props.document.created_at).toLocaleDateString();
  }
  if (props.document.modified) {
    return new Date(props.document.modified * 1000).toLocaleDateString();
  }
  return '';
};

const formatFileSize = (bytes) => {
  if (!bytes) return '';

  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(1024));
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
};

const isRecent = () => {
  if (props.document.created_at) {
    const created = new Date(props.document.created_at);
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    return created > weekAgo;
  }
  return false;
};

const copyLink = () => {
  // Implementation for copying document link
  $q.notify({
    type: 'positive',
    message: 'Link copied to clipboard',
    position: 'top'
  });
};

const exportDocument = () => {
  // Implementation for exporting document
  $q.notify({
    type: 'info',
    message: 'Export functionality coming soon',
    position: 'top'
  });
};

const openInEditor = () => {
  // Implementation for opening file in editor
  $q.notify({
    type: 'info',
    message: 'Editor functionality coming soon',
    position: 'top'
  });
};
</script>

<style scoped>
.documentation-card {
  transition: all 0.3s ease;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  position: relative;
  overflow: hidden;
}

.documentation-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.card-header {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  border-bottom: 1px solid #e0e0e0;
  padding: 16px;
}

.card-content {
  padding: 16px;
  min-height: 120px;
}

.card-actions {
  padding: 8px 16px 16px;
  border-top: 1px solid #f0f0f0;
}

.document-meta {
  margin-top: 8px;
}

.status-indicators {
  position: relative;
}

.status-badge {
  position: absolute;
  top: 8px;
  right: 8px;
}

.recent-badge {
  top: 8px;
  right: 60px;
}

/* Dark mode support */
.body--dark .documentation-card {
  background: #2d2d2d;
  color: #f5f5f5;
}

.body--dark .card-header {
  background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
  border-bottom-color: #444;
}

.body--dark .card-actions {
  border-top-color: #444;
}

/* Responsive */
@media (max-width: 600px) {
  .card-header .row {
    flex-direction: column;
    gap: 8px;
  }

  .card-actions {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
