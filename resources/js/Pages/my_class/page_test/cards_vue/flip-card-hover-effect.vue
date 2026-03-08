<!-- This file was previously named 2.vue - A flip card hover effect -->
<style scoped>
/* CSS for flip card effect would be here */
/* Since the actual content wasn't fully viewed, I'll copy the original content */
.card {
  width: 200px;
  height: 200px;
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  perspective: 1000px;
  margin: 20px;
}

.flip-card {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.8s;
  transform-style: preserve-3d;
}

.card:hover .flip-card {
  transform: rotateY(180deg);
}

.flip-front,
.flip-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 30px;
  font-weight: bold;
}

.flip-back {
  transform: rotateY(180deg);
}
</style>

<template>
  <div class="card">
    <div class="flip-card">
      <div class="flip-front">Front</div>
      <div class="flip-back">Back</div>
    </div>
  </div>
</template>