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
  background: linear-gradient(
    145deg,
    rgba(99, 102, 241, 0.9),
    rgba(79, 70, 229, 0.9)
  );
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* Simple hover effect */
.book:hover .cover {
  transform: rotateY(-140deg);
}

/* Clean neon title */
.cover-title {
  font-size: 2rem;
  font-weight: 700;
  color: #fff;
  text-align: center;
  padding: 0.75rem 1.5rem;
  text-transform: uppercase;
  letter-spacing: 2px;
  text-shadow:
    0 0 10px #fff,
    0 0 20px #fff,
    0 0 40px #5271ff,
    0 0 80px #5271ff;
}

/* Simple container */
.cover-content {
  position: relative;
  z-index: 2;
  padding: 1.5rem;
  background: rgba(0, 0, 0, 0.2);
  border-radius: 16px;
  backdrop-filter: blur(8px);
  width: 90%;
  max-width: 320px;
}

/* Clean subtitle */
.cover-subtitle {
  font-size: 1.1rem;
  color: #fff;
  text-align: center;
  margin-top: 0.75rem;
  font-weight: 500;
  letter-spacing: 1px;
  text-shadow:
    0 0 5px #fff,
    0 0 10px #5271ff;
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
    <div class="cover">
      <div class="cover-content">
        <h2 class="cover-title">{{ title }}</h2>
        <div class="cover-subtitle">{{ subtitle }}</div>
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
  }
});
</script>

