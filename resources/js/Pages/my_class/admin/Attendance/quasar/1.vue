<template>
  <div class="q-pa-md">
    <!-- Controls section -->
    <div class="row q-col-gutter-md q-mb-md items-center">
      <div class="col-12 col-sm-3">
        <q-select
          v-model="tableStyle.fontSize"
          :options="fontSizeOptions"
          label="Font Size"
          dense
          outlined
          class="q-mr-sm"
          @update:model-value="updateFontSize"
        />
      </div>
      <div class="col-12 col-sm-3">
        <q-select
          v-model="tableStyle.density"
          :options="densityOptions"
          label="Row Height"
          dense
          outlined
          @update:model-value="updateDensity"
        />
      </div>
      <div class="col-12 col-sm-3">
        <q-toggle
          v-model="isEditMode"
          label="Edit Mode"
          color="primary"
          class="q-ml-sm"
        />
      </div>
      <div class="col-12 col-sm-3">
        <q-input
          v-model="filter"
          dense
          outlined
          placeholder="Search"
          @update:model-value="updateFilter"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </div>
    </div>

    <!-- Table for editing array of objects -->
    <q-table
      :rows="filteredRows"
      :columns="localColumns"
      row-key="id"
      :filter="filter"
      :pagination="pagination"
      class="my-custom-table"
      :style="{
        '--table-font-size': tableStyle.fontSize
      }"
      :dense="tableStyle.density === 'compact'"
      flat
      bordered
      selection="multiple"
      v-model:selected="selected"
      >

      <!-- :selected-rows-label="getSelectedString" -->
      <!-- Custom header slot -->
      <template v-slot:top>
        <div class="row full-width q-pb-md items-center">
          <div class="text-h6">{{ title }}</div>
          <q-space />
          <q-btn-group flat v-if="isEditMode">
            <q-btn
              color="primary"
              icon="add"
              label="Add New"
              @click="showAddDialog = true"
            />
            <q-btn
              color="negative"
              icon="delete"
              label="Delete Selected"
              @click="deleteSelected"
              :disable="!selected.length"
            />
            <q-btn
              color="warning"
              icon="refresh"
              label="Reset"
              @click="resetData"
              flat
            />
          </q-btn-group>
        </div>
      </template>

      <!-- Custom cell slot for editing -->
      <template v-slot:body-cell="props">
        <q-td @dblclick="isEditMode=true" :props="props" :class="{ 'cursor-pointer': !isEditMode }">
          <template v-if="props.col.editable && isEditMode">
            <q-input
              v-model="props.row[props.col.field]"
              dense
              outlined
              :class="tableStyle.density === 'comfortable' ? 'q-py-sm' : ''"
              @update:model-value="onCellUpdate(props.row)"
              @blur=" isEditMode=false "
            />
          </template>
          <template v-else>
            <div class="cell-content">{{ props.row[props.col.field] }}</div>
          </template>
        </q-td>
      </template>
    </q-table>

    <!-- Add new item dialog -->
    <q-dialog v-model="showAddDialog" persistent>
      <q-card style="min-width: 400px; max-width: 600px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Add New Item</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>

        <q-card-section class="q-pt-md">
          <div class="row q-col-gutter-md">
            <div
              v-for="col in localColumns.filter(col => col.field !== 'id')"
              :key="col.field"
              class="col-12"
            >
              <q-input
                v-model="newItem[col.field]"
                :label="col.label"
                :hint="col.hint"
                outlined
                dense
                :rules="[
                  val => !!val || `${col.label} is required`,
                  val => val.trim().length > 0 || `${col.label} cannot be empty`,
                  ...(col.rules || [])
                ]"
                :type="col.type || 'text'"
                :mask="col.mask"
                :fill-mask="col.fillMask"
                :reverse-fill-mask="col.reverseFillMask"
                ref="inputRefs"
                @keyup.enter="validateAndAdd"
                @input="validateField(col.field)"
                @blur="validateField(col.field)"
                :error="!!fieldErrors[col.field]"
                :error-message="fieldErrors[col.field]"
                hide-bottom-space
              >
                <template v-if="col.prefix" v-slot:prepend>
                  <div class="text-grey-7">{{ col.prefix }}</div>
                </template>
                <template v-if="col.suffix" v-slot:append>
                  <div class="text-grey-7">{{ col.suffix }}</div>
                </template>
              </q-input>
            </div>
          </div>
        </q-card-section>

        <q-card-actions align="right" class="text-primary q-pa-md">
          <q-btn
            flat
            label="Cancel"
            color="grey-7"
            @click="handleCancel"
            :disable="submitting"
          />
          <q-btn
            :loading="submitting"
            label="Add"
            color="primary"
            @click="validateAndAdd"
            :disable="!isFormValid"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()

const props = defineProps({
  rows: {
    type: Array,
    required: true,
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  title: {
    type: String,
    default: 'Array Editor'
  }
})

const emit = defineEmits(['update:rows'])

// Style options
const fontSizeOptions = [
  { label: 'Small', value: '12px' },
  { label: 'Medium', value: '14px' },
  { label: 'Large', value: '16px' },
  { label: 'Extra Large', value: '18px' }
]

const densityOptions = [
  { label: 'Compact', value: 'compact' },
  { label: 'Comfortable', value: 'comfortable' }
]

const tableStyle = ref({
  fontSize: '14px',
  density: 'comfortable'
})

// Filter state
const filter = ref('')

// Computed property for filtered rows
const filteredRows = computed(() => {
  if (!filter.value) return localRows.value

  const searchTerm = filter.value.toLowerCase()
  return localRows.value.filter(row => {
    return Object.values(row).some(value =>
      String(value).toLowerCase().includes(searchTerm)
    )
  })
})

// Update functions
const updateFontSize = (newSize) => {
  tableStyle.value.fontSize = newSize
}

const updateDensity = (newDensity) => {
  tableStyle.value.density = newDensity
}

const updateFilter = (newFilter) => {
  filter.value = newFilter
}

// Pagination
const pagination = ref({
  sortBy: 'name',
  descending: false,
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 10
})

// Local state for rows and columns
const localRows = ref([...props.rows])
const localColumns = ref([...props.columns])
const selected = ref([])
const isEditMode = ref(false)

// Initialize new item object based on columns
const newItem = ref({})
const initNewItem = () => {
  newItem.value = localColumns.value.reduce((acc, col) => {
    if (col.field !== 'id') {
      // Set default values based on column type
      acc[col.field] = col.default || ''
    }
    return acc
  }, {})
}
initNewItem()

// Computed property to check if new item is valid
const isValidNewItem = computed(() => {
  return Object.values(newItem.value).every(value => value !== '')
})

// Watch for external changes
watch(() => props.rows, (newVal) => {
  localRows.value = [...newVal]
}, { deep: true })

// Methods
const addItem = () => {
  if (!isValidNewItem.value) return

  const newRow = {
    id: Date.now().toString(),
    ...newItem.value
  }

  localRows.value.push(newRow)
  emit('update:rows', localRows.value)
  initNewItem()
  showAddDialog.value = false
}

const deleteSelected = () => {
  const selectedIds = selected.value.map(row => row.id)
  localRows.value = localRows.value.filter(row => !selectedIds.includes(row.id))
  selected.value = []
  emit('update:rows', localRows.value)
}

const onCellUpdate = (row) => {
  const index = localRows.value.findIndex(r => r.id === row.id)
  if (index !== -1) {
    localRows.value[index] = { ...row }
    emit('update:rows', localRows.value)
    // isEditMode.value=false
  }
}

const resetData = () => {
  localRows.value = [...props.rows]
  selected.value = []
  filter.value = ''
  emit('update:rows', localRows.value)
}

// Add these to your existing script setup
const showAddDialog = ref(false)
const submitting = ref(false)
const inputRefs = ref([])
const fieldErrors = ref({})
const touchedFields = ref({})
const isFormValid = ref(false)

// Improved validation and add function
const validateAndAdd = async () => {
  try {
    const validations = await Promise.all(
      localColumns.value
        .filter(col => col.field !== 'id')
        .map(col => validateField(col.field))
    )

    if (validations.includes(false)) {
      $q.notify({
        progress: true,
        // color: 'primary',
        type: 'negative',
        actions: [{ icon: 'close', color: 'white' }],
        message: 'Please fill in all required fields correctly',
        position: 'top-right'
      })
      return
    }

    submitting.value = true

    const newRow = {
      id: Date.now().toString(),
      ...newItem.value
    }

    localRows.value.push(newRow)
    emit('update:rows', localRows.value)

    $q.notify({
      type: 'positive',
      message: 'Item added successfully',
      position: 'top-right'
    })
    handleCancel()
  } catch (error) {
    console.error('Error adding item:', error)
    $q.notify({
      type: 'negative',
      message: 'Failed to add item',
      position: 'top-right'
    })
  } finally {
    submitting.value = false
  }
}

// Improved cancel handler
const handleCancel = () => {
  showAddDialog.value = false
  initNewItem()
  fieldErrors.value = {}
  touchedFields.value = {}
  isFormValid.value = false
}

// Add this validation function
const validateField = (field) => {
  try {
    touchedFields.value[field] = true
    const value = newItem.value[field]

    // Clear previous error
    fieldErrors.value[field] = null

    // Basic validation
    if (!value || value.trim().length === 0) {
      fieldErrors.value[field] = `${getFieldLabel(field)} is required`
      return false
    }

    // Additional validation based on field type
    const column = localColumns.value.find(col => col.field === field)
    if (column && value) {
      switch (column.type) {
        case 'email':
          if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            fieldErrors.value[field] = 'Please enter a valid email'
            return false
          }
          break
        case 'number':
          if (isNaN(value)) {
            fieldErrors.value[field] = 'Please enter a valid number'
            return false
          }
          break
      }
    }

    return true
  } catch (error) {
    console.error('Validation error:', error)
    return false
  }
}

// Helper function to get field label
const getFieldLabel = (field) => {
  const column = localColumns.value.find(col => col.field === field)
  return column ? column.label : field
}

// Update overall form validity
const updateFormValidity = () => {
  try {
    const requiredFields = localColumns.value
      .filter(col => col.field !== 'id')
      .map(col => col.field)

    isFormValid.value = requiredFields.every(field => {
      const value = newItem.value[field]
      return value && value.trim().length > 0 && !fieldErrors.value[field]
    })
  } catch (error) {
    console.error('Form validity update error:', error)
    isFormValid.value = false
  }
}

// Add this to handle column definitions better
const enhanceColumns = (columns) => {
  return columns.map(col => ({
    ...col,
    required: col.required !== false, // Default to true unless explicitly set to false
    rules: [
      ...(col.rules || []),
      // Add any default rules based on column type
      ...(col.type === 'email' ? [
        val => !val || /^[^@]+@[^@]+\.[^@]+$/.test(val) || 'Please enter a valid email'
      ] : []),
      ...(col.type === 'number' ? [
        val => !val || !isNaN(val) || 'Please enter a valid number'
      ] : [])
    ]
  }))
}

// Update your columns when component mounts
onMounted(() => {
  localColumns.value = enhanceColumns(props.columns)
})

// Watch for changes in newItem
watch(newItem, () => {
  try {
    updateFormValidity()
  } catch (error) {
    console.error('Watch error:', error)
  }
}, { deep: true })
</script>

<style scoped>
.my-custom-table {
  background: white;
  border-radius: 8px;
}

/* Target all cells and headers in the table */
.my-custom-table :deep(.q-table th),
.my-custom-table :deep(.q-table td) {
  font-size: var(--table-font-size);
}

/* Target the header specifically if needed */
.my-custom-table :deep(.q-table thead tr) {
  font-size: var(--table-font-size);
}

/* Target the body specifically if needed */
.my-custom-table :deep(.q-table tbody td) {
  font-size: var(--table-font-size);
}
</style>







