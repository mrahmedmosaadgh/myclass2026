<script setup>
defineProps({
  title: {
    type: String,
    default: "Card Title",
  },
  content: {
    type: String,
    default: "This is a sample card description.",
  },
  image: {
    type: String,
    default: "https://via.placeholder.com/300", // Placeholder image
  },
  buttonText: {
    type: String,
    default: "Learn More",
  },
});
</script>

<template>
  <div class="max-w-sm bg-white rounded-xl shadow-lg p-6 transition-all duration-300 hover:shadow-2xl">
    <!-- Card Image -->
    <img :src="image" alt="Card Image" class="w-full h-40 object-cover rounded-lg" />

    <!-- Card Content -->
    <div class="mt-4">
      <h3 class="text-lg font-semibold">{{ title }}</h3>
      <p class="mt-2 text-gray-600">{{ content }}</p>
    </div>

    <!-- Card Button -->
    <button
      class="mt-4 w-full py-2 text-white bg-blue-600 rounded-lg shadow-md transition-all duration-300 hover:bg-blue-700"
    >
      {{ buttonText }}
    </button>
  </div>
</template>
