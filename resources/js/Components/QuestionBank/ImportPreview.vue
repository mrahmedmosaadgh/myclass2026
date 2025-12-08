<template>
  <div class="import-preview">
    <!-- Summary Stats -->
    <div class="row q-gutter-md q-mb-md">
      <q-chip
        v-if="summary.valid > 0"
        color="positive"
        text-color="white"
        icon="check_circle"
        size="md"
      >
        {{ summary.valid }} valid
      </q-chip>
      
      <q-chip
        v-if="summary.warnings > 0"
        color="warning"
        text-color="white"
        icon="warning"
        size="md"
      >
        {{ summary.warnings }} warnings
      </q-chip>
      
      <q-chip
        v-if="summary.errors > 0"
        color="negative"
        text-color="white"
        icon="error"
        size="md"
      >
        {{ summary.errors }} errors
      </q-chip>
    </div>

    <!-- Filter Options -->
    <div class="row items-center q-mb-md">
      <q-btn-toggle
        v-model="filter"
        toggle-color="primary"
        :options="filterOptions"
        size="sm"
      />
      
      <q-space />
      
      <div class="text-caption text-grey-7">
        Showing {{ filteredItems.length }} of {{ items.length }} questions
      </div>
    </div>

    <!-- Preview Table -->
    <q-table
      :rows="filteredItems"
      :columns="columns"
      row-key="row"
      flat
      bordered
      :pagination="{ rowsPerPage: 10 }"
      :rows-per-page-options="[5, 10, 20, 50]"
    >
      <!-- Status Column -->
      <template v-slot:body-cell-status="props">
        <q-td :props="props">
          <q-icon
            :name="props.row.valid ? 'check_circle' : 'error'"
            :color="props.row.valid ? 'positive' : 'negative'"
            size="sm"
          />
        </q-td>
      </template>

      <!-- Question Text Column -->
      <template v-slot:body-cell-question_text="props">
        <q-td :props="props">
          <div class="text-body2">{{ props.row.question_text }}</div>
          <div v-if="props.row.error" class="text-caption text-negative q-mt-xs">
            <q-icon name="error" size="xs" class="q-mr-xs" />
            {{ props.row.error }}
          </div>
          <div v-if="props.row.warning" class="text-caption text-warning q-mt-xs">
            <q-icon name="warning" size="xs" class="q-mr-xs" />
            {{ props.row.warning }}
          </div>
        </q-td>
      </template>

      <!-- Type Column -->
      <template v-slot:body-cell-question_type="props">
        <q-td :props="props">
          <q-badge :color="props.row.valid ? 'grey-7' : 'grey-4'">
            {{ props.row.question_type }}
          </q-badge>
        </q-td>
      </template>

      <!-- Difficulty Column -->
      <template v-slot:body-cell-difficulty="props">
        <q-td :props="props">
          <q-badge
            v-if="props.row.difficulty"
            :color="getDifficultyColor(props.row.difficulty)"
          >
            {{ props.row.difficulty }}
          </q-badge>
          <span v-else class="text-grey-5">N/A</span>
        </q-td>
      </template>

      <!-- Options Column -->
      <template v-slot:body-cell-options="props">
        <q-td :props="props">
          <div v-if="props.row.options && props.row.options.length > 0" class="text-caption">
            {{ props.row.options.length }} options
            <q-tooltip>
              <div v-for="(opt, idx) in props.row.options" :key="idx">
                {{ opt.option_key }}: {{ opt.option_text }}
                <q-icon v-if="opt.is_correct" name="check" color="positive" size="xs" />
              </div>
            </q-tooltip>
          </div>
          <span v-else class="text-grey-5">-</span>
        </q-td>
      </template>
    </q-table>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  items: {
    type: Array,
    required: true
  }
});

// State
const filter = ref('all');

const filterOptions = [
  { label: 'All', value: 'all' },
  { label: 'Valid Only', value: 'valid' },
  { label: 'Errors Only', value: 'errors' },
  { label: 'Warnings Only', value: 'warnings' }
];

const columns = [
  {
    name: 'status',
    label: '',
    field: 'valid',
    align: 'center',
    style: 'width: 50px'
  },
  {
    name: 'row',
    label: 'Row',
    field: 'row',
    align: 'left',
    style: 'width: 60px'
  },
  {
    name: 'question_text',
    label: 'Question',
    field: 'question_text',
    align: 'left',
    style: 'min-width: 300px'
  },
  {
    name: 'question_type',
    label: 'Type',
    field: 'question_type',
    align: 'center',
    style: 'width: 120px'
  },
  {
    name: 'difficulty',
    label: 'Difficulty',
    field: 'difficulty',
    align: 'center',
    style: 'width: 100px'
  },
  {
    name: 'options',
    label: 'Options',
    field: 'options',
    align: 'center',
    style: 'width: 100px'
  }
];

// Computed
const summary = computed(() => {
  return {
    valid: props.items.filter(item => item.valid && !item.warning).length,
    warnings: props.items.filter(item => item.warning).length,
    errors: props.items.filter(item => !item.valid).length
  };
});

const filteredItems = computed(() => {
  switch (filter.value) {
    case 'valid':
      return props.items.filter(item => item.valid && !item.warning);
    case 'errors':
      return props.items.filter(item => !item.valid);
    case 'warnings':
      return props.items.filter(item => item.warning);
    default:
      return props.items;
  }
});

// Methods
const getDifficultyColor = (difficulty) => {
  const difficultyMap = {
    'Easy': 'positive',
    'Medium': 'warning',
    'Hard': 'negative',
    'Very Easy': 'positive',
    'Very Hard': 'negative'
  };
  return difficultyMap[difficulty] || 'grey-7';
};
</script>

<style scoped lang="scss">
.import-preview {
  .text-caption {
    font-size: 0.75rem;
  }
}
</style>
