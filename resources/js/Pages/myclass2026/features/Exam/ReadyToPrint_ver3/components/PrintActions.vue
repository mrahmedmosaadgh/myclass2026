<template>
  <div class="row items-center q-col-gutter-sm">
    <!-- Print method select -->
    <q-select
      dense
      outlined
      emit-value
      map-options
      :options="methodOptions"
      v-model="selectedMethod"
      style="min-width: 120px"
      bg-color="white"
      text-color="grey-9"
    >
      <q-tooltip>Print Method</q-tooltip>
    </q-select>

    <!-- Print button -->
    <q-btn
      flat
      round
      dense
      color="white"
      icon="print"
      @click="print"
    >
      <q-tooltip>Print</q-tooltip>
    </q-btn>

    <!-- Live Preview -->
    <q-btn
      flat
      round
      dense
      color="white"
      icon="preview"
      @click="openLivePreview"
    >
      <q-tooltip>Live Preview</q-tooltip>
    </q-btn>

    <!-- Download PDF -->
    <q-btn
      flat
      round
      dense
      color="white"
      icon="picture_as_pdf"
      @click="downloadPDF"
      :loading="pdfGenerating"
    >
      <q-tooltip>Download PDF</q-tooltip>
    </q-btn>

    <!-- Fullscreen -->
    <q-btn
      flat
      round
      dense
      color="white"
      icon="fullscreen"
      @click="openFullscreenPreview"
    >
      <q-tooltip>Fullscreen Preview</q-tooltip>
    </q-btn>

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
  },
  examTitle: {
    type: String,
    default: ''
  },
  examSubject: {
    type: String,
    default: ''
  },
  examGrade: {
    type: String,
    default: ''
  },
  examId: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:method', 'update:extraMarginMm'])

const livePreviewRef = ref(null)
const pdfGenerating = ref(false)

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

function generatePrintFileName() {
  const parts = []
  
  // Add exam title if available
  if (props.examTitle) {
    parts.push(props.examTitle)
  }
  
  // Add subject if available
  if (props.examSubject) {
    parts.push(props.examSubject)
  }
  
  // Add grade if available
  if (props.examGrade) {
    parts.push(props.examGrade)
  }
  
  // Add date
  const now = new Date()
  const dateStr = now.toISOString().split('T')[0] // YYYY-MM-DD
  parts.push(dateStr)
  
  // If no parts, use default
  if (parts.length === 0) {
    return 'Exam'
  }
  
  // Join with hyphens and sanitize
  return parts
    .join(' - ')
    .replace(/[<>:"/\\|?*]/g, '') // Remove invalid filename characters
    .substring(0, 200) // Limit length
}

function openPopupWindow() {
  const w = window.open('', '_blank', 'width=800,height=1100,scrollbars=yes,resizable=yes')
  if (!w) return null
  return w
}

function extractTitleFromHtml(html) {
  const m = String(html).match(/<title[^>]*>([\s\S]*?)<\/title>/i)
  const title = (m && m[1] ? String(m[1]).trim() : '')
  return title || 'Exam'
}

function writeHtmlToWindow(w, html) {
  w.document.open()
  w.document.write(html)
  w.document.close()
  try {
    w.document.title = extractTitleFromHtml(html)
  } catch {}
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

async function downloadPDF() {
  if (pdfGenerating.value) return
  pdfGenerating.value = true
  
  try {
    if (!props.examId) {
      throw new Error('No exam ID provided. Please save the exam first.')
    }
    
    // Call the backend API to generate and download PDF
    const response = await fetch(`/api/exam/ready-to-print/generate-pdf/${props.examId}`, {
      headers: {
        'Accept': 'application/pdf, text/html, application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    
    // Check if response is JSON (error) or PDF/HTML
    const contentType = response.headers.get('content-type') || ''
    
    if (!response.ok) {
      if (contentType.includes('application/json')) {
        const errorData = await response.json()
        throw new Error(errorData.message || 'Failed to generate PDF')
      } else {
        const text = await response.text()
        throw new Error('Server error: ' + text.substring(0, 200))
      }
    }
    
    if (contentType.includes('application/json')) {
      // Server returned JSON instead of PDF - likely an error
      const errorData = await response.json()
      throw new Error(errorData.message || 'PDF generation failed')
    }
    
    if (contentType.includes('text/html')) {
      // Server returned HTML as fallback - open in new window for print-to-PDF
      const html = await response.text()
      const printWindow = window.open('', '_blank')
      if (printWindow) {
        printWindow.document.write(html)
        printWindow.document.close()
        printWindow.onload = function() {
          printWindow.print()
        }
      } else {
        throw new Error('Popup blocked. Please allow popups to use print-to-PDF fallback.')
      }
      return
    }
    
    // Get the blob and create download link
    const blob = await response.blob()
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = generatePrintFileName() + '.pdf'
    document.body.appendChild(a)
    a.click()
    window.URL.revokeObjectURL(url)
    document.body.removeChild(a)
    
  } catch (error) {
    console.error('PDF generation failed:', error)
    alert('PDF generation failed: ' + error.message)
  } finally {
    pdfGenerating.value = false
  }
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
