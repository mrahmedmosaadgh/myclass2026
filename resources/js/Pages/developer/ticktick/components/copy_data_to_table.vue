<template>
  <div class="p-4 space-y-4">
    <button @click="pasteFromClipboard" class="px-4 py-2 bg-blue-600 text-white rounded">
      📋 Paste from Clipboard
    </button>
    <!-- <button @click="copySecondColumn" class="px-4 py-2 bg-green-600 text-white rounded" :disabled="rows.length === 0">
      📤 Copy Second Column
    </button> -->
    <br>
    ---------------------------- <br>
    ---------------------------- <br>
    ---------------------------- <br>
clipboard msg:{{ msg }}
    <table v-if="rows.length" class="w-full mt-4 border border-gray-300">
      <thead>
        <tr class="bg-gray-200">
          <th class="border px-2 py-1 text-left">Key</th>
          <th class="border px-2 py-1 text-left">Value</th>
          <th class="border px-2 py-1 text-left">Copy</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(row, index) in rows" :key="index">
          <td class="border px-2 py-1">{{ row.key }}</td>
          <td class="border px-2 py-1">{{ row.value }}</td>
          <td class="border px-2 py-1">
            <button
              @click="copySingleValue(row.value)"
              class="bg-gray-200 hover:bg-gray-300 text-sm px-2 py-1 rounded"
            >
              📋 Copy
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const rows = ref([])

function pasteFromClipboard() {
  navigator.clipboard.readText().then(text => {
    const lines = text.split(/\r?\n/)
    rows.value = lines
      .filter(line => line.includes('\t'))
      .map(line => {
        const [key, value] = line.split('\t')
        return { key: key.trim(), value: value?.trim() || '' }
      })
  }).catch(err => {
    // alert("Failed to read clipboard: " + err)
  })
}

const msg =ref(null)
function copySecondColumn() {
  const values = rows.value.map(row => row.value).join('\n')
  navigator.clipboard.writeText(values).then(() => {
    // alert('Second column copied to clipboard!')
  }).catch(err => {
    // alert("Failed to copy: " + err)
  })
}
function copySingleValue(value) {
  navigator.clipboard.writeText(value).then(() => {
   msg.value=  `Copied: ${value}` 
//    msg.value= alert(`Copied: ${value}`)
  }).catch(err => {
    // alert("Failed to copy: " + err)
   msg.value=  "Failed to copy: " + err

  })
}
</script>

<style scoped>
table {
  border-collapse: collapse;
}
</style>
