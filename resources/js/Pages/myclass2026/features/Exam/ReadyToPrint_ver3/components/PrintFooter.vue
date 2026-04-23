<template>
  <div
    v-if="enabled && hasContent"
    class="print-footer"
    :class="{
      'print-footer--single-line': singleLine,
      'print-footer--top-border': showTopBorder,
      'print-footer--print-mode': printMode
    }"
    :style="wrapperStyles"
  >
    <template v-if="singleLine">
      <div
        class="footer-content footer-content--row"
        :style="{ alignItems: rowAlignItems }"
      >
        <div
          v-if="footerHtml"
          class="footer-left footer-text"
          v-html="footerHtml"
        />

        <span
          v-if="footerHtml && showPageNumbers"
          class="footer-spacer"
          aria-hidden="true"
        />

        <div
          v-if="showPageNumbers"
          class="footer-right page-number"
          :style="pageNumberStyles"
          :data-format="pageNumberFormat"
          :data-total-pages="resolvedTotalPages"
        >
          <template v-if="!printMode">
            {{ formattedPageNumber }}
          </template>

          <template v-else>
            <span
              v-if="formattedPageNumber.prefix"
              class="page-number-prefix"
            >
              {{ formattedPageNumber.prefix }}
            </span>
            <span class="page-number-value" data-page-current />
            <span
              v-if="formattedPageNumber.mid"
              class="page-number-sep"
            >
              {{ formattedPageNumber.mid }}
            </span>
            <span
              v-if="formattedPageNumber.showTotal"
              class="page-number-total"
              data-page-total
              :data-total-pages="resolvedTotalPages"
            />
            <span
              v-if="formattedPageNumber.suffix"
              class="page-number-suffix"
            >
              {{ formattedPageNumber.suffix }}
            </span>
          </template>
        </div>
      </div>
    </template>

    <template v-else>
      <div class="footer-content footer-content--stack">
        <div
          v-if="footerHtml"
          class="footer-text"
          v-html="footerHtml"
        />

        <div
          v-if="showPageNumbers"
          class="page-number"
          :style="pageNumberStyles"
          :data-format="pageNumberFormat"
          :data-total-pages="resolvedTotalPages"
        >
          <template v-if="!printMode">
            {{ formattedPageNumber }}
          </template>

          <template v-else>
            <span
              v-if="formattedPageNumber.prefix"
              class="page-number-prefix"
            >
              {{ formattedPageNumber.prefix }}
            </span>
            <span class="page-number-value" data-page-current />
            <span
              v-if="formattedPageNumber.mid"
              class="page-number-sep"
            >
              {{ formattedPageNumber.mid }}
            </span>
            <span
              v-if="formattedPageNumber.showTotal"
              class="page-number-total"
              data-page-total
              :data-total-pages="resolvedTotalPages"
            />
            <span
              v-if="formattedPageNumber.suffix"
              class="page-number-suffix"
            >
              {{ formattedPageNumber.suffix }}
            </span>
          </template>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  enabled: {
    type: Boolean,
    default: true
  },
  footerHtml: {
    type: String,
    default: ''
  },
  showPageNumbers: {
    type: Boolean,
    default: false
  },
  pageNumberFormat: {
    type: String,
    default: 'page',
    validator: (v) => ['page', 'page-of', 'page-slash', 'fraction'].includes(v)
  },
  pageNumberFontSize: {
    type: Number,
    default: 10
  },
  pageNumberColor: {
    type: String,
    default: '#555555'
  },
  singleLine: {
    type: Boolean,
    default: true
  },
  singleLineTopAlign: {
    type: Boolean,
    default: false
  },
  showTopBorder: {
    type: Boolean,
    default: false
  },
  pageNumberPosition: {
    type: String,
    default: 'bottom-right',
    validator: (v) => [
      'bottom-left',
      'bottom-center',
      'bottom-right',
      'top-left',
      'top-center',
      'top-right'
    ].includes(v)
  },
  previewCurrentPage: {
    type: Number,
    default: 1
  },
  previewTotalPages: {
    type: Number,
    default: 1
  },
  currentPage: {
    type: Number,
    default: null
  },
  totalPages: {
    type: Number,
    default: null
  },
  printMode: {
    type: Boolean,
    default: false
  },
  bottomOffsetMm: {
    type: Number,
    default: 0
  },
  bottomOffset: {
    type: Number,
    default: null
  },
  applyOffsetToPageNumbers: {
    type: Boolean,
    default: false
  }
})

const resolvedBottomOffsetMm = computed(() => {
  if (Number.isFinite(props.bottomOffset)) return props.bottomOffset
  if (Number.isFinite(props.bottomOffsetMm)) return props.bottomOffsetMm
  return 0
})

const resolvedCurrentPage = computed(() => {
  if (Number.isFinite(props.currentPage) && props.currentPage > 0) return props.currentPage
  if (Number.isFinite(props.previewCurrentPage) && props.previewCurrentPage > 0) return props.previewCurrentPage
  return 1
})

const resolvedTotalPages = computed(() => {
  if (Number.isFinite(props.totalPages) && props.totalPages > 0) return props.totalPages
  if (Number.isFinite(props.previewTotalPages) && props.previewTotalPages > 0) return props.previewTotalPages
  return 1
})

const formattedPageNumber = computed(() => {
  if (!props.showPageNumbers) return null

  const cur = resolvedCurrentPage.value
  const tot = resolvedTotalPages.value

  if (props.printMode) {
    switch (props.pageNumberFormat) {
      case 'page-of':
        return { prefix: '', mid: ' of ', suffix: '', showTotal: true }
      case 'page-slash':
        return { prefix: 'Page ', mid: ' / ', suffix: '', showTotal: true }
      case 'fraction':
        return { prefix: '', mid: '/', suffix: '', showTotal: true }
      case 'page':
      default:
        return { prefix: '', mid: '', suffix: '', showTotal: false }
    }
  }

  switch (props.pageNumberFormat) {
    case 'page-of':
      return `${cur} of ${tot}`
    case 'page-slash':
      return `Page ${cur} / ${tot}`
    case 'fraction':
      return `${cur}/${tot}`
    case 'page':
    default:
      return `${cur}`
  }
})

const wrapperStyles = computed(() => {
  const styles = {}

  if (resolvedBottomOffsetMm.value !== 0) {
    styles.marginBottom = `${resolvedBottomOffsetMm.value}mm`
  }

  return styles
})

const rowAlignItems = computed(() => (
  props.singleLineTopAlign ? 'flex-start' : 'center'
))

const pageNumberStyles = computed(() => {
  const styles = {
    fontSize: `${props.pageNumberFontSize}pt`,
    color: props.pageNumberColor,
    whiteSpace: 'nowrap',
    lineHeight: '1.2',
    '--page-number-color': props.pageNumberColor
  }

  if (props.applyOffsetToPageNumbers && resolvedBottomOffsetMm.value !== 0) {
    styles.marginBottom = `${resolvedBottomOffsetMm.value}mm`
  }

  if (!props.singleLine) {
    styles.position = 'absolute'

    const [vertical, horizontal] = props.pageNumberPosition.split('-')
    styles[vertical] = '0'

    if (horizontal === 'left') {
      styles.left = '0'
      styles.textAlign = 'left'
    }

    if (horizontal === 'center') {
      styles.left = '50%'
      styles.transform = 'translateX(-50%)'
      styles.textAlign = 'center'
    }

    if (horizontal === 'right') {
      styles.right = '0'
      styles.textAlign = 'right'
    }
  }

  return styles
})

const hasContent = computed(() => (
  (props.footerHtml && props.footerHtml.trim() !== '') || props.showPageNumbers
))
</script>

<style scoped>
.print-footer {
  width: 100%;
  box-sizing: border-box;
  padding: 2px 0;
  font-family: inherit;
  page-break-inside: avoid;
  break-inside: avoid;
}

.print-footer--top-border {
  border-top: 1px solid #d0d0d0;
  padding-top: 4px;
}

.footer-content--row {
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  width: 100%;
  gap: 8px;
}

.footer-content--stack {
  position: relative;
  width: 100%;
}

.footer-left {
  flex: 1;
  min-width: 0;
}

.footer-right {
  flex-shrink: 0;
}

.footer-spacer {
  flex: 1;
}

.footer-text {
  min-width: 0;
  overflow: hidden;
  font-size: inherit;
  line-height: 1.3;
}

.footer-text :deep(p) {
  margin: 0;
  padding: 0;
}

.page-number {
  display: inline-flex;
  align-items: center;
  gap: 0;
  flex-shrink: 0;
  user-select: none;
  font-variant-numeric: tabular-nums;
  font-feature-settings: 'tnum' 1;
}

@media print {
  .print-footer--print-mode .page-number {
    color: transparent !important;
  }

  .print-footer--print-mode .page-number::after {
    color: var(--page-number-color, #000000);
  }

  .print-footer--print-mode .page-number-value::before {
    content: counter(page);
  }

  .print-footer--print-mode .page-number-total::before {
    content: attr(data-total-pages);
  }
}
</style>
