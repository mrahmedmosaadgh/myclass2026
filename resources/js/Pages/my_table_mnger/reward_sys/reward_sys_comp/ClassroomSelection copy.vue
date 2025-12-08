<template>
  <div>
    <label class="block text-sm font-semibold mb-2">Classroom</label>
    <q-select
      :model-value="modelValue"
      :options="classrooms"
      option-value="classroom_id"
      option-label="classroom_name"
      outlined
      dense
      @update:model-value="$emit('update:modelValue', $event); $emit('change', $event)"
      emit-value
      map-options
    />
    <div class="mt-2 flex items-center gap-2">
      <q-btn
        color="primary"
        label="Init Session"
        dense
        :disable="!modelValue || loading"
        @click="$emit('init')"
      />
      <div v-if="initStatus && initStatus.message" class="text-sm text-gray-600">{{ initStatus.message }}</div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: null
  },
  classrooms: {
    type: Array,
    default: () => []
  },
  loading: {
    type: Boolean,
    default: false
  },
  initStatus: {
    type: Object,
    default: () => ({ message: '' })
  }
})

defineEmits(['update:modelValue', 'change', 'init'])
</script>
