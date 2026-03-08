<!-- This file was previously named 8.vue - An interactive menu card -->
<style scoped>
  /* Based on the pattern of the project, this likely contains an interactive menu card */
  .menu-card {
    width: 300px;
    height: 200px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    padding: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
  }

  .menu-toggle {
    width: 50px;
    height: 50px;
    background: #3498db;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    margin-bottom: 20px;
  }

  .menu-items {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
</style>

<template>
  <div class="menu-card">
    <div class="menu-toggle">☰</div>
    <div class="menu-items">
      <div>Home</div>
      <div>About</div>
      <div>Contact</div>
    </div>
  </div>
</template>