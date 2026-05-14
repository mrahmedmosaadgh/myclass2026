<script setup>
defineProps({
  groups: {
    type: Array,
    required: true
  }
})
</script>

<template>
  <q-card flat bordered class="score-card">
    <q-card-section>
      <div class="text-subtitle2 text-weight-bold q-mb-sm">Scores</div>
      <q-list dense>
        <q-item v-for="group in groups" :key="group.id">
          <q-item-section>
            <div class="group-name">
              <span class="dot" :style="{ backgroundColor: group.color }"></span>
              {{ group.name }}
            </div>
          </q-item-section>
          <q-item-section side>
            <q-badge color="primary" :label="group.score" />
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>
  </q-card>
</template>

<style scoped>
.score-card {
  border-radius: 16px;
}

.group-name {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 600;
}

.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
}
</style>
