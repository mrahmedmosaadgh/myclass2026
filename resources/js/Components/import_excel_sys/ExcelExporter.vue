<template>
  <q-card class="bg-white shadow-md rounded-lg">
    <q-card-section>
      <div class="row items-center q-gutter-md">
        <div class="col">
          <q-input
            v-model="fileName"
            label="Template File Name"
            outlined
            dense
            placeholder="e.g., curriculum_template.xlsx"
            @keyup.enter="exportToExcel"
          />
        </div>
        <div class="col-auto">
          <q-btn
            label="Download Excel Template"
            color="primary"
            icon="download"
            unelevated
            :disable="!jsonData.length"
            @click="exportToExcel"
          />
        </div>
      </div>
    </q-card-section>

    <q-card-section v-if="jsonData.length">
      <div class="q-mb-md">
        <h6 class="q-my-none">Template Preview</h6>
        <p class="text-caption text-grey-6">{{ jsonData.length }} sample records</p>
      </div>

      <q-table
        :rows="jsonData"
        :columns="dynamicColumns"
        row-key="__id"
        :pagination="{ rowsPerPage: 5 }"
        dense
        class="text-sm"
      />
    </q-card-section>

    <q-card-section v-else class="text-center text-grey-6 q-py-lg">
      <q-icon name="info" size="lg" class="q-mb-md" />
      <p>No data provided. Pass JSON data as a prop to generate a template.</p>
    </q-card-section>
  </q-card>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useQuasar } from 'quasar'
import * as XLSX from 'xlsx'

const $q = useQuasar()

const props = defineProps({
  jsonData: {
    type: Array,
    default: () => []
  },
  defaultFileName: {
    type: String,
    default: 'template.xlsx'
  }
})

const emit = defineEmits(['exported'])

// State
const fileName = ref(props.defaultFileName)

// Computed
const dynamicColumns = computed(() => {
  if (!props.jsonData.length) return []
  
  // Add __id for row-key
  const dataWithId = props.jsonData.map((item, index) => ({
    ...item,
    __id: index
  }))

  return Object.keys(dataWithId[0])
    .filter(key => key !== '__id')
    .map((key) => ({
      name: key,
      label: key.charAt(0).toUpperCase() + key.slice(1).replace(/_/g, ' '),
      field: key,
      align: 'left',
    }))
})

// Watchers
watch(() => props.defaultFileName, (newVal) => {
  fileName.value = newVal
})

// Methods
const exportToExcel = () => {
  if (!props.jsonData.length) {
    $q.notify({
      message: 'No data to export',
      color: 'warning',
      position: 'top'
    })
    return
  }

  try {
    // Create worksheet from JSON data
    const worksheet = XLSX.utils.json_to_sheet(props.jsonData)
    
    // Set column widths
    const colWidths = Object.keys(props.jsonData[0]).map(() => 20)
    worksheet['!cols'] = colWidths.map(width => ({ wch: width }))

    // Create workbook and add worksheet
    const workbook = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(workbook, worksheet, 'Template')

    // Generate Excel file
    XLSX.writeFile(workbook, fileName.value)

    $q.notify({
      message: 'Template downloaded successfully!',
      color: 'positive',
      position: 'top'
    })

    emit('exported', {
      fileName: fileName.value,
      recordCount: props.jsonData.length
    })
  } catch (error) {
    $q.notify({
      message: 'Error exporting to Excel: ' + error.message,
      color: 'negative',
      position: 'top'
    })
  }
}
</script>

<style scoped>
.q-card {
  max-width: 100%;
}
</style>
