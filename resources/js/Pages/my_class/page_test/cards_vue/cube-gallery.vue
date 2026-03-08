<template>
  <div class="gallery-container">
    <div class="cube-wrapper">
      <div class="cube-main">
        <div class="face" style="--x:-1; --y:0;">
          <span style="--i:3;"></span>
          <span style="--i:2;"></span>
          <span style="--i:1;"></span>
        </div>
        <div class="face" style="--x:0; --y:0;">
          <span style="--i:3;"></span>
          <span style="--i:2;"></span>
          <span style="--i:1;"></span>
        </div>
        <div class="face" style="--x:1; --y:0;">
          <span style="--i:3;"></span>
          <span style="--i:2;"></span>
          <span style="--i:1;"></span>
        </div>
        <div class="face" style="--x:0; --y:1;">
          <span style="--i:3;"></span>
          <span style="--i:2;"></span>
          <span style="--i:1;"></span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CubeGallery'
}
</script>

<style scoped>
.gallery-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #1a2a6c, #b21f1f, #1a2a6c);
  overflow: hidden;
}

.cube-wrapper {
  position: relative;
  width: 320px;
  height: 320px;
}

.cube-main {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  animation: rotateCube 8s linear infinite;
}

.face {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transform: rotateY(calc(var(--x) * 90deg)) rotateX(calc(var(--y) * 90deg));
  transform-origin: center;
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.face::before {
  content: '';
  position: absolute;
  top: 8px;
  left: 8px;
  right: 8px;
  bottom: 8px;
  background: rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.1);
  z-index: 1;
}

.face span {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(45deg, #00c9ff, #92fe9d);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 0 30px rgba(0, 201, 255, 0.6),
              inset 0 0 20px rgba(255, 255, 255, 0.4);
  --clip: polygon(0 0, 
                  calc((var(--i) + 0)/4 * 100%) 0, 
                  calc((var(--i) + 0)/4 * 100%) calc((var(--i) + 0)/4 * 100%), 
                  0 calc((var(--i) + 0)/4 * 100%));
  clip-path: var(--clip);
  animation: rotateFace 6s linear infinite;
  z-index: 2;
}

@keyframes rotateCube {
  0% {
    transform: perspective(1500px) rotateX(-20deg) rotateY(0deg);
  }
  100% {
    transform: perspective(1500px) rotateX(-20deg) rotateY(360deg);
  }
}

@keyframes rotateFace {
  0% {
    transform: rotateZ(0deg);
  }
  100% {
    transform: rotateZ(360deg);
  }
}
</style>