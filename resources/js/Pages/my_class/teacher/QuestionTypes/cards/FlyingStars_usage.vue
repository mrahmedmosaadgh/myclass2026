<template>
    <div class="p-6 space-y-8">
      <!-- Basic usage -->
      <FlyingStars>
        <button class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
          Click for Stars! ✨
        </button>
      </FlyingStars>

      <!-- With hover effect -->
      <FlyingStars
        :stars-count="50"
        :duration="1500"
        :colors="['yellow', 'lightyellow', 'yellow', '#FFD700']"
        :min-size="5"
        :max-size="55"
        :hover-effect="false"
        :gravity="-0.05"
        :shapes="['star']"
      >
        <div class="cursor-pointer p-4 bg-purple-500 text-white rounded hover:bg-purple-600 transition">
          Hover and Click for Magic! 🌟
        </div>
      </FlyingStars>

      <!-- Confetti style -->
      <FlyingStars
        :stars-count="20"
        :duration="2000"
        :colors="['#FFC6FF', '#BDBDFF', '#A0C4FF', '#9BF6FF', '#CAFFBF']"
        :min-distance="100"
        :max-distance="200"
        :gravity="0.05"
        :friction="0.99"
      >
        <button class="px-6 py-3 bg-gradient-to-r from-pink-500 to-purple-500 text-white rounded-lg hover:from-pink-600 hover:to-purple-600 transition">
          Celebration! 🎉
        </button>
      </FlyingStars>
    </div>
  </template>

  <script setup>
  import FlyingStars from './FlyingStars.vue';
  </script>

