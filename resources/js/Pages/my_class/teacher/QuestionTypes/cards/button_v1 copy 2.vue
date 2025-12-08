<template>
  <div class="radio-inputs">
    <button class="radio" @mousedown="createRipple" @touchstart="createRipple">
      <span class="name">
        <span class="button-content">ffff</span>
        <span class="particles"></span>
        <span class="ripple" ref="rippleEl"></span>
      </span>
    </button>
  </div>
</template>

<style scoped>
.radio-inputs {
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 350px;
  perspective: 1000px;
}

.radio {
  position: relative;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  transform-style: preserve-3d;
  transition: transform 0.2s ease;
}

.radio .name {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 120px;
  padding: 14px 28px;
  background: #fff;
  border: 2px solid #3b82f6;
  border-radius: 12px;
  color: #3b82f6;
  font-weight: 600;
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  transform: translateZ(0);
}

.button-content {
  position: relative;
  z-index: 1;
  transition: transform 0.2s ease;
}

/* Hover Effects */
.radio:hover {
  transform: translateY(-2px) scale(1.02);
}

.radio:hover .name {
  background: rgba(59, 130, 246, 0.08);
  box-shadow:
    0 8px 16px -4px rgba(59, 130, 246, 0.2),
    0 0 8px -2px rgba(59, 130, 246, 0.1);
}

/* Active/Click Effects */
.radio:active {
  transform: translateY(1px) scale(0.98);
}

.radio:active .name {
  background: rgba(59, 130, 246, 0.12);
  box-shadow:
    0 4px 8px -2px rgba(59, 130, 246, 0.2),
    0 0 4px -1px rgba(59, 130, 246, 0.1);
}

.radio:active .button-content {
  transform: scale(0.95);
}

/* Ripple Effect */
.ripple {
  position: absolute;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.4);
  transform: scale(0);
  pointer-events: none;
}

.ripple.active {
  animation: rippleEffect 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Particles */
.particles {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.particles::before,
.particles::after {
  content: "";
  position: absolute;
  width: 40%;
  height: 40%;
  border-radius: 50%;
  background: rgba(59, 130, 246, 0.15);
  opacity: 0;
}

.radio:active .particles::before {
  animation: particleLeft 0.6s ease-out;
}

.radio:active .particles::after {
  animation: particleRight 0.6s ease-out;
}

/* Animations */
@keyframes rippleEffect {
  0% {
    transform: scale(0);
    opacity: 0.8;
  }
  100% {
    transform: scale(4);
    opacity: 0;
  }
}

@keyframes particleLeft {
  0% {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
  }
  100% {
    opacity: 0;
    transform: translate(-150%, -150%) scale(0);
  }
}

@keyframes particleRight {
  0% {
    opacity: 1;
    transform: translate(50%, 50%) scale(1);
  }
  100% {
    opacity: 0;
    transform: translate(150%, 150%) scale(0);
  }
}

/* Focus styles */
.radio:focus {
  outline: none;
}

.radio:focus .name {
  box-shadow:
    0 0 0 3px rgba(59, 130, 246, 0.3),
    0 8px 16px -4px rgba(59, 130, 246, 0.2);
}

/* Disable selection */
.radio {
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}
</style>

<script setup>
import { ref } from 'vue';

const rippleEl = ref(null);

const createRipple = (event) => {
  const button = event.currentTarget;
  const ripple = rippleEl.value;

  const diameter = Math.max(button.clientWidth, button.clientHeight);
  const radius = diameter / 2;

  const rect = button.getBoundingClientRect();

  // Get click coordinates
  const x = event.type === 'mousedown'
    ? event.clientX - rect.left
    : event.touches[0].clientX - rect.left;
  const y = event.type === 'mousedown'
    ? event.clientY - rect.top
    : event.touches[0].clientY - rect.top;

  ripple.style.width = ripple.style.height = `${diameter}px`;
  ripple.style.left = `${x - radius}px`;
  ripple.style.top = `${y - radius}px`;

  ripple.classList.remove('active');
  // Trigger reflow
  void ripple.offsetWidth;
  ripple.classList.add('active');
};

defineProps({
  modelValue: {
    type: [String, Number],
  },
  options: {
    type: Array,
  },
  name: {
    type: String,
    default: 'radio-group'
  }
});

defineEmits(['update:modelValue']);
</script>


