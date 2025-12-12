<template>
  <q-page class="q-pa-md">
    <div class="row justify-center">
      <div class="col-12 col-md-8 col-lg-6">
        <q-card>
          <q-card-section>
            <div class="text-h5 q-mb-md">Lesson Plan Template Creator</div>
            
            <q-form @submit="submitTemplate" class="q-gutter-md">
              <!-- Template Name -->
              <q-input
                v-model="templateName"
                label="Template Name"
                filled
                :rules="[val => !!val || 'Template name is required']"
              />
              
              <!-- Dynamic Fields Section -->
              <div class="text-h6 q-mt-lg q-mb-md">Template Fields</div>
              
              <div v-for="(field, index) in fields" :key="index" class="q-mb-md">
                <q-card flat bordered>
                  <q-card-section>
                    <div class="row q-col-gutter-md items-center">
                      <div class="col-12 col-sm-5">
                        <q-input
                          v-model="field.label"
                          label="Field Label"
                          filled
                          :rules="[val => !!val || 'Label is required']"
                        />
                      </div>
                      
                      <div class="col-12 col-sm-5">
                        <q-select
                          v-model="field.type"
                          :options="fieldTypeOptions"
                          label="Field Type"
                          filled
                          emit-value
                          map-options
                        />
                      </div>
                      
                      <div class="col-12 col-sm-2">
                        <q-btn
                          color="negative"
                          icon="delete"
                          flat
                          round
                          @click="removeField(index)"
                        />
                      </div>
                    </div>
                  </q-card-section>
                </q-card>
              </div>
              
              <!-- Add Field Button -->
              <q-btn
                color="primary"
                icon="add"
                label="Add Field"
                @click="addField"
                class="q-mt-md"
              />
              
              <!-- Submit Button -->
              <div class="row justify-end q-mt-lg">
                <q-btn
                  type="submit"
                  color="positive"
                  label="Save Template"
                  :loading="loading"
                />
              </div>
            </q-form>
            
            <!-- JSON Preview -->
            <q-card v-if="showPreview" class="q-mt-lg">
              <q-card-section>
                <div class="text-h6 q-mb-md">Template Structure (JSON)</div>
                <pre class="bg-grey-2 q-pa-md rounded-borders">{{ jsonPreview }}</pre>
              </q-card-section>
            </q-card>
          </q-card-section>
        </q-card>
      </div>
    </div>
    
    <!-- Success Dialog -->
    <q-dialog v-model="showSuccessDialog">
      <q-card>
        <q-card-section>
          <div class="text-h6">Success</div>
        </q-card-section>
        <q-card-section>
          Template saved successfully!
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="OK" color="primary" v-close-popup />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'

const $q = useQuasar()

// Reactive data
const templateName = ref('')
const fields = ref([])
const loading = ref(false)
const showPreview = ref(false)
const showSuccessDialog = ref(false)

// Field type options
const fieldTypeOptions = [
  { label: 'Text Input', value: 'text' },
  { label: 'Text Area', value: 'textarea' },
  { label: 'Select Dropdown', value: 'select' }
]

// Computed property for JSON preview
const jsonPreview = computed(() => {
  return JSON.stringify(fields.value, null, 2)
})

// Methods
const addField = () => {
  fields.value.push({
    label: '',
    type: 'text'
  })
}

const removeField = (index) => {
  fields.value.splice(index, 1)
}

const submitTemplate = async () => {
  if (!templateName.value || fields.value.length === 0) {
    $q.notify({
      type: 'negative',
      message: 'Please provide a template name and at least one field'
    })
    return
  }

  // Validate all fields have labels
  const hasEmptyLabels = fields.value.some(field => !field.label.trim())
  if (hasEmptyLabels) {
    $q.notify({
      type: 'negative',
      message: 'Please fill in all field labels'
    })
    return
  }

  loading.value = true

  try {
    const payload = {
      name: templateName.value,
      structure: fields.value
    }

    // Send to Laravel backend
    const response = await axios.post('/api/lesson-plan-templates', payload)
    
    if (response.data.success) {
      showSuccessDialog.value = true
      resetForm()
    }
  } catch (error) {
    console.error('Error saving template:', error)
    $q.notify({
      type: 'negative',
      message: error.response?.data?.message || 'Error saving template'
    })
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  templateName.value = ''
  fields.value = []
  showPreview.value = false
}

// Initialize with some default fields
const initializeDefaultFields = () => {
  fields.value = [
    { label: 'Objective', type: 'textarea' },
    { label: 'Materials', type: 'text' },
    { label: 'Procedure', type: 'textarea' }
  ]
}

// Initialize on mount
initializeDefaultFields()
</script>

<style scoped>
.q-card {
  max-width: 800px;
  margin: 0 auto;
}

pre {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  overflow-x: auto;
}

@media (max-width: 599px) {
  .q-card {
    margin: 0;
  }
}
</style>