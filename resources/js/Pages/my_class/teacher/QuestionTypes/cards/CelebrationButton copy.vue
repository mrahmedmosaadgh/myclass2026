<template>
    <slot>Celebrate!</slot>
    <!-- <button class="celebration-button" @click="handleClick">
    </button> -->
  </template>

  <script setup>
  import confetti from 'canvas-confetti'
  import { defineEmits } from 'vue'

  const emit = defineEmits(['celebrate'])

  const handleClick = () => {
    launchConfetti()
    playSound()
    emit('celebrate')
  }

  const launchConfetti = () => {
    confetti({
      particleCount: 100,
      spread: 70,
      origin: { y: 0.6 },
    })
  }

  const playSound = async () => {
    try {
      const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2013/2013-preview.mp3')
      await audio.play()
    } catch (error) {
      console.warn('Failed to play celebration sound:', error)
    }
  }
  </script>

  <style scoped>
  .celebration-button {
    background-color: #ff4081;
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 1.2rem;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    transition: transform 0.2s ease;
  }
  .celebration-button:hover {
    transform: scale(1.05);
  }
  </style>

