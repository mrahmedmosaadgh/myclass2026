<template>
  <q-card class="gemini-prompt-card">
    <q-card-section class="bg-gradient-ai text-white">
      <div class="row items-center q-gutter-sm">
        <q-icon name="auto_awesome" size="md" />
        <div class="text-h6">AI Assistant</div>
        <q-space />
        <q-chip
          icon="psychology"
          color="white"
          text-color="primary"
          size="sm"
        >
          Powered by Gemini
        </q-chip>
      </div>
    </q-card-section>

    <q-card-section>
      <!-- Mode Selection -->
      <div class="row q-gutter-md q-mb-md">
        <q-btn-toggle
          v-model="mode"
          toggle-color="primary"
          :options="[
            { label: 'Generate Questions', value: 'questions', icon: 'quiz' },
            { label: 'Generate Answers', value: 'answers', icon: 'forum' },
            { label: 'Custom Prompt', value: 'custom', icon: 'edit' }
          ]"
          class="full-width"
        />
      </div>

      <!-- Question Generation Mode -->
      <div v-if="mode === 'questions'" class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">
          <q-icon name="quiz" class="q-mr-xs" />
          Question Generation Settings
        </div>

        <div class="row q-gutter-md q-mb-md">
          <q-select
            v-model="questionSettings.type"
            :options="questionTypes"
            label="Question Type"
            class="col"
            outlined
            dense
          />
          <q-select
            v-model="questionSettings.difficulty"
            :options="difficultyLevels"
            label="Difficulty Level"
            class="col"
            outlined
            dense
          />
        </div>

        <div class="row q-gutter-md q-mb-md">
          <q-input
            v-model="questionSettings.topic"
            label="Topic/Subject"
            placeholder="e.g., JavaScript, Project Management, Leadership"
            class="col"
            outlined
            dense
          />
          <q-input
            v-model="questionSettings.count"
            label="Number of Questions"
            type="number"
            min="1"
            max="10"
            class="col-3"
            outlined
            dense
          />
        </div>

        <q-input
          v-model="extraCmd"
          label="Additional Instructions (Optional)"
          placeholder="e.g., Focus on practical scenarios, Include code examples"
          type="textarea"
          rows="2"
          outlined
          class="q-mb-md"
          @update:model-value="val => emit('update:extra', val)"
        />
      </div>

      <!-- Answer Generation Mode -->
      <div v-if="mode === 'answers'" class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">
          <q-icon name="forum" class="q-mr-xs" />
          Answer Generation Settings
        </div>

        <q-input
          v-model="answerSettings.question"
          label="Question to Answer"
          placeholder="Enter the question you want to generate an answer for"
          type="textarea"
          rows="2"
          outlined
          class="q-mb-md"
          :rules="[val => !!val || 'Question is required for answer generation']"
        />

        <div class="row q-gutter-md q-mb-md">
          <q-select
            v-model="answerSettings.style"
            :options="answerStyles"
            label="Answer Style"
            class="col"
            outlined
            dense
          />
          <q-select
            v-model="answerSettings.length"
            :options="answerLengths"
            label="Answer Length"
            class="col"
            outlined
            dense
          />
        </div>

        <q-input
          v-model="extraCmd"
          label="Additional Context (Optional)"
          placeholder="e.g., Target audience, specific requirements, examples to include"
          type="textarea"
          rows="2"
          outlined
          class="q-mb-md"
          @update:model-value="val => emit('update:extra', val)"
        />
      </div>

      <!-- Custom Prompt Mode -->
      <div v-if="mode === 'custom'" class="q-mb-md">
        <div class="text-subtitle2 q-mb-sm">
          <q-icon name="edit" class="q-mr-xs" />
          Custom Prompt
        </div>

        <q-input
          v-model="userPrompt"
          label="Your Prompt"
          outlined
          dense
          autogrow
          :rules="[val => !!val || 'Prompt is required']"
          class="q-mb-sm"
          @update:model-value="val => emit('update:modelValue', val)"
        />

        <q-input
          v-model="extraCmd"
          label="Extra instructions (optional)"
          outlined
          dense
          autogrow
          class="q-mb-md"
          @update:model-value="val => emit('update:extra', val)"
        />
      </div>

      <!-- Generate Button -->
      <div class="row q-gutter-sm">
        <q-btn
          :loading="loading"
          :disable="!canGenerate"
          color="primary"
          icon="auto_awesome"
          :label="getGenerateButtonLabel()"
          @click="handleSubmit"
          class="col"
        />
        <q-btn
          v-if="response"
          icon="content_copy"
          flat
          color="primary"
          @click="copyToClipboard"
        >
          <q-tooltip>Copy Result</q-tooltip>
        </q-btn>
        <q-btn
          v-if="response"
          icon="clear"
          flat
          color="negative"
          @click="clearResult"
        >
          <q-tooltip>Clear Result</q-tooltip>
        </q-btn>
      </div>
    </q-card-section>

    <!-- Results Section -->
    <q-card-section v-if="response" class="bg-grey-1">
      <div class="row items-center q-mb-sm">
        <div class="text-subtitle2">
          <q-icon name="auto_awesome" class="q-mr-xs" />
          AI Generated Content
        </div>
        <q-space />
        <TextToSpeechButton
          :text="response"
          size="sm"
          round
          flat
        />
      </div>

      <q-separator class="q-mb-md" />

      <div class="ai-result">
        <q-markdown :src="response" />
      </div>

      <!-- Action Buttons for Results -->
      <div class="row q-gutter-sm q-mt-md" v-if="mode === 'questions'">
        <q-btn
          icon="add"
          label="Use as New Question"
          color="positive"
          flat
          @click="useAsQuestion"
        />
        <q-btn
          icon="edit"
          label="Edit & Use"
          color="primary"
          flat
          @click="editAndUse"
        />
      </div>

      <div class="row q-gutter-sm q-mt-md" v-if="mode === 'answers'">
        <q-btn
          icon="add"
          label="Use as Answer"
          color="positive"
          flat
          @click="useAsAnswer"
        />
        <q-btn
          icon="edit"
          label="Edit & Use"
          color="primary"
          flat
          @click="editAndUse"
        />
      </div>
    </q-card-section>
  </q-card>
</template>
  
<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import { GoogleGenerativeAI } from '@google/generative-ai'
import TextToSpeechButton from './TextToSpeechButton.vue'

// Props & Emits
const props = defineProps({
  modelValue: String,       // User prompt
  extra: String,            // Extra instructions
  autoRun: Boolean,         // Optional: auto send request on mount
  questionId: {             // For answer generation context
    type: [String, Number],
    default: null
  }
})

const emit = defineEmits([
  'update:modelValue',
  'update:extra',
  'response',
  'use-as-question',
  'use-as-answer'
])

const $q = useQuasar()

// Reactive state
const mode = ref('questions')
const userPrompt = ref(props.modelValue || '')
const extraCmd = ref(props.extra || '')
const response = ref('')
const loading = ref(false)

// Question Generation Settings
const questionSettings = ref({
  type: 'behavioral',
  difficulty: 'intermediate',
  topic: '',
  count: 3
})

// Answer Generation Settings
const answerSettings = ref({
  question: '',
  style: 'professional',
  length: 'detailed'
})
  
// Computed properties
const canGenerate = computed(() => {
  if (mode.value === 'questions') {
    return questionSettings.value.topic.trim().length > 0;
  } else if (mode.value === 'answers') {
    return answerSettings.value.question.trim().length > 0;
  } else {
    return userPrompt.value.trim().length > 0;
  }
})

// Options
const questionTypes = [
  { label: 'Behavioral', value: 'behavioral' },
  { label: 'Technical', value: 'technical' },
  { label: 'Situational', value: 'situational' },
  { label: 'Knowledge-based', value: 'knowledge' },
  { label: 'Problem-solving', value: 'problem-solving' }
]

const difficultyLevels = [
  { label: 'Beginner', value: 'beginner' },
  { label: 'Intermediate', value: 'intermediate' },
  { label: 'Advanced', value: 'advanced' },
  { label: 'Expert', value: 'expert' }
]

const answerStyles = [
  { label: 'Professional', value: 'professional' },
  { label: 'Conversational', value: 'conversational' },
  { label: 'Technical', value: 'technical' },
  { label: 'Educational', value: 'educational' },
  { label: 'Creative', value: 'creative' }
]

const answerLengths = [
  { label: 'Brief', value: 'brief' },
  { label: 'Detailed', value: 'detailed' },
  { label: 'Comprehensive', value: 'comprehensive' }
]

// Sync props with local refs
watch(() => props.modelValue, val => userPrompt.value = val)
watch(() => props.extra, val => extraCmd.value = val)
  
// Gemini API setup
const genAI = new GoogleGenerativeAI(import.meta.env.VITE_GEMINI_API_KEY)
  
// Methods
const getGenerateButtonLabel = () => {
  switch (mode.value) {
    case 'questions': return 'Generate Questions'
    case 'answers': return 'Generate Answer'
    default: return 'Ask Gemini'
  }
}

const buildPrompt = () => {
  let prompt = ''

  if (mode.value === 'questions') {
    const { type, difficulty, topic, count } = questionSettings.value
    prompt = `Generate ${count} ${difficulty} level ${type} interview questions about ${topic}.`

    if (extraCmd.value) {
      prompt += ` Additional requirements: ${extraCmd.value}`
    }

    prompt += ` Format the response as a numbered list with clear, well-structured questions. Make them practical and relevant for job interviews.`

  } else if (mode.value === 'answers') {
    const { question, style, length } = answerSettings.value
    prompt = `Provide a ${length} ${style} answer to this question: "${question}"`

    if (extraCmd.value) {
      prompt += ` Additional context: ${extraCmd.value}`
    }

    prompt += ` Make the answer well-structured, informative, and suitable for a job interview context.`

  } else {
    prompt = [userPrompt.value, extraCmd.value].filter(Boolean).join('\n\n')
  }

  return prompt
}

// Handle AI call
const handleSubmit = async () => {
  const prompt = buildPrompt()
  loading.value = true
  response.value = ''

  try {
    const model = genAI.getGenerativeModel({ model: 'gemini-1.5-flash' })
    const result = await model.generateContent(prompt)

    console.log('Full Gemini result:', result) // Debug log

    // Extract text from the response
    let text = 'No response.'

    if (result?.response) {
      console.log('Response object:', result.response) // Debug log

      try {
        text = result.response.text()
        console.log('Text extracted via .text():', text) // Debug log
      } catch (textError) {
        console.log('Error calling .text(), trying manual extraction:', textError)

        // Fallback to manual extraction from candidates
        const candidates = result.response.candidates
        console.log('Candidates:', candidates) // Debug log

        if (candidates && candidates.length > 0) {
          const parts = candidates[0]?.content?.parts
          console.log('Parts:', parts) // Debug log

          if (parts && parts.length > 0) {
            text = parts[0]?.text || 'No response.'
            console.log('Text extracted manually:', text) // Debug log
          }
        }
      }
    }

    console.log('Final AI Response:', text) // Debug log
    response.value = text
    emit('response', text)

    $q.notify({
      type: 'positive',
      message: `AI content generated successfully!`,
      position: 'top',
      icon: 'auto_awesome'
    })

  } catch (err) {
    console.error('Gemini Error:', err)
    console.error('Full error details:', err)
    $q.notify({
      type: 'negative',
      message: 'Failed to get a response from Gemini. Check console for details.'
    })
  }

  loading.value = false
}

const copyToClipboard = () => {
  navigator.clipboard.writeText(response.value).then(() => {
    $q.notify({
      type: 'positive',
      message: 'Content copied to clipboard!',
      position: 'top'
    })
  })
}

const clearResult = () => {
  response.value = ''
}

const useAsQuestion = () => {
  emit('use-as-question', response.value)
  $q.notify({
    type: 'info',
    message: 'Question content ready to use',
    position: 'top'
  })
}

const useAsAnswer = () => {
  emit('use-as-answer', response.value)
  $q.notify({
    type: 'info',
    message: 'Answer content ready to use',
    position: 'top'
  })
}

const editAndUse = () => {
  $q.notify({
    type: 'info',
    message: 'Edit functionality - copy the content and paste it in your form',
    position: 'top'
  })
}
  
// Watch for mode changes to clear results
watch(mode, () => {
  response.value = ''
})

// Auto run if enabled
onMounted(() => {
  if (props.autoRun && userPrompt.value) {
    handleSubmit()
  }
})
</script>

<style scoped>
.gemini-prompt-card {
  border-radius: 16px;
  overflow: hidden;
  max-width: 800px;
  margin: 0 auto;
}

.bg-gradient-ai {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.ai-result {
  max-height: 400px;
  overflow-y: auto;
  padding: 16px;
  background: white;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.ai-result :deep(.q-markdown) {
  font-size: 14px;
  line-height: 1.6;
}

.ai-result :deep(.q-markdown h1),
.ai-result :deep(.q-markdown h2),
.ai-result :deep(.q-markdown h3) {
  color: #1976d2;
  margin-top: 16px;
  margin-bottom: 8px;
}

.ai-result :deep(.q-markdown ul),
.ai-result :deep(.q-markdown ol) {
  padding-left: 20px;
}

.ai-result :deep(.q-markdown code) {
  background: #f5f5f5;
  padding: 2px 4px;
  border-radius: 4px;
}

.ai-result :deep(.q-markdown blockquote) {
  border-left: 4px solid #1976d2;
  padding-left: 16px;
  margin: 16px 0;
  color: #666;
}
</style>
  