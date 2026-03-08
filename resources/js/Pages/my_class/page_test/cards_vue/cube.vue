<template>
  <div class="container">
    <div class="cube">
      <div style="--x:-1; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div style="--x:0; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div style="--x:1; --y:0;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
      <div style="--x:0; --y:1;">
        <span style="--i:3;"></span>
        <span style="--i:2;"></span>
        <span style="--i:1;"></span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CubeComponent'
}
</script>

<style scoped>
/* Add appropriate styles for the cube */
.container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: #000;
}

.cube {
  position: relative;
  width: 300px;
  height: 300px;
  transform-style: preserve-3d;
  animation: animate 4s linear infinite;
}

.cube div {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  transform: rotateY(calc(var(--x) * 90deg)) rotateX(calc(var(--y) * 90deg));
  transform-origin: center;
}

.cube div span {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(#fff, #ff0000);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 0 20px rgba(255, 0, 0, 0.5),
              inset 0 0 20px rgba(255, 0, 0, 0.5);
  --clip: polygon(0 0, 
                  calc((var(--i) + 0)/4 * 100%) 0, 
                  calc((var(--i) + 0)/4 * 100%) calc((var(--i) + 0)/4 * 100%), 
                  0 calc((var(--i) + 0)/4 * 100%));
  clip-path: var(--clip);
  animation: rotateZ 4s linear infinite reverse;
}

@keyframes animate {
  0% {
    transform: perspective(1000px) rotateX(-25deg) rotateY(45deg);
  }
  100% {
    transform: perspective(1000px) rotateX(-25deg) rotateY(405deg);
  }
}

@keyframes rotateZ {
  0% {
    transform: rotateZ(0deg);
  }
  100% {
    transform: rotateZ(360deg);
  }
}
</style>