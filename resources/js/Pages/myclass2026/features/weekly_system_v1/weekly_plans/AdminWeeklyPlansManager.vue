<template>
  <Head title="Weekly Plans Manager" />
  
  <!-- Shared Weekly Plans Manager Component -->
  <Manager
    :items="allTeachers"
    title="Weekly Plans Manager"
    subtitle="Overview of all weekly plans, distributions, and teacher availability."
    :loading="loading"
    stats-title="All Teachers"
    stats-icon="groups"
    item-singular="teacher"
    item-plural="teachers"
    list-title="Teachers"
    empty-icon="people_outline"
    empty-message="No teachers found. Add teachers to your school to manage their weekly plans."
    default-item-icon="person"
    item-color="primary"
    action-icon="visibility"
    action-label="View Plans"
    @item-click="viewTeacherPlans"
    @action-click="viewTeacherPlans"
  >
    <!-- Custom stats display -->
    <template #info-stats>
      <q-card class="q-mb-xl">
        <q-card-section>
          <div class="row items-center q-gutter-md">
            <q-icon name="groups" size="3rem" color="primary" />
            <div>
              <div class="text-h6 text-weight-bold">All Teachers</div>
              <div class="text-body2 text-grey-7">
                {{ allTeachers.length }} {{ allTeachers.length === 1 ? 'teacher' : 'teachers' }} in your school
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </template>

    <!-- Custom item content -->
    <template #item-content="{ item }">
      <div v-if="item.email" class="text-caption text-grey-6 q-mt-xs">
        {{ item.email }}
      </div>
    </template>

    <!-- Additional admin actions (if needed) -->
    <template #additional-actions>
      <slot name="additional-actions"></slot>
    </template>

    <!-- Footer with bulk actions (placeholder) -->
    <template #footer>
      <div v-if="canBulkCopy" class="q-mt-lg">
        <q-btn 
          color="primary" 
          label="Bulk Copy Plans" 
          icon="content_copy"
          @click="$emit('bulk-copy')"
        />
      </div>
    </template>
  </Manager>
</template>

<script setup>
import { ref } from 'vue'
import Manager from './Manager.vue'

const props = defineProps({
  allTeachers: {
    type: Array,
    required: true
  },
  canViewAll: {
    type: Boolean,
    default: true
  },
  canBulkCopy: {
    type: Boolean,
    default: false
  },
  canViewStats: {
    type: Boolean,
    default: true
  }
})

const loading = ref(false)

const viewTeacherPlans = (teacher) => {
  console.log('Viewing teacher plans:', teacher)
  // TODO: Navigate to teacher's plans or open dialog
}
</script>
