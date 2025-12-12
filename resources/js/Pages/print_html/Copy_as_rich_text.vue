<template>
  <div>
    <div ref="contentToCopy" contenteditable="true" class="copy-content">
      <h1>Sample Title</h1>
      <p>This is a <strong>bold</strong> and <em>italic</em> text.</p>
      <ul>
        <li>First item</li>
        <li>Second item</li>
      </ul>
    </div>
    <button @click="copyToClipboard">Copy to Clipboard</button>
  </div>
</template>

<script setup>
import { ref } from "vue";

const contentToCopy = ref(null);

const copyToClipboard = async () => {
  try {
    const htmlContent = contentToCopy.value.innerHTML;

    // Create a Blob containing HTML content
    const blob = new Blob([htmlContent], { type: "text/html" });
    const clipboardItem = new ClipboardItem({ "text/html": blob });

    // Use Clipboard API
    await navigator.clipboard.write([clipboardItem]);

    alert("Copied successfully! Now paste it into Word.");
  } catch (err) {
    console.error("Failed to copy:", err);
  }
};
</script>

<style>
.copy-content {
  border: 1px solid #ccc;
  padding: 10px;
  width: 300px;
  margin-bottom: 10px;
}
</style>
