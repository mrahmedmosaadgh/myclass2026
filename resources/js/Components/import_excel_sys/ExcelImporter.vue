<template>
  <q-card class="bg-white shadow-md rounded-lg">
    <q-card-section>
      <q-file
        v-model="file"
        label="Upload Excel File"
        accept=".xlsx, .xls"
        outlined
        dense
        @update:model-value="handleFileUpload"
      >
        <template v-slot:prepend>
          <q-icon name="attach_file" />
        </template>
      </q-file>
    </q-card-section>

    <q-card-section v-if="previewData.length">
      <div class="q-mb-md">
        <h6 class="q-my-none">Preview Data</h6>
        <p class="text-caption text-grey-6">Select rows and columns to import</p>
      </div>

      <q-table
        :rows="previewData"
        :columns="dynamicColumns"
        row-key="__id"
        selection="multiple"
        v-model:selected="selectedRows"
        :pagination="{ rowsPerPage: 10 }"
        dense
        class="text-sm"
      >
        <template v-slot:header-selection>
          <q-checkbox v-model="selectAllRows" color="primary" />
        </template>
        <template v-slot:header="props">
          <q-tr :props="props">
            <q-th auto-width />
            <q-th v-for="col in props.cols" :key="col.name" :props="props">
              <q-checkbox
                v-model="selectedColumns[col.name]"
                color="primary"
                dense
                :label="col.label"
              />
            </q-th>
          </q-tr>
        </template>
      </q-table>
    </q-card-section>

    <q-card-actions align="right" v-if="previewData.length">
      <q-btn
        label="Cancel"
        flat
        @click="resetImporter"
      />
      <q-btn
        label="Import Selected as JSON"
        color="primary"
        unelevated
        :disable="selectedRows.length === 0"
        @click="importAsJson"
      />
    </q-card-actions>

    <q-dialog v-model="jsonPreviewDialog">
      <q-card style="min-width: 600px">
        <q-card-section>
          <div class="text-h6">Imported JSON Preview</div>
        </q-card-section>

        <q-separator />

        <q-card-section class="bg-grey-1" style="max-height: 400px; overflow-y: auto">
          <pre class="q-ma-none text-caption">{{ importedJson }}</pre>
        </q-card-section>

        <q-separator />

        <q-card-actions align="right">
          <q-btn label="Close" flat v-close-popup />
          <q-btn label="Copy JSON" color="primary" @click="copyJson" unelevated />
          <q-btn label="Confirm Import" color="positive" @click="confirmImport" unelevated />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import * as XLSX from 'xlsx'

const $q = useQuasar()

const props = defineProps({
  title: {
    type: String,
    default: 'Import Excel Data'
  }
})

const emit = defineEmits(['imported-json'])

// State
const file = ref(null)
const previewData = ref([])
const selectedRows = ref([])
const selectedColumns = ref({})
const selectAllRows = ref(false)
const importedJson = ref('')
const jsonPreviewDialog = ref(false)

// Computed
const dynamicColumns = computed(() => {
  if (!previewData.value.length) return []
  return Object.keys(previewData.value[0])
    .filter(key => key !== '__id')
    .map((key) => ({
      name: key,
      label: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' '),
      field: key,
      align: 'left',
      sortable: true,
    }))
})

// Watchers
watch(selectAllRows, (val) => {
  selectedRows.value = val ? [...previewData.value] : []
})

// Methods
const handleFileUpload = () => {
  if (!file.value) return

  const reader = new FileReader()
  reader.onload = (e) => {
    try {
      const data = new Uint8Array(e.target.result)
      const workbook = XLSX.read(data, { type: 'array' })
      const sheetName = workbook.SheetNames[0]
      const worksheet = workbook.Sheets[sheetName]
      
      // Convert to JSON with headers from first row
      const jsonData = XLSX.utils.sheet_to_json(worksheet)
      
      // Add unique ID for row-key
      previewData.value = jsonData.map((row, index) => ({
        ...row,
        __id: index
      }))

      // Initialize selectedColumns with all true by default
      dynamicColumns.value.forEach((col) => {
        selectedColumns.value[col.name] = true
      })

      $q.notify({
        message: `Loaded ${previewData.value.length} rows from Excel`,
        color: 'positive',
        position: 'top'
      })
    } catch (error) {
      $q.notify({
        message: 'Error reading Excel file: ' + error.message,
        color: 'negative',
        position: 'top'
      })
      resetImporter()
    }
  }
  reader.readAsArrayBuffer(file.value)
}

const importAsJson = () => {
  const filteredData = selectedRows.value.map((row) => {
    const filteredRow = {}
    Object.keys(selectedColumns.value).forEach((col) => {
      if (selectedColumns.value[col] && col !== '__id') {
        filteredRow[col] = row[col]
      }
    })
    return filteredRow
  })

  importedJson.value = JSON.stringify(filteredData, null, 2)
  jsonPreviewDialog.value = true
}

const copyJson = async () => {
  try {
    await navigator.clipboard.writeText(importedJson.value)
    $q.notify({
      message: 'JSON copied to clipboard!',
      color: 'positive',
      position: 'top'
    })
  } catch (error) {
    $q.notify({
      message: 'Failed to copy JSON',
      color: 'negative',
      position: 'top'
    })
  }
}

const confirmImport = () => {
  const filteredData = selectedRows.value.map((row) => {
    const filteredRow = {}
    Object.keys(selectedColumns.value).forEach((col) => {
      if (selectedColumns.value[col] && col !== '__id') {
        filteredRow[col] = row[col]
      }
    })
    return filteredRow
  })

  emit('imported-json', filteredData)
  jsonPreviewDialog.value = false
  
  $q.notify({
    message: `Successfully imported ${filteredData.length} records`,
    color: 'positive',
    position: 'top'
  })

  resetImporter()
}

const resetImporter = () => {
  file.value = null
  previewData.value = []
  selectedRows.value = []
  selectedColumns.value = {}
  selectAllRows.value = false
  importedJson.value = ''
}
</script>

<style scoped>
pre {
  font-family: 'Courier New', monospace;
  white-space: pre-wrap;
  word-wrap: break-word;
}
</style>
