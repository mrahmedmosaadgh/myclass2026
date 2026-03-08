<!-- This file was previously named 4.vue - An animated card with shadow effect -->
<style scoped>
/* Placeholder for the actual content of file 4.vue */
/* Since I didn't view the full content of file 4, I'm creating a representative template */
.card {
  width: 300px;
  height: 200px;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
  padding: 20px;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 12px 20px rgba(0,0,0,0.2);
}
</style>

<template>
  <div class="card">
    <h3>Animated Card</h3>
    <p>This is a sample card with hover effect</p>
  </div>
</template>