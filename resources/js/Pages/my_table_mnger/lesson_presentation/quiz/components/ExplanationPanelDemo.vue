<template>
  <div class="demo-container">
    <h1>ExplanationPanel Component Demo</h1>
    
    <div class="demo-section">
      <h2>Example 1: Visible with Explanation</h2>
      <ExplanationPanel
        :explanation="sampleExplanation1"
        :is-visible="true"
      />
    </div>

    <div class="demo-section">
      <h2>Example 2: Hidden (isVisible = false)</h2>
      <ExplanationPanel
        :explanation="sampleExplanation1"
        :is-visible="false"
      />
      <p class="note">Note: Panel should not be visible above</p>
    </div>

    <div class="demo-section">
      <h2>Example 3: No Explanation Text</h2>
      <ExplanationPanel
        :explanation="undefined"
        :is-visible="true"
      />
      <p class="note">Note: Panel should not be visible above (no explanation text)</p>
    </div>

    <div class="demo-section">
      <h2>Example 4: Long Explanation</h2>
      <ExplanationPanel
        :explanation="sampleExplanation2"
        :is-visible="true"
      />
    </div>

    <div class="demo-section">
      <h2>Example 5: Toggle Visibility</h2>
      <button @click="toggleVisibility" class="toggle-btn">
        {{ isVisible ? 'Hide' : 'Show' }} Explanation
      </button>
      <ExplanationPanel
        :explanation="sampleExplanation1"
        :is-visible="isVisible"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ExplanationPanel from './ExplanationPanel.vue'

const isVisible = ref(true)

const sampleExplanation1 = "The correct answer is B because photosynthesis is the process by which plants convert light energy into chemical energy. This occurs in the chloroplasts of plant cells."

const sampleExplanation2 = `The Pythagorean theorem states that in a right triangle, the square of the hypotenuse (the side opposite the right angle) is equal to the sum of the squares of the other two sides.

This can be written as: a² + b² = c²

Where:
- a and b are the lengths of the two legs
- c is the length of the hypotenuse

This theorem is fundamental in geometry and has numerous applications in mathematics, physics, and engineering.`

const toggleVisibility = () => {
  isVisible.value = !isVisible.value
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
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 2rem;
}

h2 {
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
}

.demo-section {
  margin-bottom: 3rem;
  padding: 1.5rem;
  background-color: #f9fafb;
  border-radius: 0.5rem;
  border: 1px solid #e5e7eb;
}

.note {
  margin-top: 1rem;
  padding: 0.75rem;
  background-color: #fef3c7;
  border-left: 3px solid #f59e0b;
  font-size: 0.875rem;
  color: #92400e;
  border-radius: 0.25rem;
}

.toggle-btn {
  padding: 0.5rem 1rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 500;
  cursor: pointer;
  margin-bottom: 1rem;
  transition: background-color 0.2s;
}

.toggle-btn:hover {
  background-color: #2563eb;
}

.toggle-btn:active {
  background-color: #1d4ed8;
}
</style>
