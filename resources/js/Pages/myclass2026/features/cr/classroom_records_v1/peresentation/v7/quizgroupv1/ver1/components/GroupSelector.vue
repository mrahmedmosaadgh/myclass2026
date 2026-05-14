<script setup>
defineProps({
  groups: {
    type: Array,
    required: true
  },
  selectedGroupId: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['select'])
</script>

<template>
  <q-card flat bordered class="selector-card">
    <q-card-section>
      <div class="text-subtitle2 text-weight-bold q-mb-sm">Select Active Group</div>
      <div class="group-list">
        <q-btn
          v-for="group in groups"
          :key="group.id"
          :label="group.name"
          :outline="selectedGroupId !== group.id"
          :style="{ color: selectedGroupId === group.id ? 'white' : group.color, backgroundColor: selectedGroupId === group.id ? group.color : 'transparent' }"
          no-caps
          rounded
          @click="emit('select', group.id)"
        />
      </div>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.selector-card {
  border-radius: 16px;
}

.group-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}
</style>
