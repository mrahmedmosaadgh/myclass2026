<style scoped>
.book {
  position: relative;
  border-radius: 15px;
  width: 220px;
  background-color: #ffffff;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  transform: preserve-3d;
  perspective: 2000px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2d3748;
  padding: 20px;
  transition: all 0.3s ease;
  overflow: hidden;
}

.book:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
}

.cover {
  top: 0;
  left: 0;
  position: absolute;
  background: linear-gradient(135deg, #6366f1, #4f46e5);
  width: 100%;
  height: 100%;
  border-radius: 15px;
  cursor: pointer;
  transition: transform 0.7s cubic-bezier(0.4, 0, 0.2, 1);
  transform-origin: 0;
  box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(5px);
}

.book:hover .cover {
  transform: rotatey(-100deg);
}

/* Glass effect for cover */
.cover::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(
    45deg,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.05) 100%
  );
  border-radius: 15px;
}

/* Text styles */
p {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ffffff;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  letter-spacing: 0.025em;
  position: relative;
  z-index: 1;
}

/* Content container */
.content {
  padding: 1rem;
  opacity: 0.9;
  transition: opacity 0.3s ease;
}

.book:hover .content {
  opacity: 1;
}

/* Optional: Add animation for content when cover opens */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.book:hover :slotted(*) {
  animation: fadeIn 0.5s ease forwards;
}
</style>

<script setup>
defineProps({
  coverText: {
    type: String,
    default: 'Hover Me'
  }
});
</script>

<template>
  <div class="book">
    <div class="content">
      <!-- Default slot for main content -->
      <slot></slot>

      <!-- Named slot for additional content -->
      <slot name="extra"></slot>
    </div>

    <div class="cover">
      <!-- Cover slot with fallback -->
      <slot name="cover">
        <p>{{ coverText }}</p>
      </slot>
    </div>
  </div>
</template>



