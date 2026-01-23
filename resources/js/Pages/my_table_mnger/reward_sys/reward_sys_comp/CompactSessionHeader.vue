<template>
  <div class="compact-session-header bg-white q-pa-sm border-b">
    <!-- Row 1: Date and Week Selection -->
    <div class="row items-center q-gutter-sm q-mb-xs">
      <q-icon name="event" color="primary" size="sm" />
      
      <!-- Date Picker -->
      <q-input
        v-model="localDate"
        type="date"
        dense
        outlined
        label="Date"
        style="max-width: 150px"
        @update:model-value="$emit('update:date', $event)"
      >
        <template v-slot:prepend>
          <q-icon name="event" />
        </template>
      </q-input>
      
      <!-- Week Selector -->
      <q-select
        v-model="localWeek"
        :options="weekOptions"
        dense
        outlined
        label="Week"
        style="max-width: 120px"
        @update:model-value="$emit('update:week', $event)"
      >
        <template v-slot:prepend>
          <q-icon name="calendar_view_week" />
        </template>
      </q-select>
      
      <!-- Period Badge -->
      <q-badge color="primary" class="q-px-md q-py-sm">
        Period {{ period }}
      </q-badge>
    </div>
    
    <!-- Row 2: Classroom Info and Stats -->
    <div class="row items-center justify-between">
      <div class="row items-center">
        <div class="text-subtitle2 text-weight-medium q-mr-md">
          <q-icon name="class" color="primary" size="sm" class="q-mr-xs" />
          {{ classroomName }}
        </div>

        <!-- Avatar Edit Toggle -->
        <q-toggle
          v-model="localAvatarEdit"
          icon="edit"
          label="Edit Avatars"
          dense
          color="secondary"
          size="sm"
          class="q-mr-md text-caption"
          @update:model-value="$emit('update:avatarEdit', $event)"
        />

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

const localDate = ref(props.date)
const localWeek = ref(props.week)
const localAvatarEdit = ref(props.avatarEditEnabled)

// Week options (1-17 for typical semester)
const weekOptions = Array.from({ length: 17 }, (_, i) => ({
  label: `Week ${i + 1}`,
  value: i + 1
}))

// Watch for prop changes
watch(() => props.date, (newVal) => { localDate.value = newVal })
watch(() => props.week, (newVal) => { localWeek.value = newVal })
watch(() => props.avatarEditEnabled, (newVal) => { localAvatarEdit.value = newVal })
</script>

<style scoped>
.compact-session-header {
  border-bottom: 1px solid #e0e0e0;
}
</style>
