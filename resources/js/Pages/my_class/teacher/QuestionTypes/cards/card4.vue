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
}

/* Simple hover effect */
.book:hover .cover {
  transform: rotateY(-140deg);
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
</style>

<template>
  <div class="book">
    <div class="content">
      <slot></slot>
    </div>
    <div
      class="cover"
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
    default: '#6366f1' // Default start color (indigo)
  },
  endColor: {
    type: String,
    default: '#4f46e5' // Default end color (darker indigo)
  }
});
</script>






