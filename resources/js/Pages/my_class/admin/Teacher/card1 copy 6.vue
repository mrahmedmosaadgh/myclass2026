<style scoped>
.book {
  position: relative;
  border-radius: 15px;
  width: 220px;
  height: 300px;
  background-color: #ffffff;
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  transform-style: preserve-3d;
  perspective: 2000px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2d3748;
  transition: all 0.5s ease;
}

.book::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 30px;
  height: 100%;
  background: linear-gradient(90deg,
    rgba(255, 255, 255, 0.1),
    rgba(255, 255, 255, 0.5)
  );
  transform: translateX(-20px) rotateY(-90deg);
  transform-origin: right;
}

.content {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 15px;
  transform-origin: left;
  transition: all 0.5s ease;
  overflow: hidden;
}

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
  background: linear-gradient(145deg, #6366f1, #4f46e5);
  box-shadow:
    inset 0 0 50px rgba(0, 0, 0, 0.2),
    0 5px 15px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

/* Spine effect */
.cover::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  width: 30px;
  height: 100%;
  background: linear-gradient(90deg,
    rgba(0, 0, 0, 0.2),
    transparent 20%
  );
}

/* Cover texture */
.cover::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background:
    linear-gradient(
      45deg,
      rgba(255, 255, 255, 0.1) 0%,
      transparent 20%,
      transparent 80%,
      rgba(255, 255, 255, 0.1) 100%
    );
  border-radius: 15px;
}

.book:hover .cover {
  transform: rotateY(-140deg);
}

.book:hover {
  transform: translateX(40px);
}

/* Text styles */
p {
  font-size: 1.25rem;
  font-weight: 600;
  color: #ffffff;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
  letter-spacing: 0.025em;
  position: relative;
  z-index: 1;
  text-align: center;
  padding: 0 20px;
}

/* Content animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.book:hover .content > * {
  animation: fadeIn 0.5s ease forwards;
  animation-delay: 0.3s;
}

/* Inner pages effect */
.inner-pages {
  position: absolute;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  background: #fff;
  transform-origin: left;
  transform: rotateY(0);
  transition: transform 0.5s ease;
  border-radius: 0 15px 15px 0;
  box-shadow:
    inset -10px 0 20px rgba(0, 0, 0, 0.1),
    1px 0 2px rgba(0, 0, 0, 0.1);
}

.book:hover .inner-pages {
  transform: rotateY(-30deg);
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
    <div class="inner-pages"></div>
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

