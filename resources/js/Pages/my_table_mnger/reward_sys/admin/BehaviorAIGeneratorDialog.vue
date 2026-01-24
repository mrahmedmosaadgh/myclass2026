<template>
  <q-dialog v-model="showDialog" maximized>
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <q-icon name="auto_awesome" size="md" color="secondary" class="q-mr-sm" />
        <div class="text-h6">AI Behavior Generator</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <q-stepper
          v-model="step"
          vertical
          color="secondary"
          animated
        >
          <!-- Step 1: Configure Prompt -->
          <q-step
            :name="1"
            title="Configure AI Prompt"
            icon="settings"
            :done="step > 1"
          >
            <div class="q-gutter-md">
              <!-- Number of Behaviors -->
              <q-input
                v-model.number="config.count"
                type="number"
                label="Number of Behaviors *"
                min="1"
                max="30"
                hint="How many behaviors to generate (1-30)"
                :rules="[val => val > 0 && val <= 30 || 'Must be between 1 and 30']"
              />

              <!-- Behavior Type -->
              <q-select
                v-model="config.type"
                :options="typeOptions"
                label="Behavior Type *"
                emit-value
                map-options
                hint="Select the type of behaviors to generate"
                :rules="[val => !!val || 'Type is required']"
              />

              <!-- Category/Theme -->
              <q-input
                v-model="config.category"
                label="Category/Theme (Optional)"
                outlined
                hint="e.g., 'Academic Excellence', 'Classroom Conduct', 'Social Skills'"
                placeholder="Enter a category or theme..."
              />

              <!-- Custom Instructions -->
              <q-input
                v-model="config.customInstructions"
                type="textarea"
                label="Additional Instructions / Context"
                outlined
                rows="3"
                hint="Add any special requirements or style preferences"
                placeholder="e.g., 'Focus on elementary school behaviors', 'Include specific examples'"
              />

              <!-- Smart Suggestions -->
              <q-expansion-item
                icon="lightbulb"
                label="Smart Presets"
                caption="Click for quick configuration templates"
                class="q-mb-md"
              >
                <q-card flat bordered class="q-pa-md">
                  <div class="text-subtitle2 q-mb-sm">Quick Presets:</div>
                  <div class="q-gutter-sm">
                    <q-btn
                      size="sm"
                      outline
                      color="secondary"
                      label="📚 Academic"
                      @click="applyPreset('academic')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="secondary"
                      label="🎯 Conduct"
                      @click="applyPreset('conduct')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="secondary"
                      label="🤝 Social"
                      @click="applyPreset('social')"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="secondary"
                      label="🌟 Comprehensive"
                      @click="applyPreset('comprehensive')"
                    />
                  </div>
                  
                  <q-separator class="q-my-md" />
                  
                  <div class="text-subtitle2 q-mb-sm">Suggested Instructions:</div>
                  <q-list dense>
                    <q-item clickable @click="addInstruction('Include specific, observable behaviors')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="secondary" />
                      </q-item-section>
                      <q-item-section>Include specific, observable behaviors</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Use age-appropriate language')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="secondary" />
                      </q-item-section>
                      <q-item-section>Use age-appropriate language</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Focus on growth mindset')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="secondary" />
                      </q-item-section>
                      <q-item-section>Focus on growth mindset</q-item-section>
                    </q-item>
                    <q-item clickable @click="addInstruction('Include both individual and group behaviors')">
                      <q-item-section avatar>
                        <q-icon name="add_circle" color="secondary" />
                      </q-item-section>
                      <q-item-section>Include both individual and group behaviors</q-item-section>
                    </q-item>
                  </q-list>
                </q-card>
              </q-expansion-item>

              <!-- Language -->
              <q-select
                v-model="config.language"
                :options="['English', 'Arabic', 'Bilingual (Both)']"
                label="Language"
                hint="Behavior names language"
              />
            </div>

            <q-stepper-navigation>
              <q-btn @click="generatePrompt" color="secondary" label="Generate Prompt" icon="content_copy" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 2: Copy Prompt -->
          <q-step
            :name="2"
            title="Copy Prompt to AI"
            icon="content_copy"
            :done="step > 2"
          >
            <q-card flat bordered>
              <q-card-section>
                <div class="text-subtitle2 q-mb-sm">Generated Prompt:</div>
                <q-input
                  v-model="generatedPrompt"
                  type="textarea"
                  readonly
                  outlined
                  rows="15"
                  class="q-mb-md"
                />
                <q-btn
                  @click="copyPrompt"
                  color="secondary"
                  label="Copy to Clipboard"
                  icon="content_copy"
                  class="q-mr-sm"
                />
                <q-banner class="bg-info text-white q-mt-md">
                  <template v-slot:avatar>
                    <q-icon name="info" />
                  </template>
                  Copy this prompt and paste it into ChatGPT, Claude, or Gemini. Then copy the AI's JSON response.
                </q-banner>
              </q-card-section>
            </q-card>

            <q-stepper-navigation>
              <q-btn @click="step = 3" color="secondary" label="Next: Paste AI Response" />
              <q-btn flat @click="step = 1" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 3: Paste AI Response -->
          <q-step
            :name="3"
            title="Paste AI Response"
            icon="paste"
            :done="step > 3"
          >
            <q-input
              v-model="aiResponse"
              type="textarea"
              label="Paste AI Response (JSON)"
              outlined
              rows="10"
              hint="Paste the JSON response from the AI"
              class="q-mb-md"
            />

            <q-btn
              @click="pasteFromClipboard"
              color="primary"
              label="Paste from Clipboard"
              icon="content_paste"
              outline
              class="q-mb-md"
            />

            <q-stepper-navigation>
              <q-btn @click="validateResponse" color="secondary" label="Validate & Preview" :loading="validating" />
              <q-btn flat @click="step = 2" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>

          <!-- Step 4: Preview & Insert -->
          <q-step
            :name="4"
            title="Preview & Insert"
            icon="preview"
          >
            <div v-if="validatedBehaviors.length > 0">
              <!-- Selection Summary -->
              <q-banner class="bg-secondary text-white q-mb-md">
                <template v-slot:avatar>
                  <q-icon name="check_circle" />
                </template>
                <div class="row items-center">
                  <div class="col">
                    <strong>{{ selectedCount }}</strong> of <strong>{{ validatedBehaviors.length }}</strong> behaviors selected
                  </div>
                  <div class="col-auto q-gutter-sm">
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Select All"
                      @click="selectAll"
                      icon="check_box"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Select None"
                      @click="selectNone"
                      icon="check_box_outline_blank"
                    />
                    <q-btn
                      size="sm"
                      outline
                      color="white"
                      label="Inverse"
                      @click="selectInverse"
                      icon="swap_vert"
                    />
                  </div>
                </div>
              </q-banner>

              <!-- Preview Table -->
              <q-table
                :rows="validatedBehaviors"
                :columns="previewColumns"
                row-key="index"
                flat
                bordered
                selection="multiple"
                v-model:selected="selectedBehaviors"
                class="q-mb-md"
              >
                <template v-slot:body-cell-number="props">
                  <q-td :props="props">
                    <strong>#{{ props.row.index + 1 }}</strong>
                  </q-td>
                </template>
                <template v-slot:body-cell-type="props">
                  <q-td :props="props">
                    <q-badge :color="props.row.type === 'positive' ? 'green' : 'red'">
                      {{ props.row.type === 'positive' ? '✅ Positive' : '❌ Negative' }}
                    </q-badge>
                  </q-td>
                </template>
                <template v-slot:body-cell-points="props">
                  <q-td :props="props">
                    <q-chip
                      :color="props.row.points > 0 ? 'green' : 'red'"
                      text-color="white"
                      size="sm"
                    >
                      {{ props.row.points > 0 ? '+' : '' }}{{ props.row.points }}
                    </q-chip>
                  </q-td>
                </template>
                <template v-slot:body-cell-status="props">
                  <q-td :props="props">
                    <q-badge :color="props.row.valid ? 'positive' : 'negative'">
                      {{ props.row.valid ? 'Valid' : 'Invalid' }}
                    </q-badge>
                    <div v-if="!props.row.valid" class="text-caption text-negative">
                      {{ props.row.errors }}
                    </div>
                  </q-td>
                </template>
              </q-table>

              <q-banner v-if="invalidCount > 0" class="bg-warning text-white q-mb-md">
                <template v-slot:avatar>
                  <q-icon name="warning" />
                </template>
                {{ invalidCount }} invalid behavior(s) detected (cannot be selected)
              </q-banner>
            </div>

            <q-stepper-navigation>
              <q-btn
                @click="bulkInsert"
                color="secondary"
                :label="`Insert ${selectedCount} Selected Behavior${selectedCount !== 1 ? 's' : ''}`"
                icon="upload"
                :loading="inserting"
                :disable="selectedCount === 0"
              />
              <q-btn flat @click="step = 3" label="Back" class="q-ml-sm" />
            </q-stepper-navigation>
          </q-step>
        </q-stepper>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()

const props = defineProps({
  modelValue: Boolean,
  schoolId: [Number, String]
})

const emit = defineEmits(['update:modelValue', 'success'])

const showDialog = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const step = ref(1)
const validating = ref(false)
const inserting = ref(false)

const config = ref({
  count: 10,
  type: 'mixed',
  category: '',
  customInstructions: '',
  language: 'Bilingual (Both)'
})

const generatedPrompt = ref('')
const aiResponse = ref('')
const validatedBehaviors = ref([])

const typeOptions = [
  { value: 'positive', label: '✅ Positive Only' },
  { value: 'negative', label: '❌ Negative Only' },
  { value: 'mixed', label: '🔀 Mixed (Both)' }
]

const previewColumns = [
  { name: 'number', label: '#', field: 'index', align: 'center', style: 'width: 50px' },
  { name: 'name', label: 'Name (EN)', field: 'name', align: 'left' },
  { name: 'name_ar', label: 'Name (AR)', field: 'name_ar', align: 'left' },
  { name: 'type', label: 'Type', field: 'type', align: 'center' },
  { name: 'points', label: 'Points', field: 'points', align: 'center' },
  { name: 'description', label: 'Description', field: 'description', align: 'left' },
  { name: 'status', label: 'Status', align: 'center' }
]

const invalidCount = computed(() => {
  return validatedBehaviors.value.filter(b => !b.valid).length
})

const selectedBehaviors = ref([])

const selectedCount = computed(() => {
  return selectedBehaviors.value.length
})

const generatePrompt = () => {
  const promptParts = [
    `Generate ${config.value.count} student behavior definitions for a school reward system.`,
    '',
    `Type: ${config.value.type === 'mixed' ? 'Both positive and negative behaviors' : config.value.type + ' behaviors only'}`,
    config.value.category ? `Category/Theme: ${config.value.category}` : '',
    `Language: ${config.value.language}`,
    '',
    config.value.customInstructions ? `Additional Instructions:\n${config.value.customInstructions}` : '',
    '',
    'Return ONLY a valid JSON object in this exact format:',
    '```json',
    JSON.stringify({
      behaviors: [
        {
          name: "Behavior name in English",
          name_ar: "Behavior name in Arabic (if language is Arabic or Bilingual)",
          type: "positive",
          points: 1,
          description: "Optional category or description"
        },
        {
          name: "Another behavior",
          name_ar: "سلوك آخر",
          type: "negative",
          points: -1,
          description: "Optional"
        }
      ]
    }, null, 2),
    '```',
    '',
    'Important rules:',
    '- name is required (English)',
    '- name_ar is optional but recommended for bilingual support',
    '- type must be "positive" or "negative"',
    '- points should be reasonable (1-10 for positive, -1 to -10 for negative)',
    '- description is optional',
    '- Make behaviors specific, observable, and age-appropriate',
    '- Focus on behaviors that can be consistently measured'
  ]

  generatedPrompt.value = promptParts.filter(Boolean).join('\n')
  step.value = 2
}

const copyPrompt = async () => {
  try {
    await navigator.clipboard.writeText(generatedPrompt.value)
    $q.notify({
      type: 'positive',
      message: 'Prompt copied to clipboard!',
      icon: 'content_copy',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to copy to clipboard',
      position: 'top'
    })
  }
}

const pasteFromClipboard = async () => {
  try {
    const text = await navigator.clipboard.readText()
    aiResponse.value = text
    $q.notify({
      type: 'positive',
      message: 'Pasted from clipboard!',
      icon: 'content_paste',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Failed to read from clipboard',
      caption: 'Please paste manually using Ctrl+V or Cmd+V',
      position: 'top'
    })
  }
}

const validateResponse = () => {
  validating.value = true
  
  try {
    // Try to parse JSON
    let data
    const cleaned = aiResponse.value.trim()
    
    // Remove markdown code blocks if present
    const jsonMatch = cleaned.match(/```(?:json)?\s*([\s\S]*?)```/)
    if (jsonMatch) {
      data = JSON.parse(jsonMatch[1])
    } else {
      data = JSON.parse(cleaned)
    }

    if (!data.behaviors || !Array.isArray(data.behaviors)) {
      throw new Error('Invalid format: "behaviors" array not found')
    }

    // Validate each behavior
    validatedBehaviors.value = data.behaviors.map((b, index) => {
      const errors = []
      
      if (!b.name) errors.push('Missing name')
      if (!['positive', 'negative'].includes(b.type)) {
        errors.push('Invalid type')
      }
      if (!b.points || typeof b.points !== 'number') {
        errors.push('Invalid points')
      }

      return {
        ...b,
        index,
        valid: errors.length === 0,
        errors: errors.join(', '),
        school_id: props.schoolId || 1
      }
    })

    step.value = 4
    
    // Auto-select all valid behaviors
    selectedBehaviors.value = validatedBehaviors.value.filter(b => b.valid)
    
    $q.notify({
      type: 'positive',
      message: `Validated ${validatedBehaviors.value.length} behaviors`,
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: 'Invalid JSON format',
      caption: error.message,
      position: 'top'
    })
  } finally {
    validating.value = false
  }
}

// Selection functions
const selectAll = () => {
  selectedBehaviors.value = validatedBehaviors.value.filter(b => b.valid)
}

const selectNone = () => {
  selectedBehaviors.value = []
}

const selectInverse = () => {
  const currentSelected = new Set(selectedBehaviors.value.map(b => b.index))
  selectedBehaviors.value = validatedBehaviors.value.filter(b => 
    b.valid && !currentSelected.has(b.index)
  )
}

const bulkInsert = async () => {
  inserting.value = true
  
  // Use only selected valid behaviors
  const behaviorsToInsert = selectedBehaviors.value.filter(b => b.valid)
  
  if (behaviorsToInsert.length === 0) {
    $q.notify({
      type: 'warning',
      message: 'No behaviors selected',
      position: 'top'
    })
    inserting.value = false
    return
  }
  
  try {
    // Try bulk endpoint first
    const response = await axios.post('/api/behaviors/bulk', {
      behaviors: behaviorsToInsert,
      school_id: props.schoolId || 1
    })

    const summary = response.data.summary || {}
    const errors = response.data.errors || []
    
    if (summary.created > 0) {
      const msg = summary.errors > 0 
        ? `Created ${summary.created} behaviors. ${summary.errors} failed.`
        : `Successfully created ${summary.created} behaviors!`
        
      $q.notify({
        type: summary.errors > 0 ? 'warning' : 'positive',
        message: msg,
        icon: 'check_circle',
        position: 'top',
        timeout: 5000
      })
      
      // Close dialog and emit success to refresh list
      showDialog.value = false
      emit('success')
    } else {
      // Nothing created
      const errorMsg = errors.length > 0 ? errors[0].error : 'No behaviors were created.'
      throw new Error(errorMsg)
    }

  } catch (error) {
    console.error('Bulk insert error:', error)
    $q.notify({
      type: 'negative',
      message: 'Failed to create behaviors',
      caption: error.response?.data?.message || error.message,
      position: 'top',
      timeout: 5000
    })
  } finally {
    inserting.value = false
  }
}

// Smart Presets
const applyPreset = (type) => {
  const presets = {
    academic: {
      count: 15,
      type: 'positive',
      category: 'Academic Excellence',
      customInstructions: 'Focus on learning achievements, homework completion, participation, and academic progress.'
    },
    conduct: {
      count: 20,
      type: 'mixed',
      category: 'Classroom Conduct',
      customInstructions: 'Include both positive behaviors (following rules, respect) and negative behaviors (disruption, tardiness).'
    },
    social: {
      count: 12,
      type: 'positive',
      category: 'Social Skills',
      customInstructions: 'Focus on interpersonal skills, teamwork, kindness, and peer relationships.'
    },
    comprehensive: {
      count: 30,
      type: 'mixed',
      category: '',
      customInstructions: 'Create a comprehensive set covering academic, social, and behavioral aspects with balanced positive and negative behaviors.'
    }
  }

  const preset = presets[type]
  if (preset) {
    Object.assign(config.value, preset)
    $q.notify({
      type: 'info',
      message: `Applied "${type}" preset`,
      position: 'top'
    })
  }
}

// Add instruction helper
const addInstruction = (instruction) => {
  if (config.value.customInstructions) {
    config.value.customInstructions += '\n- ' + instruction
  } else {
    config.value.customInstructions = '- ' + instruction
  }
  $q.notify({
    type: 'positive',
    message: 'Instruction added',
    position: 'top'
  })
}
</script>
