<template>
  <div class="first-page-settings">
    <div class="settings-header">
      <q-icon name="description" size="20px" color="primary" />
      <span class="settings-title">First Page Settings</span>
    </div>

    <div class="settings-content">
      <!-- Enable First Page -->
      <q-toggle
        v-model="localSettings.enabled"
        label="Enable special first page"
        @update:model-value="updateSettings"
      />

      <div v-if="localSettings.enabled" class="first-page-options">
        <!-- First Page Type -->
        <q-select
          dense
          outlined
          :options="[
            { label: 'Title Page', value: 'title' },
            { label: 'Cover Page', value: 'cover' },
            { label: 'Custom Content', value: 'custom' }
          ]"
          emit-value
          map-options
          v-model="localSettings.type"
          label="First page type"
          @update:model-value="updateSettings"
        />

        <!-- Title Settings (for title page) -->
        <div v-if="localSettings.type === 'title'" class="options-section">
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
            v-model="localSettings.subtitle"
            label="Subtitle (optional)"
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
            v-model="localSettings.titleAlignment"
            label="Title alignment"
            @update:model-value="updateSettings"
          />
        </div>

        <!-- Cover Page Settings -->
        <div v-if="localSettings.type === 'cover'" class="options-section">
          <q-input
            dense
            outlined
            v-model="localSettings.coverTitle"
            label="Cover title"
            @blur="updateSettings"
          />

          <q-input
            dense
            outlined
            type="textarea"
            v-model="localSettings.coverDescription"
            label="Cover description"
            rows="3"
            @blur="updateSettings"
          />

          <div class="row items-center q-col-gutter-sm">
            <div class="col-12 col-md-6">
              <q-btn
                flat
                color="primary"
                icon="image"
                label="Choose Cover Image"
                @click="triggerCoverImage"
              />
            </div>
            <div class="col-12 col-md-6">
              <q-btn
                flat
                color="secondary"
                icon="content_paste"
                label="Paste Cover Image"
                @click="pasteCoverImage"
              />
            </div>
          </div>

          <q-input
            v-if="localSettings.coverImage"
            dense
            outlined
            v-model="localSettings.coverImage"
            label="Cover image URL / Data URL"
            @blur="updateSettings"
          />

          <q-btn
            v-if="localSettings.coverImage"
            flat
            color="negative"
            icon="delete"
            label="Remove Cover Image"
            @click="removeCoverImage"
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

        <!-- Page Numbering on First Page -->
        <q-toggle
          v-if="localSettings.enabled"
          v-model="localSettings.skipPageNumber"
          label="Skip page number on first page"
          @update:model-value="updateSettings"
        />

        <!-- Page Break After First Page -->
        <q-toggle
          v-if="localSettings.enabled"
          v-model="localSettings.pageBreakAfter"
          label="Add page break after first page"
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
      type: 'title',
      title: '',
      subtitle: '',
      titleAlignment: 'center',
      coverTitle: '',
      coverDescription: '',
      coverImage: '',
      customContent: '',
      skipPageNumber: true,
      pageBreakAfter: true
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

async function triggerCoverImage() {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = 'image/*'
  input.onchange = async (event) => {
    const file = event.target.files[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = () => {
      localSettings.value.coverImage = reader.result
      updateSettings()
    }
    reader.readAsDataURL(file)
  }
  input.click()
}

async function pasteCoverImage() {
  try {
    const items = await navigator.clipboard.read()
    for (const item of items) {
      for (const type of item.types) {
        if (type.startsWith('image/')) {
          const blob = await item.getType(type)
          const dataUrl = await new Promise((resolve, reject) => {
            const r = new FileReader()
            r.onload = resolve
            r.onerror = reject
            r.readAsDataURL(blob)
          })
          localSettings.value.coverImage = dataUrl
          updateSettings()
          return
        }
      }
    }
  } catch (e) {
    console.error('Paste cover image failed', e)
  }
}

function removeCoverImage() {
  localSettings.value.coverImage = ''
  updateSettings()
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
.first-page-settings {
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
  color: #1976d2;
}

.settings-title {
  font-size: 16px;
}

.settings-content {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.first-page-options {
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding-left: 16px;
  border-left: 3px solid #1976d2;
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
