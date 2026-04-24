<template>
  <div class="live-print-preview">
    <div class="row items-center q-mb-md">
      <div class="text-subtitle1">Live Print Preview</div>
      <q-space />
      <q-btn
        color="primary"
        icon="print"
        label="Print"
        @click="print"
      />
    </div>

    <iframe
      ref="frameRef"
      class="preview-frame"
      :srcdoc="html"
    />
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { renderMathContent } from '../../ReadyToPrint_ver3/utils/mathRenderer'

const props = defineProps({
  exam: { type: Object, required: true }
})

const frameRef = ref(null)

const normalized = computed(() => {
  const exam = props.exam || {}
  const questions = Array.isArray(exam.questions)
    ? exam.questions
    : (Array.isArray(exam.sampleQuestions) ? exam.sampleQuestions : [])

  const sections = Array.isArray(exam.sections) ? exam.sections : []
  const settings = exam.settings || exam.pageOptions || {}

  return { questions, sections, settings }
})

const html = computed(() => {
  const title = normalized.value.settings?.examTitle?.text || 'Exam'

  let out = ''
  out += '<!DOCTYPE html><html><head><meta charset="utf-8" />'
  out += '<style>'
  out += '@page { size: A4; margin: 12mm; }'
  out += 'body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; padding: 0; }'
  out += '.page { padding: 12mm; }'
  out += '.section { margin-top: 18px; }'
  out += '.question { margin: 10px 0 16px; page-break-inside: avoid; }'
  out += '.qhdr { display:flex; justify-content: space-between; gap: 12px; font-weight: 600; }'
  out += '.opts { margin-top: 8px; }'
  out += '.opt { margin: 4px 0; }'
  out += '</style>'
  out += '</head><body>'
  out += '<div class="page">'
  out += '<h2 style="margin:0 0 12px;">' + renderMathContent(title) + '</h2>'

  const bySection = new Map()
  normalized.value.sections.forEach(s => bySection.set(s.id, []))

  normalized.value.questions.forEach((q) => {
    const secId = q.section || normalized.value.sections?.[0]?.id || 'default'
    if (!bySection.has(secId)) bySection.set(secId, [])
    bySection.get(secId).push(q)
  })

  if (normalized.value.sections.length) {
    normalized.value.sections.forEach((sec) => {
      const list = bySection.get(sec.id) || []
      if (!list.length) return
      out += '<div class="section">'
      out += '<h3 style="margin: 0 0 6px;">' + renderMathContent(sec.title || '') + '</h3>'
      if (sec.instructions) {
        out += '<div style="margin: 0 0 10px; color: #444;">' + renderMathContent(sec.instructions) + '</div>'
      }
      list.forEach((q, i) => {
        const n = i + 1
        out += '<div class="question">'
        out += '<div class="qhdr">'
        out += '<div>' + n + ') ' + renderMathContent(q.content?.prompt || q.prompt || '') + '</div>'
        out += '<div>' + (q.marks ?? '') + '</div>'
        out += '</div>'
        const opts = q.content?.options || q.options
        if (Array.isArray(opts) && opts.length) {
          out += '<div class="opts">'
          opts.forEach((opt, idx) => {
            const letter = String.fromCharCode('A'.charCodeAt(0) + idx)
            out += '<div class="opt"><strong>' + letter + ')</strong> ' + renderMathContent(opt) + '</div>'
          })
          out += '</div>'
        }
        out += '</div>'
      })
      out += '</div>'
    })
  } else {
    normalized.value.questions.forEach((q, i) => {
      out += '<div class="question">'
      out += '<div class="qhdr">'
      out += '<div>' + (i + 1) + ') ' + renderMathContent(q.content?.prompt || q.prompt || '') + '</div>'
      out += '<div>' + (q.marks ?? '') + '</div>'
      out += '</div>'
      out += '</div>'
    })
  }

  out += '</div>'
  out += '</body></html>'
  return out
})

watch(html, () => {
  // no-op; ensures reactive updates for iframe srcdoc
})

function print() {
  const win = frameRef.value?.contentWindow
  if (!win) return
  win.focus()
  win.print()
}
</script>

<style scoped>
.preview-frame {
  width: 100%;
  height: calc(100vh - 160px);
  border: 1px solid #ddd;
  border-radius: 8px;
  background: white;
}
</style>
