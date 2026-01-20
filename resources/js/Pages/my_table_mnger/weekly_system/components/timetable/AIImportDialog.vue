<template>
  <q-dialog :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" position="right" maximized>
    <q-card style="width: 900px; max-width: 90vw" class="column">
      <!-- Header -->
      <q-card-section class="row items-center q-pb-none bg-primary text-white">
        <q-icon name="psychology" size="sm" class="q-mr-sm" />
        <div class="text-h6">AI Timetable Import</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <!-- Main Content -->
      <q-card-section class="col scroll">
        <q-stepper v-model="step" vertical color="primary" animated>
          <!-- Step 1: Generate Prompt -->
          <q-step :name="1" title="Generate AI Prompt" icon="description" :done="step > 1">
            <div class="text-body2 q-mb-md">
              Copy the prompt below and paste it into your AI assistant (ChatGPT, Claude, etc.) to generate the timetable JSON.
            </div>
            
            <q-card flat bordered>
              <q-card-section>
                <pre class="prompt-text">{{ promptTemplate }}</pre>
              </q-card-section>
            </q-card>

            <div class="q-mt-md">
              <q-btn 
                color="primary" 
                icon="content_copy" 
                label="Copy Prompt" 
                @click="copyPrompt"
                class="q-mr-sm"
              />
              <q-btn 
                flat 
                color="primary" 
                label="Next" 
                @click="step = 2"
              />
            </div>
          </q-step>

          <!-- Step 2: Paste & Validate JSON -->
          <q-step :name="2" title="Paste JSON" icon="input" :done="step > 2">
            <div class="text-body2 q-mb-md">
              Paste the AI-generated JSON here. It will be automatically validated.
            </div>

            <q-input
              v-model="jsonInput"
              type="textarea"
              outlined
              placeholder='{"classroom_id": 123, ...}'
              :rows="12"
              class="q-mb-md"
              @update:model-value="validateJSON"
            >
              <template v-slot:append>
                <q-icon 
                  v-if="validationState === 'valid'" 
                  name="check_circle" 
                  color="positive" 
                />
                <q-icon 
                  v-if="validationState === 'invalid'" 
                  name="error" 
                  color="negative" 
                />
              </template>
            </q-input>

            <!-- Validation Errors -->
            <q-banner v-if="validationErrors.length > 0" class="bg-negative text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="warning" color="white" />
              </template>
              <div class="text-weight-bold q-mb-xs">Validation Errors:</div>
              <ul class="q-my-none q-pl-md">
                <li v-for="(error, index) in validationErrors.slice(0, 5)" :key="index">
                  {{ error }}
                </li>
              </ul>
              <div v-if="validationErrors.length > 5" class="text-caption q-mt-xs">
                ... and {{ validationErrors.length - 5 }} more errors
              </div>
              <template v-slot:action>
                <q-btn 
                  flat 
                  color="white" 
                  icon="download" 
                  label="Download JSON" 
                  @click="downloadRejectedJSON"
                  dense
                />
              </template>
            </q-banner>

            <!-- Validation Success -->
            <q-banner v-if="validationState === 'valid'" class="bg-positive text-white q-mb-md">
              <template v-slot:avatar>
                <q-icon name="check_circle" color="white" />
              </template>
              <div>
                <strong>Validation passed!</strong>
                <div class="text-caption">
                  {{ validationSummary?.total_entries }} entries ready to import
                </div>
              </div>
            </q-banner>

            <div class="q-mt-md">
              <q-btn 
                flat 
                color="primary" 
                label="Back" 
                @click="step = 1"
                class="q-mr-sm"
              />
              <q-btn 
                color="primary" 
                label="Next: Preview" 
                @click="step = 3"
                :disable="validationState !== 'valid'"
              />
            </div>
          </q-step>

          <!-- Step 3: Preview -->
          <q-step :name="3" title="Preview Timetable" icon="preview" :done="step > 3">
            <div class="text-body2 q-mb-md">
              Review the timetable before applying changes.
            </div>

            <div v-if="parsedData" class="q-mb-md">
              <div class="row q-gutter-sm q-mb-md">
                <q-chip icon="meeting_room" color="blue-2" text-color="blue-9">
                  Classroom ID: {{ parsedData.classroom_id }}
                </q-chip>
                <q-chip icon="schedule" color="green-2" text-color="green-9">
                  {{ parsedData.entries?.length }} periods
                </q-chip>
              </div>

              <!-- Preview Table -->
              <q-table
                :rows="parsedData.entries || []"
                :columns="previewColumns"
                row-key="index"
                flat
                bordered
                dense
                :rows-per-page-options="[0]"
              >
                <template v-slot:body-cell-day="props">
                  <q-td :props="props">
                    <q-badge :color="getDayColor(props.row.day)">
                      {{ props.row.day }}
                    </q-badge>
                  </q-td>
                </template>
                <template v-slot:body-cell-period="props">
                  <q-td :props="props">
                    <strong>Period {{ props.row.period }}</strong>
                  </q-td>
                </template>
              </q-table>
            </div>

            <div class="q-mt-md">
              <q-btn 
                flat 
                color="primary" 
                label="Back" 
                @click="step = 2"
                class="q-mr-sm"
              />
              <q-btn 
                color="primary" 
                label="Next: Apply" 
                @click="step = 4"
              />
            </div>
          </q-step>

          <!-- Step 4: Apply Changes -->
          <q-step :name="4" title="Apply Changes" icon="published_with_changes">
            <div class="text-body2 q-mb-md">
              The imported timetable will be merged with your existing schedule:
            </div>

            <q-banner class="bg-blue-1 q-mb-md">
              <template v-slot:avatar>
                <q-icon name="info" color="blue" />
              </template>
              <div>
                <strong>Update Mode:</strong> This will update or create schedules only for the day/period combinations in the imported data. Other schedules will remain unchanged.
              </div>
            </q-banner>

            <div class="q-mt-md">
              <q-btn 
                flat 
                color="primary" 
                label="Back" 
                @click="step = 3"
                class="q-mr-sm"
                :disable="applying"
              />
              <q-btn 
                color="positive" 
                icon="check" 
                label="Apply Import" 
                @click="applyImport"
                :loading="applying"
              />
            </div>

            <!-- Apply Results -->
            <q-banner v-if="applyResult" class="q-mt-md" :class="applyResult.success ? 'bg-positive text-white' : 'bg-negative text-white'">
              <template v-slot:avatar>
                <q-icon :name="applyResult.success ? 'check_circle' : 'error'" color="white" />
              </template>
              <div>
                <div class="text-weight-bold">{{ applyResult.message }}</div>
                <div v-if="applyResult.summary" class="text-caption q-mt-xs">
                  <div v-if="applyResult.summary.created">Created: {{ applyResult.summary.created }}</div>
                  <div v-if="applyResult.summary.updated">Updated: {{ applyResult.summary.updated }}</div>
                  <div v-if="applyResult.summary.failed">Failed: {{ applyResult.summary.failed }}</div>
                </div>
                <div v-if="applyResult.failed && applyResult.failed.length > 0" class="q-mt-sm">
                  <div class="text-weight-bold">Failed entries:</div>
                  <ul class="q-my-xs q-pl-md">
                    <li v-for="(fail, idx) in applyResult.failed.slice(0, 3)" :key="idx">
                      {{ fail.reason }}
                    </li>
                  </ul>
                </div>
              </div>
            </q-banner>
          </q-step>
        </q-stepper>
      </q-card-section>

      <q-separator />

      <!-- Footer Actions -->
      <q-card-actions align="right">
        <q-btn flat label="Close" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const props = defineProps({
  modelValue: Boolean,
  classroomId: Number,
  classroomName: String,
  subjects: Array
})

const emit = defineEmits(['update:modelValue', 'applied'])

const $q = useQuasar()

// State
const step = ref(1)
const jsonInput = ref('')
const validationState = ref(null) // null, 'valid', 'invalid'
const validationErrors = ref([])
const validationSummary = ref(null)
const parsedData = ref(null)
const applyMode = ref('update')
const applying = ref(false)
const applyResult = ref(null)

// Prompt template
const promptTemplate = computed(() => {
  // Create a list of all available subject names for the AI to reference
  const validSubjectNames = props.subjects
    .map(s => s.name)
    .sort()
    .join(', ')

  return `Provide JSON only matching the schema below. No prose. Map subjects to periods for the given classroom and week.

Classroom: ${props.classroomName || 'Unknown'} (ID: ${props.classroomId || '<classroom_id>'})

IMPORTANT: You MUST use ONLY the following subject names exactly as written (case-sensitive). If the schedule has a subject name that is slightly different (e.g. spelling or case), you MUST correct it to match one of these valid names. DO NOT invent new subject names.

Valid Subjects:
${validSubjectNames}

Assign sequential periods starting at 1 (Max 8 periods per day). Output:

{
  "entries": [
    { "day": "Sunday", "period": 1, "subject": "Math" },
    { "day": "Sunday", "period": 2, "subject": "Arabic" }
  ]
}`
})

// Apply mode (hardcoded to 'update' to protect Weekly Plan data)
// Import mode removed to prevent accidental deletion of schedules and weekly plans

// Preview table columns
const previewColumns = [
  { name: 'day', label: 'Day', field: 'day', align: 'left' },
  { name: 'period', label: 'Period', field: 'period', align: 'center' },
  { name: 'subject', label: 'Subject', field: 'subject', align: 'left' },
  { name: 'teacher', label: 'Teacher', field: 'teacher', align: 'left' },
  { name: 'notes', label: 'Notes', field: 'notes', align: 'left' }
]

// Methods
const copyPrompt = async () => {
  try {
    await navigator.clipboard.writeText(promptTemplate.value)
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy prompt',
      position: 'top'
    })
  }
}

const validateJSON = async () => {
  if (!jsonInput.value.trim()) {
    validationState.value = null
    validationErrors.value = []
    parsedData.value = null
    return
  }

  try {
    // Auto-clean markdown code blocks if present
    let cleanedInput = jsonInput.value.trim()
    
    // Find the first '{' and last '}' to handle any surrounding text/markdown
    const firstBrace = cleanedInput.indexOf('{')
    const lastBrace = cleanedInput.lastIndexOf('}')
    
    if (firstBrace !== -1 && lastBrace !== -1 && lastBrace > firstBrace) {
      cleanedInput = cleanedInput.substring(firstBrace, lastBrace + 1)
    }

    // Try to parse JSON locally first
    const parsed = JSON.parse(cleanedInput)
    
    // Auto-inject classroom_id if missing
    if (!parsed.classroom_id) {
      parsed.classroom_id = props.classroomId
    }
    
    parsedData.value = parsed

    // Validate via API
    const response = await axios.post('/weekly-system/api/ai-import/validate', {
      data: parsed
    })

    if (response.data.success) {
      validationState.value = 'valid'
      validationErrors.value = []
      validationSummary.value = response.data.summary
    } else {
      validationState.value = 'invalid'
      validationErrors.value = response.data.errors || []
    }
  } catch (error) {
    validationState.value = 'invalid'
    
    if (error.response?.data?.errors) {
      validationErrors.value = error.response.data.errors
    } else if (error instanceof SyntaxError) {
      validationErrors.value = ['Invalid JSON format: ' + error.message]
    } else {
      validationErrors.value = ['Validation failed: ' + error.message]
    }
    parsedData.value = null
  }
}

const downloadRejectedJSON = () => {
  const blob = new Blob([jsonInput.value], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `rejected-timetable-${Date.now()}.json`
  link.click()
  URL.revokeObjectURL(url)
  
  $q.notify({
    type: 'info',
    message: 'JSON downloaded for corrections',
    position: 'top'
  })
}

const getDayColor = (day) => {
  const colors = {
    Sunday: 'red-4',
    Monday: 'blue-4',
    Tuesday: 'green-4',
    Wednesday: 'orange-4',
    Thursday: 'purple-4'
  }
  return colors[day] || 'grey-4'
}

const applyImport = async () => {
  applying.value = true
  applyResult.value = null

  try {
    // Always use Update mode to preserve Weekly Plan data
    const response = await axios.post('/weekly-system/api/ai-import/update', {
      data: parsedData.value
    })

    applyResult.value = response.data

    if (response.data.success) {
      $q.notify({
        type: 'positive',
        message: response.data.message,
        position: 'top'
      })
      
      // Emit applied event to refresh parent, but keep dialog open
      emit('applied')
    }
  } catch (error) {
    applyResult.value = {
      success: false,
      message: error.response?.data?.message || 'Failed to apply import',
      errors: error.response?.data?.errors
    }
    
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Failed to apply import',
      position: 'top'
    })
  } finally {
    applying.value = false
  }
}

// Reset when dialog opens
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    step.value = 1
    jsonInput.value = ''
    validationState.value = null
    validationErrors.value = []
    parsedData.value = null
    applyResult.value = null
    applyMode.value = 'update'
  }
})
</script>

<style scoped>
.prompt-text {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  white-space: pre-wrap;
  word-wrap: break-word;
  background-color: #f5f5f5;
  padding: 12px;
  border-radius: 4px;
  max-height: 300px;
  overflow-y: auto;
}
</style>
