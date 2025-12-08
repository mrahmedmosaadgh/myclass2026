<style scoped>
/* Keep existing book styles */
.book {
  position: relative;
  border-radius: 15px;
  width: 220px;
  height: 300px;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  box-shadow:
    0 10px 30px rgba(0, 0, 0, 0.1),
    0 1px 8px rgba(0, 0, 0, 0.05);
  transform-style: preserve-3d;
  perspective: 2000px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2d3748;
  transition: all 0.5s ease;
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
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  will-change: transform;
}

/* Changed from hover to class-based animation */
.cover.is-open {
  transform: rotateY(-140deg);
}

/* Added hover effect indicator */
.cover:not(.is-open)::after {
  content: '';
  position: absolute;
  top: 50%;
  right: 10px;
  transform: translateY(-50%);
  width: 24px;
  height: 24px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='white'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 5l7 7-7 7'%3E%3C/path%3E%3C/svg%3E");
  opacity: 0.7;
  transition: opacity 0.3s ease;
}

.cover:not(.is-open):hover::after {
  opacity: 1;
}

/* Clean neon title with text control */
.cover-content {
  width: 90%; /* Adjusted from fixed max-width */
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(8px);
  border-radius: 12px;
  padding: 1rem; /* Reduced padding */
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.cover-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #ffffff;
  text-align: center;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  letter-spacing: 0.5px;
  padding: 0 0.5rem; /* Added horizontal padding */
  width: 100%; /* Ensure full width */
}

/* Clean subtitle with text control */
.cover-subtitle {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.9);
  text-align: center;
  font-weight: 500;
  line-height: 1.4;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
  padding: 0 0.5rem; /* Added horizontal padding */
  width: 100%; /* Ensure full width */
}

/* Content styles */
.content {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 1.5rem;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 15px;
  transform-origin: left;
  transition: all 0.5s ease;
  overflow: hidden;
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
  top: 10px;
  right: 10px;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(4px);
  color: #2d3748;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 20; /* Increased z-index to stay above cover */
}

.pin-button:hover {
  background: rgba(0, 0, 0, 0.2);
  transform: scale(1.1);
}

.pin-button.pinned {
  background: rgba(0, 0, 0, 0.3);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
  color: #fff;
}

.pin-icon {
  width: 16px;
  height: 16px;
  transition: transform 0.3s ease;
}

.pinned .pin-icon {
  transform: rotate(-45deg);
}

/* Remove the cover-content padding adjustment since pin is outside */
.cover-content {
  padding: 1rem;
}
</style>

<template>
  <div
    class="book"
    @mouseleave="handleMouseLeave"
  >
    <!-- Pin button -->
    <button
      @click.stop="togglePin"
      class="pin-button"
      :class="{ 'pinned': isPinned }"
      :title="isPinned ? 'Unpin card' : 'Pin card open'"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        stroke-linecap="round"
        stroke-linejoin="round"
        class="pin-icon"
      >
        <path d="M12 2L12 22" v-if="!isPinned"/>
        <path d="M18 8L6 8" />
        <path d="M15 2L9 2" />
        <path d="M12 15L12 22" />
      </svg>
    </button>

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
      <div class="cover-content">
        <slot name="cover">
          <h2 class="cover-title">{{ title }}</h2>
          <div class="cover-subtitle">{{ subtitle }}</div>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onUnmounted } from 'vue';

const isOpen = ref(false);
const isPinned = ref(false);
let closeTimeout = null;

const handleCoverClick = () => {
  isOpen.value = !isOpen.value;
};

const togglePin = () => {
  isPinned.value = !isPinned.value;
  if (isPinned.value) {
    isOpen.value = true;
  }
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












