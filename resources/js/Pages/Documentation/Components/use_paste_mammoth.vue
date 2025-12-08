<script setup>
import { ref } from "vue";

const pastedContent = ref("");

const handlePaste = (event) => {
  event.preventDefault();
  const clipboardData = event.clipboardData || window.clipboardData;
  const html = clipboardData.getData("text/html") || clipboardData.getData("text/plain");

  if (html) {
    pastedContent.value = cleanWordHtml(html);
  } else {
    pastedContent.value = clipboardData.getData("text/plain");
  }
};

// Function to clean Word formatting
const cleanWordHtml = (html) => {
  const doc = new DOMParser().parseFromString(html, "text/html");

  // Remove Word-specific styles
  doc.querySelectorAll("style, meta, link, [class], [style]").forEach((el) => el.remove());

  return doc.body.innerHTML.trim();
};
</script>

<template>
  <div>
    <h2>Paste from Word Below</h2>
    <div
      contenteditable="true"
      @paste="handlePaste"
      style="border: 1px solid #ccc; padding: 10px; min-height: 100px;"
    >
      <p v-html="pastedContent"></p>
    </div>
  </div>
</template>
