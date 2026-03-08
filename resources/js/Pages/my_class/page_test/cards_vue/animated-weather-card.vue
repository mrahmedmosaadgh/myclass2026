<!-- This file was previously named 13.vue - An animated weather card -->
<style scoped>
/* Placeholder for the actual content of file 13.vue */
.weather-card {
  width: 250px;
  height: 150px;
  background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
  border-radius: 15px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  color: white;
  box-shadow: 0 8px 16px rgba(0,0,0,0.2);
  transition: transform 0.3s ease;
}

.weather-card:hover {
  transform: translateY(-5px);
}

.temp {
  font-size: 2.5rem;
  font-weight: bold;
}

.description {
  font-size: 1.2rem;
  opacity: 0.9;
}
</style>

<template>
  <div class="weather-card">
    <div class="temp">22°C</div>
    <div class="description">Sunny</div>
  </div>
</template>