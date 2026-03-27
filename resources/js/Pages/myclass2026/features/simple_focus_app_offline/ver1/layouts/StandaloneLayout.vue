<template>
  <div class="standalone-layout">
    <slot />
  </div>
</template>

<style scoped>
.standalone-layout {
  min-height: 100vh;
  background:
    radial-gradient(circle at top, rgba(34, 197, 94, 0.08), transparent 30%),
    linear-gradient(180deg, #040404 0%, #000000 100%);
  color: #d1fae5;
}
</style>
