<template>
  <div class="p-6 space-y-6">
    <q-card class="shadow-md rounded-xl">
      <q-card-section class="flex justify-between items-center">
        <div class="text-xl font-bold">{{ t('common.settings') || 'Table Manager' }}</div>
        <div>
          <q-btn color="primary" icon="content_paste" label="Paste JSON" @click="openPasteDialog" />
          <q-btn color="secondary" icon="file_copy" label="Copy Config" @click="copyConfig" class="ml-2" />
        </div>
      </q-card-section>

      <q-separator />

      <q-card-section>
        <div v-if="!jsonData.length" class="text-gray-500 text-center">
          {{ t('common.noResults') || 'No JSON data provided' }}
        </div>

        <div v-else>
          <div class="mb-4 font-semibold">{{ t('common.preview') || 'Table Preview' }}</div>
          <DynamicTable :data="jsonData" :config="tableConfig" />

          <q-separator class="my-4" />

          <div class="font-semibold mb-2">{{ t('common.edit') || 'Edit Configuration' }}</div>
          <q-table
            flat
            :rows="tableConfig"
            :columns="configColumns"
            row-key="key"
            dense
          >
            <template v-slot:body-cell-label="props">
              <q-input v-model="props.row.label" outlined dense />
            </template>

            <template v-slot:body-cell-type="props">
              <q-select
                v-model="props.row.type"
                :options="['text', 'email', 'number', 'button', 'list']"
                outlined dense
              />
            </template>

            <template v-slot:body-cell-visible="props">
              <q-toggle v-model="props.row.visible" color="primary" />
            </template>
          </q-table>
        </div>
      </q-card-section>
    </q-card>

    <!-- Paste JSON Dialog -->
    <q-dialog v-model="showPasteDialog" persistent>
      <q-card style="min-width: 500px">
        <q-card-section>
          <div class="text-lg font-semibold mb-2">Paste JSON Data</div>
          <q-input v-model="jsonInput" type="textarea" autogrow />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancel" color="grey" v-close-popup />
          <q-btn flat label="Load" color="primary" @click="loadJsonData" />
          <q-btn flat label="Load Text" color="primary" @click="loadtextData" />

        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import DynamicTable from './DynamicTable.vue'
// import { useSafeI18n } from '@/composables/useSafeI18n'
import { useI18n } from 'vue-i18n'
// const { t } = useSafeI18n()
const { notsafe_t, locale } = useI18n()

// ✅ Safe translation function
const t = (key) => {
  if (!key || typeof key !== 'string') return key || ''
  try {
    return notsafe_t(key)
  } catch {
    return key
  }
}
const $q = useQuasar()

const jsonData = ref([])
const tableConfig = ref([])

const jsonInput = ref('')
const showPasteDialog = ref(false)

// 🧩 Columns for the configuration editor
const configColumns = [
  { name: 'key', label: t('common.field') || 'Field', field: 'key', align: 'left' },
  { name: 'label', label: t('common.label') || 'Label', field: 'label', align: 'left' },
  { name: 'type', label: t('common.type') || 'Type', field: 'type', align: 'center' },
  { name: 'visible', label: t('common.visible') || 'Visible', field: 'visible', align: 'center' }
]

// 🧠 Watcher: auto-generate config from JSON keys
watch(jsonData, (newData) => {
  if (newData.length) {
    const firstRow = newData[0]
    tableConfig.value = Object.keys(firstRow).map((key) => ({
      key,
      label: t(`user.${key}`) || key,
      type: typeof firstRow[key],
      visible: true
    }))
  }
})

// 📥 Paste JSON
function openPasteDialog() {
  showPasteDialog.value = true
}

function loadJsonData() {
  try {
    const parsed = JSON.parse(jsonInput.value)
    // const parsed = JSON.parse(jsonInput.value)
    if (Array.isArray(parsed)) {
      jsonData.value = parsed
      $q.notify({ message: '✅ JSON Loaded Successfully', color: 'green' })
    } else {
      throw new Error('Invalid format')
    }
  } catch (err) {
    $q.notify({ message: '❌ Invalid JSON', color: 'red' })
  }
  showPasteDialog.value = false
}
function loadtextData() {
  try {
    const parsed = jsonInput.value
    // const parsed = JSON.parse(jsonInput.value)
    if (Array.isArray(parsed)) {
      jsonData.value = parsed
      $q.notify({ message: '✅ JSON Loaded Successfully', color: 'green' })
    } else {
      throw new Error('Invalid format')
    }
  } catch (err) {
    $q.notify({ message: '❌ Invalid JSON', color: 'red' })
  }
  showPasteDialog.value = false
}
// 📤 Copy configuration as JSON
function copyConfig() {
  const config = JSON.stringify(tableConfig.value, null, 2)
  navigator.clipboard.writeText(config)
  $q.notify({ message: '📋 Configuration copied', color: 'primary' })
}
</script>

<style scoped>
.q-table {
  border-radius: 12px;
  overflow: hidden;
}
</style>
