<template>
  <div class="question-mcq">
    <div class="question-prompt" :style="questionStyle">
      <div class="prompt-inline">
        <slot name="label"></slot>
        <span v-html="renderedPrompt"></span>
      </div>
    </div>

    <div class="question-options" v-if="renderedOptions.length" :style="optionsLayoutStyle">
      <div class="option" v-for="(opt, idx) in renderedOptions" :key="idx" :style="optionStyle">
        <span class="option-label" :class="{ 'is-checkbox': labelStyle === 'checkbox' }" :style="labelStyleObj">
          <template v-if="labelStyle === 'checkbox'">
            <span class="checkbox-box"></span>
            <span v-if="checkboxShowLabel" class="checkbox-label-text" :style="labelTextStyle">{{ checkboxLabel(idx) }}</span>
          </template>
          <template v-else>
            <span :style="labelTextStyle">{{ optionLabel(idx) }}</span>
          </template>
        </span>
        <span class="option-text" :style="optionTextStyle" v-html="opt"></span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { renderToString } from 'katex'

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  format: {
    type: Object,
    default: () => ({})
  },
  mcqOptions: {
    type: Object,
    default: () => ({})
  }
})

const columns = computed(() => {
  const n = Number(props.mcqOptions?.columns)
  return Number.isFinite(n) && n > 0 ? Math.floor(n) : 1
})

const optionGapPt = computed(() => {
  const n = Number(props.mcqOptions?.optionGapPt)
  return Number.isFinite(n) ? n : 6
})

const labelGapPt = computed(() => {
  const n = Number(props.mcqOptions?.labelGapPt)
  return Number.isFinite(n) ? n : 8
})

const labelStyle = computed(() => props.mcqOptions?.labelStyle || 'letter')

const checkboxShowLabel = computed(() => !!props.mcqOptions?.checkboxShowLabel)
const checkboxLabelType = computed(() => props.mcqOptions?.checkboxLabelType || 'letter')

function labelForType(idx, type) {
  const i = idx + 1
  const letter = String.fromCharCode('A'.charCodeAt(0) + idx)

  if (type === 'number') return i + ')'
  if (type === 'custom') {
    const tpl = props.mcqOptions?.customLabelTemplate || '{letter})'
    return String(tpl)
      .replaceAll('{i}', String(idx))
      .replaceAll('{n}', String(i))
      .replaceAll('{letter}', String(letter))
  }
  return letter + ')'
}

function optionLabel(idx) {
  return labelForType(idx, labelStyle.value)
}

function checkboxLabel(idx) {
  return labelForType(idx, checkboxLabelType.value)
}

function stripCitations(text) {
  if (!text) return ''
  return String(text)
    .replace(/\[\s*cite\s*:\s*\d+\s*\]/gi, '')
    .replace(/\s{2,}/g, ' ')
    .trim()
}

function renderMath(text) {
  let content = stripCitations(text)

  content = content.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false, displayMode: true })
    } catch (e) {
      return match
    }
  })

  content = content.replace(/\$([^$]+)\$/g, (match, math) => {
    try {
      return renderToString(math, { throwOnError: false })
    } catch (e) {
      return match
    }
  })

  return content
}

const renderedPrompt = computed(() => renderMath(props.question?.content?.prompt || ''))

const renderedOptions = computed(() => {
  const opts = props.question?.content?.options
  if (!Array.isArray(opts)) return []
  return opts.map(o => renderMath(o))
})

const optionsLayoutStyle = computed(() => {
  return {
    display: 'grid',
    gridTemplateColumns: `repeat(${columns.value}, minmax(0, 1fr))`,
    gap: optionGapPt.value + 'pt'
  }
})

const optionStyle = computed(() => {
  return {
    display: 'flex',
    alignItems: 'flex-start',
    gap: labelGapPt.value + 'pt'
  }
})

const labelStyleObj = computed(() => {
  return {
    minWidth: labelStyle.value === 'checkbox' ? undefined : '24px'
  }
})

const labelTextStyle = computed(() => {
  const sizeRaw = Number(props.mcqOptions?.labelFontSizePt)
  const sizePt = Number.isFinite(sizeRaw) ? sizeRaw : undefined
  const bold = !!props.mcqOptions?.labelBold
  return {
    fontSize: sizePt != null ? sizePt + 'pt' : undefined,
    fontWeight: bold ? '700' : undefined
  }
})

const questionStyle = computed(() => {
  const q = props.format?.question || {}
  return {
    fontSize: q.fontSize ? String(q.fontSize) : undefined,
    lineHeight: q.lineHeight ? String(q.lineHeight) : undefined,
    fontWeight: q.bold ? '700' : undefined
  }
})

const optionsStyle = computed(() => {
  const o = props.format?.options || {}
  return {
    fontSize: o.fontSize ? String(o.fontSize) : undefined,
    lineHeight: o.lineHeight ? String(o.lineHeight) : undefined,
    fontWeight: o.bold ? '700' : undefined
  }
})

const optionTextStyle = computed(() => {
  const sizeRaw = Number(props.mcqOptions?.optionFontSizePt)
  const sizePt = Number.isFinite(sizeRaw) ? sizeRaw : undefined
  const bold = !!props.mcqOptions?.optionBold
  return {
    ...optionsStyle.value,
    fontSize: sizePt != null ? sizePt + 'pt' : optionsStyle.value.fontSize,
    fontWeight: bold ? '700' : optionsStyle.value.fontWeight
  }
})
</script>

<style scoped>
.question-prompt {
  margin-bottom: 8pt;
  line-height: 1.5;
}

.prompt-inline {
  display: flex;
  align-items: flex-start;
  gap: var(--inline-gap, 8pt);
}

.question-prompt :deep(.question-number) {
  font-weight: 700;
  white-space: nowrap;
}

.question-options {
  margin: 6pt 0 8pt;
}

.option {
  break-inside: avoid;
}

.option-label {
  font-weight: 600;
  color: #333;
}

.option-label.is-checkbox {
  min-width: auto;
  font-weight: normal;
}

.checkbox-box {
  width: 14px;
  height: 14px;
  border: 2px solid #333;
  display: inline-block;
  box-sizing: border-box;
  margin-top: 2px;
}

.checkbox-label-text {
  margin-left: 6pt;
}
</style>
