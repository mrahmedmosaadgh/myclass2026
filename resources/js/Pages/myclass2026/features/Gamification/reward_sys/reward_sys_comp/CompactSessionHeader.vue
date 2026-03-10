<template>
  <div class="compact-session-header bg-white q-pa-sm border-b">
    <!-- Row 1: Session Info -->
    <div class="row items-center q-gutter-sm q-mb-xs">
      <q-icon name="event" color="primary" size="sm" />
      
      <!-- Period Badge -->
      <q-badge color="primary" class="q-px-md q-py-sm">
        Period {{ period }}
      </q-badge>
       
       <!-- Display Text Date (Optional, but useful to keep context if inputs are gone) -->
       <div class="text-caption text-grey-7">
          {{ new Date(props.date).toLocaleDateString() }} (Week {{ week }})
       </div>
    </div>
    
    <!-- Row 2: Classroom Info and Stats -->
    <div class="row items-center justify-between">
      <div class="row items-center">
        <div class="text-subtitle2 text-weight-medium q-mr-md">
          <q-icon name="class" color="primary" size="sm" class="q-mr-xs" />
          {{ classroomName }}
        </div>

        <!-- Init Status Message -->
        <div v-if="initStatus" class="text-caption text-grey-7 bg-grey-2 px-2 py-0.5 rounded">
           {{ initStatus }}
        </div>
      </div>
      
      <!-- Quick Stats -->
      <div class="row q-gutter-sm" v-if="stats">
        <q-chip dense size="sm" color="positive" text-color="white" icon="check_circle">
          {{ stats.present || 0 }}
        </q-chip>
        <q-chip dense size="sm" color="negative" text-color="white" icon="cancel">
          {{ stats.absent || 0 }}
        </q-chip>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  date: { type: String, required: true },
  week: { type: Number, required: true },
  period: { type: Number, required: true },
  classroomName: { type: String, default: '' },
  stats: { type: Object, default: null },
  avatarEditEnabled: { type: Boolean, default: false },
  initStatus: { type: String, default: '' }
})

const emit = defineEmits(['update:date', 'update:week', 'update:avatarEdit'])
</script>

<style scoped>
.compact-session-header {
  border-bottom: 1px solid #e0e0e0;
}
</style>
