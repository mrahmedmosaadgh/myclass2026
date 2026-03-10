<script setup>
import Gum1Btn from '@/Components/Gum1Btn.vue';
</script>

<style scoped>
  .card {
    display: grid;
    place-content: center;
    width: 100%;
    height: 100%;
    background: #e7e7e7;
    font-family: "Quicksand", sans-serif;
    -webkit-tap-highlight-color: transparent;
    padding: 3rem;
    border-radius: 12px;
  }

  .clay-slab {
    max-width: 333px;
    padding: 1rem;
    border-radius: 40px;
    background: #e7e7e7;
    box-shadow:
      inset 6px 6px 12px #c5c5c5,
      inset -6px -6px 12px #ffffff;
  }

  .container-items {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
  }
</style>

<template>
  <div class="card">
    <div class="clay-slab">
      <div class="container-items">
        <Gum1Btn color="#e11d48" />
        <Gum1Btn color="#f472b6" />
        <Gum1Btn color="#fb923c" />
        <Gum1Btn color="#facc15" />
        <Gum1Btn color="#84cc16" />
        <Gum1Btn color="#10b981" />
        <Gum1Btn color="#0ea5e9" />
        <Gum1Btn color="#3b82f6" />
        <Gum1Btn color="#8b5cf6" />
        <Gum1Btn color="#a78bfa" />
      </div>
    </div>
  </div>
</template>
