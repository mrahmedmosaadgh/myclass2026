<script setup>
defineProps({
  versions: {
    type: Array,
    required: true
  },
  modelValue: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <q-card flat bordered class="version-selector">
    <q-card-section class="selector-row">
      <div>
        <div class="text-subtitle1 text-weight-bold">Group Quiz Player Version</div>
        <div class="text-caption text-grey-7">Choose the reusable player version to load.</div>
      </div>

      <q-select
        :model-value="modelValue"
        :options="versions"
        option-label="label"
        option-value="value"
        emit-value
        map-options
        dense
        outlined
        style="min-width: 180px"
        @update:model-value="emit('update:modelValue', $event)"
      />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.version-selector {
  border-radius: 14px;
}

.selector-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}
</style>
