<template>
  <div class="button-container" :style="containerStyles">
    <div class="canvas">
      <div v-for="i in 25" :key="i" :class="`tracker tr-${i}`"></div>
      <div id="button" :style="buttonStyles">
        <p v-if="showPrompt" id="prompt" :style="promptStyles">{{ promptText }}</p>
        <div class="title" :style="titleStyles">
          <slot name="title">
            {{ title }}
          </slot>
        </div>
        <div class="subtitle" :style="subtitleStyles">
          <slot name="subtitle">
            {{ subtitle }}
          </slot>
        </div>
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  // Size
  width: { type: String, default: '190px' },
  height: { type: String, default: '80px' },
  
  // Colors
  gradientColors: {
    type: Array,
    default: () => ['rgb(65, 88, 208)', 'rgb(200, 80, 192)', 'rgb(255, 204, 112)']
  },
  gradientDegree: { type: Number, default: 43 },
  
  // Text
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  promptText: { type: String, default: 'HOVER ME' },
  showPrompt: { type: Boolean, default: true },
  
  // Styles
  borderRadius: { type: String, default: '20px' },
  glowOpacity: { type: Number, default: 0.3 },
  glowBlur: { type: String, default: '2rem' },
  
  // Typography
  titleColor: { type: String, default: 'white' },
  subtitleColor: { type: String, default: 'rgb(134, 110, 221)' },
  promptColor: { type: String, default: 'rgb(255, 255, 255)' },
  titleSize: { type: String, default: 'x-large' },
  subtitleSize: { type: String, default: '1em' },
  promptSize: { type: String, default: '20px' },
  
  // Animation
  transitionDuration: { type: Number, default: 700 },
  hoverBrightness: { type: Number, default: 1.1 },
  
  // 3D Effect
  perspective: { type: String, default: '800px' },
  rotationDegree: { type: Number, default: 20 },
});

const containerStyles = computed(() => ({
  '--width': props.width,
  '--height': props.height,
}));

const buttonStyles = computed(() => ({
  '--border-radius': props.borderRadius,
  '--transition-duration': `${props.transitionDuration}ms`,
  background: `linear-gradient(${props.gradientDegree}deg, ${props.gradientColors.join(', ')})`,
}));

const titleStyles = computed(() => ({
  color: props.titleColor,
  fontSize: props.titleSize,
}));

const subtitleStyles = computed(() => ({
  color: props.subtitleColor,
  fontSize: props.subtitleSize,
}));

const promptStyles = computed(() => ({
  color: props.promptColor,
  fontSize: props.promptSize,
}));
</script>

<style scoped>
.button-container {
  position: relative;
  width: var(--width);
  height: var(--height);
  transition: 200ms;
}

.button-container:active {
  transform: scale(0.95);
}

#button {
  position: absolute;
  inset: 0;
  z-index: 0;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: var(--border-radius);
  transition: var(--transition-duration);
  cursor: pointer;
}

.subtitle {
  transform: translateY(160px);
  text-align: center;
  width: 100%;
  transition: transform 0.3s ease;
}

.title {
  opacity: 0;
  transition-duration: 300ms;
  transition-timing-function: ease-in-out;
  transition-delay: 100ms;
  position: absolute;
  font-weight: bold;
}

.tracker:hover ~ #button .title {
  opacity: 1;
}

#prompt {
  bottom: 8px;
  left: 12px;
  z-index: 20;
  font-weight: bold;
  transition: 300ms ease-in-out;
  position: absolute;
  max-width: 110px;
}

.canvas {
  perspective: v-bind('perspective');
  inset: 0;
  z-index: 200;
  position: absolute;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  grid-template-rows: repeat(5, 1fr);
}

.tracker {
  position: relative;
  z-index: 200;
}

.tracker:hover {
  cursor: pointer;
}

.tracker:hover ~ #button #prompt {
  opacity: 0;
}

.tracker:hover ~ #button {
  transition: 300ms;
  filter: brightness(v-bind('hoverBrightness'));
}

.button-container:hover #button::before {
  transition: 200ms;
  content: '';
  opacity: v-bind('glowOpacity');
}

#button::before {
  content: '';
  background: inherit;
  filter: blur(v-bind('glowBlur'));
  opacity: 0.3;
  width: 100%;
  height: 100%;
  position: absolute;
  z-index: -1;
  transition: 200ms;
  border-radius: inherit;
}

/* Generate tr-1 through tr-25 grid areas */
.tr-1 { grid-area: 1 / 1; }
.tr-2 { grid-area: 1 / 2; }
.tr-3 { grid-area: 1 / 3; }
.tr-4 { grid-area: 1 / 4; }
.tr-5 { grid-area: 1 / 5; }
.tr-6 { grid-area: 2 / 1; }
.tr-7 { grid-area: 2 / 2; }
.tr-8 { grid-area: 2 / 3; }
.tr-9 { grid-area: 2 / 4; }
.tr-10 { grid-area: 2 / 5; }
.tr-11 { grid-area: 3 / 1; }
.tr-12 { grid-area: 3 / 2; }
.tr-13 { grid-area: 3 / 3; }
.tr-14 { grid-area: 3 / 4; }
.tr-15 { grid-area: 3 / 5; }
.tr-16 { grid-area: 4 / 1; }
.tr-17 { grid-area: 4 / 2; }
.tr-18 { grid-area: 4 / 3; }
.tr-19 { grid-area: 4 / 4; }
.tr-20 { grid-area: 4 / 5; }
.tr-21 { grid-area: 5 / 1; }
.tr-22 { grid-area: 5 / 2; }
.tr-23 { grid-area: 5 / 3; }
.tr-24 { grid-area: 5 / 4; }
.tr-25 { grid-area: 5 / 5; }

/* 3D hover effects */
.tr-1:hover ~ #button { transform: rotateX(v-bind('rotationDegree')deg) rotateY(-10deg); }
.tr-2:hover ~ #button { transform: rotateX(v-bind('rotationDegree')deg) rotateY(-5deg); }
.tr-3:hover ~ #button { transform: rotateX(v-bind('rotationDegree')deg) rotateY(0deg); }
.tr-4:hover ~ #button { transform: rotateX(v-bind('rotationDegree')deg) rotateY(5deg); }
.tr-5:hover ~ #button { transform: rotateX(v-bind('rotationDegree')deg) rotateY(10deg); }
/* Add more rotation variations for other grid areas as needed */
</style>