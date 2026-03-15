<template>
  <div class="weekly-plans-manager q-pa-lg">
    <!-- Header Section -->
    <div class="header row justify-between items-center q-mb-xl">
      <div>
        <h2 class="text-h4 text-weight-bold text-primary q-ma-none">
          {{ title }}
        </h2>
        <p class="text-subtitle1 text-grey-7 q-mt-sm">
          {{ subtitle }}
        </p>
      </div>
      
      <!-- Action Buttons Slot -->
      <div class="actions row q-gutter-sm">
        <slot name="actions">
          <!-- Default actions (can be overridden by parent) -->
        </slot>
        
        <!-- Additional custom actions from parent -->
        <slot name="additional-actions"></slot>
      </div>
    </div>

    <!-- Info Badge/Stats Slot -->
    <slot name="info-stats">
      <!-- Default stats display (can be overridden) -->
      <q-card class="q-mb-xl" v-if="showDefaultStats">
        <q-card-section>
          <div class="row items-center q-gutter-md">
            <q-icon :name="statsIcon" size="3rem" color="primary" />
            <div>
              <div class="text-h6 text-weight-bold">{{ statsTitle }}</div>
              <div class="text-body2 text-grey-7">
                {{ items.length }} {{ items.length === 1 ? itemSingular : itemPlural }}
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </slot>

    <!-- Alert/Message Section -->
    <slot name="alerts">
      <!-- Default: No alerts -->
    </slot>

    <!-- Loading State -->
    <LoadingState 
      v-if="loading" 
      message="Loading weekly plans..." 
      height="400px"
    />

    <!-- Empty State -->
    <EmptyState
      v-else-if="!items || items.length === 0"
      :icon="emptyIcon"
      title="No Items Found"
      :message="emptyMessage"
    >
      <template #actions>
        <slot name="empty-actions">
          <!-- Default empty actions (can be overridden) -->
        </slot>
      </template>
    </EmptyState>

    <!-- Content List/Grid -->
    <q-card v-else>
      <q-card-section>
        <div class="text-h6 text-weight-bold q-mb-md">{{ listTitle }}</div>
        
        <q-list separator>
          <q-item 
            v-for="item in items" 
            :key="item.id"
            clickable
            v-ripple
            @click="$emit('item-click', item)"
          >
            <q-item-section avatar>
              <q-icon :name="item.icon || defaultItemIcon" :color="itemColor" />
            </q-item-section>
            
            <q-item-section>
              <q-item-label class="text-weight-bold">
                {{ getItemTitle(item) }}
              </q-item-label>
              <q-item-label caption>
                {{ getItemSubtitle(item) }}
              </q-item-label>
              
              <!-- Custom content slot per item -->
              <slot name="item-content" :item="item"></slot>
            </q-item-section>
            
            <q-item-section side>
              <slot name="item-actions" :item="item">
                <!-- Default action button -->
                <q-btn 
                  flat 
                  round 
                  dense 
                  :icon="actionIcon" 
                  :color="itemColor"
                  @click.stop="$emit('action-click', item)"
                >
                  <q-tooltip>{{ actionLabel }}</q-tooltip>
                </q-btn>
              </slot>
              
              <!-- Additional actions slot -->
              <slot name="item-additional-actions" :item="item"></slot>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>

    <!-- Footer Slot -->
    <slot name="footer">
      <!-- Optional footer content -->
    </slot>
  </div>
</template>

<script setup>
import LoadingState from '../components/common/LoadingState.vue'
import EmptyState from '../components/common/EmptyState.vue'

const props = defineProps({
  items: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Weekly Plans Manager'
  },
  subtitle: {
    type: String,
    default: 'Overview of all weekly plans'
  },
  loading: {
    type: Boolean,
    default: false
  },
  // Stats configuration
  showDefaultStats: {
    type: Boolean,
    default: true
  },
  statsTitle: {
    type: String,
    default: 'Overview'
  },
  statsIcon: {
    type: String,
    default: 'assignment'
  },
  itemSingular: {
    type: String,
    default: 'item'
  },
  itemPlural: {
    type: String,
    default: 'items'
  },
  // List configuration
  listTitle: {
    type: String,
    default: 'Items'
  },
  emptyIcon: {
    type: String,
    default: 'assignment_late'
  },
  emptyMessage: {
    type: String,
    default: 'No items available yet.'
  },
  defaultItemIcon: {
    type: String,
    default: 'assignment'
  },
  itemColor: {
    type: String,
    default: 'primary'
  },
  actionIcon: {
    type: String,
    default: 'visibility'
  },
  actionLabel: {
    type: String,
    default: 'View'
  },
  // Custom title/subtitle getters
  titleField: {
    type: String,
    default: 'name'
  },
  subtitleField: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['item-click', 'action-click'])

const getItemTitle = (item) => {
  if (typeof props.titleField === 'function') {
    return props.titleField(item)
  }
  return item[props.titleField] || item.name || 'Untitled'
}

const getItemSubtitle = (item) => {
  if (!props.subtitleField) return ''
  
  if (typeof props.subtitleField === 'function') {
    return props.subtitleField(item)
  }
  return item[props.subtitleField] || ''
}
</script>

<style scoped>
.weekly-plans-manager {
  max-width: 1400px;
  margin: 0 auto;
}

.header {
  background: white;
  padding: 24px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.text-h4 {
  font-size: 1.75rem;
  line-height: 1.2;
}

.text-subtitle1 {
  font-size: 1rem;
  line-height: 1.5;
}

.q-card {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.q-item {
  border-radius: 8px;
  margin-bottom: 8px;
  transition: all 0.3s ease;
}

.q-item:hover {
  background-color: rgba(0, 0, 0, 0.02);
}
</style>
