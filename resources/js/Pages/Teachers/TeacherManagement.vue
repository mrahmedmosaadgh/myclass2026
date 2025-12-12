<template>
  <!-- <q-page padding> -->
    <AppLayout  title="teacher">
      <!-- hhhhhhhhhhhh -->
        <!-- <q-btn color="primary" icon="add" label="Add tree" @click="fetch_data('/api/tree')" /> -->

    <div class="q-pa-md">
      <div class="row q-mb-md">
        <q-space />
        <q-btn color="primary" icon="add" label="Add Teacher" @click="openDialog()" />
        <q-btn color="secondary" icon="download" label="Export" class="q-ml-sm" @click="exportData" />
      </div>

      <!-- Data Table -->
      <q-table
        :rows="teachers"
        :columns="columns"
        row-key="id"
        :loading="loading"
        :pagination.sync="pagination"
        @request="onRequest"
      >
        <!-- Custom Column Slots -->
        <template v-slot:body-cell-actions="props">
          <q-td :props="props">
            <q-btn-group flat>
              <q-btn flat round color="info" icon="edit" @click="openDialog(props.row)" />
              <q-btn flat round color="negative" icon="delete" @click="confirmDelete(props.row)" />
            </q-btn-group>
          </q-td>
        </template>
      </q-table>

      <!-- Add/Edit Dialog -->
      <q-dialog v-model="dialog" persistent>
        <q-card style="min-width: 350px">
          <q-card-section>
            <div class="text-h6">{{ editingTeacher ? 'Edit Teacher' : 'Add Teacher' }}</div>
          </q-card-section>

          <q-card-section>
            <q-form @submit="onSubmit" class="q-gutter-md">
              <q-input
                v-model="form.name"
                label="Name"
                :rules="[val => !!val || 'Name is required']"
              />
              <q-input
                v-model="form.name_ar"
                label="Arabic Name"
              />
              <q-input
                v-model="form.email"
                label="Email"
                type="email"
                :rules="[val => !!val || 'Email is required']"
              />
              <q-select
                v-model="form.school_id"
                :options="schoolOptions"
                label="School"
                :rules="[val => !!val || 'School is required']"
              />
              <q-input
                v-model="form.phone_number"
                label="Phone Number"
              />
              <q-select
                v-model="form.gender"
                :options="['male', 'female']"
                label="Gender"
                emit-value
                map-options
              />
              <!-- <q-input
                v-model="form.date_of_birth"
                label="Date of Birth"
                type="date"
              />
              <q-input
                v-if="!editingTeacher"
                v-model="form.password"
                label="Password"
                type="password"
                :rules="[val => !!val || 'Password is required']"
              /> -->
            </q-form>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Save" color="primary" @click="onSubmit" :loading="submitting" />
          </q-card-actions>
        </q-card>
      </q-dialog>

      <!-- Delete Confirmation -->
      <q-dialog v-model="deleteDialog">
        <q-card>
          <q-card-section class="row items-center">
            <span class="q-ml-sm">Are you sure you want to delete this teacher?</span>
          </q-card-section>

          <q-card-actions align="right">
            <q-btn flat label="Cancel" color="primary" v-close-popup />
            <q-btn flat label="Delete" color="negative" @click="deleteTeacher" :loading="deleting" />
          </q-card-actions>
        </q-card>
      </q-dialog>
    </div>
  <!-- </q-page> -->
</AppLayout>

</template>

<script setup>
import { ref, onMounted, computed, defineProps } from 'vue'
// import axios from 'axios'

import { useQuasar } from 'quasar'
const $q = useQuasar()
import AppLayout from '@/Layouts/AppLayout.vue';
 
const props = defineProps({
  teachers: {
    type: Array,
    required: false,
    default: () => []
  },
  schools: {
    type: Array,
    required: true
  }
})

const schoolOptions = computed(() => {
  return props.schools.map(school => ({
    value: school.id,
    label: school.name
  }))
})

// Data
const teachers = ref([])
const loading = ref(false)
const dialog = ref(false)
const deleteDialog = ref(false)
const submitting = ref(false)
const deleting = ref(false)
const editingTeacher = ref(null)
const selectedTeacher = ref(null)
// const selectedTeacher = ref(null)

const pagination = ref({
  page: 1,
  rowsPerPage: 10,
  rowsNumber: 0
})

const form = ref({
  name: '',
  name_ar: '',
  email: '',
  school_id: null,
  phone_number: '',
  gender: null,
  date_of_birth: null,
})

const columns = [
{ name: 't_id', label: 'Teacher ID', field: 't_id', sortable: true },
  { name: 'name', label: 'Name', field: 'name', sortable: true },
  { name: 'name_ar', label: 'Arabic Name', field: 'name_ar', sortable: true },
  { name: 'email', label: 'Email', field: 'email', sortable: true },
  { name: 'school', label: 'School', field: 'school', sortable: true },
  { name: 'phone_number', label: 'Phone', field: 'phone_number', sortable: true },
  { name: 'gender', label: 'Gender', field: 'gender', sortable: true },
  { name: 'actions', label: 'Actions', field: 'actions' }
]

// Methods

const onRequest = (event) => {

console.log('onRequest',event);

}


const fetch_data = (text) => {
  loading.value = true
  axios.get(text, {
//   axios.get('/api/teachers', {
    // headers: {
    //   'X-Requested-With': 'XMLHttpRequest',
    //   'Accept': 'application/json'
    // },
    withCredentials: true
  })
    .then(response => {
      teachers.value = response.data.data
      pagination.value.rowsNumber = response.data.total
    })
    .catch(error => {
    //   if (error.response?.status === 401) {
    //     window.location.href = '/login'
    //     return
    //   }
      $q.notify({
        color: 'negative',
        message: 'Failed to load teachers'
      })
    })
    .finally(() => {
      loading.value = false
    })
}

        const loadTeachers = () => {
  loading.value = true
  axios.get('/admin/teachers', {
//   axios.get('/api/teachers', {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    withCredentials: true
  })
    .then(response => {
      teachers.value = response.data.data
      pagination.value.rowsNumber = response.data.total
    })
    .catch(error => {
    //   if (error.response?.status === 401) {
    //     window.location.href = '/login'
    //     return
    //   }
      $q.notify({
        color: 'negative',
        message: 'Failed to load teachers'
      })
    })
    .finally(() => {
      loading.value = false
    })
}

const openDialog = (teacher = null) => {
  if (teacher) {
    editingTeacher.value = teacher
    form.value = { ...teacher }
  } else {
    editingTeacher.value = null
    form.value = {
      name: '',
      name_ar: '',
      email: '',
      school_id: null,
      phone_number: '',
      gender: null,
      date_of_birth: null,
    }
  }
  dialog.value = true
}

const onSubmit = async () => {
  submitting.value = true
  try {
    // Prepare form data
    const formData = {
      ...form.value,
      school_id: form.value?.school_id?.value || form.value.school_id,
      gender: form.value.gender?.value || form.value.gender
    }

    if (editingTeacher.value) {
      await axios.put(`/api/teachers/${editingTeacher.value.id}`, formData)
    } else {
      await axios.post('/api/teachers', formData)
    }

    await loadTeachers()
    dialog.value = false
    $q.notify({
      color: 'positive',
      message: `Teacher ${editingTeacher.value ? 'updated' : 'created'} successfully`
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: error.response?.data?.message || 'Operation failed'
    })
  } finally {
    submitting.value = false
  }
}

const confirmDelete = (teacher) => {
  selectedTeacher.value = teacher
  deleteDialog.value = true
}

const deleteTeacher = async () => {
  if (!selectedTeacher.value) return

  deleting.value = true
  try {
    await axios.delete(`/api/teachers/${selectedTeacher.value.id}`)
    await loadTeachers()
    deleteDialog.value = false
    $q.notify({
      color: 'positive',
      message: 'Teacher deleted successfully'
    })
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to delete teacher'
    })
  } finally {
    deleting.value = false
    selectedTeacher.value = null
  }
}

const exportData = async () => {
  try {
    const response = await axios.get('/api/teachers/export')
    const data = response.data

    // Create and download file
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `teachers_${new Date().toISOString()}.json`)
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    $q.notify({
      color: 'negative',
      message: 'Failed to export data'
    })
  }
}

onMounted(() => {
  loadTeachers()
})
</script>
