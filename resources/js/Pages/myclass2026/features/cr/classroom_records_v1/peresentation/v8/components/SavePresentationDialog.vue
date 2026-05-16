<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import { usePresentationAPI } from '../composables/usePresentationAPI.js'

const props = defineProps({
  modelValue: Boolean,
  presentationData: Object,
  presentationId: Number, // If set, this is an update, not a new save
  presentationTitle: String, // Pre-fill title when updating
  presentationDescription: String, // Pre-fill description when updating
})

const emit = defineEmits(['update:modelValue', 'saved'])

const $q = useQuasar()
const { savePresentation, updatePresentation, loading, error } = usePresentationAPI()

const title = ref('')
const description = ref('')
const saveAsNew = ref(false)

const isUpdate = computed(() => !!props.presentationId)
const actionLabel = computed(() => isUpdate.value && !saveAsNew.value ? 'Update' : 'Save')

// Pre-fill form when dialog opens and we have title/description props
watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    if (props.presentationTitle) {
      title.value = props.presentationTitle
    }
    if (props.presentationDescription) {
      description.value = props.presentationDescription
    }
  }
})

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
    let result
    if (isUpdate.value && !saveAsNew.value) {
      result = await updatePresentation(
        props.presentationId,
        title.value,
        description.value,
        props.presentationData
      )
    } else {
      result = await savePresentation(
        title.value,
        description.value,
        props.presentationData
      )
    }

    $q.notify({
      type: 'positive',
      message: `Presentation ${isUpdate.value && !saveAsNew.value ? 'updated' : 'saved'} successfully!`,
      position: 'top',
      timeout: 3000
    })

    emit('saved', result.data)
    emit('update:modelValue', false)
    
    // Reset form
    title.value = ''
    description.value = ''
    saveAsNew.value = false
  } catch (err) {
    $q.notify({
      type: 'negative',
      message: error.value || `Failed to ${isUpdate.value && !saveAsNew.value ? 'update' : 'save'} presentation`,
      position: 'top',
      timeout: 3000
    })
  }
}

function cancel() {
  emit('update:modelValue', false)
  title.value = ''
  description.value = ''
  saveAsNew.value = false
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
        <div class="text-h6">{{ isUpdate ? 'Update Presentation' : 'Save Presentation' }}</div>
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

        <q-checkbox
          v-if="isUpdate"
          v-model="saveAsNew"
          label="Save as new copy"
          color="primary"
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
          :label="actionLabel"
          color="primary"
          :loading="loading"
          @click="handleSave"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>
