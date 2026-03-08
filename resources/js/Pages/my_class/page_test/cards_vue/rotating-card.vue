<template>
  <div class="container">
    <div class="rotating-card">
      <div class="card-face" style="--x:-1; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div class="card-face" style="--x:0; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div class="card-face" style="--x:1; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div class="card-face" style="--x:0; --y:1;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RotatingCard'
}
</script>

<style scoped>
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #1a1a1a;
}

.rotating-card {
  position: relative;
  width: 280px;
  height: 280px;
  transform-style: preserve-3d;
  animation: rotateAnimation 6s linear infinite;
}

.card-face {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transform: rotateY(calc(var(--x) * 90deg)) rotateX(calc(var(--y) * 90deg));
  transform-origin: center;
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.card-face span {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, #ff8c00, #ffd700);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 0 25px rgba(255, 140, 0, 0.7),
              inset 0 0 15px rgba(255, 255, 255, 0.3);
  --clip: polygon(0 0, 
                  calc((var(--i) + 0)/4 * 100%) 0, 
                  calc((var(--i) + 0)/4 * 100%) calc((var(--i) + 0)/4 * 100%), 
                  0 calc((var(--i) + 0)/4 * 100%));
  clip-path: var(--clip);
  animation: rotateSpan 5s linear infinite reverse;
}

@keyframes rotateAnimation {
  0% {
    transform: perspective(1200px) rotateX(-30deg) rotateY(25deg);
  }
  100% {
    transform: perspective(1200px) rotateX(-30deg) rotateY(385deg);
  }
}

@keyframes rotateSpan {
  0% {
    transform: rotateZ(0deg);
  }
  100% {
    transform: rotateZ(360deg);
  }
}
</style>