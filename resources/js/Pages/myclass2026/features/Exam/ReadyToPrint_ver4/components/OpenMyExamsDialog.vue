<template>
  <q-dialog v-model="model">
    <q-card style="min-width: 720px; max-width: 95vw;">
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">My Saved Exams</div>
        <q-space />
        <q-btn icon="refresh" flat round dense @click="loadFiles" :loading="loading" />
        <q-btn icon="close" flat round dense v-close-popup />
      </q-card-section>

      <q-separator />

      <q-card-section>
        <q-input
          v-model="search"
          dense
          outlined
          placeholder="Search by title..."
          class="q-mb-md"
        />

        <div v-if="loading" class="text-center q-pa-lg text-grey-7">
          Loading...
        </div>

        <div v-else-if="filteredFiles.length === 0" class="text-center q-pa-lg text-grey-7">
          No saved exams found.
        </div>

        <q-list v-else bordered separator>
          <q-item
            v-for="file in filteredFiles"
            :key="file.id"
            clickable
            @click="openFile(file)"
          >
            <q-item-section>
              <q-item-label>{{ file.title || file.name }}</q-item-label>
              <q-item-label caption>
                Questions: {{ file.questions_count ?? '-' }}
                
                
                Updated: {{ formatDate(file.updated_at) }}
              </q-item-label>
            </q-item-section>

            <q-item-section side>
              <q-btn dense flat icon="open_in_new" @click.stop="openFile(file)" />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>

      <q-separator />

      <q-card-actions align="right">
        <q-btn flat label="Close" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useQuasar } from 'quasar'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  modelValue: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'loaded'])

const $q = useQuasar()
const page = usePage()

const model = computed({
  get: () => props.modelValue,
  set: (v) => emit('update:modelValue', v)
})

const loading = ref(false)
const files = ref([])
const search = ref('')

const filteredFiles = computed(() => {
  const q = search.value.trim().toLowerCase()
  if (!q) return files.value
  return files.value.filter(f => String(f.title || f.name || '').toLowerCase().includes(q))
})

watch(
  () => model.value,
  (open) => {
    if (open) loadFiles()
  }
)

async function loadFiles() {
  loading.value = true
  try {
    const res = await fetch('/api/exam/ready-to-print/list-saved-exams', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || ''
      }
    })
    const json = await res.json()
    if (!res.ok) {
      throw new Error(json?.message || 'Failed to load saved exams')
    }
    files.value = json?.files || []
  } catch (e) {
    $q.notify({ type: 'negative', message: String(e?.message || e), position: 'top' })
  } finally {
    loading.value = false
  }
}

async function openFile(file) {
  if (!file?.id) return
  loading.value = true
  try {
    const res = await fetch('/api/exam/ready-to-print/load-saved-exam/' + encodeURIComponent(file.id), {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': page.props.csrf_token || ''
      }
    })
    const json = await res.json()
    if (!res.ok) {
      throw new Error(json?.message || 'Failed to load exam')
    }

    // Controller returns { success: true, data: { ... } }
    const exam = json?.data || json
    emit('loaded', exam)
    model.value = false
  } catch (e) {
    $q.notify({ type: 'negative', message: String(e?.message || e), position: 'top' })
  } finally {
    loading.value = false
  }
}

function formatDate(iso) {
  if (!iso) return '-'
  try {
    const d = new Date(iso)
    return d.toLocaleString()
  } catch {
    return String(iso)
  }
}
</script>
