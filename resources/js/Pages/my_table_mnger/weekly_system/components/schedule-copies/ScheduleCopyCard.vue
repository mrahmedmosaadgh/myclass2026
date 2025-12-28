<template>
  <q-card class="schedule-copy-card" :class="{ 'active-card': copy.status === 'active' }">
    <!-- Header with status -->
    <q-card-section class="q-pb-sm">
      <div class="row items-center justify-between">
        <div class="text-h6 text-weight-bold">{{ copy.name }}</div>
        <StatusBadge :status="copy.status" />
      </div>
      <div class="text-caption text-grey-7">{{ copy.description || 'No description' }}</div>
    </q-card-section>

    <q-separator />

    <!-- Metadata -->
    <q-card-section class="q-py-sm">
      <div class="row q-gutter-md">
        <div class="col-auto">
          <q-icon name="school" size="xs" color="primary" class="q-mr-xs" />
          <span class="text-body2">{{ copy.school?.name || 'N/A' }}</span>
        </div>
        <div class="col-auto">
          <q-icon name="calendar_today" size="xs" color="secondary" class="q-mr-xs" />
          <span class="text-body2">{{ copy.academic_year?.name || 'N/A' }}</span>
        </div>
        <div class="col-auto">
          <q-icon name="date_range" size="xs" color="accent" class="q-mr-xs" />
          <span class="text-body2">{{ copy.semester?.name || 'N/A' }}</span>
        </div>
      </div>
    </q-card-section>

    <!-- Additional Info -->
    <q-card-section class="q-pt-none">
      <div class="row q-gutter-sm text-caption text-grey-6">
        <div v-if="copy.copy_date">
          <q-icon name="event" size="xs" /> {{ formatDate(copy.copy_date) }}
        </div>
        <div v-if="copy.week_number">
          <q-icon name="view_week" size="xs" /> Week {{ copy.week_number }}
        </div>
        <div v-if="copy.created_by_user">
          <q-icon name="person" size="xs" /> {{ copy.created_by_user?.name }}
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <!-- Actions -->
    <q-card-actions align="right">
      <q-btn flat dense color="primary" icon="edit" label="Edit" @click="$emit('edit', copy)" />
      <q-btn 
        v-if="copy.status !== 'active'" 
        flat dense color="green" 
        icon="check_circle" 
        label="Activate" 
        @click="$emit('activate', copy)" 
      />
      <q-btn 
        v-if="copy.status === 'active'" 
        flat dense color="orange" 
        icon="archive" 
        label="Archive" 
        @click="$emit('archive', copy)" 
      />
      <q-btn 
        flat dense color="red" 
        icon="delete" 
        @click="$emit('delete', copy)"
      />
    </q-card-actions>
  </q-card>
</template>

<script setup>
import StatusBadge from '../shared/StatusBadge.vue'

defineProps({
  copy: {
    type: Object,
    required: true
  }
})

defineEmits(['edit', 'activate', 'archive', 'delete'])

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}
</script>

<style scoped>
.schedule-copy-card {
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.schedule-copy-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.active-card {
  border-color: var(--q-positive);
  background: linear-gradient(135deg, rgba(76, 175, 80, 0.05), transparent);
}
</style>
