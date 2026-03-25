<template>
  <div class="q-pa-md">
    <q-card flat bordered class="bg-grey-1">
      <q-card-section>
        <div class="text-h6">🧭 LMS Roadmap Editor</div>
        <div class="text-caption text-grey">Modify your roadmap tree nodes safely.</div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-input
          v-model="textValue"
          type="textarea"
          autogrow
          filled
          label="Edit JSON Tree Data"
          placeholder='[ { "label": "Root", "children": [] } ]'
        />

        <div class="q-mt-md flex justify-between">
          <q-btn color="primary" icon="save" label="Save" @click="saveData" />
          <q-btn color="secondary" icon="content_copy" label="Copy JSON" @click="copyData" />
        </div>

        <div class="q-mt-md">
          <div class="text-subtitle2 text-primary">Preview Tree:</div>
          <pre class="bg-grey-2 q-pa-sm q-mt-sm">{{ formattedData }}</pre>
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  nodes: {
    type: [Array, String],
    default: () => []
  }
})

const emit = defineEmits(['update:nodes'])

const textValue = ref('')
const parsedData = ref([])

// ✅ Safe parse function
function safeParse(value) {
  try {
    return Array.isArray(value)
      ? value
      : JSON.parse(value || '[]')
  } catch (e) {
    console.warn('Invalid JSON detected:', e)
    return []
  }
}

// Initialize from props
parsedData.value = safeParse(props.nodes)
textValue.value = JSON.stringify(parsedData.value, null, 2)

// Watch for prop changes dynamically
watch(
  () => props.nodes,
  (newVal) => {
    parsedData.value = safeParse(newVal)
    textValue.value = JSON.stringify(parsedData.value, null, 2)
  },
  { immediate: true }
)

// Save edited data
function saveData() {
  const newData = safeParse(textValue.value)
  parsedData.value = newData
  emit('update:nodes', newData)
  $q.notify({ message: 'Roadmap updated successfully ✅', color: 'green', icon: 'check' })
}

// Copy to clipboard
function copyData() {
  navigator.clipboard.writeText(JSON.stringify(parsedData.value, null, 2))
  $q.notify({ message: 'Copied to clipboard 📋', color: 'primary', icon: 'content_copy' })
}

const formattedData = computed(() => JSON.stringify(parsedData.value, null, 2))
</script>

<style scoped>
pre {
  border-radius: 8px;
  overflow-x: auto;
  font-size: 13px;
  line-height: 1.4;
}
</style>
