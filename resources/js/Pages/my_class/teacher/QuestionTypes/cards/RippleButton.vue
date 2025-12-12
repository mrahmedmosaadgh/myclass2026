<template>
    <button class="ripple-button" @click="createRipple">
      <slot>Click Me</slot>
    </button>
  </template>

  <script setup>
  import { ref } from 'vue';

  const createRipple = (event) => {
    const button = event.currentTarget;
    const ripple = document.createElement('span');
    const rect = button.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;

    ripple.className = 'ripple';
    ripple.style.width = ripple.style.height = `${size}px`;
    ripple.style.left = `${x}px`;
    ripple.style.top = `${y}px`;

    button.appendChild(ripple);

    ripple.addEventListener('animationend', () => {
      ripple.remove();
    });
  };
  </script>

  <style scoped>
  .ripple-button {
    position: relative;
    overflow: hidden;
    padding: 12px 24px;
    font-size: 16px;
    color: white;
    background-color: #4a90e2;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    outline: none;
    transition: background-color 0.3s ease;
  }

  .ripple-button:hover {
    background-color: #357ab8;
  }

  .ripple {
    position: absolute;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.4);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
    z-index: 1;
  }

  @keyframes ripple-animation {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }
  </style>
