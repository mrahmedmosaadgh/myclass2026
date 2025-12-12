<template>
  <div class="simple-card" :class="theme">
    <slot>
      <div class="card-text">Hover Me</div>
      <div class="card-text-after-hover">show after hover</div>
    </slot>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  /**
   * Defines the color theme for the card. 
   * Options: 'default' (orange/brown) or 'blue' (for a custom example).
   */
  theme: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'blue'].includes(value)
  }
});
</script>

<style >
/* --- Base Card Styles (Shared by all themes) --- */
.simple-card2 {
  /* Initial 3D Transform and Shadow */
  transform: perspective(900px) rotateX(60deg) scale(0.7);
  box-shadow: 0px 20px 100px #555;
  transition: 0.5s ease all;
  
  /* Dimensions and Content Styling */
  width: 200px;
  height: 200px;
  /* height: 300px; */
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px; /* Added for better appearance */
  cursor: pointer;
}

.simple-card {
  /* Positioning and Z-index for layering */
  position: relative; /* Crucial for z-index to work */
  z-index: 1; /* Default z-index */

  /* Initial 3D Transform and Shadow */
  transform: perspective(500px) rotateX(-20deg) scale(0.9);
  /* transform: perspective(900px) rotateX(60deg) scale(0.7); */
  box-shadow: 0px 20px 140px #555;
  transition: 0.5s ease all;
  
  /* Dimensions and Content Styling */
  width: 200px;
  height: 100px;
  /* height: 300px; */
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 4px; /* Added for better appearance */
  /* cursor: pointer; */
}
/* --- Hover Effect (Shared by all themes) --- */
.simple-card:hover {
  /* transform: rotate(0deg) scale(1.5) translateY(10px);
  z-index: 9999999;
  overflow: visible; */

   z-index: 10;
    /* Make it appear on top of other cards */
  
  /* Modified transform to bring it forward */
  transform: rotate( 0deg) scale(2) translateY(10px) translateZ(50px); /* Added translateZ */

}

.card-text {
    color: white;
    font-family: sans-serif;
    font-size: 2.5em;
    text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    transition: opacity 0.3s ease;
}

/* --- Theme Specific Backgrounds --- */

/* Default Theme (Based on your SCSS) */
.simple-card.default {
  background: linear-gradient(180deg, #eba65b 30%, #d99267);
}

/* Custom 'blue' Theme (Example for reusability) */
.simple-card.blue {
  background: linear-gradient(180deg, #59476f 30%, #7b88d1);
}










/* 1. Hide the hover text by default */
.card-text-after-hover {
    color: white;
    font-family: sans-serif;
    font-size: 1.5em;
    text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
    
    /* Key for hiding: set opacity to 0 */
    opacity: 0; 
  display: none;
    
    /* Key for smooth reveal: add a transition */
    transition: opacity 0.3s ease;
}

/* 2. Show the hover text when the parent card is hovered */
.simple-card:hover .card-text-after-hover {
    /* Key for showing: set opacity to 1 */
    opacity: 1; 
  display:flex

}

/* Optional: Hide the original text when the card is hovered */
.simple-card:hover .card-text {
    opacity: 0;
  display: none;

}
</style>