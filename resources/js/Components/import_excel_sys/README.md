# Excel Import/Export System

A reusable Vue 3 component system for importing and exporting Excel files with preview, column/row selection, and JSON conversion.

## Components

### 1. ExcelImporter.vue
Handles uploading Excel files, previewing data, and exporting as JSON.

**Features:**
- Upload .xlsx and .xls files
- Preview data in a table
- Select specific rows and columns
- Export selected data as JSON
- Copy JSON to clipboard

**Usage:**
```vue
<template>
  <ExcelImporter @imported-json="handleImportedJson" />
</template>

<script setup>
import ExcelImporter from '@/Components/import_excel_sys/ExcelImporter.vue'

const handleImportedJson = (data) => {
  console.log('Imported data:', data)
  // Send to backend or process locally
}
</script>
```

### 2. ExcelExporter.vue
Generates and downloads Excel templates from JSON data.

**Features:**
- Generate Excel files from JSON data
- Customize file name
- Preview template data
- Auto-set column widths

**Usage:**
```vue
<template>
  <ExcelExporter
    :json-data="sampleData"
    default-file-name="curriculum_template.xlsx"
    @exported="handleExported"
  />
</template>

<script setup>
import ExcelExporter from '@/Components/import_excel_sys/ExcelExporter.vue'

const sampleData = [
  { id: 1, name: 'Topic 1', description: 'Description 1' },
  { id: 2, name: 'Topic 2', description: 'Description 2' }
]

const handleExported = (info) => {
  console.log('Exported:', info)
}
</script>
```

### 3. ExcelManager.vue
Combined component with tabs for both import and export functionality.

**Features:**
- Tabbed interface for import/export
- Single component for both operations
- Cleaner UI

**Usage:**
```vue
<template>
  <ExcelManager
    :export-data="tableData"
    export-file-name="my_data.xlsx"
    @imported-json="handleImportedJson"
    @exported="handleExported"
  />
</template>

<script setup>
import ExcelManager from '@/Components/import_excel_sys/ExcelManager.vue'

const tableData = [
  { id: 1, name: 'Item 1' },
  { id: 2, name: 'Item 2' }
]

const handleImportedJson = (data) => {
  console.log('Imported:', data)
}

const handleExported = (info) => {
  console.log('Exported:', info)
}
</script>
```

## Installation

Make sure you have the required dependencies installed:

```bash
npm install xlsx file-saver
```

## Props

### ExcelImporter
- `title` (String, default: 'Import Excel Data') - Component title

### ExcelExporter
- `jsonData` (Array, default: []) - Data to export
- `defaultFileName` (String, default: 'template.xlsx') - Default file name

### ExcelManager
- `exportData` (Array, default: []) - Data to export
- `exportFileName` (String, default: 'export.xlsx') - Export file name

## Events

### ExcelImporter
- `imported-json` - Emitted when data is imported with selected rows/columns

### ExcelExporter
- `exported` - Emitted when file is exported with info object

### ExcelManager
- `imported-json` - Forwarded from ExcelImporter
- `exported` - Forwarded from ExcelExporter

## Example: Curriculum Import/Export

```vue
<template>
  <div class="q-pa-md">
    <ExcelManager
      :export-data="curriculumData"
      export-file-name="curriculum_template.xlsx"
      @imported-json="importCurricula"
      @exported="onExported"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useQuasar } from 'quasar'
import ExcelManager from '@/Components/import_excel_sys/ExcelManager.vue'
import axios from 'axios'

const $q = useQuasar()

const curriculumData = ref([
  { name: 'Math 101', subject: 'Mathematics', grade: '10' },
  { name: 'English 101', subject: 'English', grade: '10' }
])

const importCurricula = async (data) => {
  try {
    const response = await axios.post('/api/curriculum/bulk-import', { data })
    $q.notify({
      message: `Successfully imported ${data.length} curricula`,
      color: 'positive'
    })
  } catch (error) {
    $q.notify({
      message: 'Import failed: ' + error.message,
      color: 'negative'
    })
  }
}

const onExported = (info) => {
  console.log('Exported:', info)
}
</script>
```

## Notes

- The components use Quasar for UI elements
- XLSX library handles Excel parsing and generation
- All operations are client-side (no server required for basic import/export)
- For bulk operations, send the imported JSON to your backend API
- Column names are automatically formatted (underscores replaced with spaces, capitalized)

## Browser Support

Works in all modern browsers that support:
- FileReader API
- Blob API
- ES6+ JavaScript
