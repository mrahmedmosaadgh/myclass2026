<template>
    <div class="q-pa-md">
      <!-- Table for displaying and editing objects -->
      <q-table
        :rows="rows"
        :columns="columns"
        row-key="id"
        :filter="filter"
        :pagination="pagination"
        @request="onRequest"  
      >
        <template v-slot:body-cell="props">
          <q-td :props="props">
            <!-- Editable cells -->
            <template v-if="props.col.name === 'label'">
              <q-input
                v-model="props.row.label"
                dense
                autofocus
                debounce="300"
                @blur="onLabelBlur(props.row)"
              />
            </template>
            <template v-else>
              {{ props.row[props.col.field] }}
            </template>
          </q-td>
        </template>
      </q-table>

      <!-- Add new object form -->
      <div class="q-mt-md">
        <q-input
          v-model="newItem.label"
          label="New Item"
          dense
          @keyup.enter="addItem"
        />
        <q-btn label="Add Item" color="primary" @click="addItem" />
      </div>

      <!-- Delete selected items -->
      <div class="q-mt-md">
        <q-btn
          label="Remove Selected"
          color="negative"
          @click="removeSelectedItems"
        />
      </div>
    </div>
  </template>

  <script setup>
  import { ref } from 'vue'

  // Props for the component
  const props = defineProps({
    rows: {
      type: Array,
      required: true,
      default: () => [],
    },
    columns: {
      type: Array,
      required: true,
      default: () => [],
    }
  })

  // Emits to the parent
  const emit = defineEmits(['update:rows'])

  const newItem = ref({ label: '' })
  const filter = ref('')
  const pagination = ref({ page: 1, rowsPerPage: 5 })

  // Add new item to the array
  function addItem() {
    if (newItem.value.label.trim() !== '') {
      const newNode = { id: Date.now().toString(), label: newItem.value.label }
      const updatedRows = [...props.rows, newNode]
      emit('update:rows', updatedRows)
      newItem.value.label = ''
    }
  }

  // Remove selected items (implement selection logic)
  function removeSelectedItems() {
    const selectedIds = props.rows.filter(row => row.selected).map(row => row.id)
    const updatedRows = props.rows.filter(row => !selectedIds.includes(row.id))
    emit('update:rows', updatedRows)
  }

  // Handle pagination/filtering
  function onRequest({ pagination, filter }) {
    // Update pagination and filter in the parent if needed
    // You can emit an event to handle pagination/filtering if you want
    console.log('Pagination:', pagination, 'Filter:', filter)
  }

  // Handle blur event (optional logic after editing)
  function onLabelBlur(row) {
    console.log(`Updated label for item with id ${row.id}: ${row.label}`)
  }
  </script>
