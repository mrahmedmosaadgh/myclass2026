<template>
  <q-dialog v-model="open">
    <q-card style="min-width: 720px; max-width: 92vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Edit MCQ</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-input
          v-model="form.prompt"
          label="Question Prompt"
          type="textarea"
          filled
          autogrow
        />

        <div class="q-mt-md">
          <div class="text-subtitle2 q-mb-xs">Options</div>

          <div v-for="(opt, idx) in form.options" :key="idx" class="row items-start q-col-gutter-sm q-mb-sm">
            <div class="col-auto" style="width: 64px">
              <q-radio
                v-model="form.correctIndex"
                :val="idx"
                dense
                label=""
              />
            </div>

            <div class="col">
              <q-input
                v-model="form.options[idx]"
                :label="`Option ${optionLetter(idx)}`"
                filled
                dense
                autogrow
                type="textarea"
              />
            </div>

            <div class="col-auto">
              <q-btn
                flat
                dense
                icon="delete"
                color="negative"
                @click="removeOption(idx)"
              />
            </div>
          </div>

          <q-btn flat dense icon="add" label="Add option" @click="addOption" />
        </div>

        <q-separator class="q-my-md" />

        <q-input
          v-model="form.explanation"
          label="Explanation"
          type="textarea"
          filled
          autogrow
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn flat label="Cancel" color="grey" v-close-popup />
        <q-btn flat label="Save" color="primary" @click="onSave" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed, reactive, watch } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  question: { type: Object, default: null },
})

const emit = defineEmits(['update:modelValue', 'save'])

const open = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v),
})

const form = reactive({
  prompt: '',
  options: [],
  correctIndex: null,
  explanation: '',
})

function normalizeOption(opt) {
  if (opt && typeof opt === 'object' && 'text' in opt) return String(opt.text ?? '')
  return String(opt ?? '')
}

function optionLetter(idx) {
  return String.fromCharCode('A'.charCodeAt(0) + idx)
}

function resetFromQuestion(q) {
  const content = q?.content || {}
  form.prompt = String(content.prompt ?? '')
  form.options = Array.isArray(content.options) ? content.options.map(normalizeOption) : []

  const v = content.correct_option_index ?? content.correct_answer ?? q?.correct_answer
  const idx = (typeof v === 'number' && Number.isFinite(v))
    ? v
    : ((typeof v === 'string' && v.trim() !== '' && !Number.isNaN(Number(v))) ? Number(v) : null)
  form.correctIndex = idx

  form.explanation = String(content.explanation ?? q?.explanation ?? '')

  if (!form.options.length) form.options = ['', '', '', '']
}

watch(
  () => props.question,
  (q) => resetFromQuestion(q),
  { immediate: true }
)

watch(
  () => props.modelValue,
  (v) => {
    if (v) resetFromQuestion(props.question)
  }
)

function addOption() {
  form.options.push('')
}

function removeOption(idx) {
  if (form.options.length <= 2) return
  form.options.splice(idx, 1)
  if (form.correctIndex === idx) form.correctIndex = null
  if (typeof form.correctIndex === 'number' && form.correctIndex > idx) form.correctIndex -= 1
}

function onSave() {
  emit('save', {
    prompt: form.prompt,
    options: form.options.map((t) => ({ text: String(t ?? '') })),
    correct_option_index: form.correctIndex,
    explanation: form.explanation,
  })
  open.value = false
}
</script>
