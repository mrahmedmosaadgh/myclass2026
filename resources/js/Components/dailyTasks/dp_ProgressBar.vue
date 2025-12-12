<template>
  <div class="q-pa-md">
    <div class="row justify-between q-mb-xs">
      <div class="text-subtitle2 text-primary">نقاطك اليوم: {{ points }}</div>
      <div class="text-caption text-grey">المستوى التالي: {{ max }}</div>
    </div>
    <q-linear-progress :value="progress" color="secondary" size="15px" rounded stripe class="q-mb-sm">
      <div class="absolute-full flex flex-center">
        <q-badge color="white" text-color="secondary" :label="`${Math.round(progress * 100)}%`" />
      </div>
    </q-linear-progress>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  points: {
    type: Number,
    default: 0
  },
  max: {
    type: Number,
    default: 100
  }
})

const progress = computed(() => Math.min(props.points / props.max, 1))
</script>
