            <q-icon name="verified" size="sm" class="q-mr-xs" />
            Pro Quiz
            <q-badge color="positive" floating rounded>Best</q-badge>
          </q-tab>
          <q-tab name="quiz" icon="quiz" label="Quick Quiz" />
          <q-tab name="lesson" icon="school" label="Lesson Plan" />
          <q-tab name="worksheet" icon="assignment" label="Worksheet" />
          <q-tab name="flashcards" icon="style" label="Flashcards" />
          <q-tab name="custom" icon="tune" label="Custom" />
        </q-tabs>

                  <template v-slot:append>
                    <q-btn flat round icon="lightbulb" color="amber" size="sm">
                      <q-tooltip>Try: 4-1 Fractions (p.89), 4-2 Decimals (p.95)</q-tooltip>
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="quiz.topic" filled label="Topic" placeholder="e.g. The Water Cycle, Pythagorean Theorem" />
              <q-input v-model="quiz.grade" filled label="Grade/Level" placeholder="e.g. Grade 6, Middle School" />
              <q-slider v-model="quiz.numQuestions" :min="5" :max="40" label-always label-value="questions" />
              <q-select v-model="quiz.types" filled multiple chips label="Question Types" :options="questionTypes" use-chips />
              <q-toggle v-model="quiz.withAnswerKey" label="Include detailed answer key with explanations" />
            </div>
          </q-tab-panel>

          <!-- LESSON PLAN -->
          <q-tab-panel name="lesson">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="lesson.topic" filled label="Lesson Title" />
              <q-input v-model="lesson.grade" filled label="Grade Level" />
              <q-input v-model.number="lesson.duration" filled type="number" suffix=" minutes" label="Duration" />
              <q-select v-model="lesson.style" filled label="Teaching Style" :options="['Direct Instruction', 'Inquiry-Based', 'Project-Based', 'Flipped']" />
              <q-option-group v-model="lesson.sections" inline :options="lessonSections" type="checkbox" />
            </div>
          </q-tab-panel>

          <!-- WORKSHEET -->
          <q-tab-panel name="worksheet">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="worksheet.topic" filled label="Topic" />
              <q-input v-model="worksheet.grade" filled label="Grade" />
              <q-input v-model.number="worksheet.numProblems" filled type="number" label="Problems" />
              <q-rating v-model="worksheet.difficulty" size="2rem" :max="3" icon="sentiment_very_dissatisfied" icon-half="sentiment_neutral" icon-selected="sentiment_very_satisfied" />
              <span class="q-ml-md text-grey-7">{{ ['Easy', 'Medium', 'Hard'][worksheet.difficulty - 1] }}</span>
            </div>
          </q-tab-panel>

          <!-- FLASHCARDS (New!) -->
          <q-tab-panel name="flashcards">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="flashcards.topic" filled label="Topic" />
              <q-input v-model="flashcards.grade" filled label="Grade" />
              <q-input v-model.number="flashcards.count" filled type="number" label="Number of Flashcards" :min="10" :max="100" />
              <q-toggle v-model="flashcards.withImages" label="Suggest relevant images/diagrams" />
              <q-toggle v-model="flashcards.qrCode" label="Include QR code linking to Quizlet/Anki import" />
            </div>
          </q-tab-panel>

          <!-- CUSTOM -->
          <q-tab-panel name="custom">
            <q-input v-model="customPrompt" filled type="textarea" autogrow label="Your Custom Prompt"
              placeholder="Paste or write anything... you have full control!" class="q-mt-lg" />
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>

      <!-- Live Preview Section -->
      <q-separator />
      <q-card-section class="bg-grey-1">
        <div class="row items-center justify-between q-mb-sm">
          <div class="text-subtitle1 flex items-center gap-2">
            <q-icon name="preview" /> Live Preview
            <q-chip size="sm" color="deep-purple" text-color="white">Auto-updating</q-chip>
          </div>
          <q-btn flat round icon="refresh" @click="generatePrompt(true)" />
        </div>

        <q-scroll-area style="height: 180px;" class="rounded-borders bg-white">
          <div class="q-pa-md font-mono text-body2" v-html="highlightedPrompt"></div>
        </q-scroll-area>

        <div class="row q-mt-md q-gutter-sm">
const customPrompt = ref('')

const questionTypes = ['multiple-choice', 'true-false', 'fill-blank', 'short-answer', 'matching']
const lessonSections = [
  { label: 'Learning Objectives', value: 'objectives' },
  { label: 'Warm-Up', value: 'warmup' },
  { label: 'Direct Instruction', value: 'content' },
  { label: 'Guided Practice', value: 'guided' },
  { label: 'Independent Practice', value: 'independent' },
  { label: 'Assessment', value: 'assessment' },
  { label: 'Closure', value: 'closure' }
]
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>')
})

function saveToHistory(text) {
  if (!text || text.length < 20) return
  const entry = { text, template: activeTemplate.value, date: new Date() }
  recentPrompts.value = [entry, ...recentPrompts.value.filter(p => p.text !== text)].slice(0, 15)
  localStorage.setItem('promptBuilderHistory', JSON.stringify(recentPrompts.value))
}

function loadRecent(p) {
  generatedPrompt.value = p.text
  activeTemplate.value = p.template
  $q.notify({ message: 'Loaded recent prompt!', type: 'positive' })
}

function truncate(str, n) {
  return str.length > n ? str.slice(0, n-3) + '...' : str
}

function copyPrompt() {
  navigator.clipboard.writeText(generatedPrompt.value)
  $q.notify({ type: 'positive', message: 'Copied!', icon: 'content_copy' })
}

            <q-icon name="verified" size="sm" class="q-mr-xs" />
            Pro Quiz
            <q-badge color="positive" floating rounded>Best</q-badge>
          </q-tab>
          <q-tab name="quiz" icon="quiz" label="Quick Quiz" />
          <q-tab name="lesson" icon="school" label="Lesson Plan" />
          <q-tab name="worksheet" icon="assignment" label="Worksheet" />
          <q-tab name="flashcards" icon="style" label="Flashcards" />
          <q-tab name="custom" icon="tune" label="Custom" />
        </q-tabs>

                  <template v-slot:append>
                    <q-btn flat round icon="lightbulb" color="amber" size="sm">
                      <q-tooltip>Try: 4-1 Fractions (p.89), 4-2 Decimals (p.95)</q-tooltip>
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="quiz.topic" filled label="Topic" placeholder="e.g. The Water Cycle, Pythagorean Theorem" />
              <q-input v-model="quiz.grade" filled label="Grade/Level" placeholder="e.g. Grade 6, Middle School" />
              <q-slider v-model="quiz.numQuestions" :min="5" :max="40" label-always label-value="questions" />
              <q-select v-model="quiz.types" filled multiple chips label="Question Types" :options="questionTypes" use-chips />
              <q-toggle v-model="quiz.withAnswerKey" label="Include detailed answer key with explanations" />
            </div>
          </q-tab-panel>

          <!-- LESSON PLAN -->
          <q-tab-panel name="lesson">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="lesson.topic" filled label="Lesson Title" />
              <q-input v-model="lesson.grade" filled label="Grade Level" />
              <q-input v-model.number="lesson.duration" filled type="number" suffix=" minutes" label="Duration" />
              <q-select v-model="lesson.style" filled label="Teaching Style" :options="['Direct Instruction', 'Inquiry-Based', 'Project-Based', 'Flipped']" />
              <q-option-group v-model="lesson.sections" inline :options="lessonSections" type="checkbox" />
            </div>
          </q-tab-panel>

          <!-- WORKSHEET -->
          <q-tab-panel name="worksheet">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="worksheet.topic" filled label="Topic" />
              <q-input v-model="worksheet.grade" filled label="Grade" />
              <q-input v-model.number="worksheet.numProblems" filled type="number" label="Problems" />
              <q-rating v-model="worksheet.difficulty" size="2rem" :max="3" icon="sentiment_very_dissatisfied" icon-half="sentiment_neutral" icon-selected="sentiment_very_satisfied" />
              <span class="q-ml-md text-grey-7">{{ ['Easy', 'Medium', 'Hard'][worksheet.difficulty - 1] }}</span>
            </div>
          </q-tab-panel>

          <!-- FLASHCARDS (New!) -->
          <q-tab-panel name="flashcards">
            <div class="q-gutter-y-lg q-mt-md">
              <q-input v-model="flashcards.topic" filled label="Topic" />
              <q-input v-model="flashcards.grade" filled label="Grade" />
              <q-input v-model.number="flashcards.count" filled type="number" label="Number of Flashcards" :min="10" :max="100" />
              <q-toggle v-model="flashcards.withImages" label="Suggest relevant images/diagrams" />
              <q-toggle v-model="flashcards.qrCode" label="Include QR code linking to Quizlet/Anki import" />
            </div>
          </q-tab-panel>

          <!-- CUSTOM -->
          <q-tab-panel name="custom">
            <q-input v-model="customPrompt" filled type="textarea" autogrow label="Your Custom Prompt"
              placeholder="Paste or write anything... you have full control!" class="q-mt-lg" />
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>

      <!-- Live Preview Section -->
      <q-separator />
      <q-card-section class="bg-grey-1">
        <div class="row items-center justify-between q-mb-sm">
          <div class="text-subtitle1 flex items-center gap-2">
            <q-icon name="preview" /> Live Preview
            <q-chip size="sm" color="deep-purple" text-color="white">Auto-updating</q-chip>
          </div>
          <q-btn flat round icon="refresh" @click="generatePrompt(true)" />
        </div>

        <q-scroll-area style="height: 180px;" class="rounded-borders bg-white">
          <div class="q-pa-md font-mono text-body2" v-html="highlightedPrompt"></div>
        </q-scroll-area>

        <div class="row q-mt-md q-gutter-sm">
const customPrompt = ref('')

const questionTypes = ['multiple-choice', 'true-false', 'fill-blank', 'short-answer', 'matching']
const lessonSections = [
  { label: 'Learning Objectives', value: 'objectives' },
  { label: 'Warm-Up', value: 'warmup' },
  { label: 'Direct Instruction', value: 'content' },
  { label: 'Guided Practice', value: 'guided' },
  { label: 'Independent Practice', value: 'independent' },
  { label: 'Assessment', value: 'assessment' },
  { label: 'Closure', value: 'closure' }
]
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
    .replace(/\n/g, '<br>')
})

function saveToHistory(text) {
  if (!text || text.length < 20) return
  const entry = { text, template: activeTemplate.value, date: new Date() }
  recentPrompts.value = [entry, ...recentPrompts.value.filter(p => p.text !== text)].slice(0, 15)
  localStorage.setItem('promptBuilderHistory', JSON.stringify(recentPrompts.value))
}

function loadRecent(p) {
  generatedPrompt.value = p.text
  activeTemplate.value = p.template
  $q.notify({ message: 'Loaded recent prompt!', type: 'positive' })
}

function truncate(str, n) {
  return str.length > n ? str.slice(0, n-3) + '...' : str
}

function copyPrompt() {
  navigator.clipboard.writeText(generatedPrompt.value)
  $q.notify({ type: 'positive', message: 'Copied!', icon: 'content_copy' })
}

function useInAI() {
  emit('use-prompt', generatedPrompt.value)
  $q.notify({ message: 'Prompt sent to AI!', type: 'positive', icon: 'send' })
}
</script>

<style scoped>
.bg-gradient {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.prompt-builder-card {
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}
.font-mono {
  font-family: monospace;
}
</style>