<template>
  <div class="row items-center q-col-gutter-sm">
    <div class="col-auto">
      <div class="text-caption q-mb-xs">Print method</div>
      <q-select
        dense
        outlined
        emit-value
        map-options
        :options="methodOptions"
        v-model="selectedMethod"
        style="min-width: 160px"
      />
    </div>

    <div class="col-auto">
      <q-btn
        label="Print"
        color="primary"
        icon="print"
        @click="print"
      />
    </div>

    <div class="col-auto">
      <q-btn
        label="Live Preview"
        color="teal"
        icon="preview"
        @click="openLivePreview"
      />
    </div>

    <div class="col-auto">
      <q-btn
        label="Fullscreen"
        color="grey-7"
        icon="fullscreen"
        @click="openFullscreenPreview"
      />
    </div>

    <!-- Live print preview dialog -->
    <LivePrintPreview
      ref="livePreviewRef"
      :generate-print-html="generatePrintHtml"
      :extra-margin-mm="extraMarginMm"
      @update:extra-margin-mm="$emit('update:extraMarginMm', $event)"
      @print="print"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import LivePrintPreview from './LivePrintPreview.vue'

const props = defineProps({
  generatePrintHtml: {
    type: Function,
    required: true
  },
  extraMarginMm: {
    type: Number,
    default: 0
  },
  initialMethod: {
    type: String,
    default: 'v1'
  }
})

const emit = defineEmits(['update:method', 'update:extraMarginMm'])

const livePreviewRef = ref(null)

const PRINT_METHOD_KEY = 'exam_ready_to_print_print_method_v1'

const selectedMethod = ref(props.initialMethod)

try {
  const saved = localStorage.getItem(PRINT_METHOD_KEY)
  if (saved) selectedMethod.value = saved
} catch (e) {}

watch(selectedMethod, (v) => {
  try {
    localStorage.setItem(PRINT_METHOD_KEY, String(v || 'v1'))
  } catch (e) {}

  emit('update:method', String(v || 'v1'))
})

const methodOptions = computed(() => [
  { label: 'Print V1 – iframe (recommended)', value: 'v1' },
  { label: 'Print V2 – popup manual', value: 'v2' },
  { label: 'Print V3 – popup auto', value: 'v3' },
  { label: 'Print V4 – iframe + injected styles', value: 'v4' }
])

function openPopupWindow() {
  const w = window.open('', '_blank', 'width=800,height=1100,scrollbars=yes,resizable=yes')
  if (!w) return null
  return w
}

function writeHtmlToWindow(w, html) {
  w.document.open()
  w.document.write(html)
  w.document.close()
  w.focus()
}

function buildV2Html(baseHtml) {
  return baseHtml
    .replace(
      '<head>',
      '<head><style>' +
        ' .print-toolbar { position: fixed; top: 0; left: 0; right: 0; z-index: 10000; background: #fff; border-bottom: 1px solid #ddd; padding: 10px 12px; font-family: Arial, sans-serif; }' +
        ' .print-toolbar button { padding: 8px 12px; font-size: 14px; }' +
        ' @media print { .print-toolbar { display: none !important; } }' +
      '</style>'
    )
    .replace(
      '<body>',
      '<body><div class="print-toolbar"><button onclick="window.print()">Print</button></div>'
    )
}

function extractHeadLinks(baseHtml) {
  const headMatch = String(baseHtml).match(/<head[^>]*>([\s\S]*?)<\/head>/i)
  if (!headMatch) return ''
  const headInner = headMatch[1] || ''
  const links = headInner.match(/<link[^>]*>/gi)
  return Array.isArray(links) ? links.join('\n') : ''
}

function extractBodyInner(baseHtml) {
  const parts = String(baseHtml).split(/<body[^>]*>/i)
  if (parts.length < 2) return ''
  const afterBody = parts.slice(1).join('<body>')
  const bodyParts = afterBody.split(/<\/body>/i)
  return bodyParts[0] || ''
}

function collectCurrentStyles() {
  try {
    return [...document.styleSheets]
      .map(sheet => {
        try {
          return [...sheet.cssRules].map(r => r.cssText).join('\n')
        } catch {
          return ''
        }
      })
      .join('\n')
  } catch (e) {
    return ''
  }
}

function printHtmlViaIframe(html) {
  const iframe = document.createElement('iframe')
  // Off-screen but real A4 width (794px ≈ A4 at 96dpi) so offsetHeight measurement works.
  iframe.style.position = 'fixed'
  iframe.style.left = '-9999px'
  iframe.style.top = '0'
  iframe.style.width = '794px'
  iframe.style.height = '1px'
  iframe.style.border = '0'
  iframe.setAttribute('aria-hidden', 'true')

  const cleanup = () => {
    try {
      iframe.onload = null
      iframe.srcdoc = ''
      iframe.remove()
    } catch (e) {}
  }

  iframe.onload = () => {
    const win = iframe.contentWindow
    const doc = iframe.contentDocument
    if (!win || !doc) { cleanup(); return }

    const doPrint = () => {
      try { win.focus(); win.print() } catch (e) { console.error('Print failed', e) }
      setTimeout(cleanup, 1500)
    }

    // 1. Wait for fonts (KaTeX etc.)
    const fontsReady = doc.fonts ? doc.fonts.ready : Promise.resolve()

    // 2. Wait for __printReady signal from the header measurement script.
    //    If undefined → no header script injected → resolve immediately.
    const printReady = new Promise(resolve => {
      if (win.__printReady === undefined) { resolve(); return }
      if (win.__printReady === true) { resolve(); return }
      let elapsed = 0
      const timer = setInterval(() => {
        elapsed += 50
        if (win.__printReady === true || elapsed >= 2000) { clearInterval(timer); resolve() }
      }, 50)
    })

    // Hard cap: 2.5s then print whatever we have
    const timeout = new Promise(resolve => setTimeout(resolve, 2500))

    Promise.race([Promise.all([fontsReady, printReady]), timeout])
      .then(doPrint).catch(doPrint)
  }

  document.body.appendChild(iframe)
  iframe.srcdoc = String(html || '')
}


function buildV3Html(baseHtml) {
  const parts = String(baseHtml).split(/<body[^>]*>/i)
  if (parts.length < 2) return buildV2Html(baseHtml)
  const afterBody = parts.slice(1).join('<body>')
  const bodyParts = afterBody.split(/<\/body>/i)
  const bodyInner = bodyParts[0] || ''
  const headPart = parts[0]

  const style =
    '<style>' +
      ' .no-print { display: inline-block; }' +
      ' .print-toolbar { position: fixed; top: 0; left: 0; right: 0; z-index: 10000; background: #fff; border-bottom: 1px solid #ddd; padding: 10px 12px; font-family: Arial, sans-serif; }' +
      ' .print-toolbar button { padding: 8px 12px; font-size: 14px; }' +
      ' .print-container { position: relative; }' +
      ' @media print {' +
      '   body * { visibility: hidden; }' +
      '   .print-container, .print-container * { visibility: visible; }' +
      '   .print-container { position: absolute; left: 0; top: 0; width: 100%; }' +
      '   .no-print, .print-toolbar { display: none !important; }' +
      '   * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
      ' }' +
    '</style>'

  return (
    headPart.replace(
      '<head>',
      '<head>' + style
    ) +
    '<body>' +
      '<div class="print-toolbar"><button class="no-print" onclick="window.print()">Print</button></div>' +
      '<div class="print-container">' + bodyInner + '</div>' +
    '</body></html>'
  )
}

function openFullscreenPreview() {
  const w = openPopupWindow()
  if (!w) return
  const html = props.generatePrintHtml()
  writeHtmlToWindow(w, html)
}

function printV1() {
  // iframe approach: waits for fonts.ready before printing (no popup-blocker issues)
  const html = props.generatePrintHtml()
  printHtmlViaIframe(html)
}

function printV2() {
  const w = openPopupWindow()
  if (!w) return

  const baseHtml = props.generatePrintHtml()
  const html = buildV2Html(baseHtml)
  writeHtmlToWindow(w, html)
}

function printV3() {
  // iframe approach with print-container isolation
  const baseHtml = props.generatePrintHtml()
  const html = buildV3Html(baseHtml)
  printHtmlViaIframe(html)
}

function printV4() {
  const baseHtml = props.generatePrintHtml()
  const links = extractHeadLinks(baseHtml)
  const bodyInner = extractBodyInner(baseHtml)
  const styles = collectCurrentStyles()

  const titleMatch = String(baseHtml).match(/<title[^>]*>([\s\S]*?)<\/title>/i)
  const title = titleMatch ? String(titleMatch[1] || '').trim() : 'Print'

  const html =
    '<!DOCTYPE html>' +
    '<html><head>' +
    '<meta charset="UTF-8" />' +
    '<title>' + title + '</title>' +
    (links ? (links + '\n') : '') +
    '<style>' +
      '@page { size: A4 portrait; margin: 12mm; }' +
      'body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
      '@media print { .no-print { display: none !important; } }' +
      styles +
    '</style>' +
    '</head><body>' +
    bodyInner +
    '</body></html>'

  printHtmlViaIframe(html)
}

function openLivePreview() {
  livePreviewRef.value?.show(props.extraMarginMm)
}

function print() {
  if (selectedMethod.value === 'v2') {
    printV2()
    return
  }
  if (selectedMethod.value === 'v3') {
    printV3()
    return
  }
  if (selectedMethod.value === 'v4') {
    printV4()
    return
  }
  printV1()
}
</script>
