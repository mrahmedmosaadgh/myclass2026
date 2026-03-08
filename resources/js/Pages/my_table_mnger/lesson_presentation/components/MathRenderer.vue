<template>
  <span class="math-renderer" v-html="renderedContent"></span>
</template>

<script setup>
import { computed } from 'vue';
import katex from 'katex';

const props = defineProps({
  content: {
    type: String,
    default: ''
  },
  inlineDelimiter: {
    type: String,
    default: '$'
  },
  displayDelimiter: {
    type: String,
    default: '$$'
  }
});

const renderedContent = computed(() => {
  if (!props.content) return '';

  // Escape special regex characters in delimiters
  const escapeRegex = (string) => string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  const inline = escapeRegex(props.inlineDelimiter);
  const display = escapeRegex(props.displayDelimiter);

  // Fixed regex to properly handle consecutive inline math expressions
  let html = props.content;
  
  // First handle display math ($$...$$)
  html = html.replace(new RegExp(`${display}(.*?)${display}`, 'g'), (match, content) => {
    try {
      return katex.renderToString(content, {
        throwOnError: false,
        displayMode: true
      });
    } catch (e) {
      return `<span class="text-red-500">Error: ${e.message}</span>`;
    }
  });

  // Then handle inline math ($...$) - this prevents conflicts with display math
  html = html.replace(new RegExp(`${inline}(.*?)${inline}`, 'g'), (match, content) => {
    // Check if this is actually part of a double delimiter (display math that was already processed)
    if (match.startsWith(props.inlineDelimiter) && match.endsWith(props.inlineDelimiter)) {
      // Verify this isn't part of a double-delimiter sequence by checking context
      const matchStart = arguments[arguments.length - 2];
      const prevChar = matchStart > 0 ? props.content.charAt(matchStart - 1) : '';
      const nextChar = matchStart + match.length < props.content.length ? props.content.charAt(matchStart + match.length) : '';
      
      // If it was already processed as display math, skip it
      if ((prevChar === props.inlineDelimiter || nextChar === props.inlineDelimiter)) {
        return match; // Return as-is, it was already processed
      }
    }

    try {
      return katex.renderToString(content, {
        throwOnError: false,
        displayMode: false
      });
    } catch (e) {
      return `<span class="text-red-500">Error: ${e.message}</span>`;
    }
  });

  return html;
});

const escapeHtml = (text) => {
  return text
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
};
</script>

<style>
.math-renderer .katex {
  font-size: 1.1em;
}
</style>
