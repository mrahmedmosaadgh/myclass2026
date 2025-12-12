<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import SkillForm from './SkillForm.vue'
import ImportExcel from '@/Components/Common/ImportExcel.vue'

const skills = ref([])
const search = ref('')
const loading = ref(false)
const showForm = ref(false)
const selectedSkill = ref(null)

const skillColumns = [
  { key: 'name', label: 'Skill Name', required: true },
  { key: 'description', label: 'Description' },
  { key: 'level_id', label: 'Level ID', required: true },
  { key: 'order', label: 'Display Order' }
]

function fetchSkills() {
  loading.value = true
  axios.get('/qdrat/skills', { params: { search: search.value } })
    .then(res => skills.value = res.data)
    .finally(() => loading.value = false)
}

function editSkill(skill) {
  selectedSkill.value = skill
  showForm.value = true
}

function addNewSkill() {
  selectedSkill.value = null
  showForm.value = true
}

function downloadTemplate() {
  window.open('/qdrat/skills/import/template', '_blank')
}

function reloadSkills() {
  fetchSkills()
}

onMounted(fetchSkills)
</script>

<template>
  <div>
    <div class="q-gutter-md mb-4">
      <div class="row q-gutter-md items-center">
        <q-input 
          v-model="search" 
          label="Search Skills" 
          debounce="400" 
          @update:model-value="fetchSkills"
          class="col"
        />
        <q-btn 
          label="Add Skill" 
          icon="add" 
          @click="addNewSkill" 
          color="primary" 
        />
        <q-btn 
          label="Download Template" 
          icon="download" 
          @click="downloadTemplate" 
          color="secondary" 
        />
      </div>
      
      <ImportExcel
        :columns="skillColumns"
        validate-url="/qdrat/skills/validate"
        import-url="/qdrat/skills/import"
        undo-url="/qdrat/skills/undo"
        button-text="Import Skills from Excel"
        @imported="reloadSkills"
      />
    </div>

    <q-table 
      :rows="skills" 
      :columns="[
        { name: 'name', label: 'Skill Name', field: 'name', sortable: true },
        { name: 'description', label: 'Description', field: 'description' },
        { name: 'level', label: 'Level', field: row => row.level?.name || '—' },
        { name: 'order', label: 'Order', field: 'order', sortable: true },
        { name: 'actions', label: '', sortable: false }
      ]" 
      row-key="id"
      :loading="loading"
      :pagination="{ rowsPerPage: 20 }"
    >
      <template v-slot:body-cell-actions="props">
        <q-btn icon="edit" dense flat color="primary" @click="editSkill(props.row)" />
      </template>
    </q-table>

    <q-dialog v-model="showForm" persistent>
      <SkillForm :model="selectedSkill" @saved="() => { fetchSkills(); showForm.value = false }" />
    </q-dialog>
  </div>
</template>