<template>
  <div class="context-editor">
    <div v-if="selectedSection" class="editor-section">
      <h3>{{ $t('exam.readyToPrint.editor.section') }}</h3>
      <q-form class="editor-form">
        <q-input
          v-model="sectionForm.title"
          :label="$t('exam.readyToPrint.section.title')"
          outlined
          dense
          @update:model-value="updateSection"
        />
        <q-input
          v-model="sectionForm.instructions"
          type="textarea"
          :label="$t('exam.readyToPrint.section.instructions')"
          outlined
          rows="3"
          @update:model-value="updateSection"
        />

        <q-toggle
          v-model="sectionForm.forceQuestionsToEssay"
          label="Force questions to be essay (hide options, add workspace)"
          @update:model-value="updateSection"
        />
      </q-form>
    </div>

    <div v-else-if="selectedQuestion" class="editor-question">
      <h3>{{ $t('exam.readyToPrint.editor.question') }}</h3>
      <q-form class="editor-form">
        <q-select
          v-model="questionForm.type"
          :options="questionTypeOptions"
          :label="$t('exam.readyToPrint.question.type')"
          outlined
          dense
          emit-value
          map-options
          @update:model-value="updateQuestion"
        />
        <q-input
          v-model.number="questionForm.marks"
          type="number"
          :label="$t('exam.readyToPrint.question.marks')"
          outlined
          dense
          @update:model-value="updateQuestion"
        />
        <q-input
          v-model="questionForm.content.prompt"
          type="textarea"
          :label="$t('exam.readyToPrint.question.prompt')"
          outlined
          rows="4"
          @update:model-value="updateQuestion"
        />

        <div v-if="questionForm.type === 'mcq'">
          <div style="font-weight:600; margin-bottom: 6px;">Options</div>

          <q-input
            v-for="(opt, idx) in (questionForm.content.options || [])"
            :key="idx"
            v-model="questionForm.content.options[idx]"
            :label="'Option ' + (idx + 1)"
            outlined
            dense
            @update:model-value="updateQuestion"
          >
            <template #append>
              <q-btn
                flat
                dense
                round
                icon="delete"
                @click="removeOption(idx)"
              />
            </template>
          </q-input>

          <q-btn
            flat
            icon="add"
            label="Add option"
            @click="addOption"
          />
        </div>
      </q-form>
    </div>

    <div v-else class="editor-placeholder">
      <div class="placeholder-content">
        <q-icon name="edit" size="48px" color="grey-5" />
        <p>{{ $t('exam.readyToPrint.editor.placeholder') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, watch, reactive } from 'vue'
import { useI18n } from 'vue-i18n'
import { useExamReadyToPrintStore } from '@/Stores/examReadyToPrintStore'

const { t } = useI18n()
const store = useExamReadyToPrintStore()

const selectedSection = computed(() => store.selectedSection)
const selectedQuestion = computed(() => store.selectedQuestion)

const sectionForm = reactive({
  title: '',
  instructions: '',
  forceQuestionsToEssay: false,
})

const questionForm = reactive({
  type: 'text',
  marks: 1,
  content: { prompt: '' },
})

const questionTypeOptions = [
  { label: t('exam.readyToPrint.questionTypes.text'), value: 'text' },
  { label: t('exam.readyToPrint.questionTypes.mcq'), value: 'mcq' },
  { label: t('exam.readyToPrint.questionTypes.essay'), value: 'essay' },
  { label: t('exam.readyToPrint.questionTypes.trueFalse'), value: 'true_false' },
]

// Sync forms with store selection
watch(selectedSection, (section) => {
  if (section) {
    sectionForm.title = section.title || ''
    sectionForm.instructions = section.instructions || ''
    sectionForm.forceQuestionsToEssay = !!section.rules?.forceQuestionsToEssay
  }
}, { immediate: true })

watch(selectedQuestion, (question) => {
  if (question) {
    questionForm.type = question.type || 'text'
    questionForm.marks = question.marks || 1
    const content = question.content || { prompt: '' }
    questionForm.content = {
      ...content,
      options: Array.isArray(content.options) ? [...content.options] : (questionForm.type === 'mcq' ? ['', '', '', ''] : []),
    }
  }
}, { immediate: true })

function ensureOptionsArray() {
  if (!questionForm.content) questionForm.content = { prompt: '' }
  if (!Array.isArray(questionForm.content.options)) questionForm.content.options = []
}

function addOption() {
  ensureOptionsArray()
  questionForm.content.options.push('')
  updateQuestion()
}

function removeOption(idx) {
  ensureOptionsArray()
  questionForm.content.options.splice(idx, 1)
  updateQuestion()
}

function updateSection() {
  if (!selectedSection.value) return
  store.updateSection(selectedSection.value.id, {
    title: sectionForm.title,
    instructions: sectionForm.instructions,
    rules: {
      ...(selectedSection.value.rules || {}),
      forceQuestionsToEssay: !!sectionForm.forceQuestionsToEssay,
    },
  })
}

function updateQuestion() {
  if (!selectedQuestion.value) return
  store.updateQuestion(store.selection.sectionId, selectedQuestion.value.id, {
    type: questionForm.type,
    marks: questionForm.marks,
    content: questionForm.content,
  })
}
</script>

<style scoped>
.context-editor {
  height: 100%;
  padding: 24px;
  overflow-y: auto;
}

.editor-section,
.editor-question {
  max-width: 600px;
}

.editor-section h3,
.editor-question h3 {
  margin-top: 0;
  margin-bottom: 20px;
  font-size: 18px;
  font-weight: 600;
}

.editor-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.editor-placeholder {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.placeholder-content {
  text-align: center;
  color: #666;
}

.placeholder-content p {
  margin-top: 16px;
  font-size: 16px;
}
</style>
