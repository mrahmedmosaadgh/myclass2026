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
  exam: { type: Object, required: true },
  previewSettings: { type: Object, default: null }
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

const mcqOptions = computed(() => {
  const fromExam = normalized.value.settings?.mcqOptions || {}
  const fromPreview = props.previewSettings?.mcqOptions || {}
  return { ...fromExam, ...fromPreview }
})

function labelForType(idx, type) {
  const i = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)
  if (type === 'number') return i + ')'
  if (type === 'custom') {
    const tpl = mcqOptions.value?.customLabelTemplate || '{letter})'
    return String(tpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(i))
      .replaceAll('{letter}', String(letter))
  }
  return letter + ')'
}

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
  out += '.opt { break-inside: avoid; }'
  out += '.opt-row { display: flex; align-items: flex-start; }'
  out += '.opt-label { font-weight: 600; color: #333; }'
  out += '.opt-checkbox { width: 14px; height: 14px; border: 2px solid #333; display: inline-block; box-sizing: border-box; margin-top: 2px; }'
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
          const colsRaw = Number(mcqOptions.value?.columns)
          const cols = Number.isFinite(colsRaw) && colsRaw > 0 ? Math.floor(colsRaw) : 1
          const optionGapRaw = Number(mcqOptions.value?.optionGapPt)
          const optionGapPt = Number.isFinite(optionGapRaw) ? optionGapRaw : 6
          const labelGapRaw = Number(mcqOptions.value?.labelGapPt)
          const labelGapPt = Number.isFinite(labelGapRaw) ? labelGapRaw : 8

          const labelStyle = mcqOptions.value?.labelStyle || 'letter'
          const checkboxShowLabel = !!mcqOptions.value?.checkboxShowLabel
          const checkboxLabelType = mcqOptions.value?.checkboxLabelType || 'letter'

          const labelFontSizeRaw = Number(mcqOptions.value?.labelFontSizePt)
          const labelFontSizePt = Number.isFinite(labelFontSizeRaw) && labelFontSizeRaw > 0 ? labelFontSizeRaw : null
          const optionFontSizeRaw = Number(mcqOptions.value?.optionFontSizePt)
          const optionFontSizePt = Number.isFinite(optionFontSizeRaw) && optionFontSizeRaw > 0 ? optionFontSizeRaw : null

          const labelBold = !!mcqOptions.value?.labelBold
          const optionBold = !!mcqOptions.value?.optionBold

          out += '<div class="opts" style="display:grid; grid-template-columns:repeat(' + cols + ', minmax(0,1fr)); gap:' + optionGapPt + 'pt;">'
          opts.forEach((opt, idx) => {
            const labelText = labelStyle === 'checkbox'
              ? (checkboxShowLabel ? labelForType(idx, checkboxLabelType) : '')
              : labelForType(idx, labelStyle)

            const labelTextStyle = 'style="' +
              (labelFontSizePt != null ? ('font-size:' + labelFontSizePt + 'pt;') : '') +
              (labelBold ? 'font-weight:700;' : '') +
              '"'

            const checkboxLabelTextStyle = 'style="' +
              'margin-left:6pt;' +
              (labelFontSizePt != null ? ('font-size:' + labelFontSizePt + 'pt;') : '') +
              (labelBold ? 'font-weight:700;' : '') +
              '"'
            const optionTextStyle = 'style="' +
              (optionFontSizePt != null ? ('font-size:' + optionFontSizePt + 'pt;') : '') +
              (optionBold ? 'font-weight:700;' : '') +
              '"'

            out += '<div class="opt">'
            out += '<div class="opt-row" style="gap:' + labelGapPt + 'pt;">'

            if (labelStyle === 'checkbox') {
              out += '<span class="opt-label" style="font-weight:normal;">'
              out += '<span class="opt-checkbox"></span>'
              if (checkboxShowLabel) {
                out += '<span ' + checkboxLabelTextStyle + '>' + renderMathContent(labelText) + '</span>'
              }
              out += '</span>'
            } else {
              out += '<span class="opt-label" ' + labelTextStyle + '>' + renderMathContent(labelText) + '</span>'
            }

            out += '<span class="opt-text" ' + optionTextStyle + '>' + renderMathContent(opt) + '</span>'
            out += '</div>'
            out += '</div>'
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
