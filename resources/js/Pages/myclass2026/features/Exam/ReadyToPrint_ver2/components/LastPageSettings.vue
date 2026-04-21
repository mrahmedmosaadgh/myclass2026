<template>
  <div class="last-page-settings">
    <div class="settings-header">
      <q-icon name="last_page" size="20px" color="secondary" />
      <span class="settings-title">Last Page Settings</span>
    </div>

    <div class="settings-content">
      <!-- Enable Last Page -->
      <q-toggle
        v-model="localSettings.enabled"
        label="Enable last page"
        @update:model-value="updateSettings"
      />

      <div v-if="localSettings.enabled" class="last-page-options">
        <!-- Last Page Type -->
        <q-select
          dense
          outlined
          :options="[
            { label: 'End Message', value: 'message' },
            { label: 'Thank You Page', value: 'thankyou' },
            { label: 'Custom Content', value: 'custom' }
          ]"
          emit-value
          map-options
          v-model="localSettings.type"
          label="Last page type"
          @update:model-value="updateSettings"
        />

        <!-- Message Settings (for message type) -->
        <div v-if="localSettings.type === 'message'" class="options-section">
          <q-input
            dense
            outlined
            v-model="localSettings.title"
            label="Page title"
            @blur="updateSettings"
          />

          <q-input
            dense
            outlined
            type="textarea"
            v-model="localSettings.message"
            label="End message"
            rows="4"
            @blur="updateSettings"
          />

          <q-select
            dense
            outlined
            :options="[
              { label: 'Center', value: 'center' },
              { label: 'Left', value: 'left' },
              { label: 'Right', value: 'right' }
            ]"
            emit-value
            map-options
            v-model="localSettings.alignment"
            label="Content alignment"
            @update:model-value="updateSettings"
          />
        </div>

        <!-- Thank You Page Settings -->
        <div v-if="localSettings.type === 'thankyou'" class="options-section">
          <q-input
            dense
            outlined
            v-model="localSettings.title"
            label="Thank you title"
            @blur="updateSettings"
          />

          <q-input
            dense
            outlined
            type="textarea"
            v-model="localSettings.message"
            label="Thank you message"
            rows="4"
            @blur="updateSettings"
          />

          <q-toggle
            v-model="localSettings.showTotalMarks"
            label="Show total marks"
            @update:model-value="updateSettings"
          />

          <q-toggle
            v-model="localSettings.showCompletionTime"
            label="Show completion time placeholder"
            @update:model-value="updateSettings"
          />
        </div>

        <!-- Custom Content Settings -->
        <div v-if="localSettings.type === 'custom'" class="options-section">
          <q-input
            dense
            outlined
            type="textarea"
            v-model="localSettings.customContent"
            label="Custom HTML content"
            rows="8"
            @blur="updateSettings"
          />

          <div class="row items-center q-col-gutter-sm">
            <div class="col-auto">
              <q-btn
                flat
                color="primary"
                icon="content_paste"
                label="Paste HTML"
                @click="pasteCustomContent"
              />
            </div>
          </div>
        </div>

        <!-- Page Numbering on Last Page -->
        <q-toggle
          v-if="localSettings.enabled"
          v-model="localSettings.skipPageNumber"
          label="Skip page number on last page"
          @update:model-value="updateSettings"
        />

        <!-- Page Break Before Last Page -->
        <q-toggle
          v-if="localSettings.enabled"
          v-model="localSettings.pageBreakBefore"
          label="Add page break before last page"
          @update:model-value="updateSettings"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({
      enabled: false,
      type: 'message',
      title: 'End of Exam',
      message: 'Thank you for completing the exam.',
      alignment: 'center',
      showTotalMarks: false,
      showCompletionTime: false,
      customContent: '',
      skipPageNumber: false,
      pageBreakBefore: true
    })
  }
})

const emit = defineEmits(['update:modelValue'])

// Local copy of settings to avoid direct mutation
const localSettings = ref({ ...props.modelValue })

// Watch for external changes
watch(() => props.modelValue, (newValue) => {
  localSettings.value = { ...newValue }
}, { deep: true })

function updateSettings() {
  emit('update:modelValue', { ...localSettings.value })
}

async function pasteCustomContent() {
  try {
    const text = await navigator.clipboard.readText()
    if (!text) return
    localSettings.value.customContent = text
    updateSettings()
  } catch (e) {
    console.error('Paste custom content failed', e)
  }
}
</script>

<style scoped>
.last-page-settings {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 16px;
  background: #fafafa;
}

.settings-header {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 16px;
  font-weight: 600;
  color: #9c27b0;
}

.settings-title {
  font-size: 16px;
}

.settings-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.last-page-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-left: 16px;
  border-left: 3px solid #9c27b0;
  margin-left: 8px;
}

.options-section {
  display: flex;
  flex-direction: column;
  gap: 12px;
  padding: 12px;
  background: white;
  border-radius: 6px;
  border: 1px solid #e0e0e0;
}
</style>
