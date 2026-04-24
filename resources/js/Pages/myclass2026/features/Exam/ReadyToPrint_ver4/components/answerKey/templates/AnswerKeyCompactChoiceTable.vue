<template>
  <div class="answer-key-section">
    <h3 class="ak-title">Answer Key</h3>

    <table class="ak-table">
      <thead>
        <tr>
          <th class="col-num">#</th>
          <th class="col-choice">Correct Choice</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(q, i) in questions" :key="String(q?.id ?? i)">
          <td class="col-num">{{ i + 1 }}</td>
          <td class="col-choice">
            <strong class="choice">{{ getAnswerKeyChoiceLabel(q, mcqOptions) }}</strong>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { getAnswerKeyChoiceLabel } from '../utils/answerKeyFormat'

defineProps({
  questions: { type: Array, required: true },
  mcqOptions: { type: Object, default: () => ({}) }
})
</script>

<style scoped>
.answer-key-section {
  margin: 20px 0;
  page-break-inside: avoid;
}

.ak-title {
  text-align: center;
  border-bottom: 2px solid #333;
  padding-bottom: 10px;
  margin: 0 0 15px;
}

.ak-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
  margin: 20px 0;
}

.ak-table thead {
  background: #f5f5f5;
}

.ak-table th,
.ak-table td {
  padding: 8px;
  border: 1px solid #ddd;
}

.col-num {
  width: 60px;
  text-align: center;
}

.col-choice {
  width: 140px;
  text-align: center;
}

.ak-table tbody tr:nth-child(even) {
  background: #fafafa;
}

.choice {
  color: #1976d2;
}
</style>
