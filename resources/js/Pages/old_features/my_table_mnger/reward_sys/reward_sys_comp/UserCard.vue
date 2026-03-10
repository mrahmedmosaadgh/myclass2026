<template>
  <div class="user-card">
    <div class="card-monster">
      <img :src="monsterSrc" :alt="`${name}'s Monster`" />
    </div>
    
    <div 
      v-if="notificationCount > 0" 
      class="notification-badge"
    >
      {{ notificationCount }}
    </div>
    
    <span class="card-name">{{ name }}</span>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
    default: 'User',
  },
  monsterSrc: {
    type: String,
    required: true,
    // Note: You must ensure the path is correct relative to your Vue project's structure (e.g., using @/assets)
    default: '/img/default-monster.png', 
  },
  notificationCount: {
    type: Number,
    default: 0,
  },
});
</script>

<style scoped>
/* --- 🎨 Variables for easy customization --- */
:root {
  --card-width: 150px;
  --card-height: 200px;
  --card-bg-color: #ffffff;
  --card-border-radius: 20px;
  --text-color: #000000;
  --font-family: sans-serif;
  --badge-color: #28a745; /* Green */
  --badge-text-color: #ffffff;
}

.user-card {
  /* Card Container */
  width: var(--card-width);
  height: var(--card-height);
  background-color: var(--card-bg-color);
  border-radius: var(--card-border-radius);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  position: relative; /* Essential for positioning the monster and badge */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-end; /* Push text to the bottom */
  padding-bottom: 20px;
  text-align: center;
  /* Optional: Add a subtle transition for hover effects */
  transition: transform 0.2s ease-in-out; 
}

/* Hover Effect Example */
.user-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
}

/* --- 👾 Monster Positioning --- */
.card-monster {
  position: absolute;
  top: -45px; /* Adjust this value to control overlap */
  width: 100px; /* Base size */
  height: 100px; /* Base size */
}

.card-monster img {
  width: 100%;
  height: auto;
  display: block;
}

/* --- 📝 Name Text --- */
.card-name {
  color: var(--text-color);
  font-family: var(--font-family);
  font-size: 1.2em;
  font-weight: 600;
  margin-top: 5px;
}

/* --- 🟢 Notification Badge --- */
.notification-badge {
  position: absolute;
  top: 5px; 
  right: 5px;
  width: 30px;
  height: 30px;
  background-color: var(--badge-color);
  color: var(--badge-text-color);
  border-radius: 50%; /* Perfect circle */
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 1em;
  /* White border to lift it off the card/background, matching the original image */
  border: 2px solid var(--card-bg-color); 
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}
</style>