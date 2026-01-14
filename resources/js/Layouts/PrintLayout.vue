<template>
  <div class="print-layout">
    <slot />
  </div>
</template>

<script setup>
// Minimal layout for printing
</script>

<style>
/* Global print styles for this layout */
html, body, #app {
  height: auto !important;
  min-height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  width: 100% !important;
  background-color: white !important;
  color: black !important;
}

@media print {
  @page {
    margin: 1cm;
    size: auto;
  }
}
</style>
