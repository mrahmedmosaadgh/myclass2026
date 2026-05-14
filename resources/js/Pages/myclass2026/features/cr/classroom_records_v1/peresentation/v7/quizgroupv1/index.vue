<script setup>
import GroupQuizPlayerShell from './ver1/GroupQuizPlayerShell.vue'
</script>

<template>
  <div class="quizgroup-page">
    <div class="page-header">
      <div>
        <div class="eyebrow">Presentation V7</div>
        <h1>Reusable Group Quiz Player</h1>
        <p>Versioned test page for the reusable Group Quiz player.</p>
      </div>
    </div>

    <GroupQuizPlayerShell />
  </div>
</template>

<style scoped>
.quizgroup-page {
  min-height: 100vh;
  padding: 24px;
  background: #f8fafc;
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.eyebrow {
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

h1 {
  margin: 4px 0 6px;
  color: #0f172a;
  font-size: 28px;
  font-weight: 800;
}

p {
  margin: 0;
  color: #64748b;
}
</style>
