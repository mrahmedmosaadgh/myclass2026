<template>
  <Head title="My Weekly Plans Editor" />
  
  <!-- Shared Weekly Plans Manager Component -->
  <Manager
    :items="myAssignments"
    title="My Weekly Plans"
    subtitle="Edit and manage your weekly lesson plans."
    :loading="loading"
    stats-title="My Assignments"
    stats-icon="assignment"
    item-singular="class"
    item-plural="classes"
    list-title="My Classes"
    empty-icon="assignment_late"
    empty-message="You haven't been assigned to any classes yet."
    default-item-icon="class"
    item-color="secondary"
    action-icon="edit"
    action-label="Edit Plans"
    @item-click="editPlans"
    @action-click="editPlans"
  >
    <!-- Custom stats display -->
    <template #info-stats>
      <q-card class="q-mb-xl">
        <q-card-section>
          <div class="row items-center q-gutter-md">
            <q-icon name="assignment" size="3rem" color="primary" />
            <div>
              <div class="text-h6 text-weight-bold">My Assignments</div>
              <div class="text-body2 text-grey-7">
                {{ myAssignments.length }} assigned {{ myAssignments.length === 1 ? 'class' : 'classes' }}
              </div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </template>

    <!-- Custom item content -->
    <template #item-content="{ item }">
      <div v-if="item.subject_name" class="text-caption text-grey-6 q-mt-xs">
        {{ item.subject_name }}
      </div>
    </template>

    <!-- Quick Actions Section -->
    <template #footer>
      <q-card class="q-mt-lg">
        <q-card-section>
          <div class="text-h6 text-weight-bold q-mb-md">Quick Actions</div>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-4">
              <q-btn 
                outline 
                rounded 
                color="primary" 
                icon="content_copy" 
                label="Copy Plans" 
                class="full-width"
                :disable="!canCopyBetweenClasses"
                @click="$emit('copy-plans')"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-btn 
                outline 
                rounded 
                color="secondary" 
                icon="calendar_today" 
                label="View Schedule" 
                class="full-width"
                @click="$emit('view-schedule')"
              />
            </div>
            <div class="col-12 col-md-4">
              <q-btn 
                outline 
                rounded 
                color="accent" 
                icon="school" 
                label="View Curriculum" 
                class="full-width"
                @click="$emit('view-curriculum')"
              />
            </div>
          </div>
        </q-card-section>
      </q-card>
    </template>
  </Manager>
</template>

<script setup>
import { ref } from 'vue'
import Manager from './Manager.vue'

const props = defineProps({
  myAssignments: {
    type: Array,
    required: true
  },
  canEditOwn: {
    type: Boolean,
    default: true
  },
  canCopyBetweenClasses: {
    type: Boolean,
    default: false
  }
})

const loading = ref(false)

const editPlans = (assignment) => {
  console.log('Editing plans for:', assignment)
  // TODO: Navigate to plan editor or open dialog
}
</script>
