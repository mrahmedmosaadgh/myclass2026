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

  // Regex to match display math ($$...$$) or inline math ($...$)
  // We prioritize display math first
  const regex = new RegExp(`${display}([\\s\\S]*?)${display}|${inline}([\\s\\S]*?)${inline}`, 'g');

  let lastIndex = 0;
  let html = '';
  let match;

  while ((match = regex.exec(props.content)) !== null) {
    // Add text before the match
    const textBefore = props.content.slice(lastIndex, match.index);
    html += escapeHtml(textBefore);

    const displayMath = match[1];
    const inlineMath = match[2];

    try {
      if (displayMath) {
        html += katex.renderToString(displayMath, {
          throwOnError: false,
          displayMode: true
        });
      } else if (inlineMath) {
        html += katex.renderToString(inlineMath, {
          throwOnError: false,
          displayMode: false
        });
      }
    } catch (e) {
      html += `<span class="text-red-500">Error: ${e.message}</span>`;
    }

    lastIndex = regex.lastIndex;
  }

  // Add remaining text
  html += escapeHtml(props.content.slice(lastIndex));

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
