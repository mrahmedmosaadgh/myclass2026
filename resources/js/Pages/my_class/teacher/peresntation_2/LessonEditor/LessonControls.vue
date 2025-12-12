<template>
  <div>
    <q-card>
      <q-card-section>
        <q-btn label="Save Draft" @click="save" color="primary" />
        <q-btn label="Export JSON" @click="exportJson" class="q-ml-sm" />
        <div class="q-mt-md">
          <input ref="fileInput" type="file" @change="onFile" />
        </div>
      </q-card-section>
    </q-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const props = defineProps({ lesson: Object })
const emit = defineEmits(['export','import','save'])
const fileInput = ref(null)
function save(){ emit('save') }
function exportJson(){ emit('export') }
function onFile(e){ const f = e.target.files[0]; if(f) emit('import', f) }
</script>
