<template>
  <div class="question-display">
    <img
      v-if="imageUrl"
      class="question-fly-image"
      :src="imageUrl"
      :style="imageStyle"
      alt=""
    />
    <div class="question-header">
      <template v-if="!numberingOptions?.inlineWithText">
        <QuestionNumber
          :index="questionNumber"
          :options="numberingOptions"
        />
      </template>
      <span class="question-marks" v-if="showMarks && question.marks">{{ question.marks }} marks</span>
    </div>

    <QuestionMCQ
      v-if="isMcq"
      :question="question"
      :format="format"
      :mcq-options="mcqOptions"
      :style="mcqInlineStyle"
    >
      <template v-if="numberingOptions?.inlineWithText" #label>
        <QuestionNumber
          class-name="question-number-inline"
          :index="questionNumber"
          :options="numberingOptions"
        />
      </template>
    </QuestionMCQ>

    <template v-else>
      <div
        v-if="numberingOptions?.inlineWithText"
        class="question-content-inline"
        :style="questionContentInlineStyle"
      >
        <QuestionNumber
          class-name="question-number-inline"
          :index="questionNumber"
          :options="numberingOptions"
        />
        <span class="question-text-inline" v-html="renderedContent"></span>
      </div>

      <div v-else class="question-content" :style="questionTextStyle" v-html="renderedContent"></div>

      <div class="question-options" v-if="effectiveHasOptions">
        <div class="option" v-for="(opt, idx) in renderedOptions" :key="idx">
          <span class="option-label">{{ optionLabel(idx) }}</span>
          <span class="option-text" :style="optionsTextStyle" v-html="opt"></span>
        </div>
      </div>
    </template>
    
    <div class="answer-area" v-if="showAnswerArea">
      <div class="answer-lines">
        <div class="answer-line" v-for="n in answerLines" :key="n"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { renderToString } from 'katex'
import QuestionMCQ from './QuestionMCQ.vue'
import QuestionNumber from './QuestionNumber.vue'

const props = defineProps({
  question: {
    type: Object,
    required: true
  },
  questionNumber: {
    type: Number,
    required: true
  },
  numberingOptions: {
    type: Object,
    default: () => ({})
  },
  mcqOptions: {
    type: Object,
    default: () => ({})
  },
  showAnswerArea: {
    type: Boolean,
    default: true
  },
  showMarks: {
    type: Boolean,
    default: true
  },
  format: {
    type: Object,
    default: () => ({})
  },
  answerLinesOverride: {
    type: Number,
    default: null
  },
  forceEssay: {
    type: Boolean,
    default: false
  }
})

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
      return renderToString(math, { throwOnError: false })
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

// Render question content with math expressions
const renderedContent = computed(() => {
  if (!props.question?.content?.prompt) return ''

  return renderMath(props.question.content.prompt)
})

const hasOptions = computed(() => {
  const opts = props.question?.content?.options
  return Array.isArray(opts) && opts.length > 0
})

const effectiveHasOptions = computed(() => {
  return !props.forceEssay && hasOptions.value
})

const isMcq = computed(() => {
  return !props.forceEssay && props.question?.type === 'multiple_choice'
})

const renderedOptions = computed(() => {
  if (!effectiveHasOptions.value) return []
  return props.question.content.options.map(opt => renderMath(opt))
})

const imageUrl = computed(() => {
  const img = props.question?.content?.image
  if (!img) return ''
  if (typeof img === 'string') return img
  if (typeof img === 'object' && img.url) return String(img.url)
  return ''
})

const imageStyle = computed(() => {
  const img = props.question?.content?.image
  const cfg = (img && typeof img === 'object') ? img : {}
  const widthPt = Number(cfg.widthPt)
  const topPt = Number(cfg.topPt)
  const rightPt = Number(cfg.rightPt)
  const opacity = Number(cfg.opacity)

  return {
    width: (Number.isFinite(widthPt) ? widthPt : 90) + 'pt',
    top: (Number.isFinite(topPt) ? topPt : 0) + 'pt',
    right: (Number.isFinite(rightPt) ? rightPt : 0) + 'pt',
    opacity: Number.isFinite(opacity) ? String(opacity) : '1'
  }
})

const questionTextStyle = computed(() => {
  const q = props.format?.question || {}
  return {
    fontSize: q.fontSize ? String(q.fontSize) : undefined,
    lineHeight: q.lineHeight ? String(q.lineHeight) : undefined,
    fontWeight: q.bold ? '700' : undefined
  }
})

const questionContentInlineStyle = computed(() => {
  const gap = Number(props.numberingOptions?.inlineGap)
  const gapPt = Number.isFinite(gap) ? gap : 8
  return {
    ...questionTextStyle.value,
    gap: gapPt + 'pt'
  }
})

const mcqInlineStyle = computed(() => {
  const gap = Number(props.numberingOptions?.inlineGap)
  const gapPt = Number.isFinite(gap) ? gap : 8
  return {
    '--inline-gap': gapPt + 'pt'
  }
})

const optionsTextStyle = computed(() => {
  const o = props.format?.options || {}
  return {
    fontSize: o.fontSize ? String(o.fontSize) : undefined,
    lineHeight: o.lineHeight ? String(o.lineHeight) : undefined,
    fontWeight: o.bold ? '700' : undefined
  }
})

function optionLabel(idx) {
  const charCode = 'A'.charCodeAt(0) + idx
  return String.fromCharCode(charCode) + ') '
}

// Calculate answer lines based on question type and marks
const answerLines = computed(() => {
  if (!props.question) return 3

  if (typeof props.answerLinesOverride === 'number' && Number.isFinite(props.answerLinesOverride)) {
    return Math.max(0, Math.floor(props.answerLinesOverride))
  }
  
  // More lines for higher mark questions
  const baseLines = 3
  const extraLines = Math.floor(props.question.marks / 2)
  return baseLines + extraLines
})
</script>

<style scoped>
.question-display {
  position: relative;
  margin-bottom: 24pt;
  page-break-inside: avoid;
}

.question-fly-image {
  position: absolute;
  z-index: 1;
  pointer-events: none;
}

.question-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8pt;
  font-weight: bold;
}

.question-header :deep(.question-number) {
  color: #333;
}

.question-content-inline {
  display: flex;
  align-items: flex-start;
  margin-bottom: 12pt;
  line-height: 1.5;
}

.question-number-inline {
  white-space: nowrap;
}

.question-text-inline {
  flex: 1;
}

.question-marks {
  color: #666;
  font-size: 0.9em;
}

.question-content {
  margin-bottom: 12pt;
  line-height: 1.5;
}

.question-content :deep(.katex) {
  font-size: 1em;
}

.question-content :deep(.katex-display) {
  margin: 8pt 0;
}

.question-options {
  margin: 8pt 0 12pt;
  display: flex;
  flex-direction: column;
  gap: 6pt;
}

.option {
  display: flex;
  align-items: flex-start;
  gap: 8pt;
}

.option-label {
  min-width: 24px;
  font-weight: 600;
  color: #333;
}

.option-text {
  line-height: 1.4;
}

.answer-area {
  border-top: 1px solid #ddd;
  padding-top: 8pt;
  margin-top: 12pt;
}

.answer-lines {
  display: flex;
  flex-direction: column;
  gap: 8pt;
}

.answer-line {
  height: 20pt;
  border-bottom: 1px solid #eee;
}
</style>
