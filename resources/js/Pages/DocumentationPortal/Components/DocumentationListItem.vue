<template>
  <q-card class="documentation-list-item">
    <q-card-section class="row items-center q-pa-md">
      <!-- Icon and Title -->
      <div class="col-auto q-mr-md">
        <q-avatar
          :color="getDocumentColor()"
          text-color="white"
          :icon="getDocumentIcon()"
          size="48px"
        />
      </div>

      <!-- Content -->
      <div class="col">
        <div class="row items-center q-mb-xs">
          <div class="text-h6 text-weight-bold q-mr-md">
            {{ document.title }}
          </div>
          <q-chip
            :color="document.source === 'database' ? 'green' : 'orange'"
            text-color="white"
            size="sm"
            :icon="document.source === 'database' ? 'storage' : 'folder'"
          >
            {{ document.source }}
          </q-chip>
          <q-chip
            :color="getCategoryColor()"
            text-color="white"
            size="sm"
            :icon="getCategoryIcon()"
            class="q-ml-xs"
          >
            {{ document.category }}
          </q-chip>
        </div>
        
        <div class="text-body2 text-grey-7 q-mb-sm">
          {{ getExcerpt() }}
        </div>
        
        <div class="row items-center text-caption text-grey-6">
          <div v-if="document.author" class="q-mr-md">
            <q-icon name="person" size="xs" class="q-mr-xs" />
            {{ document.author.name }}
          </div>
          <div v-if="document.created_at || document.modified" class="q-mr-md">
            <q-icon name="schedule" size="xs" class="q-mr-xs" />
            {{ formatDate() }}
          </div>
          <div v-if="document.size">
            <q-icon name="description" size="xs" class="q-mr-xs" />
            {{ formatFileSize(document.size) }}
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="col-auto">
        <div class="row q-gutter-sm">
          <q-btn
            flat
            color="primary"
            icon="visibility"
            label="View"
            @click="$emit('view', document)"
            size="sm"
          />
          <q-btn
            v-if="document.source === 'database'"
            flat
            color="secondary"
            icon="edit"
            label="Edit"
            @click="$emit('edit', document)"
            size="sm"
          />
          <q-btn
            flat
            round
            color="grey-6"
            icon="more_vert"
            size="sm"
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
        </div>
      </div>

      <!-- Status badges -->
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
    </q-card-section>
  </q-card>
</template>

<script setup>
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

// Methods (same as DocumentationCard)
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
      return props.document.content.substring(0, 200) + '...';
    }
    if (Array.isArray(props.document.content)) {
      const text = props.document.content.map(item => item.content || '').join(' ');
      return text.substring(0, 200) + '...';
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
  $q.notify({
    type: 'positive',
    message: 'Link copied to clipboard',
    position: 'top'
  });
};

const exportDocument = () => {
  $q.notify({
    type: 'info',
    message: 'Export functionality coming soon',
    position: 'top'
  });
};

const openInEditor = () => {
  $q.notify({
    type: 'info',
    message: 'Editor functionality coming soon',
    position: 'top'
  });
};
</script>

<style scoped>
.documentation-list-item {
  transition: all 0.3s ease;
  border-radius: 8px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.1);
  position: relative;
  margin-bottom: 8px;
}

.documentation-list-item:hover {
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transform: translateX(4px);
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
.body--dark .documentation-list-item {
  background: #2d2d2d;
  color: #f5f5f5;
}

/* Responsive */
@media (max-width: 768px) {
  .documentation-list-item .row {
    flex-direction: column;
    gap: 16px;
  }
  
  .documentation-list-item .col-auto:last-child {
    align-self: stretch;
  }
  
  .documentation-list-item .col-auto:last-child .row {
    justify-content: center;
  }
}
</style>
