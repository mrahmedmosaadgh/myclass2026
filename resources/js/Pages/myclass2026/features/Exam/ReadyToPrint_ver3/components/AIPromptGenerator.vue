<template>
  <q-dialog v-model="isOpen" maximized transition-show="slide-up" transition-hide="slide-down">
    <q-card class="prompt-dialog">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">General AI Prompt</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pt-none">
        <div class="text-caption q-mb-md">
          This prompt includes all current settings, question validation, and instructions for AI to ask about missing information before generating.
        </div>

        <q-input
          v-model="localPrompt"
          outlined
          type="textarea"
          label="AI Prompt"
          rows="20"
          readonly
          class="prompt-textarea"
        />

        <div v-if="validationStats.errors.length > 0" class="q-mt-md">
          <q-banner class="bg-orange-1" rounded>
            <template v-slot:avatar>
              <q-icon name="warning" color="orange" />
            </template>
            <div class="text-subtitle2">Detected Issues ({{ validationStats.errors.length }})</div>
            <div class="text-caption">
              <ul>
                <li v-for="(error, index) in validationStats.errors.slice(0, 5)" :key="index">
                  Question {{ error.questionId }}: {{ error.message }}
                </li>
                <li v-if="validationStats.errors.length > 5">
                  ... and {{ validationStats.errors.length - 5 }} more
                </li>
              </ul>
            </div>
          </q-banner>
        </div>

        <div v-else class="q-mt-md">
          <q-banner class="bg-green-1" rounded>
            <template v-slot:avatar>
              <q-icon name="check_circle" color="green" />
            </template>
            <div class="text-subtitle2">No issues detected</div>
            <div class="text-caption">All questions are valid and ready for AI generation.</div>
          </q-banner>
        </div>
      </q-card-section>

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          color="primary"
          icon="content_copy"
          label="Copy to Clipboard"
          @click="handleCopy"
          :loading="copying"
        />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useQuasar } from 'quasar'
import { useAIPrompts } from '../composables/useAIPrompts'
import { useQuestionValidation } from '../composables/useQuestionValidation'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  pageOptions: {
    type: Object,
    required: true
  },
  sampleQuestions: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const $q = useQuasar()
const { generatedPrompt, generateGeneralAIPrompt, copyPrompt } = useAIPrompts()
const { detectQuestionErrors } = useQuestionValidation()

const isOpen = ref(props.modelValue)
const localPrompt = ref('')
const copying = ref(false)

const validationStats = computed(() => {
  const errors = detectQuestionErrors(props.sampleQuestions)
  const total = props.sampleQuestions?.length || 0
  return {
    total,
    valid: total - errors.length,
    invalid: errors.length,
    errors
  }
})

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal
  if (newVal) {
    generatePrompt()
  }
})

watch(isOpen, (newVal) => {
  emit('update:modelValue', newVal)
})

function generatePrompt() {
  localPrompt.value = generateGeneralAIPrompt(
    { value: props.pageOptions },
    { value: props.sampleQuestions },
    () => detectQuestionErrors(props.sampleQuestions)
  )
}

async function handleCopy() {
  copying.value = true
  const success = await copyPrompt()
  copying.value = false

  if (success) {
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      position: 'top'
    })
  } else {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy prompt',
      position: 'top'
    })
  }
}
</script>

<style scoped>
.prompt-dialog {
  max-width: 900px;
}

.prompt-textarea {
  font-family: 'Courier New', monospace;
  font-size: 13px;
}
</style>
