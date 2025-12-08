<template>
    <div
      class="flying-stars-wrapper"
      ref="wrapper"
      @click="handleClick"
      @mouseenter="handleHover"
    >
      <slot></slot>
      <div ref="particleContainer" class="particle-container"></div>
    </div>
  </template>

  <script setup>
  import { ref, onMounted, onBeforeUnmount } from 'vue';
  import { useThrottleFn } from '@vueuse/core';

  const props = defineProps({
    // Animation settings
    starsCount: { type: Number, default: 10 },
    duration: { type: Number, default: 1000 },
    minSize: { type: Number, default: 10 },
    maxSize: { type: Number, default: 20 },
    minDistance: { type: Number, default: 50 },
    maxDistance: { type: Number, default: 150 },

    // Visual settings
    colors: {
      type: Array,
      default: () => ['#FFD700', '#FFA500', '#FF69B4', '#00CED1', '#87CEEB']
    },

    // Behavior settings
    hoverEffect: { type: Boolean, default: false },
    gravity: { type: Number, default: 0.1 },
    friction: { type: Number, default: 0.98 },

    // Shape settings
    shapes: {
      type: Array,
      default: () => [
        'star', // ★
        'circle', // ●
        'heart', // ♥
        'sparkle' // ✨
      ]
    }
  });

  const wrapper = ref(null);
  const particleContainer = ref(null);
  const activeParticles = ref([]);

  const SHAPES = {
    star:  'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)',
    circle: 'circle(50% at 50% 50%)',
    heart: 'path("M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z")',
    sparkle: 'polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%)'
  };

  const createParticle = (x, y, isHover = false) => {
    const particle = document.createElement('div');

    // Random properties
    const size = props.minSize + Math.random() * (props.maxSize - props.minSize);
    const angle = Math.random() * Math.PI * 2;
    const velocity = isHover ? 1 + Math.random() : 2 + Math.random() * 2;
    const distance = props.minDistance + Math.random() * (props.maxDistance - props.minDistance);
    const color = props.colors[Math.floor(Math.random() * props.colors.length)];
    const shape = props.shapes[Math.floor(Math.random() * props.shapes.length)];
    const rotation = Math.random() * 360;
    const scale = 0.5 + Math.random() * 0.5;

    // Initial properties
    const particleState = {
      x: 0,
      y: 0,
      vx: Math.cos(angle) * velocity,
      vy: Math.sin(angle) * velocity,
      rotation,
      scale,
      alpha: 1
    };

    particle.className = 'flying-particle';
    particle.style.cssText = `
      position: absolute;
      left: ${x}px;
      top: ${y}px;
      width: ${size}px;
      height: ${size}px;
      background-color: ${color};
    
      clip-path: ${SHAPES[shape]};
      pointer-events: none;
      will-change: transform, opacity;
    `;

    particleContainer.value.appendChild(particle);

    const startTime = performance.now();

    const animate = (currentTime) => {
      const elapsed = currentTime - startTime;
      const progress = elapsed / props.duration;

      if (progress >= 1) {
        particle.remove();
        activeParticles.value = activeParticles.value.filter(p => p !== particle);
        return;
      }

      // Physics-based animation
      particleState.vy += props.gravity;
      particleState.vx *= props.friction;
      particleState.vy *= props.friction;

      particleState.x += particleState.vx;
      particleState.y += particleState.vy;
      particleState.rotation += 2;
      particleState.alpha = 1 - progress;

      particle.style.transform = `
        translate(${particleState.x}px, ${particleState.y}px)
        rotate(${particleState.rotation}deg)
        scale(${particleState.scale * (1 - progress * 0.5)})
      `;
      particle.style.opacity = particleState.alpha;

      requestAnimationFrame(animate);
    };

    requestAnimationFrame(animate);
    activeParticles.value.push(particle);
  };

  const createParticles = (x, y, isHover = false) => {
    const count = isHover ? Math.floor(props.starsCount / 2) : props.starsCount;
    for (let i = 0; i < count; i++) {
      createParticle(x, y, isHover);
    }
  };

  // Throttle the particle creation for better performance
  const throttledCreateParticles = useThrottleFn(createParticles, 50);

  const handleClick = (event) => {
    const rect = wrapper.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    createParticles(x, y);
  };

  const handleHover = (event) => {
    if (!props.hoverEffect) return;

    const rect = wrapper.value.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const y = event.clientY - rect.top;
    throttledCreateParticles(x, y, true);
  };

  // Cleanup
  onBeforeUnmount(() => {
    activeParticles.value.forEach(particle => particle.remove());
    activeParticles.value = [];
  });
  </script>

  <style scoped>
  .flying-stars-wrapper {
    display: inline-block;
    position: relative;
    overflow: visible;
  }

  .particle-container {
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
    z-index: 9999;
  }

  .flying-particle {
    position: absolute;
    backface-visibility: hidden;
    transform-style: preserve-3d;
  }
  </style>
