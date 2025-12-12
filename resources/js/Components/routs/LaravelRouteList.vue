<script setup>
import { ref, onMounted, computed } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import { saveAs } from 'file-saver'
import { router, Link } from '@inertiajs/vue3'

const $q = useQuasar()
const routes = ref([])
const search = ref('')
const methodFilter = ref('')
const loading = ref(true)
const error = ref(null)
const currentPage = ref(1)
const rowsPerPage = ref(10)

onMounted(() => {
  axios.get('/api/dev/routes')
    .then(res => {
      routes.value = res.data
    })
    .catch(err => {
      error.value = err.response?.data?.message || err.message
    })
    .finally(() => {
      loading.value = false
    })
})

const filteredRoutes = computed(() => {
  let result = routes.value

  if (search.value) {
    const keyword = search.value.toLowerCase()
    result = result.filter(route =>
      route.uri.toLowerCase().includes(keyword) ||
      route.name?.toLowerCase().includes(keyword) ||
      route.action?.toLowerCase().includes(keyword)
    )
  }

  if (methodFilter.value) {
    result = result.filter(route => route.methods.includes(methodFilter.value))
  }

  return result
})

const paginatedRoutes = computed(() => {
  const start = (currentPage.value - 1) * rowsPerPage.value
  return filteredRoutes.value.slice(start, start + rowsPerPage.value)
})

function copyToClipboard(text) {
  navigator.clipboard.writeText(text)
  $q.notify({ type: 'positive', message: 'Copied to clipboard!' })
}

function exportToJSON() {
  const blob = new Blob([JSON.stringify(filteredRoutes.value, null, 2)], { type: 'application/json' })
  saveAs(blob, 'routes.json')
}

function exportToCSV() {
  const header = ['Method', 'URI', 'Name', 'Action']
  const rows = filteredRoutes.value.map(route => [
    route.methods.join(', '),
    route.uri,
    route.name || '',
    route.action || ''
  ])
  const csvContent = [header, ...rows].map(e => e.map(v => '"' + v + '"').join(',')).join('\n')
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  saveAs(blob, 'routes.csv')
}
</script>

<template>
  <q-card class="q-pa-md">
    <q-card-section>
      <div class="row items-center q-gutter-md">
        <q-input v-model="search" label="Search routes" debounce="300" outlined dense class="col" />
        <q-select
          v-model="methodFilter"
          :options="['GET', 'POST', 'PUT', 'PATCH', 'DELETE']"
          label="Filter by method"
          clearable
          outlined
          dense
          class="col-3"
        />
        <q-btn label="Export JSON" color="primary" @click="exportToJSON" />
        <q-btn label="Export CSV" color="secondary" @click="exportToCSV" />
      </div>
    </q-card-section>

    <q-card-section>
      <q-table
        :rows="paginatedRoutes"
        :columns="[
          { name: 'method', label: 'Method', field: row => row.methods.join(', '), sortable: true },
          { name: 'uri', label: 'URI', field: 'uri', sortable: true },
          { name: 'name', label: 'Name', field: 'name', sortable: true },
          { name: 'action', label: 'Action', field: 'action', sortable: true },
          { name: 'copy', label: 'Copy', field: 'uri' }
        ]"
        row-key="uri"
        flat
        dense
        bordered
      >
        <template v-slot:body-cell-name="props">
          <q-td :props="props">
            <component
              :is="Link"
              :href="'/' + props.row.uri"
              class="text-primary hover:underline"
              v-if="props.row.uri && props.row.methods.includes('GET')"
            >
              {{ props.row.name || '(no name)' }}
            </component>
            <span v-else>{{ props.row.name || '(no name)' }}</span>
          </q-td>
        </template>

        <template v-slot:body-cell-copy="props">
          <q-td :props="props">
            <q-btn dense flat icon="content_copy" @click="copyToClipboard(props.row.uri)" />
          </q-td>
        </template>
      </q-table>

      <q-pagination
        v-model="currentPage"
        :max="Math.ceil(filteredRoutes.length / rowsPerPage)"
        color="primary"
        class="q-mt-md"
        size="sm"
        input
      />
    </q-card-section>
  </q-card>
</template>

<style scoped>
.q-table td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
