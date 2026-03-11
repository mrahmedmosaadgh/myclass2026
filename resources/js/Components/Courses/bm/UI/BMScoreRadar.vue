<template>
  <div class="bm-score-radar full-width flex flex-center column">
    <div class="text-h5 text-weight-bold text-dark q-mb-md">Domain Mastery Profile</div>
    
    <div class="radar-container" style="width: 100%; max-width: 450px; position: relative;">
      <!-- Using viewBox for perfect scaling natively -->
      <svg viewBox="-20 -20 240 240" class="full-width" style="overflow: visible;">
        <!-- Background Grid (Web lines) -->
        <polygon points="100,0 195,69 158,180 41,180 5,69" fill="rgba(33, 150, 243, 0.05)" stroke="#e0e0e0" stroke-width="1"/>
        <polygon points="100,20 176,75 147,164 53,164 24,75" fill="none" stroke="#e0e0e0" stroke-width="1"/>
        <polygon points="100,40 157,81 135,148 65,148 43,81" fill="none" stroke="#e0e0e0" stroke-width="1"/>
        <polygon points="100,60 138,88 124,132 76,132 62,88" fill="none" stroke="#e0e0e0" stroke-width="1"/>
        <polygon points="100,80 119,94 112,116 88,116 81,94" fill="none" stroke="#e0e0e0" stroke-width="1"/>

        <!-- Spoke Lines from Center (100,100) -->
        <line x1="100" y1="100" x2="100" y2="0" stroke="#e0e0e0" stroke-width="1" />
        <line x1="100" y1="100" x2="195" y2="69" stroke="#e0e0e0" stroke-width="1" />
        <line x1="100" y1="100" x2="158" y2="180" stroke="#e0e0e0" stroke-width="1" />
        <line x1="100" y1="100" x2="41" y2="180" stroke="#e0e0e0" stroke-width="1" />
        <line x1="100" y1="100" x2="5" y2="69" stroke="#e0e0e0" stroke-width="1" />
        
        <!-- Score Polygon overlay -->
        <polygon :points="scorePoints" fill="rgba(76, 175, 80, 0.4)" stroke="#4CAF50" stroke-width="3" stroke-linejoin="round"/>
        
        <!-- 5 Nodes plotting the actual scores -->
        <circle :cx="nodeCoords[0].x" :cy="nodeCoords[0].y" r="4" fill="#388E3C" />
        <circle :cx="nodeCoords[1].x" :cy="nodeCoords[1].y" r="4" fill="#388E3C" />
        <circle :cx="nodeCoords[2].x" :cy="nodeCoords[2].y" r="4" fill="#388E3C" />
        <circle :cx="nodeCoords[3].x" :cy="nodeCoords[3].y" r="4" fill="#388E3C" />
        <circle :cx="nodeCoords[4].x" :cy="nodeCoords[4].y" r="4" fill="#388E3C" />
        
        <!-- Readable Labels -->
        <text x="100" y="-10" font-size="12" font-weight="bold" fill="#333" text-anchor="middle">Addition: {{ scores.addition || 0 }}%</text>
        <text x="200" y="65" font-size="12" font-weight="bold" fill="#333" text-anchor="start">Subtraction: {{ scores.subtraction || 0 }}%</text>
        <text x="165" y="195" font-size="12" font-weight="bold" fill="#333" text-anchor="start">Multiplication: {{ scores.multiplication || 0 }}%</text>
        <text x="35" y="195" font-size="12" font-weight="bold" fill="#333" text-anchor="end">Division: {{ scores.division || 0 }}%</text>
        <text x="0" y="65" font-size="12" font-weight="bold" fill="#333" text-anchor="end">Fractions: {{ scores.fractions || 0 }}%</text>
      </svg>
    </div>

    <div class="text-caption text-grey-8 q-mt-md text-center" style="max-width: 300px;">
      The green shape shows how close you are to 100% mastery in each math domain!
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  scores: {
    type: Object,
    required: true,
    default: () => ({ addition: 0, subtraction: 0, multiplication: 0, division: 0, fractions: 0 })
  }
});

// Helper coordinate mapper for a 100-radius pentagon centered at 100,100
// Angle 0 is straight up (Addition). Angles move clockwise.
const getCoord = (score, angleDeg) => {
  const s = Math.max(0, Math.min(100, score || 0)); // Valid bounds
  const r = (s / 100) * 100; // Radius based on 0-100%
  const rad = (angleDeg - 90) * (Math.PI / 180);
  return {
    x: 100 + r * Math.cos(rad),
    y: 100 + r * Math.sin(rad)
  };
};

const nodeCoords = computed(() => {
  return [
    getCoord(props.scores.addition, 0),        // Top
    getCoord(props.scores.subtraction, 72),    // Top Right
    getCoord(props.scores.multiplication, 144),// Bottom Right
    getCoord(props.scores.division, 216),      // Bottom Left
    getCoord(props.scores.fractions, 288),     // Top Left
  ];
});

const scorePoints = computed(() => {
  return nodeCoords.value.map(pt => `${pt.x},${pt.y}`).join(' ');
});
</script>
