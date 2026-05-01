<template>
  <span v-html="renderedContent" class="text-renderer"></span>
</template>

<script setup>
import { computed } from 'vue'
import { renderToString } from 'katex'

const props = defineProps({
  content: {
    type: [String, Number],
    default: ''
  },
  enableMath: {
    type: Boolean,
    default: true
  },
  enableHtml: {
    type: Boolean,
    default: true
  },
  enableMarkdown: {
    type: Boolean,
    default: false
  },
  stripCitations: {
    type: Boolean,
    default: true
  }
})

const katexConfig = {
  throwOnError: false,
  macros: {
    "\\text": "\\text"
  }
}

const renderedContent = computed(() => {
  if (!props.content) return ''

  let text = String(props.content)

  // Strip citations if enabled
  if (props.stripCitations) {
    text = text.replace(/\[\s*cite\s*:\s*\d+\s*\]/gi, '')
  }

  // Normalize whitespace
  text = text.replace(/\s{2,}/g, ' ').trim()

  // Render LaTeX math if enabled
  if (props.enableMath) {
    // Display math ($$...$$)
    text = text.replace(/\$\$([^$]+)\$\$/g, (match, math) => {
      try {
        return renderToString(math, { ...katexConfig, displayMode: true })
      } catch (e) {
        return match
      }
    })

    // Inline math ($...$)
    text = text.replace(/\$([^$]+)\$/g, (match, math) => {
      try {
        return renderToString(math, katexConfig)
      } catch (e) {
        return match
      }
    })
  }

  // Render markdown if enabled (basic support)
  if (props.enableMarkdown) {
    // Bold: **text** or __text__
    text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
    text = text.replace(/__([^_]+)__/g, '<strong>$1</strong>')

    // Italic: *text* or _text_
    text = text.replace(/\*([^*]+)\*/g, '<em>$1</em>')
    text = text.replace(/_([^_]+)_/g, '<em>$1</em>')

    // Strikethrough: ~~text~~
    text = text.replace(/~~([^~]+)~~/g, '<del>$1</del>')

    // Code: `text`
    text = text.replace(/`([^`]+)`/g, '<code>$1</code>')
  }

  // HTML is already in the string, just return it
  // If HTML is disabled, we would escape it here

  return text
})
</script>

<style scoped>
.text-renderer :deep(.katex) {
  font-size: 1em;
}

.text-renderer :deep(.katex-display) {
  margin: 8pt 0;
}

.text-renderer :deep(code) {
  background: #f5f5f5;
  padding: 2px 4px;
  border-radius: 3px;
  font-family: 'Courier New', monospace;
  font-size: 0.9em;
}
</style>
