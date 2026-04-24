<template>
  <q-dialog v-model="model">
    <q-card style="min-width: 560px; max-width: 90vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Open Exam</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="text-caption text-grey-7 q-mb-sm">
          Paste exam JSON here.
        </div>
        <q-input
          v-model="jsonText"
          type="textarea"
          autogrow
          outlined
          placeholder='{"questions": [...], "sections": [...], "settings": {...}}'
        />
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn color="primary" label="Open" @click="openFromJson" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  modelValue: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'loaded'])

const $q = useQuasar()
const jsonText = ref('')

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

function openFromJson() {
  let parsed
  try {
    parsed = JSON.parse(jsonText.value)
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Invalid JSON', position: 'top' })
    return
  }

  if (!parsed || (!Array.isArray(parsed.questions) && !Array.isArray(parsed.sampleQuestions))) {
    $q.notify({ type: 'negative', message: 'No questions found in JSON', position: 'top' })
    return
  }

  // Normalize questions to the current format before emitting
  const questions = (parsed.questions || parsed.sampleQuestions || []).map(normalizeQuestion)
  emit('loaded', { ...parsed, questions })
  model.value = false
}

/**
 * Migrate a question object to the current JSON format (ver 3):
 * - Stamps ver: 3 if missing
 * - Renames content.correct_answer → content.correct_option_index for MCQ
 *   (resolves text-based correct_answer to a numeric index when possible)
 */
function normalizeQuestion(q) {
  const out = { ...q }

  // Stamp version
  if (!out.ver) out.ver = 3

  if (out.type === 'multiple_choice' && out.content) {
    const c = { ...out.content }

    // Already has correct_option_index — nothing to do
    if (c.correct_option_index === undefined || c.correct_option_index === null) {
      const legacy = c.correct_answer

      if (legacy !== undefined && legacy !== null && legacy !== '') {
        // If it's already a number or numeric string, use it directly
        const asNum = Number(legacy)
        if (!Number.isNaN(asNum) && Number.isFinite(asNum)) {
          c.correct_option_index = asNum
        } else if (typeof legacy === 'string' && Array.isArray(c.options)) {
          // Try to match by text
          const idx = c.options.findIndex(o => String(o).trim() === String(legacy).trim())
          if (idx >= 0) c.correct_option_index = idx
        }
        // Remove the old field
        delete c.correct_answer
      }
    }

    out.content = c
  }

  return out
}
</script>
