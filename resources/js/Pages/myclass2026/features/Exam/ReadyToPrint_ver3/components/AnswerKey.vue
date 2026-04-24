<template>
  <div class="answer-key-container">
    <div class="answer-key-header">
      <h3 class="text-h6 text-center q-mb-md">Answer Key</h3>
    </div>

    <!-- Diagnostics panel: shown when there are issues -->
    <div v-if="diagnostics.length" class="answer-key-diagnostics q-mb-md">
      <q-banner rounded class="bg-orange-1 text-orange-10">
        <template #avatar>
          <q-icon name="warning" color="orange-8" />
        </template>
        <div class="text-subtitle2 q-mb-xs">Answer Key Issues ({{ diagnostics.length }})</div>
        <ul class="diagnostics-list q-ma-none q-pl-md">
          <li v-for="(d, i) in diagnostics" :key="i" class="text-caption">
            <strong>Q{{ d.index + 1 }}</strong>
            <span v-if="d.type === 'missing_answer'"> — Missing correct answer</span>
            <span v-else-if="d.type === 'invalid_json'"> — Invalid JSON in content: <code>{{ d.detail }}</code></span>
            <span v-else-if="d.type === 'invalid_index'"> — correct_answer index out of range ({{ d.detail }})</span>
            <span v-else> — {{ d.detail }}</span>
          </li>
        </ul>
        <div class="text-caption q-mt-xs text-grey-7">See browser console for full details.</div>
      </q-banner>
    </div>

    <AnswerTable :questions="questions" />

    <div v-if="showNotes" class="answer-key-notes q-mt-md">
      <p class="text-caption text-grey-7">
        <em>Note: This answer key should be separated from the exam paper before distribution to students.</em>
      </p>
    </div>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import AnswerTable from './AnswerTable.vue'

const props = defineProps({
  questions: {
    type: Array,
    required: true
  },
  showNotes: {
    type: Boolean,
    default: true
  }
})

/**
 * Validate each question's answer key data.
 * Returns an array of diagnostic objects.
 */
const diagnostics = computed(() => {
  const issues = []

  props.questions.forEach((q, i) => {
    // 1. Validate content is parseable (if it's a string, try JSON.parse)
    let content = q.content
    if (typeof content === 'string') {
      try {
        content = JSON.parse(content)
      } catch (e) {
        issues.push({ index: i, type: 'invalid_json', detail: String(e.message).slice(0, 80) })
        return // skip further checks for this question
      }
    }

    const correctAnswer = content?.correct_answer ?? q.correct_answer

    // 2. Check for missing correct answer
    if (correctAnswer === undefined || correctAnswer === null || correctAnswer === '') {
      issues.push({ index: i, type: 'missing_answer', detail: null })
      return
    }

    // 3. For MCQ: validate index is within options range
    if (q.type === 'multiple_choice') {
      const options = content?.options ?? q.options ?? []
      const idx = typeof correctAnswer === 'number'
        ? correctAnswer
        : (!Number.isNaN(Number(correctAnswer)) ? Number(correctAnswer) : null)

      if (idx !== null && (idx < 0 || idx >= options.length)) {
        issues.push({
          index: i,
          type: 'invalid_index',
          detail: `index ${idx}, but only ${options.length} option(s) available`
        })
      }
    }
  })

  return issues
})

// Log to console whenever diagnostics change
watch(diagnostics, (issues) => {
  if (!issues.length) return

  console.group('[AnswerKey] Validation Issues')
  issues.forEach(d => {
    const q = props.questions[d.index]
    const label = `Q${d.index + 1} (id: ${q?.id ?? 'unknown'}, type: ${q?.type ?? 'unknown'})`
    if (d.type === 'missing_answer') {
      console.warn(`${label} — Missing correct answer`)
      console.log('  Expected JSON shape:', JSON.stringify({
        id: '<string>',
        type: q?.type ?? 'multiple_choice',
        marks: 1,
        content: {
          prompt: '<question text>',
          options: q?.type === 'multiple_choice' ? ['Option A', 'Option B', 'Option C', 'Option D'] : undefined,
          correct_answer: q?.type === 'multiple_choice' ? 0 : '<answer text>'
        }
      }, null, 2))
    } else if (d.type === 'invalid_json') {
      console.error(`${label} — Invalid JSON in content: ${d.detail}`)
    } else if (d.type === 'invalid_index') {
      console.warn(`${label} — correct_answer index out of range: ${d.detail}`)
    }
  })
  console.groupEnd()
}, { immediate: true })
</script>

<style scoped>
.answer-key-container {
  page-break-inside: avoid;
  margin: 20px 0;
}

.answer-key-header {
  border-bottom: 2px solid #333;
  padding-bottom: 10px;
  margin-bottom: 15px;
}

.answer-key-notes {
  border-top: 1px dashed #ccc;
  padding-top: 10px;
}

.answer-key-diagnostics {
  border-radius: 6px;
  overflow: hidden;
}

.diagnostics-list {
  list-style: disc;
}

.diagnostics-list li {
  margin-bottom: 2px;
}
</style>
