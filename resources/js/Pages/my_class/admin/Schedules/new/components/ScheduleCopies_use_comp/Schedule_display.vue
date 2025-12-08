<template>
    <div v-if="school_id && scheduleCopyId" class="mt-4">
      <q-table
        title="Schedule Entries"
        :rows="filteredData"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination="pagination"
        :rows-per-page-options="[10, 20, 50]"
        flat
        bordered
        dense
        @request="onRequest"
      >
        <!-- Top section with search and filters -->
        <template v-slot:top>
          <div class="row full-width q-col-gutter-md">
            <div class="col-12 col-md-6">
              <q-input
                v-model="searchQuery"
                dense
                outlined
                placeholder="Search..."
                class="q-mb-md"
                @update:model-value="onSearch"
              >
                <template v-slot:append>
                  <q-icon name="search" />
                </template>
              </q-input>
            </div>
            <div class="col-12 col-md-6 flex justify-end items-center">
              <q-btn
                color="primary"
                icon="refresh"
                @click="fetchScheduleData"
                :loading="loading"
                label="Refresh"
                class="q-ml-md"
              />
            </div>
          </div>
        </template>

        <!-- Custom cell renderers -->
        <template v-slot:body-cell-classroom="props">
          <q-td :props="props">
            {{ props.row.cst?.classroom?.name || '-' }}
          </q-td>
        </template>

        <template v-slot:body-cell-subject="props">
          <q-td :props="props">
            {{ props.row.cst?.subject?.name || '-' }}
          </q-td>
        </template>

        <template v-slot:body-cell-teacher="props">
          <q-td :props="props">
            {{ props.row.cst?.teacher?.name || '-' }}
          </q-td>
        </template>

        <template v-slot:body-cell-period_number="props">
          <q-td :props="props" class="editable-cell">
            <template v-if="editingId === props.row.id && editingField === 'period_number'">
              <q-select
                v-model="tempValue"
                :options="periodOptions"
                option-value="value"
                option-label="label"
                emit-value
                map-options
                dense
                outlined
                class="edit-select"
                @update:model-value="showConfirmDialog(props.row)"
                :rules="[val => !!val || 'Period is required']"
              />
              <div class="edit-actions q-mt-sm">
                <q-btn
                  flat
                  dense
                  color="positive"
                  icon="check"
                  @click="saveEdit(props.row)"
                />
                <q-btn
                  flat
                  dense
                  color="negative"
                  icon="close"
                  @click="cancelEdit"
                />
              </div>
            </template>
            <template v-else>
              <div class="editable-content" @click="startEditing(props.row.id, 'period_number', props.row.period_number)">
                {{ props.row.period_number }}
              </div>
            </template>
          </q-td>
        </template>

        <template v-slot:body-cell-day="props">
          <q-td :props="props" class="editable-cell">
            <template v-if="editingId === props.row.id && editingField === 'day'">
              <q-select
                v-model="tempValue"
                :options="dayOptions"
                option-value="value"
                option-label="label"
                emit-value
                map-options
                dense
                outlined
                class="edit-select"
                @update:model-value="showConfirmDialog(props.row,'day')"
                :rules="[val => !!val || 'Day is required']"
              />
              <div class="edit-actions q-mt-sm">
                <q-btn
                  flat
                  dense
                  color="positive"
                  icon="check"
                  @click="saveEdit(props.row)"
                />
                <q-btn
                  flat
                  dense
                  color="negative"
                  icon="close"
                  @click="cancelEdit"
                />
              </div>
            </template>
            <template v-else>
              <div class="editable-content" @click="startEditing(props.row.id, 'day', props.row.day)">
                {{ getDayName(props.row.day) }}
              </div>
            </template>
          </q-td>
        </template>

        <template v-slot:body-cell-active="props">
          <q-td :props="props">
            <q-icon
              :name="props.row.active ? 'check_circle' : 'cancel'"
              :color="props.row.active ? 'positive' : 'negative'"
            />
          </q-td>
        </template>

        <template v-slot:no-data>
          <div class="full-width row flex-center q-gutter-sm">
            <q-icon size="2em" name="sentiment_dissatisfied" />
            <span>No matching records found</span>
          </div>
        </template>
      </q-table>

      <q-dialog v-model="errorDialog">
        <q-card>
          <q-card-section>
            <div class="text-h6">Error</div>
            <div class="text-negative">{{ errorMessage }}</div>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="OK" color="primary" v-close-popup />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <q-dialog v-model="confirmDialog">
        <q-card>
          <q-card-section>
            <div class="text-h6">Confirm Changes</div>
            <div class="text-body1">Are you sure you want to save the changes?</div>
          </q-card-section>
          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="negative" v-close-popup />
            <q-btn flat label="Save" color="positive" @click="saveEdit(itemToSave)" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  </template>

  <script setup>
  import { ref, watch, computed } from 'vue'
  import axios from 'axios'
  import { useQuasar } from 'quasar'
  const $q = useQuasar()
  const props = defineProps({
    schoolId: {
      type: Number,
      required: true
    },
    scheduleCopyId: {
      type: Number,
      required: true
    }
  })

  const columns = [
    { name: 'id', label: 'ID', field: 'id', align: 'left', sortable: true },
    { name: 'classroom', label: 'Classroom', field: row => row.cst?.classroom?.name, align: 'left', sortable: true },
    { name: 'subject', label: 'Subject', field: row => row.cst?.subject?.name, align: 'left', sortable: true },
    { name: 'teacher', label: 'Teacher', field: row => row.cst?.teacher?.name, align: 'left', sortable: true },
    { name: 'period_number', label: 'Period', field: 'period_number', align: 'center', sortable: true },
    { name: 'day', label: 'Day', field: 'day', align: 'center', sortable: true },
    { name: 'active', label: 'Active', field: 'active', align: 'center', sortable: true }
  ]

  const pagination = ref({
    sortBy: 'period_number',
    descending: false,
    page: 1,
    rowsPerPage: 10
  })

  const school_id = ref(props.schoolId)
  const scheduleData = ref([])
  const loading = ref(false)
  const errorDialog = ref(false)
  const errorMessage = ref('')
  const searchQuery = ref('')

  // Editing functionality
  const editingId = ref(null)
  const editingField = ref('')
  const tempValue = ref('')
  const confirmDialog = ref(false)
  const itemToSave = ref(null)
  // Helper function to get day name
  const getDayName = (day) => {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
    return days[day - 1] || `Day ${day}`
  }
  const periodOptions = Array.from({length: 10}, (_, i) => ({
    label: `Period ${i+1}`,
    value: i+1
  }))

  const dayOptions = Array.from({length: 5}, (_, i) => ({
    label: getDayName(i+1),
    value: i+1
  }))

  const startEditing = (id, field, value) => {
    editingId.value = id
    editingField.value = field
    tempValue.value = value
  }

  const cancelEdit = () => {
    editingId.value = null
    editingField.value = ''
  }


  const showConfirmDialog = (item ) => {
  itemToSave.value = item

  confirmDialog.value = true
}

  const saveEdit = async (item ) => {

    try {
      loading.value = true
      await axios.post(`/admin/schedules/update/${item.id}`, {
        [editingField.value]: tempValue.value
      })
      item[editingField.value] = tempValue.value
      $q.notify({
        type: 'positive',
        message: 'Schedule updated successfully'
      })
    } catch (error) {
      $q.notify({
        type: 'negative',
        message: error.response?.data?.message || 'Failed to update schedule'
      })
      console.error('Error updating schedule:', error)
    } finally {
      editingId.value = null
      editingField.value = ''
      confirmDialog.value = false
      loading.value = false
    }
  }



  // Filtered data based on search and filters
  const filteredData = computed(() => {
    let data = [...scheduleData.value]

    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      data = data.filter(item => {
        return (
          (item.cst?.classroom?.name || '').toLowerCase().includes(query) ||
          (item.cst?.subject?.name || '').toLowerCase().includes(query) ||
          (item.cst?.teacher?.name || '').toLowerCase().includes(query) ||
          String(item.period_number).includes(query) ||
          getDayName(item.day).toLowerCase().includes(query)
        )
      })
    }

    return data
  })

  const fetchScheduleData = async () => {
    try {
      loading.value = true
      const response = await axios.get(`/admin/schedules/${props.schoolId}/${props.scheduleCopyId}`)
      scheduleData.value = response.data
    } catch (error) {
      errorMessage.value = error.response?.data?.message || 'Failed to fetch schedule data'
      errorDialog.value = true
      console.error('Error fetching schedule data:', error)
    } finally {
      loading.value = false
    }
  }

  const onSearch = () => {
    pagination.value.page = 1
  }

  const onRequest = (props) => {
    pagination.value.page = props.pagination.page
    pagination.value.rowsPerPage = props.pagination.rowsPerPage
    pagination.value.sortBy = props.pagination.sortBy
    pagination.value.descending = props.pagination.descending
  }

  watch(() => props.scheduleCopyId, fetchScheduleData, { immediate: true })
  </script>

  <style scoped>
  .editable-cell {
    position: relative;
    min-width: 120px;
  }

  .edit-select {
    min-width: 150px;
  }

  .editable-content {
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 4px;
    transition: background-color 0.3s;
  }

  .editable-content:hover {
    background-color: rgba(0, 0, 0, 0.05);
  }

  .edit-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
  }
  </style>
