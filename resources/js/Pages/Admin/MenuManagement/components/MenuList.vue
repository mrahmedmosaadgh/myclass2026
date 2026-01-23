<template>
  <div class="menu-list">
    <div v-if="menus.length === 0" class="text-center q-pa-xl text-grey-6 bg-white rounded-borders">
      <q-icon name="menu" size="64px" class="q-mb-md text-grey-4" />
      <div class="text-h6 text-grey-7">No menus in this module</div>
      <div class="text-subtitle2 text-grey-5">Create your first menu to get started</div>
    </div>

    <q-list v-else bordered separator class="rounded-borders overflow-hidden bg-white" ref="sortableList">
      <template v-for="menu in sortedMenus" :key="menu.id">
        <!-- Parent Menu Item -->
        <q-item
          :class="['menu-item q-py-sm', { 'menu-inactive': !menu.is_active }]"
          :data-id="menu.id"
          clickable
          v-ripple
        >
          <q-item-section avatar>
            <q-avatar size="32px" color="grey-2" text-color="primary" :icon="menu.icon || 'folder'" />
          </q-item-section>

          <q-item-section>
            <q-item-label class="text-weight-bold text-subtitle1">{{ menu.label }}</q-item-label>
            <q-item-label caption class="row items-center q-gutter-x-sm">
                <span v-if="menu.route" class="text-code bg-grey-2 q-px-xs rounded-borders text-grey-8">
                    {{ menu.route }}
                </span>
                <span v-if="menu.permission" class="text-code bg-orange-1 text-orange-9 q-px-xs rounded-borders">
                    <q-icon name="lock" size="xs" />
                    {{ menu.permission }}
                </span>
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <div class="row items-center q-gutter-x-sm">
               <!-- Status Indicators -->
              <q-badge v-if="!menu.is_active" color="red-1" text-color="red-9" label="Inactive" />
              <q-badge v-if="menu.is_feature_flag" color="purple-1" text-color="purple-9" label="Feature Flag" />
              
              <div class="q-separator vertical q-mx-sm" />

              <!-- Actions -->
              <q-btn
                flat dense round
                icon="edit"
                color="grey-7"
                class="hover_primary"
                @click.stop="$emit('edit', menu)"
              >
                <q-tooltip>Edit</q-tooltip>
              </q-btn>
              
              <q-btn
                flat dense round
                icon="delete"
                color="grey-7"
                class="hover_negative"
                @click.stop="$emit('delete', menu)"
              >
                <q-tooltip>Delete</q-tooltip>
              </q-btn>
              
              <q-icon name="drag_indicator" class="drag-handle cursor-move text-grey-5 hover_text_dark q-ml-sm" size="sm" />
            </div>
          </q-item-section>
        </q-item>

        <!-- Children Menu Items -->
        <div v-if="menu.children && menu.children.length" class="menu-children-container bg-grey-1 q-py-xs">
            <q-item
            v-for="child in menu.children"
            :key="child.id"
            :class="['menu-child-item q-py-xs', { 'menu-inactive': !child.is_active }]"
            clickable
            v-ripple
            dense
            >
            
            <!-- Tree Line Visual -->
            <q-item-section avatar class="q-pl-lg" style="min-width: 60px;">
                <div class="tree-line"></div>
                 <q-icon :name="child.icon || 'subdirectory_arrow_right'" size="xs" color="grey-6" />
            </q-item-section>

            <q-item-section>
                <q-item-label>{{ child.label }}</q-item-label>
                <q-item-label caption class="row items-center q-gutter-x-sm">
                 <span v-if="child.route" class="text-caption text-grey-6">{{ child.route }}</span>
                 <span v-if="child.permission" class="text-caption text-orange-8">
                     <q-icon name="lock" size="10px" /> {{ child.permission }}
                 </span>
                </q-item-label>
            </q-item-section>

            <q-item-section side>
                <div class="row q-gutter-x-xs opacity-hover-group">
                <q-badge v-if="!child.is_active" color="red-1" text-color="red-9" label="Inactive" dense />
                 <!-- Feature Flag Badge -->
                 <q-icon v-if="child.is_feature_flag" name="flag" color="purple" size="xs">
                    <q-tooltip>Feature Flagged</q-tooltip>
                 </q-icon>

                <q-btn
                    flat dense round
                    icon="edit"
                    color="grey-7"
                    size="sm"
                    @click.stop="$emit('edit', child)"
                />
                <q-btn
                    flat dense round
                    icon="delete"
                    color="grey-7"
                    size="sm"
                    class="hover_negative_text"
                    @click.stop="$emit('delete', child)"
                />
                </div>
            </q-item-section>
            </q-item>
        </div>
      </template>
    </q-list>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useSortable } from '@vueuse/integrations/useSortable';

const props = defineProps({
  menus: {
    type: Array,
    required: true,
  },
  module: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(['edit', 'delete', 'reorder']);

const sortableList = ref(null);

const sortedMenus = computed(() => {
  return [...props.menus].sort((a, b) => a.order - b.order);
});

// Initialize sortable
onMounted(() => {
  if (sortableList.value && sortableList.value.$el) {
    useSortable(sortableList.value.$el, sortedMenus.value, {
      animation: 150,
      handle: '.drag-handle',
      ghostClass: 'sortable-ghost',
      chosenClass: 'sortable-chosen',
      dragClass: 'sortable-drag',
      filter: '.menu-child', // Don't allow dragging children
      onEnd: (evt) => {
        const items = sortedMenus.value.map((menu, index) => ({
          id: menu.id,
          order: index,
        }));
        emit('reorder', items);
      },
    });
  }
});
</script>

<style scoped>
.menu-item {
  transition: background-color 0.2s ease;
}

.menu-item:hover {
  background-color: #f5f5f5;
}

.menu-child-item {
    transition: background-color 0.2s ease;
}
.menu-child-item:hover {
    background-color: #e0e0e0;
}

.menu-inactive {
  opacity: 0.6;
  filter: grayscale(0.8);
}

.sortable-ghost {
  opacity: 0.4;
  background-color: #e3f2fd;
  border: 2px dashed #1976d2;
}

.sortable-chosen {
  background-color: #e3f2fd;
}

.sortable-drag {
  opacity: 1;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.hover_primary:hover {
    color: var(--q-primary) !important;
    background-color: rgba(25, 118, 210, 0.1);
}

.hover_negative:hover {
    color: var(--q-negative) !important;
    background-color: rgba(193, 7, 7, 0.1);
}

.hover_negative_text:hover {
    color: var(--q-negative) !important;
}

.text-code {
    font-family: monospace;
    font-size: 0.85em;
}

.tree-line {
    position: absolute;
    left: 28px;
    top: 0;
    bottom: 0px;
    width: 2px;
    background-color: #e0e0e0;
    z-index: 0;
}
/* Connect tree line horizontally to icon */
.menu-child-item .q-item__section--avatar {
    position: relative;
    overflow: visible; 
}
.menu-child-item .q-item__section--avatar::before {
    content: '';
    position: absolute;
    left: -14px; /* Adjust based on tree-line left */
    top: 50%;
    width: 14px;
    height: 2px;
    background-color: #e0e0e0;
}

.menu-children-container {
    position: relative;
}
.menu-children-container::before {
    content: '';
    position: absolute;
    left: 44px; /* Align with parent avatar center usually around 16px + 16px pad */
    top: 0;
    bottom: 15px; /* Stop before last item fully ends */
    width: 2px;
    background-color: #e0e0e0;
}
</style>
