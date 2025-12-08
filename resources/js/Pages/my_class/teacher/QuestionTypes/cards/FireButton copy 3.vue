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
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
    transform: perspective(1000px) translateZ(0) translateY(0);
    box-shadow:
        0 6px 0 #111,
        0 8px 15px rgba(0, 0, 0, 0.3);
  }

  .fire-button::before {
    content: '';
    position: absolute;
    inset: -2px;
    background: linear-gradient(
      90deg,
      #ff0000,
      #ff8800,
      #ffff00,
      #ff8800,
      #ff0000
    );
    background-size: 400% 100%;
    border-radius: 12px;
    z-index: -2;
    animation: move-fire 3s linear infinite;
    filter: blur(2px) brightness(1.5);
    opacity: 0.9;
    transform: translateZ(-1px);
  }

  .fire-button::after {
    content: '';
    position: absolute;
    inset: 2px;
    background: linear-gradient(
      to bottom,
      #333,
      #111
    );
    border-radius: 8px;
    z-index: -1;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform: translateZ(0);
  }

  @keyframes move-fire {
    0% {
      background-position: 0% 50%;
    }
    100% {
      background-position: 400% 50%;
    }
  }

  .fire-button:hover {
    color: #ffd700;
    transform: perspective(1000px) translateZ(10px) translateY(-6px);
    box-shadow:
        0 8px 0 #111,
        0 16px 25px rgba(255, 61, 0, 0.3),
        0 0 20px rgba(255, 136, 0, 0.5),
        0 0 40px rgba(255, 61, 0, 0.3);
  }

  .fire-button:active {
    transform: perspective(1000px) translateZ(5px) translateY(4px);
    box-shadow:
        0 2px 0 #111,
        0 4px 8px rgba(255, 61, 0, 0.2),
        0 0 10px rgba(255, 136, 0, 0.3),
        0 0 20px rgba(255, 61, 0, 0.2);
    transition: all 0.1s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .fire-button:active::after {
    transform: translateZ(0) scale(0.98);
  }

  .fire-button:hover::before {
    filter: blur(3px) brightness(2);
    animation: move-fire 2s linear infinite;
  }

  /* Add pulsing glow effect */
  .fire-button {
    animation: pulse-glow 2s ease-in-out infinite;
  }

  @keyframes pulse-glow {
    0%, 100% {
      box-shadow:
        0 6px 0 #111,
        0 8px 15px rgba(0, 0, 0, 0.3),
        0 0 5px rgba(255, 136, 0, 0.3),
        0 0 10px rgba(255, 61, 0, 0.2);
    }
    50% {
      box-shadow:
        0 6px 0 #111,
        0 8px 15px rgba(0, 0, 0, 0.3),
        0 0 10px rgba(255, 136, 0, 0.5),
        0 0 20px rgba(255, 61, 0, 0.3);
    }
  }
  </style>








