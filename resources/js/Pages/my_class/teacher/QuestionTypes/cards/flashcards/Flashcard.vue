<template>
    <div class="app">
      <FlashcardDeck :cards="flashcards" />
    </div>
  </template>

  <script setup>
  import FlashcardDeck from './FlashcardDeck.vue';

  const flashcards = [
    { front: 'Which big cat helps forests store carbon?', back: 'Tigers' },
    { front: 'How many legs does a spider have?', back: '8' },
    { front: 'What is the capital of France?', back: 'Paris' },
  ];
  </script>

  <style>
  body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f9f9f9;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
  }
  </style>
