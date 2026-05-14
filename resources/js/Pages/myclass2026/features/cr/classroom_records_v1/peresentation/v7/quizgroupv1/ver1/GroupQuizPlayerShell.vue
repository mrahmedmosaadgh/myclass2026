<script setup>
import { computed } from 'vue'
import GroupQuizVersionSelector from './components/GroupQuizVersionSelector.vue'
import GroupQuizPlayerV1 from './GroupQuizPlayerV1.vue'
import { useGroupQuizVersion } from './composables/useGroupQuizVersion'

const { versions, selectedVersion, selectedVersionMeta, setVersion } = useGroupQuizVersion()

const activeComponent = computed(() => {
  if (selectedVersion.value === 'ver1') return GroupQuizPlayerV1
  return GroupQuizPlayerV1
})
</script>

<template>
  <div class="shell">
    <GroupQuizVersionSelector :versions="versions" :model-value="selectedVersion" @update:model-value="setVersion" />

    <q-card flat bordered class="meta-card">
      <q-card-section>
        <div class="text-caption text-grey-7">Loaded version</div>
        <div class="text-h6 text-weight-bold">{{ selectedVersionMeta.label }}</div>
        <div class="text-body2 text-grey-8">{{ selectedVersionMeta.description }}</div>
      </q-card-section>
    </q-card>

    <component :is="activeComponent" />
  </div>
</template>

<style scoped>
.shell {
  display: grid;
  gap: 16px;
}

.meta-card {
  border-radius: 14px;
}
</style>
