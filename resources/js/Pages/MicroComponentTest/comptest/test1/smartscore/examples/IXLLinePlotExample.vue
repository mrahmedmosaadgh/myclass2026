<template>
  <div class="example-container">
    <h2>IXL Line Plot Example</h2>
    
    <!-- Question card with line plot and smart score -->
    <QuestionCard>
      <template #header>
        <div class="question-header-content">
          <h3>Someone counted how many stuffed animals each student has.</h3>
        </div>
      </template>
      
      <template #content>
        <!-- Line plot visualization -->
        <LinePlot 
          :data="linePlotData"
          title="Stuffed animals"
          :axis="{ label: 'Number of stuffed animals' }"
          :visual="{
            xMarkColor: '#FF8C00',
            xMarkSize: 'medium',
            animation: true,
            showTicks: true,
            showLabels: true,
            gridVisible: true
          }"
        />
        
        <!-- Question text -->
        <div class="question-text mt-4">
          <p><strong>How many students in the class have exactly 3 stuffed animals?</strong></p>
        </div>
      </template>
      
      <template #answer-input>
        <div class="answer-section">
          <AnswerInput
            v-model="userAnswer"
            type="number"
            placeholder="Enter your answer"
            @submit="handleSubmit"
          />
          
          <div class="smart-score-section mt-4">
            <SmartScoreDisplay :data="smartScoreData" />
          </div>
        </div>
      </template>
      
      <template #footer>
        <div class="footer-actions">
          <SubmitButton 
            text="Submit" 
            :disabled="!userAnswer || submitting"
            :loading="submitting"
            @click="handleSubmit"
          />
        </div>
      </template>
    </QuestionCard>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { LinePlot, QuestionCard, AnswerInput, SubmitButton, SmartScoreDisplay } from '../index';

// Line plot data (matching the IXL example)
const linePlotData = {
  counts: {
    "0": 2,
    "1": 3,
    "2": 4,
    "3": 3,
    "4": 2
  },
  min: 0,
  max: 4,
  step: 1
};

// Smart score data
const smartScoreData = {
  currentScore: 85,
  maxScore: 100,
  masteryLevel: "Advanced",
  previousScore: 72,
  improvement: "+13",
  visual: {
    showPercentage: true,
    showMasteryBadge: true,
    showImprovement: true
  }
};

const userAnswer = ref('');
const submitting = ref(false);

const handleSubmit = () => {
  if (!userAnswer.value) return;
  
  submitting.value = true;
  
  // Simulate submission delay
  setTimeout(() => {
    submitting.value = false;
    // In a real implementation, this would send the answer to the server
    console.log('Submitted answer:', userAnswer.value);
  }, 1000);
};
</script>

<style scoped>
.example-container {
  padding: 20px;
}

.question-header-content h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
  color: #333;
}

.question-text {
  margin-top: 16px;
  font-size: 16px;
  color: #333;
}

.answer-section {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.smart-score-section {
  display: flex;
  justify-content: center;
}

.footer-actions {
  display: flex;
  justify-content: center;
}

.mt-4 {
  margin-top: 16px;
}
</style>