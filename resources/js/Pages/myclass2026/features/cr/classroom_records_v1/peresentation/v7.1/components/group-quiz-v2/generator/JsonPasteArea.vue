<script setup>
const props = defineProps({
  modelValue: { type: String, default: '' },
  errorMessage: { type: String, default: '' }
});

const emit = defineEmits(['update:modelValue', 'preview', 'pasteFromClipboard']);
</script>

<template>
  <div class="json-paste-area q-gutter-y-md">
    <q-input
      :model-value="modelValue"
      @update:model-value="$emit('update:modelValue', $event)"
      type="textarea"
      label="Paste AI Output (JSON)"
      placeholder="Paste the raw JSON Array back from the AI here..."
      outlined
      rows="8"
      class="json-textarea"
    />

    <div class="row q-gutter-sm">
      <q-btn
        color="primary"
        icon="visibility"
        label="Preview Questions"
        no-caps
        unelevated
        :disable="!modelValue.trim()"
        @click="$emit('preview')"
      />
      <q-btn
        color="grey-6"
        icon="content_paste"
        label="Paste from Clipboard"
        no-caps
        flat
        @click="$emit('pasteFromClipboard')"
      />
    </div>

    <q-banner v-if="errorMessage" rounded class="bg-negative-1 q-mt-sm">
      <template #avatar>
        <q-icon name="error" color="negative" />
      </template>
      {{ errorMessage }}
    </q-banner>
  </div>
</template>

<style scoped>
.json-textarea :deep(textarea) {
  font-family: 'Fira Code', 'Consolas', monospace;
  font-size: 0.85rem;
}
.bg-negative-1 {
  background: #fef2f2;
}
</style>
