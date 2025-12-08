<template>
    <button class="fire-button">
      <slot>🔥 Fire Button 🔥</slot>
    </button>
  </template>

  <script setup>
  // No script logic needed unless you want props for customization
  </script>

  <style scoped>
  .fire-button {
    position: relative;
    padding: 1em 2em;
    background: #222;
    color: white;
    font-size: 1.2em;
    border: none;
    cursor: pointer;
    z-index: 0;
    overflow: hidden;
    border-radius: 10px;
    transition: color 0.3s;
  }

  .fire-button::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: conic-gradient(
      from 0deg,
      #ffcc00,
      #ff3300,
      #ff6600,
      #ff0000,
      #ffcc00
    );
    z-index: -2;
    animation: fire-spin 2s linear infinite;
    border-radius: 12px;
  }

  .fire-button::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    right: 2px;
    bottom: 2px;
    background: #111;
    border-radius: 8px;
    z-index: -1;
  }

  @keyframes fire-spin {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

  .fire-button:hover {
    color: #ffd700;
  }
  </style>
