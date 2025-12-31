<template>
  <div class="menu-list">
    <div v-if="menus.length === 0" class="text-center q-pa-lg text-grey-6">
      <q-icon name="menu" size="64px" class="q-mb-md" />
      <div class="text-h6">No menus in this module</div>
      <div class="text-subtitle2">Create your first menu to get started</div>
    </div>

    <q-list v-else bordered separator ref="sortableList">
      <template v-for="menu in sortedMenus" :key="menu.id">
        <!-- Parent Menu Item -->
        <q-item
          :class="['menu-item', { 'menu-inactive': !menu.is_active }]"
          :data-id="menu.id"
        >
          <q-item-section avatar>
            <q-icon :name="menu.icon || 'folder'" size="sm" />
          </q-item-section>

          <q-item-section>
            <q-item-label>{{ menu.label }}</q-item-label>
            <q-item-label caption>
              <q-badge v-if="menu.route" color="blue-grey-5" class="q-mr-xs">
                {{ menu.route }}
              </q-badge>
              <q-badge v-if="menu.permission" color="orange-5">
                {{ menu.permission }}
              </q-badge>
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <div class="row q-gutter-xs">
              <q-badge v-if="!menu.is_active" color="red" label="Inactive" />
              <q-badge v-if="menu.is_feature_flag" color="purple" label="Feature Flag" />
              <q-btn
                flat
                dense
                round
                icon="edit"
                color="primary"
                size="sm"
                @click="$emit('edit', menu)"
              >
                <q-tooltip>Edit Menu</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="delete"
                color="negative"
                size="sm"
                @click="$emit('delete', menu)"
              >
                <q-tooltip>Delete Menu</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="drag_indicator"
                color="grey-7"
                size="sm"
                class="drag-handle"
              >
                <q-tooltip>Drag to reorder</q-tooltip>
              </q-btn>
            </div>
          </q-item-section>
        </q-item>

        <!-- Children Menu Items -->
        <q-item
          v-for="child in menu.children"
          :key="child.id"
          :class="['menu-item menu-child', { 'menu-inactive': !child.is_active }]"
          :inset-level="1"
        >
          <q-item-section avatar>
            <q-icon :name="child.icon || 'subdirectory_arrow_right'" size="sm" />
          </q-item-section>

          <q-item-section>
            <q-item-label>{{ child.label }}</q-item-label>
            <q-item-label caption>
              <q-badge v-if="child.route" color="blue-grey-5" class="q-mr-xs">
                {{ child.route }}
              </q-badge>
              <q-badge v-if="child.permission" color="orange-5">
                {{ child.permission }}
              </q-badge>
            </q-item-label>
          </q-item-section>

          <q-item-section side>
            <div class="row q-gutter-xs">
              <q-badge v-if="!child.is_active" color="red" label="Inactive" />
              <q-btn
                flat
                dense
                round
                icon="edit"
                color="primary"
                size="sm"
                @click="$emit('edit', child)"
              >
                <q-tooltip>Edit Menu</q-tooltip>
              </q-btn>
              <q-btn
                flat
                dense
                round
                icon="delete"
                color="negative"
                size="sm"
                @click="$emit('delete', child)"
              >
                <q-tooltip>Delete Menu</q-tooltip>
              </q-btn>
            </div>
          </q-item-section>
        </q-item>
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
  transition: all 0.3s ease;
}

.menu-inactive {
  opacity: 0.5;
}

.menu-child {
  background-color: rgba(0, 0, 0, 0.02);
}

.sortable-ghost {
  opacity: 0.4;
  background-color: #e3f2fd;
}

.sortable-chosen {
  background-color: #bbdefb;
}

.sortable-drag {
  opacity: 1;
}

.drag-handle {
  cursor: move;
}
</style>
