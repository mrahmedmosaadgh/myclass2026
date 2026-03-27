<template>
  <div class="standalone-layout">
    <slot />
  </div>
</template>

<style scoped>
.standalone-layout {
  min-height: 100vh;
  background:
    radial-gradient(circle at top, rgba(34, 197, 94, 0.12), transparent 40%),
    linear-gradient(180deg, #0a0a0a 0%, #000000 100%);
  color: #f0fdf4;
  text-shadow: 0 0 8px rgba(74, 222, 128, 0.15);
}
</style>
