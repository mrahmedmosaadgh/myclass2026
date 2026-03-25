<template>
  <div class="template-select flex items-center gap-3">
    <q-select
      v-model="selectedId"
      :options="options"
      emit-value
      map-options
      label="Template"
      outlined
      dense
      clearable
      class="min-w-[240px]"
    />

    <q-btn
      size="sm"
      icon="visibility"
      flat
      dense
      color="info"
      @click="preview = !preview"
      v-if="selectedTemplate"
    >
      <q-tooltip>Preview template</q-tooltip>
    </q-btn>

    <q-btn
      size="sm"
      icon="file_download"
      unelevated
      color="primary"
      @click="applyTemplate(false)"
      :disable="!selectedTemplate"
    >
      Apply (Append)
    </q-btn>

    <q-btn
      size="sm"
      icon="vertical_align_top"
      unelevated
      color="negative"
      @click="applyTemplate(true)"
      :disable="!selectedTemplate"
    >
      Apply (Overwrite)
    </q-btn>

    <q-dialog v-model="preview" persistent maximized>
      <q-card>
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Template Preview</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup @click="preview = false" />
        </q-card-section>

        <q-card-section>
          <div v-if="selectedTemplate">
            <div class="text-h6">{{ selectedTemplate.name }}</div>
            <div class="text-caption q-mb-md" v-if="selectedTemplate.description">{{ selectedTemplate.description }}</div>

            <div v-if="selectedTemplate.structure?.length">
              <div v-for="(s, idx) in selectedTemplate.structure" :key="idx" class="q-mb-md">
                <div class="text-subtitle2 font-semibold">{{ s.label || s.id || 'Section ' + (idx+1) }}</div>
                <div class="text-caption text-grey-6">Type: {{ s.type || s.default_slide_type || 'text' }} • Slides: {{ s.slides || 1 }}</div>
              </div>
            </div>
            <div v-else>
              <div class="text-caption">No structure defined</div>
            </div>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useQuasar } from 'quasar';

const props = defineProps({
  templates: {
    type: Array,
    default: () => []
  },
  modelValue: {
    type: [Number, String],
    default: null
  }
});

const emit = defineEmits(['update:modelValue', 'apply']);

const $q = useQuasar();

const selectedId = ref(props.modelValue);
const preview = ref(false);

watch(() => props.modelValue, (v) => selectedId.value = v);
watch(selectedId, (v) => emit('update:modelValue', v));

const options = computed(() => props.templates.map(t => ({ label: t.name, value: t.id })));
const selectedTemplate = computed(() => props.templates.find(t => t.id === selectedId.value));

const applyTemplate = (overwrite = true) => {
  if (!selectedTemplate.value) return;
  emit('apply', { template: selectedTemplate.value, overwrite });
};
</script>

<style scoped>
.template-select {
  align-items: center;
}
</style>
