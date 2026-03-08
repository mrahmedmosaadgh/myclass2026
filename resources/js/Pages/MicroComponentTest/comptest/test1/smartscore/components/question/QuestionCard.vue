<template>
  <div class="question-card">
    <!-- Header slot -->
    <div v-if="$slots.header" class="question-header">
      <slot name="header"></slot>
    </div>
    
    <!-- Question content -->
    <div class="question-content">
      <slot name="content"></slot>
    </div>
    
    <!-- Answer input slot -->
    <div v-if="$slots['answer-input']" class="answer-input">
      <slot name="answer-input"></slot>
    </div>
    
    <!-- Footer slot -->
    <div v-if="$slots.footer" class="question-footer">
      <slot name="footer"></slot>
    </div>
  </div>
</template>

<script setup>
// This component provides a flexible container for questions
// It uses slots to allow maximum flexibility for different question types
</script>

<style scoped>
.question-card {
  width: 100%;
  max-width: 600px;
  margin: 0 auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  overflow: hidden;
}

.question-header {
  padding: 16px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.question-content {
  padding: 24px;
}

.answer-input {
  padding: 16px 24px;
  border-top: 1px solid #e5e7eb;
  background-color: #f9fafb;
}

.question-footer {
  padding: 16px;
  border-top: 1px solid #e5e7eb;
  background-color: #f3f4f6;
}
</style>