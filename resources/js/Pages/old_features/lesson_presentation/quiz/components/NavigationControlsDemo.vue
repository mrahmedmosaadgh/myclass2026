<template>
  <div class="demo-container">
    <h1>NavigationControls Component Demo</h1>
    
    <div class="demo-section">
      <h2>Standard Mode (No Review)</h2>
      <p>Next button is disabled until question is answered</p>
      <NavigationControls
        :allow-review-mode="false"
        :is-answered="isAnswered1"
        :is-last="false"
        :current-index="0"
        @next="handleNext"
      />
      <button @click="isAnswered1 = !isAnswered1" class="toggle-button">
        Toggle Answered: {{ isAnswered1 }}
      </button>
    </div>

    <div class="demo-section">
      <h2>Review Mode Enabled</h2>
      <p>Previous button appears, navigation always enabled</p>
      <NavigationControls
        :allow-review-mode="true"
        :is-answered="true"
        :is-last="false"
        :current-index="2"
        @previous="handlePrevious"
        @next="handleNext"
      />
    </div>

    <div class="demo-section">
      <h2>Last Question</h2>
      <p>Next button changes to "Finish Quiz" with green styling</p>
      <NavigationControls
        :allow-review-mode="false"
        :is-answered="true"
        :is-last="true"
        :current-index="9"
        @finish="handleFinish"
      />
    </div>

    <div class="demo-section">
      <h2>First Question in Review Mode</h2>
      <p>Previous button is disabled at index 0</p>
      <NavigationControls
        :allow-review-mode="true"
        :is-answered="true"
        :is-last="false"
        :current-index="0"
        @previous="handlePrevious"
        @next="handleNext"
      />
    </div>

    <div class="event-log">
      <h3>Event Log</h3>
      <div class="log-entries">
        <div v-for="(event, index) in events" :key="index" class="log-entry">
          {{ event }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import NavigationControls from './NavigationControls.vue'

const isAnswered1 = ref(false)
const events = ref<string[]>([])

const handlePrevious = () => {
  events.value.unshift(`[${new Date().toLocaleTimeString()}] Previous clicked`)
}

const handleNext = () => {
  events.value.unshift(`[${new Date().toLocaleTimeString()}] Next clicked`)
}

const handleFinish = () => {
  events.value.unshift(`[${new Date().toLocaleTimeString()}] Finish clicked`)
}
</script>

<style scoped>
.demo-container {
  max-width: 800px;
  margin: 2rem auto;
  padding: 2rem;
  font-family: system-ui, -apple-system, sans-serif;
}

h1 {
  color: #1f2937;
  margin-bottom: 2rem;
}

.demo-section {
  margin-bottom: 3rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.demo-section h2 {
  color: #374151;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.demo-section p {
  color: #6b7280;
  margin-bottom: 1rem;
}

.toggle-button {
  margin-top: 1rem;
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  cursor: pointer;
  font-size: 0.875rem;
}

.toggle-button:hover {
  background-color: #2563eb;
}

.event-log {
  margin-top: 2rem;
  padding: 1.5rem;
  background-color: #1f2937;
  border-radius: 0.5rem;
  color: #f9fafb;
}

.event-log h3 {
  margin-bottom: 1rem;
  color: #10b981;
}

.log-entries {
  max-height: 200px;
  overflow-y: auto;
  font-family: 'Courier New', monospace;
  font-size: 0.875rem;
}

.log-entry {
  padding: 0.25rem 0;
  border-bottom: 1px solid #374151;
}

.log-entry:last-child {
  border-bottom: none;
}
</style>
