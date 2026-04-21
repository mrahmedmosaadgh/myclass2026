<template>
  <div 
    class="print-footer" 
    :style="footerStyles"
  >
    <div class="footer-content" :style="contentStyles">
      <!-- Left side: Footer text/content -->
      <div class="footer-left" :style="leftStyles">
        <slot name="left-content">
          <div v-if="footerText" class="footer-text" v-html="footerText"></div>
        </slot>
      </div>
      
      <!-- Center: Optional center content -->
      <div class="footer-center" :style="centerStyles" v-if="hasCenterContent">
        <slot name="center-content"></slot>
      </div>
      
      <!-- Right side: Page numbers -->
      <div class="footer-right" :style="rightStyles">
        <slot name="right-content">
          <div 
            v-if="showPageNumbers" 
            class="page-number" 
            :style="pageNumberStyles"
          >
            <span class="page-number-content" :data-format="pageNumberFormat">
              {{ displayPageNumber }}
            </span>
          </div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  // Footer positioning
  bottomOffset: {
    type: Number,
    default: 0
  },
  applyOffsetToPageNumbers: {
    type: Boolean,
    default: false
  },
  
  // Footer content
  footerText: {
    type: String,
    default: ''
  },
  
  // Page number settings
  showPageNumbers: {
    type: Boolean,
    default: true
  },
  pageNumberPosition: {
    type: String,
    default: 'bottom-right',
    validator: (value) => [
      'bottom-left', 'bottom-center', 'bottom-right',
      'top-left', 'top-center', 'top-right'
    ].includes(value)
  },
  pageNumberFormat: {
    type: String,
    default: 'page'
  },
  pageNumberFontSize: {
    type: Number,
    default: 10
  },
  pageNumberColor: {
    type: String,
    default: '#000000'
  },
  currentPage: {
    type: Number,
    default: 1
  },
  totalPages: {
    type: Number,
    default: 1
  },
  
  // Layout options
  singleLine: {
    type: Boolean,
    default: true
  },
  alignment: {
    type: String,
    default: 'space-between',
    validator: (value) => ['left', 'center', 'right', 'space-between', 'space-around'].includes(value)
  },
  
  // Styling
  height: {
    type: String,
    default: 'auto'
  },
  padding: {
    type: String,
    default: '6mm 12mm'
  },
  backgroundColor: {
    type: String,
    default: 'transparent'
  },
  showTopBorder: {
    type: Boolean,
    default: false
  },
  borderColor: {
    type: String,
    default: '#000000'
  }
})

const emit = defineEmits(['page-change'])

// Computed styles
const footerStyles = computed(() => {
  const styles = {
    position: 'fixed',
    bottom: props.bottomOffset + 'mm',
    left: 0,
    right: 0,
    zIndex: 999,
    backgroundColor: props.backgroundColor,
    overflow: 'hidden',
    boxSizing: 'border-box',
    padding: 0
  }
  
  if (props.showTopBorder) {
    styles.borderTop = `1px solid ${props.borderColor}`
  }
  
  return styles
})

const contentStyles = computed(() => {
  if (props.singleLine) {
    return {
      display: 'flex',
      alignItems: 'center',
      justifyContent: props.alignment,
      gap: '12px',
      padding: props.padding
    }
  } else {
    return {
      padding: props.padding
    }
  }
})

const leftStyles = computed(() => {
  if (props.singleLine) {
    return {
      flex: '1',
      minWidth: 0
    }
  } else {
    return {
      textAlign: 'left'
    }
  }
})

const centerStyles = computed(() => {
  if (props.singleLine) {
    return {
      flex: '0 0 auto',
      textAlign: 'center'
    }
  } else {
    return {
      textAlign: 'center',
      margin: '8px 0'
    }
  }
})

const rightStyles = computed(() => {
  if (props.singleLine) {
    return {
      flex: '0 0 auto',
      whiteSpace: 'nowrap',
      textAlign: 'right'
    }
  } else {
    return {
      textAlign: 'right'
    }
  }
})

const pageNumberStyles = computed(() => {
  const baseStyles = {
    fontSize: props.pageNumberFontSize + 'pt',
    color: props.pageNumberColor
  }
  
  if (!props.singleLine) {
    const position = props.pageNumberPosition.includes('top') ? 'top' : 'bottom'
    const positionStyles = {
      'bottom-left': { textAlign: 'left', left: '12mm', right: 'auto' },
      'bottom-center': { textAlign: 'center', left: 0, right: 0 },
      'bottom-right': { textAlign: 'right', right: '12mm', left: 'auto' },
      'top-left': { textAlign: 'left', left: '12mm', right: 'auto', top: 0 },
      'top-center': { textAlign: 'center', left: 0, right: 0, top: 0 },
      'top-right': { textAlign: 'right', right: '12mm', left: 'auto', top: 0 }
    }
    
    return {
      ...baseStyles,
      position: 'absolute',
      [position]: '0',
      ...positionStyles[props.pageNumberPosition],
      ...(props.applyOffsetToPageNumbers && props.bottomOffset !== 0 ? { [position]: props.bottomOffset + 'mm' } : {})
    }
  }
  
  return baseStyles
})

// Computed properties
const hasCenterContent = computed(() => !!props.$slots['center-content'])
const displayPageNumber = computed(() => {
  switch (props.pageNumberFormat) {
    case 'page':
      return `Page ${props.currentPage}`
    case 'page-of':
      return `Page ${props.currentPage} of ${props.totalPages}`
    case 'page-slash':
      return `Page ${props.currentPage} / ${props.totalPages}`
    case 'fraction':
      return `${props.currentPage}/${props.totalPages}`
    default:
      return `Page ${props.currentPage}`
  }
})

// Methods
function updatePageNumber(page) {
  emit('page-change', page)
}

defineExpose({
  updatePageNumber
})
</script>

<style scoped>
.print-footer {
  font-family: Arial, sans-serif;
  font-size: 10pt;
  line-height: 1.4;
}

.footer-content {
  width: 100%;
  box-sizing: border-box;
}

.footer-text {
  font-size: 10pt;
  color: #000000;
}

.page-number {
  font-weight: normal;
}

.page-number-content {
  /* Page number content styling */
}

@media print {
  .print-footer {
    /* Ensure footer prints correctly */
  }
}
</style>
