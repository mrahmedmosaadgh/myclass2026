<template>
  <q-dialog v-model="model" maximized>
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">Settings</div>
        <q-space />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section class="q-pa-none">
        <q-tabs
          v-model="tab"
          dense
          active-color="primary"
          indicator-color="primary"
          align="left"
        >
          <q-tab name="mcq" label="MCQ" />
          <q-tab name="questions" label="Questions" />
        </q-tabs>

        <q-separator />

        <q-tab-panels v-model="tab" animated>
          <q-tab-panel name="mcq">
            <McqDisplaySettings v-model="localSettings" />
          </q-tab-panel>

          <q-tab-panel name="questions">
            <div class="q-pa-md text-grey-7">
              Coming soon.
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat label="Close" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import McqDisplaySettings from './settings/McqDisplaySettings.vue'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  initialTab: { type: String, default: 'mcq' },
  settings: { type: Object, required: true }
})

const emit = defineEmits(['update:modelValue', 'update:settings'])

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

const tab = ref('mcq')

const localSettings = computed({
  get: () => props.settings,
  set: (v) => emit('update:settings', v)
})

watch(
  () => props.initialTab,
  (v) => {
    tab.value = v || 'mcq'
  },
  { immediate: true }
)
</script>
