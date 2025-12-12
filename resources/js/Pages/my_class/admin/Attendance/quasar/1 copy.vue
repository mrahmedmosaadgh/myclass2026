<template>
  <div class="q-pa-md">
    <!-- Table for editing array of objects -->
    <q-table
      :rows="localRows"
      :columns="localColumns"
      row-key="id"
      :filter="filter"
      :pagination.sync="pagination"
      selection="multiple"
      v-model:selected="selected"
    >
      <!-- Custom header slot -->
      <template v-slot:top>
        <div class="q-table__title">{{ title }}</div>
        <q-space />
        <q-input
          v-model="filter"
          dense
          placeholder="Search"
          class="q-ml-md"
        >
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
      </template>

      <!-- Custom cell slot for editing -->
      <template v-slot:body-cell="props">
        <q-td :props="props">
          <template v-if="props.col.editable">
            <q-input
              v-model="props.row[props.col.field]"
              dense
              autofocus
              @update:model-value="onCellUpdate(props.row)"
            />
          </template>
          <template v-else>
            {{ props.row[props.col.field] }}
          </template>
        </q-td>
      </template>
    </q-table>

    <!-- Add new item form -->
    <div class="q-mt-md row q-col-gutter-sm items-center">
      <div 
        v-for="col in localColumns.filter(col => col.field !== 'id')" 
        :key="col.field"
        class="col-12 col-sm-3"
      >
        <q-input
          v-model="newItem[col.field]"
          :label="col.label"
          dense
          @keyup.enter="addItem"
        />
      </div>
      <div class="col-12 col-sm-auto">
        <q-btn
          label="Add"
          color="primary"
          @click="addItem"
          :disable="!isValidNewItem"
        />
      </div>
    </div>

    <!-- Action buttons -->
    <div class="q-mt-md">
      <q-btn
        label="Delete Selected"
        color="negative"
        @click="deleteSelected"
        :disable="!selected.length"
        class="q-mr-sm"
      />
      <q-btn
        label="Reset"
        color="warning"
        @click="resetData"
        flat
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

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

// Local state
const localRows = ref([...props.rows])
const localColumns = ref([...props.columns])
const selected = ref([])
const filter = ref('')
const pagination = ref({
  rowsPerPage: 10,
  page: 1
})

// Initialize new item object based on columns
const newItem = ref({})
const initNewItem = () => {
  newItem.value = localColumns.value.reduce((acc, col) => {
    if (col.field !== 'id') {
      acc[col.field] = ''
    }
    return acc
  }, {})
}
initNewItem()

// Computed property to check if new item is valid
const isValidNewItem = computed(() => {
  return Object.values(newItem.value).some(value => value !== '')
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
  }
}

const resetData = () => {
  localRows.value = [...props.rows]
  selected.value = []
  filter.value = ''
  emit('update:rows', localRows.value)
}
</script>