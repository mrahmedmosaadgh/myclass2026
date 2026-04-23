<template>
  <div class="answer-table">
    <table class="answer-key-table">
      <thead>
        <tr>
          <th class="text-center" style="width: 60px;">#</th>
          <th>Question</th>
          <th class="text-center" style="width: 80px;">Marks</th>
          <th class="text-center" style="width: 100px;">Answer</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(question, index) in questions" :key="question.id">
          <td class="text-center">{{ index + 1 }}</td>
          <td>
            <div class="question-content">{{ getQuestionText(question) }}</div>
          </td>
          <td class="text-center">{{ question.marks }}</td>
          <td class="text-center">
            <span class="answer-text">{{ getAnswerText(question) }}</span>
          </td>
        </tr>
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="2" class="text-right"><strong>Total:</strong></td>
          <td class="text-center"><strong>{{ totalMarks }}</strong></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  questions: {
    type: Array,
    required: true
  }
})

const totalMarks = computed(() => {
  return props.questions.reduce((sum, q) => sum + (q.marks || 0), 0)
})

function getQuestionText(question) {
  if (question.content) {
    // Strip HTML tags for cleaner display
    return question.content.replace(/<[^>]*>/g, '').substring(0, 100) + (question.content.length > 100 ? '...' : '')
  }
  return 'N/A'
}

function getAnswerText(question) {
  if (question.type === 'mcq' && question.correct_answer) {
    return question.correct_answer
  } else if (question.type === 'true_false' && question.correct_answer !== undefined) {
    return question.correct_answer ? 'True' : 'False'
  } else if (question.correct_answer) {
    return question.correct_answer
  }
  return '-'
}
</script>

<style scoped>
.answer-table {
  width: 100%;
  margin: 20px 0;
}

.answer-key-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.answer-key-table thead {
  background-color: #f5f5f5;
}

.answer-key-table th {
  padding: 8px;
  border: 1px solid #ddd;
  font-weight: 600;
  color: #333;
}

.answer-key-table td {
  padding: 8px;
  border: 1px solid #ddd;
  vertical-align: top;
}

.answer-key-table tbody tr:nth-child(even) {
  background-color: #fafafa;
}

.answer-key-table tbody tr:hover {
  background-color: #f0f0f0;
}

.question-content {
  max-width: 300px;
  word-wrap: break-word;
  line-height: 1.4;
}

.answer-text {
  font-weight: 600;
  color: #1976d2;
}

.total-row {
  background-color: #e8f5e9;
  font-weight: 600;
}

.total-row td {
  border-top: 2px solid #4caf50;
}
</style>
