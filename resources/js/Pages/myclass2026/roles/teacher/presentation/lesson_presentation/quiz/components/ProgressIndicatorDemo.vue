<template>
  <div class="demo-container">
    <h1>ProgressIndicator Component Demo</h1>
    
    <div class="demo-section">
      <h2>Interactive Demo</h2>
      <p>Use the controls below to test the ProgressIndicator component:</p>
      
      <div class="controls">
        <label>
          Current Question: {{ current }}
          <input 
            v-model.number="current" 
            type="range" 
            min="1" 
            :max="total" 
            class="slider"
          />
        </label>
        
        <label>
          Total Questions: {{ total }}
          <input 
            v-model.number="total" 
            type="range" 
            min="1" 
            max="50" 
            class="slider"
          />
        </label>
      </div>

      <div class="component-wrapper">
        <ProgressIndicator 
          :current="current" 
          :total="total" 
          :percentage="percentage" 
        />
      </div>

      <div class="info">
        <p><strong>Calculated Percentage:</strong> {{ percentage.toFixed(2) }}%</p>
      </div>
    </div>

    <div class="demo-section">
      <h2>Test Cases</h2>
      
      <div class="test-case">
        <h3>Start of Quiz (0%)</h3>
        <ProgressIndicator :current="1" :total="10" :percentage="0" />
      </div>

      <div class="test-case">
        <h3>Mid Quiz (50%)</h3>
        <ProgressIndicator :current="5" :total="10" :percentage="50" />
      </div>

      <div class="test-case">
        <h3>Near End (90%)</h3>
        <ProgressIndicator :current="9" :total="10" :percentage="90" />
      </div>

      <div class="test-case">
        <h3>Complete (100%)</h3>
        <ProgressIndicator :current="10" :total="10" :percentage="100" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import ProgressIndicator from './ProgressIndicator.vue'

const current = ref(1)
const total = ref(10)

const percentage = computed(() => {
  if (total.value === 0) return 0
  return ((current.value - 1) / total.value) * 100
})
</script>

<style scoped>
.demo-container {
  max-width: 900px;
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

h2 {
  color: #374151;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

h3 {
  color: #4b5563;
  margin-bottom: 0.75rem;
  font-size: 1rem;
}

.controls {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

label {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  font-weight: 500;
  color: #374151;
}

.slider {
  width: 100%;
  height: 6px;
  border-radius: 5px;
  background: #d1d5db;
  outline: none;
  -webkit-appearance: none;
}

.slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
}

.slider::-moz-range-thumb {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  border: none;
}

.component-wrapper {
  padding: 1.5rem;
  background-color: white;
  border-radius: 0.5rem;
  border: 2px solid #e5e7eb;
}

.info {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #eff6ff;
  border-radius: 0.375rem;
  border-left: 4px solid #3b82f6;
}

.info p {
  margin: 0;
  color: #1e40af;
}

.test-case {
  margin-bottom: 1.5rem;
  padding: 1rem;
  background-color: white;
  border-radius: 0.375rem;
  border: 1px solid #e5e7eb;
}

.test-case:last-child {
  margin-bottom: 0;
}

@media (max-width: 640px) {
  .demo-container {
    padding: 1rem;
  }

  .demo-section {
    padding: 1rem;
  }
}
</style>
