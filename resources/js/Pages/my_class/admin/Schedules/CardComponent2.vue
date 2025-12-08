<script setup>
defineProps({
  title: String,
  description: String,
  image: String,
});
</script>

<template>
  <div class="card-wrapper p-2 overflow-hidden">
    <div class="border-animation w-12 h-12"></div>
    <div class="border-animation"></div>
    <div class="card2">
      <img v-if="image" :src="image" alt="Card Image" class="card-image">
      <div class="card-content">
        <h3 class="card-title">{{ title }}</h3>
        <p class="card-description">{{ description }}</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Wrapper to hold the animated border */
.card-wrapper {
  position: relative;
  width: 320px;
  height: 400px;
  border-radius: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Animated Border Effect */
.border-animation {
  position: absolute;
  inset: 0;
  border-radius: 24px;
  padding: 2px;
  background: linear-gradient(90deg, #ff9a9e, #fad0c4, #ffdde1);
  animation: borderAnimation 3s infinite linear;
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: exclude;
  -webkit-mask-composite: xor;
  z-index: 1;
}

@keyframes borderAnimation {
  0% {
    transform: rotate(0deg);
  }
  50% {
    transform: rotate(180deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

/* Card Styling */
.card {
  width: 100%;
  height: 100%;
  padding: 20px;
  background: url(https://images.imyfone.com/filme/assets/overview/magicvoice/topbanner-bg.jpg) no-repeat center/cover;
  position: relative;
  border-radius: 24px;
  text-align: center;
  color: white;
  z-index: 2;
}

/* Image */
.card-image {
  width: 80%;
  height: 150px;
  object-fit: cover;
  border-radius: 12px;
}

/* Title and Description */
.card-title {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 12px 0;
}

.card-description {
  font-size: 1rem;
}
</style>
