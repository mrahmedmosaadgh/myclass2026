<template>
     <q-card  class="prompt-builder-dialog" style="width: 960px; max-width: 96vw; border-radius: 20px; overflow: hidden;">
      <!-- Header -->
      <q-card-section class="bg-gradient text-white q-py-md">
        <div class="row items-center justify-between q-px-md">
          <div>
            <div class="text-h5 flex items-center gap-3">
              <q-icon name="auto_awesome" size="2rem" />
              Quick Prompt Builder
              <q-chip dense color="white" text-color="primary" icon="bolt">
                {{ easyMode ? 'Easy Mode' : 'Pro Mode' }}
              </q-chip>
            </div>
            <div class="text-subtitle2 opacity-90 q-mt-xs">
              Build perfect AI prompts • History tags • Instant preview
            </div>
          </div>

          <div class="row items-center gap-3">
            <q-toggle
              v-model="easyMode"
              color="yellow"
              size="lg"
              icon="child_care"
              :label="easyMode ? 'Easy' : 'Pro'"
              keep-color
            />
            <q-btn icon="close" flat round dense v-close-popup />
          </div>
        </div>
      </q-card-section>

      <!-- Tabs -->
      <q-tabs
        v-model="activeTemplate"
        dense
        class="bg-grey-2"
        active-color="primary"
        indicator-color="primary"
        align="justify"
      >
        <q-tab name="pro-quiz" icon="verified" label="Pro Quiz">
          <q-badge color="positive" floating>Best</q-badge>
        </q-tab>
        <q-tab name="quiz" icon="quiz" label="Quick Quiz" />
        <q-tab name="lesson" icon="school" label="Lesson Plan" />
        <q-tab name="worksheet" icon="assignment" label="Worksheet" />
        <q-tab name="flashcards" icon="style" label="Flashcards" />
        <q-tab name="custom" icon="tune" label="Custom" />
      </q-tabs>

      <q-separator />

      <!-- Main Layout: Form + Preview Side by Side -->
      <div class="row q-pa-md" style="min-height: 560px;">
        <!-- LEFT: Form -->
        <div class="col-12 col-lg-6 q-pr-lg-lg" style="max-height: 70vh; overflow-y: auto;">
          <q-tab-panels v-model="activeTemplate" animated keep-alive>
            <!-- PRO QUIZ -->
            <q-tab-panel name="pro-quiz" class="q-pa-none">
              <div class="q-gutter-y-md">
                <q-input
                  v-model="proQuiz.grade"
                  filled
                  label="Grade / Course *"
                  placeholder="e.g. Grade 8, Algebra I, AP Biology"
                  @focus="trackField('grade')"
                >
                  <template v-slot:prepend><q-icon name="school" /></template>
                </q-input>

                <!-- History Tags for Grade -->
                <div v-if="getHistoryTags('grade').length" class="q-mb-md">
                  <span class="text-caption text-grey-6">Quick insert:</span>
                  <div class="q-mt-xs">
                    <q-chip
                      v-for="tag in getHistoryTags('grade').slice(0,6)"
                      :key="tag"
                      clickable @click="proQuiz.grade = tag"
                      color="primary"
                      text-color="white"
                      size="sm"
                      class="q-ma-xs"
                    >{{ tag }}</q-chip>
                  </div>
                </div>

                <q-input
                  v-model.number="proQuiz.numQuestions"
                  filled
                  type="number"
                  label="Total Questions"
                  :min="5"
                  :max="easyMode ? 25 : 60"
                  hint="Easy Mode: max 25"
                >
                  <template v-slot:prepend><q-icon name="format_list_numbered" /></template>
                </q-input>

                <q-input
                  v-model="proQuiz.lessons"
                  filled
                  type="textarea"
                  :rows="easyMode ? 4 : 7"
                  label="Lessons / Topics * (one per line)"
                  placeholder="e.g. 5-1 Ratios\n5-2 Percent Problems"
                  autogrow
                  @focus="trackField('lessons')"
                >
                  <template v-slot:prepend><q-icon name="menu_book" /></template>
                  <template v-slot:append>
                    <q-btn flat round icon="lightbulb" color="amber">
                      <q-tooltip>Tip: Add page numbers!</q-tooltip>
                    </q-btn>
                  </template>
                </q-input>

                <!-- History Tags for Lessons -->
                <div v-if="getHistoryTags('lessons').length">
                  <span class="text-caption text-grey-6">Recent topics:</span>
                  <div class="q-mt-xs">
                    <q-chip
                      v-for="lesson in getHistoryTags('lessons').slice(0,8)"
                      :key="lesson"
                      clickable
                      @click="insertLesson(lesson)"
                      color="teal"
                      text-color="white"
                      size="sm"
                      icon="history"
                      class="q-ma-xs"
                    >{{ truncate(lesson, 22) }}</q-chip>
                  </div>
                </div>

                <!-- Advanced Options (Hidden in Easy Mode) -->
                <transition name="q-transition--fade">
                  <div v-if="!easyMode" class="q-mt-lg">
                    <div class="text-h6 text-primary q-mb-md">Advanced Options</div>
                    <q-option-group
                      v-model="proQuiz.options"
                      :options="advancedToggles"
                      type="toggle"
                      inline
                      color="primary"
                    />
                  </div>
                </transition>
              </div>
            </q-tab-panel>

            <!-- QUICK QUIZ -->
            <q-tab-panel name="quiz">
              <div class="q-gutter-y-lg">
                <q-input v-model="quiz.topic" filled label="Main Topic" @focus="trackField('topic')" />
                <div v-if="getHistoryTags('topic').length">
                  <q-chip
                    v-for="t in getHistoryTags('topic').slice(0,6)"
                    :key="t"
                    clickable @click="quiz.topic = t"
                    color="orange"
                    text-color="white"
                    size="sm"
                    class="q-ma-xs"
                  >{{ t }}</q-chip>
                </div>
                <q-input v-model="quiz.grade" filled label="Grade Level" />
                <q-slider v-model="quiz.numQuestions" :min="5" :max="40" label label-always />
                <q-select v-model="quiz.types" filled multiple use-chips :options="questionTypes" label="Question Types" />
                <q-toggle v-model="quiz.withAnswerKey" label="Include Answer Key + Explanations" />
              </div>
            </q-tab-panel>

            <!-- Other tabs simplified for brevity -->
            <q-tab-panel name="lesson">
              <div class="q-gutter-y-md">
                <q-input v-model="lesson.topic" filled label="Lesson Title" />
                <q-input v-model="lesson.grade" filled label="Grade" />
                <q-input v-model.number="lesson.duration" filled type="number" suffix=" min" label="Duration" />
                <q-select v-model="lesson.style" filled :options="['Direct', 'Inquiry', 'Flipped', 'Project-Based']" label="Style" />
              </div>
            </q-tab-panel>

            <q-tab-panel name="worksheet"><div class="text-center text-grey">Worksheet builder coming soon...</div></q-tab-panel>
            <q-tab-panel name="flashcards"><div class="text-center text-grey">Flashcards coming soon...</div></q-tab-panel>

            <!-- CUSTOM -->
            <q-tab-panel name="custom">
              <q-input
                v-model="customPrompt"
                filled
                type="textarea"
                autogrow
                label="Your Custom Prompt"
                placeholder="Write or paste anything... full control!"
                class="text-body1"
              />
            </q-tab-panel>
          </q-tab-panels>
        </div>

        <!-- RIGHT: Live Preview -->
        <div class="col-12 col-lg-6">
          <div class="column full-height">
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-h6 flex items-center gap-2">
                <q-icon name="preview" color="primary" />
                Live Preview
                <q-chip size="sm" color="positive" icon="auto_mode">Auto</q-chip>
              </div>
              <q-btn flat round icon="refresh" color="grey-7" @click="generatePrompt(true)" />
            </div>

            <q-scroll-area class="rounded-borders bg-grey-1 flex-grow-1 q-mb-md" style="height: 1px;">
              <div class="q-pa-md font-mono text-body2" v-html="highlightedPrompt"></div>
            </q-scroll-area>

            <!-- Action Buttons -->
            <div class="row q-gutter-sm">
              <q-btn
                color="grey-8"
                icon="content_copy"
                label="Copy"
                @click="copyPrompt"
                class="col"
                unelevated
              />
              <q-btn
                color="positive"
                icon="send"
                label="Send to AI"
                @click="useInAI"
                class="col"
                unelevated
              />
            </div>

            <!-- Recent Prompts Chips -->
            <div class="q-mt-md text-center" v-if="recentPrompts.length">
              <span class="text-caption text-grey">Recent prompts:</span>
              <div class="q-mt-xs">
                <q-chip
                  v-for="(p, i) in recentPrompts.slice(0,5)"
                  :key="i"
                  clickable
                  @click="loadRecent(p)"
                  size="sm"
                  color="grey-4"
                  class="q-ma-xs"
                >
                  {{ truncate(p.text, 28) }}
                </q-chip>
              </div>
            </div>
          </div>
        </div>
      </div>
    </q-card>
 
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const emit = defineEmits(['use-prompt', 'close'])

const isOpen = ref(true)
const easyMode = ref(true)
const activeTemplate = ref('pro-quiz')
const currentField = ref('')

// Form Data
const proQuiz = ref({
  grade: 'Grade 7',
  numQuestions: 20,
  lessons: '5-1 Ratios and Rates\n5-2 Proportions\n5-3 Percent Problems',
  options: ['withSections', 'withAnswerKey', 'withLatex']
})

const quiz = ref({
  topic: '',
  grade: 'Grade 6',
  numQuestions: 15,
  types: ['multiple-choice', 'short-answer'],
  withAnswerKey: true
})

const lesson = ref({ topic: '', grade: '', duration: 60, style: 'Direct' })
const customPrompt = ref('')
const generatedPrompt = ref('')
const recentPrompts = ref([])
const fieldHistory = ref({ grade: [], lessons: [], topic: [] })

// Load history
try {
  const saved = localStorage.getItem('promptBuilderHistory')
  const fieldSaved = localStorage.getItem('promptBuilderFieldHistory')
  if (saved) recentPrompts.value = JSON.parse(saved)
  if (fieldSaved) fieldHistory.value = JSON.parse(fieldSaved)
} catch (e) { console.error(e) }

const advancedToggles = [
  { label: 'Section per lesson', value: 'withSections' },
  { label: 'Group by type', value: 'groupByType' },
  { label: 'KaTeX math', value: 'withLatex' },
  { label: 'Answer key + explanations', value: 'withAnswerKey' },
  { label: 'Print-ready', value: 'printReady' }
]

const questionTypes = ['multiple-choice', 'true-false', 'fill-in-blank', 'short-answer', 'matching']

// Track what user is typing to show relevant tags
function trackField(field) {
  currentField.value = field
}

// Get recent values for a field
function getHistoryTags(field) {
  return [...new Set(fieldHistory.value[field] || [])].slice(0, 8)
}

function insertLesson(text) {
  proQuiz.value.lessons += (proQuiz.value.lessons ? '\n' : '') + text
}

// Auto generate prompt
watch([proQuiz, quiz, lesson, customPrompt, activeTemplate, easyMode], () => {
  generatePrompt()
}, { deep: true, immediate: true })

function generatePrompt(force = false) {
  let prompt = ''

  if (activeTemplate.value === 'pro-quiz') {
    const lessons = proQuiz.value.lessons.split('\n').filter(l => l.trim()).map(l => `• ${l.trim()}`).join('\n') || 'these topics'
    const opts = proQuiz.value.options || []

    prompt = `Create a high-quality, ${proQuiz.value.numQuestions}-question quiz for ${proQuiz.value.grade} students covering:\n\n${lessons}\n\n` +
      (opts.includes('withSections') ? 'Organize into sections by lesson.\n' : '') +
      (opts.includes('groupByType') ? 'Group by question type.\n' : '') +
      (opts.includes('withLatex') ? 'Use KaTeX for all math: \\frac{1}{2}, \\sqrt{x}\n' : '') +
      (opts.includes('withAnswerKey') ? 'Include detailed answer key with explanations at the end.\n' : '') +
      (opts.includes('printReady') ? 'Output in clean, print-ready HTML with title and name lines.\n' : '') +
      'Use student misconceptions as distractors. Make it professional and ready to use.'
  }

  else if (activeTemplate.value === 'quiz') {
    prompt = `Generate a ${quiz.value.numQuestions}-question quiz about "${quiz.value.topic}" for ${quiz.value.grade} students. ` +
      `Include: ${quiz.value.types.join(', ')}. ` +
      (quiz.value.withAnswerKey ? 'Add detailed answer key.' : '')
  }

  else if (activeTemplate.value === 'custom') {
    prompt = customPrompt.value || 'Your custom prompt will appear here...'
  }

  // Default fallback
  else prompt = 'Select a template to begin...'

  generatedPrompt.value = prompt.trim()
  saveToHistory(prompt)
}

const highlightedPrompt = computed(() => {
  if (!generatedPrompt.value) return '<em class="text-grey-5">Your prompt will appear here...</em>'
  return generatedPrompt.value
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/`(.*?)`/g, '<code class="bg-grey-3 q-px-xs rounded">$1</code>')
    .replace(/\n/g, '<br>')
})

function saveToHistory(text) {
  if (!text || text.length < 20) return
  const entry = { text, template: activeTemplate.value, timestamp: Date.now() }
  recentPrompts.value = [entry, ...recentPrompts.value.filter(p => p.text !== text)].slice(0, 20)
  localStorage.setItem('promptBuilderHistory', JSON.stringify(recentPrompts.value))

  // Save field values for tags
  if (proQuiz.value.grade) {
    fieldHistory.value.grade = [proQuiz.value.grade, ...fieldHistory.value.grade.filter(g => g !== proQuiz.value.grade)].slice(0, 15)
  }
  if (proQuiz.value.lessons) {
    const lines = proQuiz.value.lessons.split('\n').map(l => l.trim()).filter(Boolean)
    lines.forEach(l => {
      if (!fieldHistory.value.lessons.includes(l)) {
        fieldHistory.value.lessons = [l, ...fieldHistory.value.lessons].slice(0, 30)
      }
    })
  }
  if (quiz.value.topic) fieldHistory.value.topic = [quiz.value.topic, ...fieldHistory.value.topic.filter(t => t !== quiz.value.topic)].slice(0, 15)

  localStorage.setItem('promptBuilderFieldHistory', JSON.stringify(fieldHistory.value))
}

function loadRecent(p) {
  generatedPrompt.value = p.text
  activeTemplate.value = p.template
  $q.notify({ message: 'Loaded from history', type: 'positive', icon: 'history' })
}

function truncate(str, n) {
  return str.length > n ? str.substring(0, n-3) + '...' : str
}

function copyPrompt() {
  navigator.clipboard.writeText(generatedPrompt.value)
  $q.notify({ message: 'Copied to clipboard!', type: 'positive', icon: 'content_copy' })
}

function useInAI() {
  emit('use-prompt', generatedPrompt.value)
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.prompt-builder-dialog {
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
}
</style>