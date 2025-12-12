<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'
import { useQuasar } from 'quasar'

const props = defineProps({ model: Object })
const emit = defineEmits(['saved'])

const $q = useQuasar()
const form = ref({ name: '', description: '', level_id: null, order: null })
const levels = ref([])

function fetchLevels() {
  axios.get('/qdrat/skill-levels').then(res => levels.value = res.data)
}

watch(() => props.model, (val) => {
  if (val) form.value = { ...val }
  else form.value = { name: '', description: '', level_id: null, order: null }
}, { immediate: true })

function save() {
  const url = props.model ? `/qdrat/skills/${props.model.id}` : '/qdrat/skills'
  const method = props.model ? 'put' : 'post'

  axios[method](url, form.value).then(() => {
    $q.notify({ type: 'positive', message: 'Saved successfully' })
    emit('saved')
  }).catch(() => {
    $q.notify({ type: 'negative', message: 'Failed to save' })
  })
}

onMounted(fetchLevels)
</script>

<template>
  <q-card>
    <q-card-section>
      <div class="text-h6">{{ props.model ? 'Edit Skill' : 'Add Skill' }}</div>
    </q-card-section>

    <q-separator />

    <q-card-section>
      <q-input v-model="form.name" label="Skill Name" />
      <q-input v-model="form.description" label="Description" type="textarea" />
      <q-select v-model="form.level_id" label="Level" :options="levels" option-value="id" option-label="name" />
      <q-input v-model="form.order" type="number" label="Order" />
    </q-card-section>

    <q-card-actions align="right">
      <q-btn flat label="Cancel" v-close-popup />
      <q-btn color="primary" label="Save" @click="save" />
    </q-card-actions>
  </q-card>
</template>