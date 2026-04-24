<template>
  <div class="choice-key-table">
    <table class="choice-key">
      <thead>
        <tr>
          <th class="text-center" style="width: 60px;">#</th>
          <th class="text-center" style="width: 120px;">Correct Choice</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(question, index) in questions" :key="question.id">
          <td class="text-center">{{ index + 1 }}</td>
          <td class="text-center">
            <span class="choice-text">{{ getCorrectChoiceLabel(question) }}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  questions: {
    type: Array,
    required: true
  },
  labelStyle: {
    type: String,
    default: 'letter'
  },
  customLabelTemplate: {
    type: String,
    default: '{letter})'
  }
})

function labelForIndex(idx) {
  const i = Number(idx)
  if (!Number.isFinite(i) || i < 0) return '-'

  const n = i + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + i)

  if (props.labelStyle === 'number') return String(n)
  if (props.labelStyle === 'custom') {
    return String(props.customLabelTemplate)
      .replaceAll('{i}', String(i))
      .replaceAll('{n}', String(n))
      .replaceAll('{letter}', String(letter))
  }

  return String(letter)
}

function getCorrectChoiceIndex(question) {
  const type = question?.type
  if (type !== 'multiple_choice') return null

  // correct_option_index is canonical (ver 3); fall back to correct_answer for legacy data
  const topLevel = question?.correct_option_index ?? question?.correct_answer
  if (typeof topLevel === 'number' && Number.isFinite(topLevel)) return topLevel
  if (typeof topLevel === 'string' && topLevel.trim() !== '' && !Number.isNaN(Number(topLevel))) return Number(topLevel)

  const opts = question?.content?.options
  // Try content.correct_option_index first, then content.correct_answer
  const correctRef = question?.content?.correct_option_index ?? question?.content?.correct_answer

  // If it's already a numeric index
  if (typeof correctRef === 'number' && Number.isFinite(correctRef)) return correctRef
  if (typeof correctRef === 'string' && correctRef.trim() !== '' && !Number.isNaN(Number(correctRef))) return Number(correctRef)

  // Fall back: match by text
  if (!Array.isArray(opts) || opts.length === 0) return null
  if (typeof correctRef !== 'string' || correctRef.trim() === '') return null

  const idx = opts.findIndex(o => String(o).trim() === String(correctRef).trim())
  return idx >= 0 ? idx : null
}

function getCorrectChoiceLabel(question) {
  const idx = getCorrectChoiceIndex(question)
  if (idx === null) return '-'
  return labelForIndex(idx)
}
</script>

<style scoped>
.choice-key-table {
  width: 100%;
}

.choice-key {
  width: 100%;
  border-collapse: collapse;
  font-size: 12px;
}

.choice-key thead {
  background-color: #f5f5f5;
}

.choice-key th,
.choice-key td {
  padding: 8px;
  border: 1px solid #ddd;
}

.choice-key tbody tr:nth-child(even) {
  background-color: #fafafa;
}

.choice-text {
  font-weight: 600;
  color: #1976d2;
}
</style>
