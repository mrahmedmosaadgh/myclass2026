<template>
  <div>
    <!-- Basic usage -->
    <my_button1>
      Click me!
    </my_button1>

    <!-- Custom styling -->
    <my_button1
      width="250px"
      height="100px"
      :gradient-colors="['#FF6B6B', '#4ECDC4', '#45B7D1']"
      gradient-degree="60"
      border-radius="15px"
      title="Custom Button"
      subtitle="With amazing effects"
      prompt-text="HOVER HERE"
      :glow-opacity="0.5"
      glow-blur="3rem"
      title-color="#FFFFFF"
      title-size="24px"
      :hover-brightness="1.2"
      :rotation-degree="25"
    >
      <template #title>
        Custom Title
      </template>
      <template #subtitle>
        Custom Subtitle
      </template>
    </my_button1>

    <!-- Minimal setup -->
    <my_button1
      width="150px"
      height="50px"
      :show-prompt="false"
    >
      Simple Button
    </my_button1>
  </div>
</template>

<script setup>
import my_button1 from './components/my_button1.vue';
</script>