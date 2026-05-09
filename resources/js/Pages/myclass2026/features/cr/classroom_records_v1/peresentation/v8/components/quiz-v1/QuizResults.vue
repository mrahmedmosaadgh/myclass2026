<script setup>
import { computed } from 'vue'

const props = defineProps({
  questions: {
    type: Array,
    required: true
  },
  userAnswers: {
    type: Object,
    default: () => ({})
  },
  quizTitle: {
    type: String,
    default: 'Quiz'
  }
})

const score = computed(() => {
  let correct = 0
  props.questions.forEach((question, index) => {
    if (props.userAnswers?.[index] === question.correctAnswer) {
      correct++
    }
  })
  return correct
})

const totalQuestions = computed(() => props.questions.length)

const percentage = computed(() => {
  return Math.round((score.value / totalQuestions.value) * 100)
})

const getScoreClass = () => {
  if (percentage.value >= 80) return 'excellent'
  if (percentage.value >= 60) return 'good'
  if (percentage.value >= 40) return 'fair'
  return 'poor'
}

const getScoreEmoji = () => {
  if (percentage.value >= 80) return '🎉'
  if (percentage.value >= 60) return '👍'
  if (percentage.value >= 40) return '😐'
  return '😔'
}

const getQuestionStatus = (question, index) => {
  const userAnswer = props.userAnswers?.[index]
  if (userAnswer === undefined) return 'unanswered'
  if (userAnswer === question.correctAnswer) return 'correct'
  return 'incorrect'
}

const getStatusIcon = (status) => {
  switch (status) {
    case 'correct':
      return '✓'
    case 'incorrect':
      return '✗'
    default:
      return '?'
  }
}
</script>

<template>
  <div class="quiz-results">
    <!-- Score Summary -->
    <div class="score-summary">
      <div class="score-circle" :class="getScoreClass()">
        <div class="score-emoji">{{ getScoreEmoji() }}</div>
        <div class="score-text">{{ score }}/{{ totalQuestions }}</div>
        <div class="score-percentage">{{ percentage }}%</div>
      </div>
      
      <div class="score-details">
        <h4 class="quiz-title">{{ quizTitle }} - Results</h4>
        <div class="score-stats">
          <div class="stat-item">
            <span class="stat-label">Correct:</span>
            <span class="stat-value correct">{{ score }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Incorrect:</span>
            <span class="stat-value incorrect">{{ totalQuestions - score }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Unanswered:</span>
            <span class="stat-value unanswered">{{ totalQuestions - Object.keys(userAnswers).length }}</span>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Question Breakdown -->
    <div class="question-breakdown">
      <h5>Question Breakdown</h5>
      
      <div class="questions-list">
        <div
          v-for="(question, index) in questions"
          :key="index"
          class="question-result"
          :class="getQuestionStatus(question, index)"
        >
          <div class="question-header">
            <div class="question-number">
              <span class="status-icon">{{ getStatusIcon(getQuestionStatus(question, index)) }}</span>
              Question {{ index + 1 }}
            </div>
            <div class="question-status">
              {{ getQuestionStatus(question, index).charAt(0).toUpperCase() + getQuestionStatus(question, index).slice(1) }}
            </div>
          </div>
          
          <div class="question-content">
            <div class="question-text">{{ question.question }}</div>
            
            <div class="answer-comparison">
              <div class="user-answer">
                <span class="answer-label">Your Answer:</span>
                <span class="answer-text">
                  {{ userAnswers?.[index] !== undefined ? question.options[userAnswers[index]] : 'Not answered' }}
                </span>
              </div>
              
              <div class="correct-answer">
                <span class="answer-label">Correct Answer:</span>
                <span class="answer-text">{{ question.options[question.correctAnswer] }}</span>
              </div>
            </div>
            
            <div v-if="question.explanation" class="explanation">
              <span class="explanation-label">Explanation:</span>
              <span class="explanation-text">{{ question.explanation }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.quiz-results {
  display: flex;
  flex-direction: column;
  gap: 20px;
  height: 100%;
  overflow-y: auto;
  padding: 4px;
}

.score-summary {
  display: flex;
  gap: 16px;
  align-items: center;
  padding: 16px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.score-circle {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  flex-shrink: 0;
}

.score-circle.excellent {
  background: rgba(34, 197, 94, 0.2);
  border-color: rgba(34, 197, 94, 0.4);
}

.score-circle.good {
  background: rgba(59, 130, 246, 0.2);
  border-color: rgba(59, 130, 246, 0.4);
}

.score-circle.fair {
  background: rgba(245, 158, 11, 0.2);
  border-color: rgba(245, 158, 11, 0.4);
}

.score-circle.poor {
  background: rgba(239, 68, 68, 0.2);
  border-color: rgba(239, 68, 68, 0.4);
}

.score-emoji {
  font-size: 24px;
  margin-bottom: 4px;
}

.score-text {
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 2px;
}

.score-percentage {
  font-size: 12px;
  opacity: 0.8;
}

.score-details {
  flex: 1;
}

.quiz-title {
  margin: 0 0 12px 0;
  font-size: 18px;
  font-weight: 600;
  color: white;
}

.score-stats {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
}

.stat-label {
  opacity: 0.8;
}

.stat-value {
  font-weight: 600;
}

.stat-value.correct {
  color: #22c55e;
}

.stat-value.incorrect {
  color: #ef4444;
}

.stat-value.unanswered {
  color: #f59e0b;
}

.question-breakdown {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.question-breakdown h5 {
  margin: 0 0 12px 0;
  font-size: 16px;
  font-weight: 600;
  color: white;
}

.questions-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.question-result {
  padding: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  border-left: 4px solid transparent;
}

.question-result.correct {
  border-left-color: #22c55e;
  background: rgba(34, 197, 94, 0.1);
}

.question-result.incorrect {
  border-left-color: #ef4444;
  background: rgba(239, 68, 68, 0.1);
}

.question-result.unanswered {
  border-left-color: #f59e0b;
  background: rgba(245, 158, 11, 0.1);
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.question-number {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
}

.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  font-size: 12px;
  font-weight: bold;
  background: rgba(255, 255, 255, 0.1);
}

.question-result.correct .status-icon {
  background: rgba(34, 197, 94, 0.2);
  color: #22c55e;
}

.question-result.incorrect .status-icon {
  background: rgba(239, 68, 68, 0.2);
  color: #ef4444;
}

.question-result.unanswered .status-icon {
  background: rgba(245, 158, 11, 0.2);
  color: #f59e0b;
}

.question-status {
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  opacity: 0.8;
}

.question-content {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.question-text {
  font-size: 14px;
  line-height: 1.4;
  margin-bottom: 8px;
}

.answer-comparison {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-answer,
.correct-answer {
  display: flex;
  gap: 8px;
  font-size: 13px;
}

.answer-label {
  font-weight: 600;
  opacity: 0.8;
  min-width: 80px;
}

.answer-text {
  flex: 1;
}

.question-result.correct .correct-answer .answer-text {
  color: #22c55e;
  font-weight: 600;
}

.question-result.incorrect .user-answer .answer-text {
  color: #ef4444;
  font-weight: 600;
}

.explanation {
  padding: 8px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
  font-size: 12px;
  line-height: 1.4;
}

.explanation-label {
  font-weight: 600;
  opacity: 0.8;
  display: block;
  margin-bottom: 4px;
}

.explanation-text {
  opacity: 0.9;
}

/* Mobile-first responsive design */
@media (max-width: 767px) {
  .score-summary {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }
  
  .score-circle {
    width: 70px;
    height: 70px;
  }
  
  .score-emoji {
    font-size: 20px;
  }
  
  .score-text {
    font-size: 13px;
  }
  
  .score-percentage {
    font-size: 11px;
  }
  
  .quiz-title {
    font-size: 16px;
  }
  
  .stat-item {
    font-size: 13px;
  }
  
  .question-result {
    padding: 10px;
  }
  
  .question-text {
    font-size: 13px;
  }
  
  .user-answer,
  .correct-answer {
    font-size: 12px;
  }
  
  .explanation {
    font-size: 11px;
  }
}

@media (max-width: 480px) {
  .score-circle {
    width: 60px;
    height: 60px;
  }
  
  .score-emoji {
    font-size: 18px;
  }
  
  .score-text {
    font-size: 12px;
  }
  
  .score-percentage {
    font-size: 10px;
  }
}
</style>
