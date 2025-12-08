<template>
  <div>
    <textarea
      v-model="code"
      class="w-full h-60 border p-2 mb-4 font-mono text-sm"
      placeholder="Type your Vue 3 component with <template> and <script setup>..."
    ></textarea>

    <div class="flex justify-between mb-2">
      <button @click="updateIframe" class="px-4 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Run</button>
      <button @click="resetCode" class="px-4 py-1 bg-gray-300 text-black rounded hover:bg-gray-400">Reset</button>
    </div>

    <iframe ref="iframeRef" class="w-full h-[400px] border" sandbox="allow-scripts"></iframe>
  </div>
</template>

<script setup>
import { onMounted, ref, watch, defineProps } from 'vue'

const props = defineProps({
  initialCode: { type: String, default: '' }
})

const iframeRef = ref(null)
const defaultCode = `<template>
  <div>
    <h1>Hello from dynamic component!</h1>
    <button @click=\"count++\">Clicked {{ count }} times</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const count = ref(0)
<\/script>
`

const code = ref(props.initialCode || defaultCode)

onMounted(() => {
  updateIframe()
})

function resetCode() {
  code.value = defaultCode
  updateIframe()
}

function updateIframe() {
  const iframe = iframeRef.value
  const doc = iframe.contentDocument || iframe.contentWindow.document

  const html = `
    <!DOCTYPE html>
    <html>
      <head>
        <script type="module">
          import { createApp, ref } from 'https://unpkg.com/vue@3/dist/vue.esm-browser.js'

          const App = {
            template: \`${extractTemplate(code.value)}\`,
            setup() {
              ${extractScript(code.value)}
              return { ${extractScriptVars(code.value)} }
            }
          }

          createApp(App).mount('#app')
        <\/script>
      <\/head>
      <body>
        <div id="app"><\/div>
      <\/body>
    <\/html>
  `

  doc.open()
  doc.write(html)
  doc.close()
}

function extractTemplate(input) {
  const match = input.match(/<template>([\s\S]+?)<\/template>/)
  return match ? match[1].trim() : ''
}

function extractScript(input) {
  const match = input.match(/<script setup>([\s\S]+?)<\/script>/)
  return match ? match[1].trim() : ''
}

function extractScriptVars(script) {
  const vars = script.match(/const\s+(\w+)/g)
  return vars ? vars.map(v => v.replace('const ', '')).join(', ') : ''
}
</script>

<style scoped>
textarea {
  font-family: monospace;
}
</style>
