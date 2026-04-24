<template>
  <q-dialog v-model="model">
    <q-card style="min-width: 560px; max-width: 90vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Open Exam</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div class="text-caption text-grey-7 q-mb-sm">
          Paste exam JSON here.
        </div>
        <q-input
          v-model="jsonText"
          type="textarea"
          autogrow
          outlined
          placeholder='{"questions": [...], "sections": [...], "settings": {...}}'
        />
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat label="Cancel" v-close-popup />
        <q-btn color="primary" label="Open" @click="openFromJson" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useQuasar } from 'quasar'

const props = defineProps({
  modelValue: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'loaded'])

const $q = useQuasar()
const jsonText = ref('')

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

function openFromJson() {
  let parsed
  try {
    parsed = JSON.parse(jsonText.value)
  } catch (e) {
    $q.notify({ type: 'negative', message: 'Invalid JSON', position: 'top' })
    return
  }

  if (!parsed || (!Array.isArray(parsed.questions) && !Array.isArray(parsed.sampleQuestions))) {
    $q.notify({ type: 'negative', message: 'No questions found in JSON', position: 'top' })
    return
  }

  emit('loaded', parsed)
  model.value = false
}
</script>
