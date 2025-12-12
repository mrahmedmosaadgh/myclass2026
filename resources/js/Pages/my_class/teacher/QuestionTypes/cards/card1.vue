<style scoped>
/* Keep existing book styles */
.book {
  position: relative;
  border-radius: 15px;
  width: 280px;
  height: 380px;
  transform-style: preserve-3d;
  perspective: 2000px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2d3748;
  transition: all 0.5s cubic-bezier(0.645, 0.045, 0.355, 1);
}

/* Book spine effect */
.book-spine {
  position: absolute;
  left: 0;
  width: 60px;
  height: 100%;
  transform: translateX(-30px) rotateY(-90deg);
  background: linear-gradient(to right,
    rgba(45, 55, 72, 0.9),
    rgba(74, 85, 104, 0.9)
  );
  transform-origin: right;
  border-radius: 3px 0 0 3px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.spine-text {
  transform: rotateZ(-180deg);
  writing-mode: vertical-lr;
  text-orientation: mixed;
  color: white;
  font-weight: 600;
  font-size: 1.1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-height: 80%;
  padding: 1rem 0;
}

/* Book pages effect */
.book-pages {
  position: absolute;
  right: 0;
  width: 50px;
  height: 97%;
  top: 1.5%;
  transform: translateX(0) rotateY(15deg);
  transform-origin: left;
  border-radius: 0 3px 3px 0;
  background: linear-gradient(to left,
    #e2e8f0 0%,
    white 4%,
    #e2e8f0 4.5%,
    white 5%,
    #e2e8f0 5.5%,
    white 6%,
    #e2e8f0 6.5%,
    white 7%,
    #e2e8f0 7.5%,
    white 8%,
    #e2e8f0 8.5%,
    white 9%,
    #e2e8f0 9.5%
  );
  box-shadow:
    inset -2px 0 5px rgba(0, 0, 0, 0.1),
    2px 0 5px rgba(0, 0, 0, 0.1);
}

/* Simple clean cover */
.cover {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: 15px;
  cursor: pointer;
  transform-origin: left;
  transition: transform 0.7s cubic-bezier(0.645, 0.045, 0.355, 1);
  transform-style: preserve-3d;
  background: linear-gradient(145deg, rgba(99, 102, 241, 0.9), rgba(79, 70, 229, 0.9));
  box-shadow:
    -5px 5px 15px rgba(0, 0, 0, 0.2),
    -15px 15px 30px rgba(0, 0, 0, 0.1);
}

.cover-inner {
  position: relative;
  width: 100%;
  height: 100%;
  transform-style: preserve-3d;
  border-radius: 15px;
}

/* Changed from hover to class-based animation */
.cover.is-open {
  transform: rotateY(-180deg);
}

/* Added hover effect indicator */
.cover::after {
  content: '';
  position: absolute;
  right: 0;
  width: 24px;
  height: 24px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'%3E%3C/path%3E%3C/svg%3E");
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

/* Enhanced 3D shadow effects */
.book:hover {
  transform: translateY(-10px) rotateY(15deg);
  box-shadow:
    -20px 20px 40px rgba(0, 0, 0, 0.2),
    -5px 5px 15px rgba(0, 0, 0, 0.1);
}

/* Clean neon title with text control */
.cover-content {
  position: absolute;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  backface-visibility: hidden;
  background: inherit;
  border-radius: 15px;
  transform-style: preserve-3d;
}

/* Glass effect for cover */
.cover::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.1) 0%,
    transparent 40%,
    transparent 60%,
    rgba(255, 255, 255, 0.1) 100%
  );
  border-radius: 15px;
  backdrop-filter: blur(5px);
}

.cover-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: white;
  text-align: center;
  margin-bottom: 0.5rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  transform: translateZ(20px);
}

.cover-subtitle {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
  transform: translateZ(15px);
}

/* Content styles */
.content {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 1.5rem;
  background: white;
  border-radius: 15px;
  box-shadow:
    inset -5px 0 15px rgba(0, 0, 0, 0.1),
    0 5px 10px rgba(0, 0, 0, 0.1);
}

/* Optional: Add focus styles for accessibility */
.cover:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.5);
}

/* Optional: Add keyboard support styles */
.cover:focus-visible {
  outline: 2px solid white;
  outline-offset: 2px;
}

/* Pin button styles */
.pin-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 20;
  transform: translateZ(30px);
}

.pin-button:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateZ(30px) scale(1.1);
}

.pin-button.pinned {
  background: rgba(255, 255, 255, 0.3);
  box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
}

.pin-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.3s ease;
}

.pinned .pin-icon {
  transform: rotate(-45deg);
}
</style>

<template>
  <div
    class="book"
    @mouseleave="handleMouseLeave"
  >
    <!-- Book spine -->
    <div class="book-spine">
      <div class="spine-text">{{ title }}</div>
    </div>

    <!-- Book pages effect -->
    <div class="book-pages"></div>

    <div class="content">
      <slot></slot>
    </div>
    <div
      class="cover"
      :class="{ 'is-open': isOpen }"
      @click="handleCoverClick"
      :style="{
        background: `linear-gradient(145deg, ${startColor}, ${endColor})`
      }"
    >
      <div class="cover-inner">
        <div class="cover-content">
          <slot name="cover">
            <h2 class="cover-title">{{ title }}</h2>
            <div class="cover-subtitle">{{ subtitle }}</div>
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue';

const isOpen = ref(false);
let closeTimeout = null;

const handleCoverClick = () => {
  isOpen.value = !isOpen.value;
};

const handleMouseLeave = () => {
  closeTimeout = setTimeout(() => {
    isOpen.value = false;
  }, 300);
};

onUnmounted(() => {
  if (closeTimeout) {
    clearTimeout(closeTimeout);
  }
});

defineProps({
  title: {
    type: String,
    default: 'Title'
  },
  subtitle: {
    type: String,
    default: 'Subtitle'
  },
  startColor: {
    type: String,
    default: '#6366f1'
  },
  endColor: {
    type: String,
    default: '#4f46e5'
  }
});
</script>
















