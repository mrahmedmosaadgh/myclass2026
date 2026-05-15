<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationAPI } from '../composables/usePresentationAPI.js'

const props = defineProps({
  modelValue: Boolean,
  presentationData: Object,
})

const emit = defineEmits(['update:modelValue', 'saved'])

const $q = useQuasar()
const { savePresentation, loading, error } = usePresentationAPI()

const title = ref('')
const description = ref('')

async function handleSave() {
  if (!title.value.trim()) {
    $q.notify({
      type: 'warning',
      message: 'Please enter a title',
      position: 'top',
      timeout: 3000
    })
    return
  }

  try {
    const result = await savePresentation(
      title.value,
      description.value,
      props.presentationData
    )

    $q.notify({
      type: 'positive',
      message: 'Presentation saved successfully!',
      position: 'top',
      timeout: 3000
    })

    emit('saved', result.data)
    emit('update:modelValue', false)
    
    // Reset form
    title.value = ''
    description.value = ''
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: error.value || 'Failed to save presentation',
      position: 'top',
      timeout: 3000
    })
  }
}

function cancel() {
  emit('update:modelValue', false)
}
</script>

<template>
  <q-dialog
    :model-value="modelValue"
    @update:model-value="emit('update:modelValue', $event)"
    persistent
  >
    <q-card style="min-width: 400px; max-width: 500px;">
      <q-card-section>
        <div class="text-h6">Save Presentation</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-input
          v-model="title"
          label="Title *"
          outlined
          dense
          :disable="loading"
          @keyup.enter="handleSave"
        />
        
        <q-input
          v-model="description"
          label="Description (optional)"
          type="textarea"
          outlined
          dense
          rows="3"
          :disable="loading"
          class="q-mt-md"
        />
      </q-card-section>

      <q-card-actions align="right">
        <q-btn
          flat
          label="Cancel"
          color="grey-7"
          :disable="loading"
          @click="cancel"
        />
        <q-btn
          flat
          label="Save"
          color="primary"
          :loading="loading"
          @click="handleSave"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
