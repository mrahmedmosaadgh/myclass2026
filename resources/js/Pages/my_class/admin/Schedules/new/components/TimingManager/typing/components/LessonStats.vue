<template>
  <div class="lesson-stats q-pa-md">
    <div class="row justify-between items-center">
      <div class="stat-card">
        <q-icon name="speed" size="2rem" color="primary" />
        <div class="stat-content">
          <div class="stat-value">{{ wpm }}</div>
          <div class="stat-label">WPM</div>
        </div>
      </div>

      <div class="stat-card">
        <q-icon name="grade" size="2rem" color="secondary" />
        <div class="stat-content">
          <div class="stat-value">{{ accuracy }}%</div>
          <div class="stat-label">Accuracy</div>
        </div>
      </div>

      <div class="stat-card">
        <q-icon name="error" size="2rem" color="negative" />
        <div class="stat-content">
          <div class="stat-value">{{ errors }}</div>
          <div class="stat-label">Errors</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  wpm: {
    type: Number,
    default: 0
  },
  accuracy: {
    type: Number,
    default: 100
  },
  errors: {
    type: Number,
    default: 0
  }
});
</script>

<style scoped>
.lesson-stats {
  background: #f5f5f5;
  border-radius: 8px;
  margin: 20px auto;
  max-width: 800px;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  min-width: 120px;
}

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  line-height: 1;
}

.stat-label {
  font-size: 14px;
  color: #666;
  margin-top: 4px;
}
</style>