<!-- This file was previously named 9.vue - A card that reveals content on hover -->
<style scoped>
  .reveal-card {
    width: 300px;
    height: 200px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    overflow: hidden;
    position: relative;
    transition: all 0.3s ease;
  }

  .front-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: #3498db;
    color: white;
  }

  .back-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: #2ecc71;
    color: white;
    transform: rotateX(90deg);
    transition: transform 0.5s ease;
  }

  .reveal-card:hover .front-content {
    transform: rotateX(90deg);
  }

  .reveal-card:hover .back-content {
    transform: rotateX(0);
  }
</style>

<template>
  <div class="reveal-card">
    <div class="front-content">
      <h3>Hover Over Me</h3>
    </div>
    <div class="back-content">
      <h3>Revealed Content!</h3>
    </div>
  </div>
</template>