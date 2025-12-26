<template>
  <q-card class="bg-white shadow-md rounded-lg">
    <!-- Header with Tabs -->
    <q-tabs
      v-model="activeTab"
      dense
      class="text-primary"
      active-color="primary"
      indicator-color="primary"
      align="left"
    >
      <q-tab name="import" label="Import from Excel" icon="upload" />
      <q-tab name="ai" label="AI Assistant" icon="psychology" />
      <q-tab name="export" label="Export to Excel" icon="download" />
    </q-tabs>

    <q-separator />

    <!-- Tabs Content -->
    <q-tab-panels v-model="activeTab" animated>
      <!-- Import Tab -->
      <q-tab-panel name="import">
        <ExcelImporter @imported-json="handleImportedJson" />
      </q-tab-panel>

      <!-- AI Assistant Tab -->
      <q-tab-panel name="ai">
        <div class="q-pa-md">
          <div class="row items-center q-mb-md">
            <div>
              <div class="text-h6">AI JSON Helper</div>
              <div class="text-caption text-grey-7">Generate JSON using AI and paste it here</div>
            </div>
            <q-space />
            <q-btn
              color="primary"
              icon="content_copy"
              label="Copy AI Prompt"
              @click="copyAiPrompt"
              unelevated
            />
          </div>

          <q-banner class="bg-blue-1 text-blue-9 q-mb-lg rounded-borders">
            <template v-slot:avatar>
              <q-icon name="info" color="blue-9" />
            </template>
            1. Click "Copy AI Prompt" and paste it into an AI (ChatGPT/Claude).<br />
            2. Provide your lessons list to the AI.<br />
            3. Paste the generated JSON below and click "Process".
          </q-banner>

          <q-input
            v-model="pastedJson"
            type="textarea"
            label="Paste AI Generated JSON"
            outlined
            rows="12"
            placeholder='[
  {
    "topic_number": "1",
    "topic_title": "Example Topic",
    "lesson_number": "1.1",
    "lesson_title": "Example Lesson",
    "lesson_type": "main"
  }
]'
            class="bg-grey-1"
          />

          <div class="row justify-end q-mt-md">
            <q-btn
              color="positive"
              icon="check_circle"
              label="Process & Import JSON"
              @click="processPastedJson"
              :disable="!pastedJson.trim()"
              unelevated
            />
          </div>
        </div>
      </q-tab-panel>

      <!-- Export Tab -->
      <q-tab-panel name="export">
        <ExcelExporter
          :json-data="exportData"
          :default-file-name="exportFileName"
          @exported="handleExported"
        />
      </q-tab-panel>
    </q-tab-panels>
  </q-card>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import ExcelImporter from './ExcelImporter.vue'
import ExcelExporter from './ExcelExporter.vue'

const $q = useQuasar()

const props = defineProps({
  exportData: {
    type: Array,
    default: () => []
  },
  exportFileName: {
    type: String,
    default: 'export.xlsx'
  },
  initialTab: {
    type: String,
    default: 'import'
  }
})

const emit = defineEmits(['imported-json', 'exported'])

// State
const activeTab = ref(props.initialTab)
const pastedJson = ref('')

// AI Prompt Template
const aiPrompt = `Please generate a JSON array of curriculum topics and lessons based on the following list.
Output ONLY the raw JSON array. Do not include any markdown formatting like \`\`\`json or any other text.

Required Fields for each object:
- topic_number: Topic number (e.g. "1")
- topic_title: Title of the topic
- topic_description: Optional description
- lesson_number: Lesson number (e.g. "1.1")
- lesson_title: Title of the lesson
- lesson_description: Optional description
- lesson_content: Optional content/details
- lesson_type: One of [main, revision, quiz, project, extra]
- page_number: Numeric page number
- standard, strand, skill, activities, assignment, assessment, objective: Optional fields

LIST TO CONVERT:
[PASTE YOUR LIST HERE]`

// Methods
const handleImportedJson = (data) => {
  emit('imported-json', data)
}

const handleExported = (info) => {
  emit('exported', info)
}

const copyAiPrompt = async () => {
  try {
    await navigator.clipboard.writeText(aiPrompt)
    $q.notify({
      color: 'positive',
      message: 'AI Prompt copied to clipboard!',
      icon: 'content_copy'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Failed to copy prompt',
      icon: 'error'
    })
  }
}

const processPastedJson = () => {
  try {
    // Basic cleanup of common AI formatting issues
    let jsonString = pastedJson.value.trim()
    
    // Remove markdown code blocks if present
    if (jsonString.startsWith('```')) {
      jsonString = jsonString.replace(/^```(json)?\n?/, '').replace(/\n?```$/, '')
    }

    const data = JSON.parse(jsonString)
    
    if (!Array.isArray(data)) {
      throw new Error('Data must be a JSON array')
    }

    if (data.length === 0) {
      throw new Error('Empty JSON array')
    }

    emit('imported-json', data)
    pastedJson.value = ''
    $q.notify({
      color: 'positive',
      message: `Successfully processed ${data.length} records from AI JSON`,
      icon: 'check'
    })
  } catch (err) {
    $q.notify({
      color: 'negative',
      message: 'Invalid JSON format: ' + err.message,
      icon: 'error'
    })
  }
}
</script>

<style scoped>
.q-card {
  max-width: 100%;
}
</style>
