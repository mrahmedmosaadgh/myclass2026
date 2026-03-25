<template>
  <div class="neon-wrapper">
    <h1 class="neon-text" :class="ani_class" :style="neonStyle">{{ text }}</h1>
  </div>
</template>

<script setup>
import { computed } from 'vue'

// Define props with defaults
const props = defineProps({
  text: {
    type: String,
    default: 'NEON GLOW'
  },
  colorA: {
    type: String,
    default: '#00d4ff' // Pink glow
    // default: '#ff005e' // Pink glow
  },
  colorB: {
    type: String,
    default: '#00d4ff' // Blue glow
  },
  ani_class: {
    type: String,
    default: 'neon-text' // Animation class
  }
})

// Computed style for dynamic neon colors
const neonStyle = computed(() => ({
  '--neon-color-a': props.colorA,
  '--neon-color-b': props.colorB
}))
</script>

<style  >
.neon-wrapper {
  /* display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  font-family: 'Arial', sans-serif;
  overflow: hidden; */
  /* background: #000; */
}

.neon-text {
   
  color: #fff;
  text-shadow:
    0 0 5px var(--neon-color-a),
    0 0 10px var(--neon-color-a),
    0 0 20px var(--neon-color-a),
    0 0 40px var(--neon-color-a),
    0 0 80px var(--neon-color-a);
  animation: glow 0.5s   alternate  ;
}
.neon-text-on {
  /* font-size: 4rem; */
  color: #fff;
  text-shadow:
    0 0 5px var(--neon-color-a),
    0 0 10px var(--neon-color-a),
    0 0 20px var(--neon-color-a),
    0 0 40px var(--neon-color-a),
    0 0 80px var(--neon-color-a);
  animation: glow 0.3s   infinite  ;
}

 
neon-text-on-hover {
  /* font-size: 4rem; */
  color: #fff;
  text-shadow:
    0 0 5px var(--neon-color-a),
    0 0 10px var(--neon-color-a),
    0 0 20px var(--neon-color-a),
    0 0 40px var(--neon-color-a),
    0 0 80px var(--neon-color-a);
  animation: glow 0.5s   alternate  ;
}
neon-text-on-hover:hover{
background-color: blueviolet;
 animation: glow 0.3s   infinite  ;
}
.neon-text-inf {
  /* font-size: 4rem; */
  color: #fff;
  text-shadow:
    0 0 5px var(--neon-color-a),
    0 0 10px var(--neon-color-a),
    0 0 20px var(--neon-color-a),
    0 0 40px var(--neon-color-a),
    0 0 80px var(--neon-color-a);
  animation: glow 0.5s   alternate infinite;
}
@keyframes glow {
  0% {
    text-shadow:
      0 0 5px var(--neon-color-a),
      0 0 10px var(--neon-color-a),
      0 0 20px var(--neon-color-a),
      0 0 40px var(--neon-color-a),
      0 0 80px var(--neon-color-a);
  }
  100% {
    text-shadow:
      0 0 10px var(--neon-color-b),
      0 0 20px var(--neon-color-b),
      0 0 40px var(--neon-color-b),
      0 0 80px var(--neon-color-b),
      0 0 160px var(--neon-color-b);
  }
}
</style>
