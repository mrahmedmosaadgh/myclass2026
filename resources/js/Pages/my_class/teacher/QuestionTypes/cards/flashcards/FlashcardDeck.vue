<template>
    <div
      class="deck-container"
      @touchstart="onTouchStart"
      @touchmove="onTouchMove"
      @touchend="onTouchEnd"
      @mousedown="onMouseDown"
      @mousemove="onMouseMove"
      @mouseup="onMouseUp"
    >
      <Flashcard
        v-if="currentCard"
        :key="cardIndex"
        :style="{ transform: `translateX(${offsetX}px) rotate(${offsetX / 10}deg)` }"
      >
        <template #front>{{ currentCard.front }}</template>
        <template #back>{{ currentCard.back }}</template>
      </Flashcard>

      <div class="actions">
        <button @click="markReview">Need to review</button>
        <button @click="markKnown">Got it</button>
      </div>
    </div>
  </template>

  <script setup>
  import { ref, computed } from 'vue';
  import Flashcard from './Flashcard.vue';

  const props = defineProps({
    cards: Array,
  });

  const cardIndex = ref(0);
  const offsetX = ref(0);
  const touchStartX = ref(null);
  const isDragging = ref(false);

  const currentCard = computed(() => props?.cards?.[cardIndex.value]);

  const nextCard = () => {
    cardIndex.value++;
    offsetX.value = 0;
  };

  const markReview = () => {
    nextCard();
  };
  const markKnown = () => {
    nextCard();
  };

  // Swipe logic
  const onTouchStart = (e) => {
    touchStartX.value = e.touches[0].clientX;
  };
  const onTouchMove = (e) => {
    if (touchStartX.value !== null) {
      offsetX.value = e.touches[0].clientX - touchStartX.value;
    }
  };
  const onTouchEnd = () => {
    if (Math.abs(offsetX.value) > 100) {
      offsetX.value < 0 ? markReview() : markKnown();
    } else {
      offsetX.value = 0;
    }
    touchStartX.value = null;
  };

  const onMouseDown = (e) => {
    isDragging.value = true;
    touchStartX.value = e.clientX;
  };
  const onMouseMove = (e) => {
    if (isDragging.value && touchStartX.value !== null) {
      offsetX.value = e.clientX - touchStartX.value;
    }
  };
  const onMouseUp = () => {
    if (isDragging.value) {
      onTouchEnd();
      isDragging.value = false;
    }
  };
  </script>

  <style scoped>
  .deck-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    touch-action: pan-y;
    user-select: none;
  }
  </style>
