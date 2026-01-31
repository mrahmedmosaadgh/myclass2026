<template>
  <div class="menu-icon-picker">
    <!-- Input Field (Trigger) -->
    <q-input
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      label="Icon"
      outlined
      dense
      class="cursor-pointer"
      readonly
      @click="showDialog = true"
    >
      <template v-slot:prepend>
        <q-icon :name="modelValue || 'help_outline'" />
      </template>
      <template v-slot:append>
        <q-icon name="grid_view" class="cursor-pointer" @click.stop="showDialog = true" />
      </template>
    </q-input>

    <!-- Icon Selection Dialog -->
    <q-dialog v-model="showDialog">
      <q-card style="min-width: 350px; max-width: 600px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Select Icon</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section>
          <q-input
            v-model="searchQuery"
            placeholder="Search icons..."
            outlined
            dense
            autofocus
            clearable
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </q-card-section>

        <q-card-section class="scroll" style="max-height: 400px">
          <div v-if="filteredIcons.length === 0" class="text-center text-grey q-pa-md">
            No icons found matching "{{ searchQuery }}"
          </div>
          
          <div class="row q-col-gutter-sm">
            <div 
                v-for="icon in filteredIcons" 
                :key="icon" 
                class="col-3 col-sm-2 text-center"
            >
              <q-btn
                flat
                round
                :color="modelValue === icon ? 'primary' : 'grey-8'"
                :icon="icon"
                @click="selectIcon(icon)"
                size="lg"
                class="q-pa-xs"
              >
                <q-tooltip>{{ icon }}</q-tooltip>
              </q-btn>
              <div class="text-caption ellipsis" style="font-size: 10px">{{ icon }}</div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue']);

const showDialog = ref(false);
const searchQuery = ref('');

// Curated list of common Material Icons for School/Admin context
const commonIcons = [
  // General
  'home', 'dashboard', 'settings', 'search', 'info', 'help', 'check_circle', 'warning', 'error',
  'add', 'edit', 'delete', 'save', 'close', 'menu', 'more_vert', 'refresh', 'filter_list',
  
  // People & Roles
  'person', 'group', 'people', 'school', 'face', 'support_agent', 'admin_panel_settings',
  'supervised_user_circle', 'perm_identity', 'badge',
  
  // Academic & Learning
  'class', 'menu_book', 'library_books', 'assignment', 'quiz', 'grade', 'history_edu',
  'science', 'calculate', 'language', 'computer', 'auto_stories', 'sticky_note_2',
  
  // Time & Schedule
  'schedule', 'calendar_today', 'calendar_month', 'event', 'alarm', 'timer', 'pending_actions',
  
  // Communication
  'email', 'chat', 'forum', 'notifications', 'campaign', 'announcement', 'call',
  'contact_mail',
  
  // Finance & Stats
  'payments', 'attach_money', 'receipt', 'analytics', 'bar_chart', 'pie_chart', 'trending_up',
  
  // Tech & Media
  'laptop', 'print', 'image', 'movie', 'mic', 'cloud', 'wifi', 'security', 'lock',
  
  // Misc
  'star', 'favorite', 'verified', 'flag', 'label', 'category', 'folder', 'description',
  'article', 'list', 'view_module', 'widgets', 'extension'
];

const filteredIcons = computed(() => {
  if (!searchQuery.value) return commonIcons;
  const query = searchQuery.value.toLowerCase();
  return commonIcons.filter(icon => icon.includes(query));
});

const selectIcon = (icon) => {
  emit('update:modelValue', icon);
  showDialog.value = false;
};
</script>

<style scoped>
.menu-icon-picker :deep(.q-field__native) {
    cursor: pointer;
}
</style>
