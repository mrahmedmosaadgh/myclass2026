<template>
  <div class="curriculum-lessons-container q-pa-lg">
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
          <q-btn
            v-if="canCreate"
            color="primary"
            label="Create Curriculum"
            @click="$emit('create')"
            icon="add"
          />
          
          <q-btn
            v-if="canSetLockDates"
            color="secondary"
            label="Manage Lock Dates"
            @click="$emit('manage-lock-dates')"
            icon="lock"
          />
        </slot>
        
        <!-- Additional custom actions from parent -->
        <slot name="additional-actions"></slot>
      </div>
    </div>

    <!-- Info Badge Slot (for teacher name, school info, etc.) -->
    <slot name="info-badge"></slot>

    <!-- Alert/Message Section -->
    <slot name="alerts">
      <!-- Default: No alerts -->
    </slot>

    <!-- Loading State -->
    <LoadingState 
      v-if="loading" 
      message="Loading curricula..." 
      height="400px"
    />

    <!-- Empty State -->
    <EmptyState
      v-else-if="!curricula || curricula.length === 0"
      icon="library_books"
      title="No Curricula Found"
      message="There are no curricula available yet."
    >
      <template #actions>
        <q-btn 
          v-if="canCreate"
          color="primary" 
          label="Create First Curriculum" 
          @click="$emit('create')"
          icon="add"
        />
      </template>
    </EmptyState>

    <!-- Data Table -->
    <q-table
      v-else
      :rows="curricula"
      :columns="computedColumns"
      row-key="id"
      flat
      bordered
      :loading="loading"
      :rows-per-page-options="[10, 25, 50]"
      :pagination="{ rowsPerPage: 25 }"
      class="curriculum-table"
    >
      <!-- Custom column rendering slots -->
      <template #body-cell-name="props">
        <q-td key="name" :props="props">
          <div class="text-weight-medium">{{ props.row.name }}</div>
          <div class="text-caption text-grey-7">{{ props.row.description }}</div>
        </q-td>
      </template>

      <template #body-cell-grade_name="props">
        <q-td key="grade_name" :props="props">
          <q-badge :color="props.row.grade_name ? 'primary' : 'grey'">
            {{ props.row.grade_name || 'N/A' }}
          </q-badge>
        </q-td>
      </template>

      <template #body-cell-subject_name="props">
        <q-td key="subject_name" :props="props">
          {{ props.row.subject_name || 'N/A' }}
        </q-td>
      </template>

      <template #body-cell-edit_lock_date="props">
        <q-td key="edit_lock_date" :props="props">
          <StatusBadge
            v-if="props.row.edit_lock_date"
            :status="isDatePast(props.row.edit_lock_date) ? 'locked' : 'upcoming'"
            :status-map="{
              locked: { label: 'Locked', color: 'negative' },
              upcoming: { label: 'Active', color: 'positive' }
            }"
          />
          <span v-else class="text-grey-7">Not set</span>
        </q-td>
      </template>

      <!-- Actions column - customizable via slot -->
      <template #body-cell-actions="props">
        <q-td key="actions" :props="props">
          <slot name="actions-cell" :row="props.row" :permissions="{
            canEdit: canEdit,
            canDelete: canDelete,
            isEditable: props.row.is_editable ?? true
          }">
            <!-- Default actions (can be overridden) -->
            <q-btn
              v-if="canEdit && (props.row.is_editable ?? true)"
              flat
              round
              dense
              color="primary"
              icon="edit"
              @click="$emit('edit', props.row)"
            >
              <q-tooltip>Edit</q-tooltip>
            </q-btn>
            
            <q-btn
              v-if="canDelete"
              flat
              round
              dense
              color="negative"
              icon="delete"
              @click="$emit('delete', props.row)"
            >
              <q-tooltip>Delete</q-tooltip>
            </q-btn>

            <q-badge 
              v-if="canEdit && !(props.row.is_editable ?? true)" 
              color="grey-6"
            >
              Locked
            </q-badge>
          </slot>
        </q-td>
      </template>
    </q-table>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import LoadingState from '../components/common/LoadingState.vue'
import EmptyState from '../components/common/EmptyState.vue'
import StatusBadge from '../components/common/StatusBadge.vue'

const props = defineProps({
  curricula: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Curriculum Management'
  },
  subtitle: {
    type: String,
    default: 'Manage your school\'s curricula'
  },
  canCreate: {
    type: Boolean,
    default: false
  },
  canEdit: {
    type: Boolean,
    default: false
  },
  canDelete: {
    type: Boolean,
    default: false
  },
  canSetLockDates: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  customColumns: {
    type: Array,
    default: null
  }
})

const emit = defineEmits(['create', 'edit', 'delete', 'manage-lock-dates'])

// Default columns
const defaultColumns = [
  { 
    name: 'name', 
    label: 'Curriculum', 
    field: 'name', 
    align: 'left', 
    sortable: true,
    style: 'width: 30%'
  },
  { 
    name: 'grade_name', 
    label: 'Grade', 
    field: 'grade_name', 
    align: 'left',
    sortable: true 
  },
  { 
    name: 'subject_name', 
    label: 'Subject', 
    field: 'subject_name', 
    align: 'left',
    sortable: true 
  },
  { 
    name: 'edit_lock_date', 
    label: 'Lock Date', 
    field: 'edit_lock_date', 
    align: 'center'
  },
  { 
    name: 'actions', 
    label: 'Actions', 
    field: 'actions', 
    align: 'right',
    style: 'width: 150px'
  }
]

const computedColumns = computed(() => {
  return props.customColumns || defaultColumns
})

const isDatePast = (dateString) => {
  if (!dateString) return false
  const date = new Date(dateString)
  const today = new Date()
  today.setHours(0, 0, 0, 0)
  return date < today
}
</script>

<style scoped>
.curriculum-lessons-container {
  max-width: 1400px;
  margin: 0 auto;
}

.curriculum-table {
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
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
</style>
